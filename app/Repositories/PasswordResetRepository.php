<?php

namespace App\Repositories;

use App\Models\PasswordReset;
use App\Repositories\Interfaces\PasswordResetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class PasswordResetRepository extends BaseRepository implements PasswordResetRepositoryInterface
{
    public function __construct(PasswordReset $model)
    {
        parent::__construct($model);
    }

    /**
     * Create a new password reset token
     */
    public function create(array $data): PasswordReset
    {
        // Clean up old tokens for this email first
        $this->cleanupOldTokens($data['email']);
        
        return $this->model->create($data);
    }

    /**
     * Find password reset by token
     */
    public function findByToken(string $token): ?PasswordReset
    {
        return $this->model->where('token', $token)->first();
    }

    /**
     * Find password reset by email
     */
    public function findByEmail(string $email): ?PasswordReset
    {
        return $this->model->where('email', $email)->latest()->first();
    }

    /**
     * Delete password reset by token
     */
    public function deleteByToken(string $token): bool
    {
        return $this->model->where('token', $token)->delete();
    }

    /**
     * Delete password reset by email
     */
    public function deleteByEmail(string $email): bool
    {
        return $this->model->where('email', $email)->delete();
    }

    /**
     * Delete expired tokens
     */
    public function deleteExpiredTokens(): int
    {
        $expiryTime = Carbon::now()->subMinutes($this->getTokenExpiryMinutes());
        return $this->model->where('created_at', '<', $expiryTime)->delete();
    }

    /**
     * Check if token is valid and not expired
     */
    public function isValidToken(string $token): bool
    {
        $passwordReset = $this->findByToken($token);
        
        if (!$passwordReset) {
            return false;
        }

        $expiryTime = Carbon::parse($passwordReset->created_at)->addMinutes($this->getTokenExpiryMinutes());
        
        return Carbon::now()->lessThan($expiryTime);
    }

    /**
     * Get all password resets for an email
     */
    public function getByEmail(string $email): Collection
    {
        return $this->model->where('email', $email)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Clean up old tokens for email (keep only latest)
     */
    public function cleanupOldTokens(string $email): int
    {
        return $this->model->where('email', $email)->delete();
    }

    /**
     * Get token expiry time in minutes
     */
    public function getTokenExpiryMinutes(): int
    {
        return config('auth.passwords.users.expire', 60); // Default 60 minutes
    }

    /**
     * Check if token exists and belongs to email
     */
    public function tokenBelongsToEmail(string $token, string $email): bool
    {
        return $this->model->where('token', $token)->where('email', $email)->exists();
    }

    /**
     * Get password reset statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_resets' => $this->model->count(),
            'resets_today' => $this->model->whereDate('created_at', today())->count(),
            'resets_this_week' => $this->model->where('created_at', '>=', Carbon::now()->startOfWeek())->count(),
            'resets_this_month' => $this->model->whereMonth('created_at', now()->month)->count(),
            'expired_tokens' => $this->model->where('created_at', '<', Carbon::now()->subMinutes($this->getTokenExpiryMinutes()))->count(),
        ];
    }
}
