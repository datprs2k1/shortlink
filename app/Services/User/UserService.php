<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\IUserRepository;
use App\Services\_Abstract\BaseService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService extends BaseService
{
    // Constants
    private const USER_NOT_FOUND = 'User not found.';
    private const EMAIL_EXISTS = 'The email address is already taken.';
    private const INVALID_EMAIL = 'The provided email is invalid.';
    private const WEAK_PASSWORD = 'The password must be at least 8 characters long.';
    
    private const FIELD_NAME = 'name';
    private const FIELD_EMAIL = 'email';
    private const FIELD_PASSWORD = 'password';
    private const FIELD_EMAIL_VERIFIED_AT = 'email_verified_at';
    
    private const MIN_PASSWORD_LENGTH = 8;

    public function __construct(IUserRepository $userRepository)
    {
        $this->mainRepository = $userRepository;
    }

    // ========================================
    // CRUD Operations
    // ========================================

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        $this->validateUserData($data);
        $this->validateEmailUniqueness($data[self::FIELD_EMAIL]);
        
        $data[self::FIELD_PASSWORD] = $this->hashPassword($data[self::FIELD_PASSWORD]);

        return $this->mainRepository->create($data);
    }

    /**
     * Update user information
     */
    public function updateUser(int $id, array $data): bool
    {
        $user = $this->ensureUserExists($id);
        
        $this->validateUserDataForUpdate($data);
        $this->validateEmailForUpdate($data, $user);

        // Hash password if provided
        if (isset($data[self::FIELD_PASSWORD])) {
            $data[self::FIELD_PASSWORD] = $this->hashPassword($data[self::FIELD_PASSWORD]);
        }

        return $this->mainRepository->update($data, $id);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $this->ensureUserExists($id);
        $this->validatePassword($newPassword);

        return $this->mainRepository->update([
            self::FIELD_PASSWORD => $this->hashPassword($newPassword)
        ], $id);
    }

    /**
     * Update user profile (name and email only)
     */
    public function updateProfile(int $id, array $profileData): bool
    {
        $user = $this->ensureUserExists($id);
        
        // Only allow name and email updates
        $allowedFields = [self::FIELD_NAME, self::FIELD_EMAIL];
        $data = array_intersect_key($profileData, array_flip($allowedFields));
        
        if (isset($data[self::FIELD_EMAIL])) {
            $this->validateEmail($data[self::FIELD_EMAIL]);
            $this->validateEmailForUpdate($data, $user);
            
            // Reset email verification when email changes
            $data[self::FIELD_EMAIL_VERIFIED_AT] = null;
        }

        return $this->mainRepository->update($data, $id);
    }

    // ========================================
    // Query Operations
    // ========================================

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->mainRepository->findWhere([self::FIELD_EMAIL => $email])->first();
    }

    /**
     * Get verified users
     */
    public function getVerifiedUsers(): Collection
    {
        return $this->mainRepository->getQuery()
            ->whereNotNull(self::FIELD_EMAIL_VERIFIED_AT)
            ->get();
    }

    /**
     * Get unverified users
     */
    public function getUnverifiedUsers(): Collection
    {
        return $this->mainRepository->getQuery()
            ->whereNull(self::FIELD_EMAIL_VERIFIED_AT)
            ->get();
    }

    /**
     * Search users by name or email
     */
    public function searchUsers(string $query): Collection
    {
        return $this->mainRepository->getQuery()
            ->where(self::FIELD_NAME, 'LIKE', "%{$query}%")
            ->orWhere(self::FIELD_EMAIL, 'LIKE', "%{$query}%")
            ->get();
    }

    /**
     * Get recent users
     */
    public function getRecentUsers(int $limit = 10): Collection
    {
        return $this->mainRepository->getQuery()
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    // ========================================
    // Authentication Support
    // ========================================

    /**
     * Verify user credentials
     */
    public function verifyCredentials(string $email, string $password): ?User
    {
        $user = $this->findByEmail($email);
        
        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(int $id): bool
    {
        $this->ensureUserExists($id);
        
        return $this->mainRepository->update([
            self::FIELD_EMAIL_VERIFIED_AT => now()
        ], $id);
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified(int $id): bool
    {
        $user = $this->ensureUserExists($id);
        
        return !is_null($user->email_verified_at);
    }

    // ========================================
    // Statistics & Analytics
    // ========================================

    /**
     * Get user statistics
     */
    public function getUserStats(): array
    {
        $query = $this->mainRepository->getQuery();

        return [
            'total_users' => $query->count(),
            'verified_users' => $query->whereNotNull(self::FIELD_EMAIL_VERIFIED_AT)->count(),
            'unverified_users' => $query->whereNull(self::FIELD_EMAIL_VERIFIED_AT)->count(),
            'recent_registrations' => $this->getRecentRegistrationsCount(),
            'this_month_registrations' => $this->getThisMonthRegistrationsCount(),
        ];
    }

    /**
     * Get user registration trends
     */
    public function getRegistrationTrends(int $days = 30): Collection
    {
        $startDate = now()->subDays($days);
        
        return $this->mainRepository->getQuery()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as registrations')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    // ========================================
    // Validation Methods
    // ========================================

    /**
     * Check if email exists
     */
    public function isEmailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    // ========================================
    // Private Helper Methods
    // ========================================

    /**
     * Ensure user exists
     */
    private function ensureUserExists(int $id): User
    {
        $user = $this->mainRepository->findById($id);
        
        if (!$user) {
            throw new Exception(self::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * Hash password
     */
    private function hashPassword(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * Validate user data for creation
     */
    private function validateUserData(array $data): void
    {
        if (isset($data[self::FIELD_EMAIL])) {
            $this->validateEmail($data[self::FIELD_EMAIL]);
        }

        if (isset($data[self::FIELD_PASSWORD])) {
            $this->validatePassword($data[self::FIELD_PASSWORD]);
        }
    }

    /**
     * Validate user data for update
     */
    private function validateUserDataForUpdate(array $data): void
    {
        if (isset($data[self::FIELD_EMAIL])) {
            $this->validateEmail($data[self::FIELD_EMAIL]);
        }

        if (isset($data[self::FIELD_PASSWORD])) {
            $this->validatePassword($data[self::FIELD_PASSWORD]);
        }
    }

    /**
     * Validate email format
     */
    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                self::FIELD_EMAIL => [self::INVALID_EMAIL]
            ]);
        }
    }

    /**
     * Validate password strength
     */
    private function validatePassword(string $password): void
    {
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw ValidationException::withMessages([
                self::FIELD_PASSWORD => [self::WEAK_PASSWORD]
            ]);
        }
    }

    /**
     * Validate email uniqueness
     */
    private function validateEmailUniqueness(string $email): void
    {
        if ($this->isEmailExists($email)) {
            throw ValidationException::withMessages([
                self::FIELD_EMAIL => [self::EMAIL_EXISTS]
            ]);
        }
    }

    /**
     * Validate email for update operations
     */
    private function validateEmailForUpdate(array $data, User $user): void
    {
        if (!isset($data[self::FIELD_EMAIL]) || $data[self::FIELD_EMAIL] === $user->email) {
            return;
        }

        $this->validateEmailUniqueness($data[self::FIELD_EMAIL]);
    }

    /**
     * Get recent registrations count (last 7 days)
     */
    private function getRecentRegistrationsCount(): int
    {
        return $this->mainRepository->getQuery()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
    }

    /**
     * Get this month registrations count
     */
    private function getThisMonthRegistrationsCount(): int
    {
        return $this->mainRepository->getQuery()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }
}