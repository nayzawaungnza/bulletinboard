<?php

namespace App\Repositories\Interfaces;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    /**
     * Get all posts with optional filters
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get paginated posts with optional filters
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Find post by ID
     */
    public function findById(int $id): ?Post;

    /**
     * Find post by slug
     */
    public function findBySlug(string $slug): ?Post;

    /**
     * Create a new post
     */
    public function create(array $data): Post;

    /**
     * Update post by ID
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete post by ID (soft delete)
     */
    public function delete(int $id): bool;

    /**
     * Force delete post by ID
     */
    public function forceDelete(int $id): bool;

    /**
     * Restore soft deleted post
     */
    public function restore(int $id): bool;

    /**
     * Get posts by user
     */
    public function getByUser(int $userId): Collection;

    /**
     * Get posts by status
     */
    public function getByStatus(int $status): Collection;

    /**
     * Get published posts
     */
    public function getPublishedPosts(): Collection;

    /**
     * Get draft posts
     */
    public function getDraftPosts(): Collection;

    /**
     * Get posts by category
     */
    public function getByCategory(string $category): Collection;

    /**
     * Get posts by tag
     */
    public function getByTag(string $tag): Collection;

    /**
     * Search posts by title or content
     */
    public function search(string $query): Collection;

    /**
     * Get recent posts
     */
    public function getRecentPosts(int $limit = 10): Collection;

    /**
     * Get popular posts (most viewed)
     */
    public function getPopularPosts(int $limit = 10): Collection;

    /**
     * Get posts statistics
     */
    public function getStatistics(): array;

    /**
     * Increment post views
     */
    public function incrementViews(int $id): bool;

    /**
     * Get posts with user information
     */
    public function getPostsWithUser(): Collection;

    /**
     * Get user's posts with pagination
     */
    public function getUserPostsPaginated(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get posts by date range
     */
    public function getByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Get all categories
     */
    public function getAllCategories(): Collection;

    /**
     * Get all tags
     */
    public function getAllTags(): Collection;

    /**
     * Bulk update posts status
     */
    public function bulkUpdateStatus(array $postIds, int $status): bool;

    /**
     * Get posts count by user
     */
    public function getPostsCountByUser(int $userId): int;
}
