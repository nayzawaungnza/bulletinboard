<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Interfaces\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all posts with optional filters
     */
    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->with('user');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['create_user_id'])) {
            $query->where('create_user_id', $filters['create_user_id']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('content', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get paginated posts with optional filters
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with('user');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['create_user_id'])) {
            $query->where('create_user_id', $filters['create_user_id']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('content', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find post by ID
     */
    public function findById(int $id): ?Post
    {
        return $this->model->with('user')->find($id);
    }

    /**
     * Find post by slug
     */
    public function findBySlug(string $slug): ?Post
    {
        return $this->model->with('user')->where('slug', $slug)->first();
    }

    /**
     * Create a new post
     */
    public function create(array $data): Post
    {
       

        $data['create_user_id'] = auth()->id();

        return $this->model->create($data);
    }

    /**
     * Update post by ID
     */
    public function update(int $id, array $data): bool
    {
        $post = $this->findById($id);
        if (!$post) {
            return false;
        }

       

        $data['updated_at'] = now();
        $data['updated_user_id'] = auth()->id();

        return $post->update($data);
    }

    /**
     * Delete post by ID (soft delete)
     */
    public function delete(int $id): bool
    {
        $post = $this->findById($id);
        if (!$post) {
            return false;
        }

        $post->update([
            'deleted_user_id' => auth()->id(),
            'deleted_at' => now()
        ]);

        return $post->delete();
    }

    /**
     * Force delete post by ID
     */
    public function forceDelete(int $id): bool
    {
        $post = $this->model->withTrashed()->find($id);
        if (!$post) {
            return false;
        }

        return $post->forceDelete();
    }

    /**
     * Restore soft deleted post
     */
    public function restore(int $id): bool
    {
        $post = $this->model->withTrashed()->find($id);
        if (!$post) {
            return false;
        }

        return $post->restore();
    }

    /**
     * Get posts by user
     */
    public function getByUser(int $userId): Collection
    {
        return $this->model->where('create_user_id', $userId)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get posts by status
     */
    public function getByStatus(int $status): Collection
    {
        return $this->model->with('user')->where('status', $status)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get published posts
     */
    public function getPublishedPosts(): Collection
    {
        return $this->getByStatus(1);
    }

    /**
     * Get draft posts
     */
    public function getDraftPosts(): Collection
    {
        return $this->getByStatus(0);
    }

    /**
     * Get posts by category
     */
    public function getByCategory(string $category): Collection
    {
        return $this->model->with('user')->where('category', $category)->where('status', 1)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get posts by tag
     */
    public function getByTag(string $tag): Collection
    {
        return $this->model->with('user')->where('tags', 'like', '%' . $tag . '%')->where('status', 1)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Search posts by title or content
     */
    public function search(string $query): Collection
    {
        return $this->model->with('user')->where(function ($q) use ($query) {
            $q->where('title', 'like', '%' . $query . '%')
              ->orWhere('content', 'like', '%' . $query . '%')
              ->orWhere('tags', 'like', '%' . $query . '%');
        })->where('status', 1)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get recent posts
     */
    public function getRecentPosts(int $limit = 10): Collection
    {
        return $this->model->with('user')->where('status', 1)->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    /**
     * Get popular posts (most viewed)
     */
    public function getPopularPosts(int $limit = 10): Collection
    {
        return $this->model->with('user')->where('status', 1)->orderBy('views', 'desc')->limit($limit)->get();
    }

    /**
     * Get posts statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_posts' => $this->model->count(),
            'published_posts' => $this->model->where('status', 1)->count(),
            'draft_posts' => $this->model->where('status', 0)->count(),
            'posts_today' => $this->model->whereDate('created_at', today())->count(),
            'posts_this_month' => $this->model->whereMonth('created_at', now()->month)->count(),
            'total_views' => $this->model->sum('views'),
            'average_views' => $this->model->avg('views'),
            'categories_count' => $this->model->distinct('category')->count('category'),
        ];
    }

    /**
     * Increment post views
     */
    public function incrementViews(int $id): bool
    {
        return $this->model->where('id', $id)->increment('views');
    }

    /**
     * Get posts with user information
     */
    public function getPostsWithUser(): Collection
    {
        return $this->model->with('user')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get user's posts with pagination
     */
    public function getUserPostsPaginated(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('create_user_id', $userId)->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get posts by date range
     */
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->with('user')->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get all categories
     */
    public function getAllCategories(): Collection
    {
        return $this->model->select('category')->distinct()->whereNotNull('category')->orderBy('category')->get();
    }

    /**
     * Get all tags
     */
    public function getAllTags(): Collection
    {
        $posts = $this->model->select('tags')->whereNotNull('tags')->get();
        $allTags = [];
        
        foreach ($posts as $post) {
            if ($post->tags) {
                $tags = explode(',', $post->tags);
                foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag) && !in_array($tag, $allTags)) {
                        $allTags[] = $tag;
                    }
                }
            }
        }
        
        sort($allTags);
        return collect($allTags);
    }

    /**
     * Bulk update posts status
     */
    public function bulkUpdateStatus(array $postIds, int $status): bool
    {
        return $this->model->whereIn('id', $postIds)->update([
            'status' => $status,
            'updated_at' => now(),
            'updated_by' => auth()->id()
        ]);
    }

    /**
     * Get posts count by user
     */
    public function getPostsCountByUser(int $userId): int
    {
        return $this->model->where('create_user_id', $userId)->count();
    }

    /**
     * Generate unique slug from title
     */
    private function generateUniqueSlug(string $title, int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slugExists(string $slug, int $excludeId = null): bool
    {
        $query = $this->model->where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
}