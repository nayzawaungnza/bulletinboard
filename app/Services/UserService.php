<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class UserService
{
    protected UserRepositoryInterface $userRepository;
    protected PostRepositoryInterface $postRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PostRepositoryInterface $postRepository
    ) {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * Create a new user with profile image handling
     */
    public function createUser(array $userData, ?UploadedFile $profileImage = null): array
    {
        try {
            if ($profileImage) {
                $userData['profile_path'] = $this->uploadProfileImage($profileImage);
            }

            $user = $this->userRepository->create($userData);

            return [
                'success' => true,
                'message' => 'User created successfully!',
                'user' => $user
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create user. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update user with profile image handling
     */
    public function updateUser(int $userId, array $userData, ?UploadedFile $profileImage = null): array
    {
        try {
            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }

            if ($profileImage) {
                // Delete old profile image
                if ($user->profile_path) {
                    Storage::disk('public')->delete($user->profile_path);
                }
                $userData['profile_path'] = $this->uploadProfileImage($profileImage);
            }

            $this->userRepository->update($userId, $userData);

            return [
                'success' => true,
                'message' => 'User updated successfully!'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update user. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete user and handle related data
     */
    public function deleteUser(int $userId): array
    {
        DB::beginTransaction();
        
        try {
            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }

            if ($userId === auth()->id()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'You cannot delete yourself!'
                ];
            }

            // Delete profile image if exists
            if ($user->profile_path) {
                Storage::disk('public')->delete($user->profile_path);
            }

            // Soft delete user's posts
            $userPosts = $this->postRepository->getByUser($userId);
            foreach ($userPosts as $post) {
                $this->postRepository->delete($post->id);
            }

            // Delete user
            $this->userRepository->delete($userId);

            DB::commit();
            return [
                'success' => true,
                'message' => 'User deleted successfully!'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to delete user. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Toggle user account lock status
     */
    public function toggleUserLock(int $userId): array
    {
        try {
            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }

            if ($user->is_locked) {
                $this->userRepository->unlockAccount($userId);
                $status = 'unlocked';
            } else {
                $this->userRepository->lockAccount($userId);
                $status = 'locked';
            }

            return [
                'success' => true,
                'message' => "User {$status} successfully!",
                'status' => $status
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update user status. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(int $userId, array $profileData, ?UploadedFile $profileImage = null): array
    {
        try {
            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }

            if ($profileImage) {
                // Delete old profile image
                if ($user->profile_path) {
                    Storage::disk('public')->delete($user->profile_path);
                }
                $profileData['profile_path'] = $this->uploadProfileImage($profileImage);
            }

            $this->userRepository->updateProfile($userId, $profileData);

            return [
                'success' => true,
                'message' => 'Profile updated successfully!'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update profile. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user dashboard data
     */
    public function getUserDashboardData(int $userId): array
    {
        $user = $this->userRepository->findById($userId);
        $userPosts = $this->postRepository->getByUser($userId);
        $postsCount = $this->postRepository->getPostsCountByUser($userId);

        return [
            'user' => $user,
            'posts' => $userPosts,
            'posts_count' => $postsCount,
            'published_posts' => $userPosts->where('status', 1)->count(),
            'draft_posts' => $userPosts->where('status', 0)->count(),
        ];
    }

    /**
     * Get users with advanced filtering
     */
    public function getUsersWithFilters(array $filters = [], int $perPage = 15): array
    {
        $users = $this->userRepository->getPaginated($perPage, $filters);
        $statistics = $this->userRepository->getStatistics();

        return [
            'users' => $users,
            'statistics' => $statistics,
            'filters' => $filters
        ];
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(int $userId): array
    {
        $user = $this->userRepository->findById($userId);
        $posts = $this->postRepository->getByUser($userId);

        return [
            'user' => $user,
            'total_posts' => $posts->count(),
            'published_posts' => $posts->where('status', 1)->count(),
            'draft_posts' => $posts->where('status', 0)->count(),
            'total_views' => $posts->sum('views'),
            'recent_posts' => $posts->take(5),
            'account_age_days' => $user->created_at->diffInDays(now()),
            'last_login' => $user->last_login,
        ];
    }

    /**
     * Upload profile image
     */
    private function uploadProfileImage(UploadedFile $image): string
    {
        return $image->store('profiles', 'public');
    }
}
