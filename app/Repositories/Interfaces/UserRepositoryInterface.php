<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Get all users with optional filters
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get paginated users with optional filters
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user
     */
    public function create(array $data): User;

    /**
     * Update user by ID
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete user by ID (soft delete)
     */
    public function delete(int $id): bool;

    /**
     * Force delete user by ID
     */
    public function forceDelete(int $id): bool;

    /**
     * Restore soft deleted user
     */
    public function restore(int $id): bool;

    /**
     * Lock user account
     */
    public function lockAccount(int $id): bool;

    /**
     * Unlock user account
     */
    public function unlockAccount(int $id): bool;

    /**
     * Increment failed login attempts
     */
    public function incrementFailedAttempts(int $id): bool;

    /**
     * Reset failed login attempts
     */
    public function resetFailedAttempts(int $id): bool;

    /**
     * Get users by role
     */
    public function getByRole(int $role): Collection;

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection;

    /**
     * Get locked users
     */
    public function getLockedUsers(): Collection;

    /**
     * Search users by name or email
     */
    public function search(string $query): Collection;

    /**
     * Get user statistics
     */
    public function getStatistics(): array;

    /**
     * Update user profile
     */
    public function updateProfile(int $id, array $data): bool;

    /**
     * Change user password
     */
    public function changePassword(int $id, string $password): bool;

    /**
     * Get users with their posts count
     */
    public function getUsersWithPostsCount(): Collection;
}
