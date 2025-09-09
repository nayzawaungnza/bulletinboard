<?php

namespace App\Services;

use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;

class DashboardService
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
     * Get dashboard data for admin users
     */
    public function getAdminDashboardData(): array
    {
        $postStats = $this->postRepository->getStatistics();
        $userStats = $this->userRepository->getStatistics();
        
        $recentPosts = $this->postRepository->getRecentPosts(5);
        $popularPosts = $this->postRepository->getPopularPosts(5);
        $recentUsers = $this->userRepository->latest()->take(5);

        return [
            'statistics' => [
                'posts' => $postStats,
                'users' => $userStats,
                'growth' => $this->getGrowthStatistics()
            ],
            'recent_posts' => $recentPosts,
            'popular_posts' => $popularPosts,
            'recent_users' => $recentUsers,
            'charts_data' => $this->getChartsData()
        ];
    }

    /**
     * Get dashboard data for regular users
     */
    public function getUserDashboardData(int $userId): array
    {
        $userPosts = $this->postRepository->getByUser($userId);
        $userStats = [
            'total_posts' => $userPosts->count(),
            'published_posts' => $userPosts->where('status', 1)->count(),
            'draft_posts' => $userPosts->where('status', 0)->count(),
            'total_views' => $userPosts->sum('views'),
        ];

        $recentPosts = $userPosts->take(5);
        $popularPosts = $userPosts->sortByDesc('views')->take(5);

        return [
            'user_statistics' => $userStats,
            'recent_posts' => $recentPosts,
            'popular_posts' => $popularPosts,
            'activity_data' => $this->getUserActivityData($userId)
        ];
    }

    /**
     * Get general dashboard data (for all users)
     */
    public function getGeneralDashboardData(): array
    {
        $user = auth()->user();
        
        if ($user->role === 0) {
            return $this->getAdminDashboardData();
        } else {
            return $this->getUserDashboardData($user->id);
        }
    }

    /**
     * Get growth statistics
     */
    private function getGrowthStatistics(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'users_today' => $this->userRepository->where(['created_at' => $today])->count(),
            'users_yesterday' => $this->userRepository->where(['created_at' => $yesterday])->count(),
            'posts_today' => $this->postRepository->getByDateRange($today, $today->copy()->endOfDay())->count(),
            'posts_yesterday' => $this->postRepository->getByDateRange($yesterday, $yesterday->copy()->endOfDay())->count(),
            'users_this_month' => $this->userRepository->where(['created_at' => $thisMonth])->count(),
            'users_last_month' => $this->userRepository->getByDateRange($lastMonth, $lastMonth->copy()->endOfMonth())->count(),
            'posts_this_month' => $this->postRepository->getByDateRange($thisMonth, $thisMonth->copy()->endOfMonth())->count(),
        ];
    }

    /**
     * Get charts data for dashboard
     */
    private function getChartsData(): array
    {
        $last7Days = collect();
        $last12Months = collect();

        // Last 7 days data
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last7Days->push([
                'date' => $date->format('Y-m-d'),
                'users' => $this->userRepository->where(['created_at' => $date])->count(),
                'posts' => $this->postRepository->getByDateRange($date, $date->copy()->endOfDay())->count(),
            ]);
        }

        // Last 12 months data
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->startOfMonth();
            $last12Months->push([
                'month' => $month->format('Y-m'),
                'users' => $this->userRepository->getByDateRange($month, $month->copy()->endOfMonth())->count(),
                'posts' => $this->postRepository->getByDateRange($month, $month->copy()->endOfMonth())->count(),
            ]);
        }

        return [
            'last_7_days' => $last7Days,
            'last_12_months' => $last12Months,
            'categories' => $this->postRepository->getAllCategories(),
            'posts_by_status' => [
                'published' => $this->postRepository->getByStatus(1)->count(),
                'draft' => $this->postRepository->getByStatus(0)->count(),
            ]
        ];
    }

    /**
     * Get user activity data
     */
    private function getUserActivityData(int $userId): array
    {
        $last30Days = collect();

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $posts = $this->postRepository->getByUser($userId)
                ->where('created_at', '>=', $date->startOfDay())
                ->where('created_at', '<=', $date->endOfDay());

            $last30Days->push([
                'date' => $date->format('Y-m-d'),
                'posts' => $posts->count(),
                'views' => $posts->sum('views'),
            ]);
        }

        return [
            'last_30_days' => $last30Days,
            'total_activity_score' => $last30Days->sum('posts') + ($last30Days->sum('views') / 10)
        ];
    }

    /**
     * Get system health data
     */
    public function getSystemHealthData(): array
    {
        return [
            'database_status' => 'healthy',
            'total_records' => [
                'users' => $this->userRepository->count(),
                'posts' => $this->postRepository->count(),
            ],
            'disk_usage' => $this->getDiskUsage(),
            'recent_activity' => $this->getRecentActivity()
        ];
    }

    /**
     * Get disk usage information
     */
    private function getDiskUsage(): array
    {
        $totalSpace = disk_total_space(storage_path());
        $freeSpace = disk_free_space(storage_path());
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'percentage' => round(($usedSpace / $totalSpace) * 100, 2)
        ];
    }

    /**
     * Get recent system activity
     */
    private function getRecentActivity(): array
    {
        $recentUsers = $this->userRepository->latest()->take(3);
        $recentPosts = $this->postRepository->getRecentPosts(3);

        return [
            'recent_users' => $recentUsers,
            'recent_posts' => $recentPosts
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
