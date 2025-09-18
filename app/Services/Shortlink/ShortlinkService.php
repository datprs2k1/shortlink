<?php

namespace App\Services\Shortlink;

use App\Models\Shortlink;
use App\Repositories\Shortlink\IShortlinkRepository;
use App\Services\_Abstract\BaseService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShortlinkService extends BaseService
{
    // Constants
    private const SHORTLINK_NOT_FOUND = 'Shortlink not found.';
    private const SHORT_CODE_EXISTS = 'The short code already exists.';
    private const INVALID_URL = 'The provided URL is invalid.';
    private const SHORTLINK_EXPIRED = 'This shortlink has expired.';
    private const SHORTLINK_INACTIVE = 'This shortlink is inactive.';
    
    private const FIELD_DOMAIN_ID = 'domain_id';
    private const FIELD_SHORT_CODE = 'short_code';
    private const FIELD_ORIGINAL_URL = 'original_url';
    private const FIELD_IS_ACTIVE = 'is_active';
    private const FIELD_EXPIRES_AT = 'expires_at';
    
    private const DEFAULT_ACTIVE_STATUS = true;
    private const DEFAULT_CODE_LENGTH = 6;
    private const MAX_CODE_GENERATION_ATTEMPTS = 10;

    public function __construct(IShortlinkRepository $shortlinkRepository)
    {
        $this->mainRepository = $shortlinkRepository;
    }

    // ========================================
    // CRUD Operations
    // ========================================

    /**
     * Create a new shortlink
     */
    public function createShortlink(array $data): Shortlink
    {
        $this->validateUrl($data[self::FIELD_ORIGINAL_URL]);
        
        // Generate short code if not provided
        if (!isset($data[self::FIELD_SHORT_CODE]) || empty($data[self::FIELD_SHORT_CODE])) {
            $data[self::FIELD_SHORT_CODE] = $this->generateUniqueShortCode($data[self::FIELD_DOMAIN_ID]);
        } else {
            $this->validateShortCodeUniqueness($data[self::FIELD_SHORT_CODE], $data[self::FIELD_DOMAIN_ID]);
        }

        $data[self::FIELD_IS_ACTIVE] = $data[self::FIELD_IS_ACTIVE] ?? self::DEFAULT_ACTIVE_STATUS;

        return $this->mainRepository->create($data);
    }

    /**
     * Update shortlink
     */
    public function updateShortlink(int $id, array $data): bool
    {
        $shortlink = $this->ensureShortlinkExists($id);

        if (isset($data[self::FIELD_ORIGINAL_URL])) {
            $this->validateUrl($data[self::FIELD_ORIGINAL_URL]);
        }

        $this->validateShortCodeForUpdate($data, $shortlink);

        return $this->mainRepository->update($data, $id);
    }

    /**
     * Delete shortlink with its clicks
     */
    public function deleteShortlinkWithClicks(int $id): bool
    {
        $shortlink = $this->ensureShortlinkExists($id);

        return DB::transaction(fn() => $this->performCascadeDelete($shortlink));
    }

    // ========================================
    // Query Operations
    // ========================================

    /**
     * Get all active shortlinks
     */
    public function getAllActive(): Collection
    {
        return $this->mainRepository->findWhere([self::FIELD_IS_ACTIVE => true]);
    }

    /**
     * Get all inactive shortlinks
     */
    public function getAllInactive(): Collection
    {
        return $this->mainRepository->findWhere([self::FIELD_IS_ACTIVE => false]);
    }

    /**
     * Find shortlink by short code and domain
     */
    public function findByShortCode(string $shortCode, int $domainId): ?Shortlink
    {
        return $this->mainRepository->findWhere([
            self::FIELD_SHORT_CODE => $shortCode,
            self::FIELD_DOMAIN_ID => $domainId
        ])->first();
    }

    /**
     * Get shortlinks by domain
     */
    public function getByDomain(int $domainId): Collection
    {
        return $this->mainRepository->findWhere([self::FIELD_DOMAIN_ID => $domainId]);
    }

    /**
     * Get expired shortlinks
     */
    public function getExpiredShortlinks(): Collection
    {
        return $this->mainRepository->getQuery()
            ->whereNotNull(self::FIELD_EXPIRES_AT)
            ->where(self::FIELD_EXPIRES_AT, '<', now())
            ->get();
    }

    /**
     * Get shortlinks expiring soon
     */
    public function getExpiringShortlinks(int $days = 7): Collection
    {
        return $this->mainRepository->getQuery()
            ->whereNotNull(self::FIELD_EXPIRES_AT)
            ->whereBetween(self::FIELD_EXPIRES_AT, [now(), now()->addDays($days)])
            ->get();
    }

    /**
     * Get shortlinks with click counts
     */
    public function getShortlinksWithClickCounts(): Collection
    {
        return $this->mainRepository->getQuery()
            ->withCount('clicks')
            ->get();
    }

    // ========================================
    // Status Management
    // ========================================

    /**
     * Toggle shortlink status
     */
    public function toggleStatus(int $id): bool
    {
        $shortlink = $this->ensureShortlinkExists($id);
        
        return $this->mainRepository->update([self::FIELD_IS_ACTIVE => !$shortlink->is_active], $id);
    }

    /**
     * Activate shortlink
     */
    public function activate(int $id): bool
    {
        return $this->updateStatus($id, true);
    }

    /**
     * Deactivate shortlink
     */
    public function deactivate(int $id): bool
    {
        return $this->updateStatus($id, false);
    }

    /**
     * Set expiration date
     */
    public function setExpiration(int $id, ?Carbon $expiresAt): bool
    {
        $this->ensureShortlinkExists($id);
        
        return $this->mainRepository->update([self::FIELD_EXPIRES_AT => $expiresAt], $id);
    }

    // ========================================
    // URL Resolution & Tracking
    // ========================================

    /**
     * Resolve shortlink and prepare for redirect
     */
    public function resolveShortlink(string $shortCode, int $domainId): array
    {
        $shortlink = $this->findByShortCode($shortCode, $domainId);

        if (!$shortlink) {
            throw new Exception(self::SHORTLINK_NOT_FOUND);
        }

        if (!$shortlink->is_active) {
            throw new Exception(self::SHORTLINK_INACTIVE);
        }

        if ($shortlink->is_expired) {
            throw new Exception(self::SHORTLINK_EXPIRED);
        }

        return [
            'shortlink' => $shortlink,
            'original_url' => $shortlink->original_url,
            'can_redirect' => true
        ];
    }

    // ========================================
    // Statistics & Analytics
    // ========================================

    /**
     * Get shortlink statistics
     */
    public function getShortlinkStats(?int $domainId = null): array
    {
        $query = $this->mainRepository->getQuery();

        if ($domainId) {
            $query->where(self::FIELD_DOMAIN_ID, $domainId);
        }

        return [
            'total_shortlinks' => $query->count(),
            'active_shortlinks' => $query->where(self::FIELD_IS_ACTIVE, true)->count(),
            'inactive_shortlinks' => $query->where(self::FIELD_IS_ACTIVE, false)->count(),
            'expired_shortlinks' => $this->getExpiredCount($domainId),
            'expiring_soon' => $this->getExpiringSoonCount($domainId),
            'total_clicks' => $this->getTotalClicksCount($domainId),
        ];
    }

    /**
     * Get top performing shortlinks
     */
    public function getTopPerforming(int $limit = 10, ?int $domainId = null): Collection
    {
        $query = $this->mainRepository->getQuery()
            ->withCount('clicks');

        if ($domainId) {
            $query->where(self::FIELD_DOMAIN_ID, $domainId);
        }

        return $query->orderByDesc('clicks_count')
            ->limit($limit)
            ->get();
    }

    // ========================================
    // Maintenance Operations
    // ========================================

    /**
     * Clean up expired shortlinks
     */
    public function cleanupExpired(): int
    {
        $expiredShortlinks = $this->getExpiredShortlinks();
        $deletedCount = 0;

        foreach ($expiredShortlinks as $shortlink) {
            if ($this->deleteShortlinkWithClicks($shortlink->id)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Deactivate expired shortlinks instead of deleting
     */
    public function deactivateExpired(): int
    {
        return $this->mainRepository->getQuery()
            ->whereNotNull(self::FIELD_EXPIRES_AT)
            ->where(self::FIELD_EXPIRES_AT, '<', now())
            ->where(self::FIELD_IS_ACTIVE, true)
            ->update([self::FIELD_IS_ACTIVE => false]);
    }

    // ========================================
    // Validation Methods
    // ========================================

    /**
     * Check if short code exists for domain
     */
    public function isShortCodeExists(string $shortCode, int $domainId): bool
    {
        return $this->findByShortCode($shortCode, $domainId) !== null;
    }

    // ========================================
    // Private Helper Methods
    // ========================================

    /**
     * Ensure shortlink exists
     */
    private function ensureShortlinkExists(int $id): Shortlink
    {
        $shortlink = $this->mainRepository->findById($id);
        
        if (!$shortlink) {
            throw new Exception(self::SHORTLINK_NOT_FOUND);
        }

        return $shortlink;
    }

    /**
     * Generate unique short code
     */
    private function generateUniqueShortCode(int $domainId, int $length = self::DEFAULT_CODE_LENGTH): string
    {
        $attempts = 0;
        
        do {
            $shortCode = $this->generateRandomCode($length);
            $attempts++;
            
            if ($attempts > self::MAX_CODE_GENERATION_ATTEMPTS) {
                $length++; // Increase length if too many collisions
                $attempts = 0;
            }
        } while ($this->isShortCodeExists($shortCode, $domainId));

        return $shortCode;
    }

    /**
     * Generate random code
     */
    private function generateRandomCode(int $length): string
    {
        return Str::random($length);
    }

    /**
     * Validate URL format
     */
    private function validateUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages([
                self::FIELD_ORIGINAL_URL => [self::INVALID_URL]
            ]);
        }
    }

    /**
     * Validate short code uniqueness
     */
    private function validateShortCodeUniqueness(string $shortCode, int $domainId): void
    {
        if ($this->isShortCodeExists($shortCode, $domainId)) {
            throw ValidationException::withMessages([
                self::FIELD_SHORT_CODE => [self::SHORT_CODE_EXISTS]
            ]);
        }
    }

    /**
     * Validate short code for update
     */
    private function validateShortCodeForUpdate(array $data, Shortlink $shortlink): void
    {
        if (!isset($data[self::FIELD_SHORT_CODE]) || $data[self::FIELD_SHORT_CODE] === $shortlink->short_code) {
            return;
        }

        $this->validateShortCodeUniqueness($data[self::FIELD_SHORT_CODE], $shortlink->domain_id);
    }

    /**
     * Update shortlink status
     */
    private function updateStatus(int $id, bool $status): bool
    {
        $this->ensureShortlinkExists($id);
        
        return $this->mainRepository->update([self::FIELD_IS_ACTIVE => $status], $id);
    }

    /**
     * Get expired shortlinks count
     */
    public function getExpiredCount(?int $domainId): int
    {
        $query = $this->mainRepository->getQuery()
            ->whereNotNull(self::FIELD_EXPIRES_AT)
            ->where(self::FIELD_EXPIRES_AT, '<', now());

        if ($domainId) {
            $query->where(self::FIELD_DOMAIN_ID, $domainId);
        }

        return $query->count();
    }

    /**
     * Get expiring soon count
     */
    private function getExpiringSoonCount(?int $domainId): int
    {
        $query = $this->mainRepository->getQuery()
            ->whereNotNull(self::FIELD_EXPIRES_AT)
            ->whereBetween(self::FIELD_EXPIRES_AT, [now(), now()->addDays(7)]);

        if ($domainId) {
            $query->where(self::FIELD_DOMAIN_ID, $domainId);
        }

        return $query->count();
    }

    /**
     * Get total clicks count
     */
    private function getTotalClicksCount(?int $domainId): int
    {
        $query = $this->mainRepository->getQuery();

        if ($domainId) {
            $query->where(self::FIELD_DOMAIN_ID, $domainId);
        }

        return $query->withCount('clicks')->get()->sum('clicks_count');
    }

    /**
     * Perform cascade delete
     */
    private function performCascadeDelete(Shortlink $shortlink): bool
    {
        $shortlink->clicks()->delete();
        
        return $shortlink->delete();
    }

    /**
     * Get total count of shortlinks
     */
    public function getTotalCount(): int
    {
        return $this->mainRepository->getQuery()->count();
    }
    
    /**
     * Get count of active shortlinks
     */
    public function getActiveCount(): int
    {
        return $this->mainRepository->getQuery()->where('is_active', true)->count();
    }
    
    /**
     * Get total clicks across all shortlinks
     */
    public function getTotalClicks(): int
    {
        return $this->getTotalClicksCount(null);
    }

    /**
     * Get paginated shortlinks with filters and click counts
     */
    public function getPaginatedShortlinks(array $filters = [], int $perPage = 15)
    {
        $query = $this->mainRepository->getQuery()->withCount('clicks');
        
        // Apply filters
        if (!empty($filters['domain_id'])) {
            $query->where('domain_id', $filters['domain_id']);
        }
        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active'] === 'active');
        }
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('short_code', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('original_url', 'LIKE', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}