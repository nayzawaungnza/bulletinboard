<?php

namespace App\Services;

use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostService
{
    protected PostRepositoryInterface $postRepository;
    protected UserRepositoryInterface $userRepository;

    public function __construct(
        PostRepositoryInterface $postRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Create a new post
     */
    public function createPost(array $postData): array
    {
        try {
            // Generate excerpt from content
            if (isset($postData['content']) && !isset($postData['excerpt'])) {
                $postData['excerpt'] = Str::limit(strip_tags($postData['content']), 200);
            }

            $post = $this->postRepository->create($postData);

            return [
                'success' => true,
                'message' => 'Post created successfully!',
                'post' => $post
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create post. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update post
     */
    public function updatePost(int $postId, array $postData): array
    {
        try {
            $post = $this->postRepository->findById($postId);
            
            if (!$post) {
                return [
                    'success' => false,
                    'message' => 'Post not found.'
                ];
            }

            // Check authorization
            $user = auth()->user();
            if ($user->role !== 0 && $post->user_id !== $user->id) {
                return [
                    'success' => false,
                    'message' => 'You are not authorized to update this post.'
                ];
            }

            // Generate excerpt from content
            if (isset($postData['content']) && !isset($postData['excerpt'])) {
                $postData['excerpt'] = Str::limit(strip_tags($postData['content']), 200);
            }

            $this->postRepository->update($postId, $postData);

            return [
                'success' => true,
                'message' => 'Post updated successfully!'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update post. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete post
     */
    public function deletePost(int $postId): array
    {
        try {
            $post = $this->postRepository->findById($postId);
            
            if (!$post) {
                return [
                    'success' => false,
                    'message' => 'Post not found.'
                ];
            }

            // Check authorization
            $user = auth()->user();
            if ($user->role !== 0 && $post->user_id !== $user->id) {
                return [
                    'success' => false,
                    'message' => 'You are not authorized to delete this post.'
                ];
            }

            $this->postRepository->delete($postId);

            return [
                'success' => true,
                'message' => 'Post deleted successfully!'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete post. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get posts with advanced filtering
     */
    public function getPostsWithFilters(array $filters = [], int $perPage = 15): array
    {
        // Apply user-specific filters for non-admin users
        $user = auth()->user();
        if ($user->role !== 0 && !isset($filters['user_id'])) {
            $filters['user_id'] = $user->id;
        }

        $posts = $this->postRepository->getPaginated($perPage, $filters);
        $statistics = $this->postRepository->getStatistics();

        return [
            'posts' => $posts,
            'statistics' => $statistics,
            'filters' => $filters
        ];
    }

    /**
     * Get post with view increment
     */
    public function getPostWithView(int $postId): array
    {
        $post = $this->postRepository->findById($postId);
        
        if (!$post) {
            return [
                'success' => false,
                'message' => 'Post not found.',
                'post' => null
            ];
        }

        // Increment view count
        $this->postRepository->incrementViews($postId);
        
        // Refresh post data with updated view count
        $post = $this->postRepository->findById($postId);

        return [
            'success' => true,
            'post' => $post
        ];
    }

    /**
     * Bulk update posts status
     */
    public function bulkUpdateStatus(array $postIds, int $status): array
    {
        DB::beginTransaction();
        
        try {
            $user = auth()->user();
            
            // For non-admin users, filter posts to only their own
            if ($user->role !== 0) {
                $userPosts = $this->postRepository->getByUser($user->id)->pluck('id')->toArray();
                $postIds = array_intersect($postIds, $userPosts);
            }

            if (empty($postIds)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'No posts to update.'
                ];
            }

            $this->postRepository->bulkUpdateStatus($postIds, $status);
            $statusText = $status === 1 ? 'published' : 'drafted';

            DB::commit();
            return [
                'success' => true,
                'message' => count($postIds) . " posts {$statusText} successfully!",
                'updated_count' => count($postIds)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to update posts. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Search posts
     */
    public function searchPosts(string $query, array $filters = []): array
    {
        $posts = $this->postRepository->search($query);
        
        // Apply additional filters
        if (isset($filters['category'])) {
            $posts = $posts->where('category', $filters['category']);
        }
        
        if (isset($filters['user_id'])) {
            $posts = $posts->where('user_id', $filters['user_id']);
        }

        return [
            'posts' => $posts,
            'query' => $query,
            'count' => $posts->count()
        ];
    }

    /**
     * Get trending posts
     */
    public function getTrendingPosts(int $limit = 10): array
    {
        $popularPosts = $this->postRepository->getPopularPosts($limit);
        $recentPosts = $this->postRepository->getRecentPosts($limit);

        return [
            'popular_posts' => $popularPosts,
            'recent_posts' => $recentPosts
        ];
    }

    /**
     * Get post analytics
     */
    public function getPostAnalytics(int $postId): array
    {
        $post = $this->postRepository->findById($postId);
        
        if (!$post) {
            return [
                'success' => false,
                'message' => 'Post not found.'
            ];
        }

        return [
            'success' => true,
            'post' => $post,
            'analytics' => [
                'views' => $post->views,
                'created_days_ago' => $post->created_at->diffInDays(now()),
                'last_updated' => $post->updated_at,
                'word_count' => str_word_count(strip_tags($post->content)),
                'reading_time' => ceil(str_word_count(strip_tags($post->content)) / 200) // Assuming 200 words per minute
            ]
        ];
    }
}
