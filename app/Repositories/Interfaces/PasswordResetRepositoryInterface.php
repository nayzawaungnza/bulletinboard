<?php

namespace App\Repositories\Interfaces;

use App\Models\PasswordReset;
use Illuminate\Database\Eloquent\Collection;

interface PasswordResetRepositoryInterface
{
    /**
     * Create a new password reset token
     */
    public function create(array $data): PasswordReset;

    /**
     * Find password reset by token
     */
    public function findByToken(string $token): ?PasswordReset;

    /**
     * Find password reset by email
     */
    public function findByEmail(string $email): ?PasswordReset;

    /**
     * Delete password reset by token
     */
    public function deleteByToken(string $token): bool;

    /**
     * Delete password reset by email
     */
    public function deleteByEmail(string $email): bool;

    /**
     * Delete expired tokens
     */
    public function deleteExpiredTokens(): int;

    /**
     * Check if token is valid and not expired
     */
    public function isValidToken(string $token): bool;

    /**
     * Get all password resets for an email
     */
    public function getByEmail(string $email): Collection;

    /**
     * Clean up old tokens for email (keep only latest)
     */
    public function cleanupOldTokens(string $email): int;

    /**
     * Get token expiry time in minutes
     */
    public function getTokenExpiryMinutes(): int;

    /**
     * Check if token exists and belongs to email
     */
    public function tokenBelongsToEmail(string $token, string $email): bool;

    /**
     * Get password reset statistics
     */
    public function getStatistics(): array;
}
