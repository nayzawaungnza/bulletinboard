<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all users with optional filters
     */
    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get paginated users with optional filters
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get paginated users with detailed filters
     */
    public function getPaginatedWithFilters(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Filter by name
        if (isset($filters['name']) && !empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        // Filter by email
        if (isset($filters['email']) && !empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        // Filter by role
        if (isset($filters['role']) && $filters['role'] !== '') {
            $query->where('role', $filters['role']);
        }

        // Filter by date range
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $data['created_at'] = now();
        $data['create_user_id'] =  auth()->id() ? auth()->id() : null;

        return $this->model->create($data);
    }

    /**
     * Update user by ID
     */
    public function update(int $id, array $data): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['updated_at'] = now();
        $data['updated_user_id'] = auth()->id();

        return $user->update($data);
    }

    /**
     * Delete user by ID (soft delete)
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        $user->update([
            'deleted_user_id' => auth()->id(),
            'deleted_at' => now()
        ]);

        return $user->delete();
    }

    /**
     * Force delete user by ID
     */
    public function forceDelete(int $id): bool
    {
        $user = $this->model->withTrashed()->find($id);
        if (!$user) {
            return false;
        }

        return $user->forceDelete();
    }

    /**
     * Restore soft deleted user
     */
    public function restore(int $id): bool
    {
        $user = $this->model->withTrashed()->find($id);
        if (!$user) {
            return false;
        }

        return $user->restore();
    }

    /**
     * Lock user account
     */
    public function lockAccount(int $id): bool
    {
        return $this->update($id, [
            'lock_count' => $this->findById($id)->lock_count + 1,
            'lock_flag' => 1,
            'last_lock_at' => now()
        ]);
    }

    /**
     * Unlock user account
     */
    public function unlockAccount(int $id): bool
    {
        return $this->update($id, [
            'lock_flag' => 0,
            'last_lock_at' => null,
            'failed_login_attempts' => 0
        ]);
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedAttempts(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        DB::beginTransaction();
        
        try {
            $attempts = $user->failed_login_attempts + 1;
            $updateData = [
                'failed_login_attempts' => $attempts,
                'last_failed_login' => now()
            ];

            // Lock account after 5 failed attempts
            if ($attempts >= 5) {
                $updateData['lock_flag'] = 1;
                $updateData['last_lock_at'] = now();
            }

            $result = $user->update($updateData);
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedAttempts(int $id): bool
    {
        return $this->update($id, [
            'failed_login_attempts' => 0,
            'last_failed_login' => null,
            'last_login' => now()
        ]);
    }

    /**
     * Get users by role
     */
    public function getByRole(int $role): Collection
    {
        return $this->model->where('role', $role)->get();
    }

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection
    {
        return $this->model->where('status', 1)->where('lock_flag', 0)->get();
    }

    /**
     * Get locked users
     */
    public function getLockedUsers(): Collection
    {
        return $this->model->where('lock_flag', 1)->get();
    }

    /**
     * Search users by name or email
     */
    public function search(string $query): Collection
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
              ->orWhere('email', 'like', '%' . $query . '%');
        })->get();
    }

    /**
     * Get user statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_users' => $this->model->count(),
            'active_users' => $this->model->where('status', 1)->count(),
            'inactive_users' => $this->model->where('status', 0)->count(),
            'locked_users' => $this->model->where('lock_flag', 1)->count(),
            'admin_users' => $this->model->where('role', 0)->count(),
            'regular_users' => $this->model->where('role', 1)->count(),
            'users_today' => $this->model->whereDate('created_at', today())->count(),
            'users_this_month' => $this->model->whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Update user profile
     */
    public function updateProfile(int $id, array $data): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        $data['updated_at'] = now();
        $data['updated_by'] = $id; // User updating their own profile

        return $user->update($data);
    }

    /**
     * Change user password
     */
    public function changePassword(int $id, string $password): bool
    {
        return $this->update($id, [
            'password' => Hash::make($password),
            'password_changed_at' => now()
        ]);
    }

    /**
     * Get users with their posts count
     */
    public function getUsersWithPostsCount(): Collection
    {
        return $this->model->withCount('posts')->get();
    }
}