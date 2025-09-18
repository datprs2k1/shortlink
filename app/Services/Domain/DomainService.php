<?php

namespace App\Services\Domain;

use App\Models\Domain;
use App\Repositories\Domain\IDomainRepository;
use App\Services\_Abstract\BaseService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DomainService extends BaseService
{
    // Constants
    private const DOMAIN_NOT_FOUND = 'Domain not found.';
    private const DOMAIN_NAME_EXISTS = 'The domain name already exists.';
    private const FIELD_NAME = 'name';
    private const FIELD_IS_ACTIVE = 'is_active';
    private const DEFAULT_ACTIVE_STATUS = true;

    public function __construct(IDomainRepository $domainRepository)
    {
        $this->mainRepository = $domainRepository;
    }

    // ========================================
    // CRUD Operations
    // ========================================

    /**
     * Create a new domain with validation
     */
    public function createDomain(array $data): Domain
    {
        $this->validateDomainNameUniqueness($data[self::FIELD_NAME]);
        
        $data[self::FIELD_IS_ACTIVE] = $data[self::FIELD_IS_ACTIVE] ?? self::DEFAULT_ACTIVE_STATUS;

        return $this->mainRepository->create($data);
    }

    /**
     * Update domain with validation
     */
    public function updateDomain(int $id, array $data): bool
    {
        $domain = $this->ensureDomainExists($id);
        
        $this->validateDomainNameForUpdate($data, $domain);

        return $this->mainRepository->update($data, $id);
    }

    /**
     * Delete domain with its associated shortlinks
     */
    public function deleteDomainWithShortlinks(int $id): bool
    {
        $domain = $this->ensureDomainExists($id);

        return DB::transaction(fn() => $this->performCascadeDelete($domain));
    }

    // ========================================
    // Query Operations
    // ========================================

    /**
     * Get all active domains
     */
    public function getAllActive(): Collection
    {
        return $this->mainRepository->findWhere([self::FIELD_IS_ACTIVE => true]);
    }

    /**
     * Get all inactive domains
     */
    public function getAllInactive(): Collection
    {
        return $this->mainRepository->findWhere([self::FIELD_IS_ACTIVE => false]);
    }

    /**
     * Find domain by name
     */
    public function findByName(string $name): ?Domain
    {
        return $this->mainRepository->findWhere([self::FIELD_NAME => $name])->first();
    }

    /**
     * Get domains with shortlink counts
     */
    public function getDomainsWithShortlinkCounts(): Collection
    {
        return $this->mainRepository->getQuery()
            ->withCount(['shortlinks', 'activeShortlinks'])
            ->get();
    }

    // ========================================
    // Status Management
    // ========================================

    /**
     * Toggle domain active status
     */
    public function toggleStatus(int $id): bool
    {
        $domain = $this->ensureDomainExists($id);
        
        return $this->mainRepository->update([self::FIELD_IS_ACTIVE => !$domain->is_active], $id);
    }

    /**
     * Activate a domain
     */
    public function activate(int $id): bool
    {
        return $this->updateStatus($id, true);
    }

    /**
     * Deactivate a domain
     */
    public function deactivate(int $id): bool
    {
        return $this->updateStatus($id, false);
    }

    // ========================================
    // Statistics & Analytics
    // ========================================

    /**
     * Get domain statistics
     */
    public function getDomainStats(?int $domainId = null): array
    {
        if ($domainId) {
            return $this->getSpecificDomainStats($domainId);
        }

        return $this->getAllDomainsStats();
    }

    // ========================================
    // Validation Methods
    // ========================================

    /**
     * Check if domain name exists
     */
    public function isDomainNameExists(string $name): bool
    {
        return $this->findByName($name) !== null;
    }

    // ========================================
    // Private Helper Methods
    // ========================================

    /**
     * Ensure domain exists, throw exception if not
     */
    private function ensureDomainExists(int $id): Domain
    {
        $domain = $this->mainRepository->findById($id);
        
        if (!$domain) {
            throw new Exception(self::DOMAIN_NOT_FOUND);
        }

        return $domain;
    }

    /**
     * Validate domain name uniqueness
     */
    private function validateDomainNameUniqueness(string $name): void
    {
        if ($this->isDomainNameExists($name)) {
            throw ValidationException::withMessages([
                self::FIELD_NAME => [self::DOMAIN_NAME_EXISTS]
            ]);
        }
    }

    /**
     * Validate domain name for update operations
     */
    private function validateDomainNameForUpdate(array $data, Domain $domain): void
    {
        if (!isset($data[self::FIELD_NAME]) || $data[self::FIELD_NAME] === $domain->name) {
            return;
        }

        $this->validateDomainNameUniqueness($data[self::FIELD_NAME]);
    }

    /**
     * Update domain status
     */
    private function updateStatus(int $id, bool $status): bool
    {
        $this->ensureDomainExists($id);
        
        return $this->mainRepository->update([self::FIELD_IS_ACTIVE => $status], $id);
    }

    /**
     * Get statistics for all domains
     */
    private function getAllDomainsStats(): array
    {
        $query = $this->mainRepository->getQuery();
        
        return [
            'total_domains' => $query->count(),
            'active_domains' => $query->where(self::FIELD_IS_ACTIVE, true)->count(),
            'inactive_domains' => $query->where(self::FIELD_IS_ACTIVE, false)->count(),
        ];
    }

    /**
     * Get statistics for a specific domain
     */
    private function getSpecificDomainStats(int $domainId): array
    {
        $domain = $this->ensureDomainExists($domainId);
        
        $baseStats = $this->getAllDomainsStats();
        
        return array_merge($baseStats, [
            'shortlinks_count' => $domain->shortlinks()->count(),
            'active_shortlinks_count' => $domain->activeShortlinks()->count(),
        ]);
    }

    /**
     * Perform cascade delete of domain and its shortlinks
     */
    private function performCascadeDelete(Domain $domain): bool
    {
        $domain->shortlinks()->delete();
        
        return $domain->delete();
    }
}