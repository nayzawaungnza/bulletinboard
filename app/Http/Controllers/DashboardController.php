<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected PostRepositoryInterface $postRepository;
    protected UserRepositoryInterface $userRepository;

    public function __construct(
        PostRepositoryInterface $postRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->middleware('auth');
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $posts = $this->postRepository->getPaginated(10, ['status' => 1]);
        
        // $statistics = [
        //     'posts' => $this->postRepository->getStatistics(),
        //     'users' => $this->userRepository->getStatistics(),
        //     'myPosts' => $this->postRepository->getPostsCountByUser(auth()->id())
        // ];
        // dd($statistics->posts->total_posts);

        $statistics = (object)[
    'posts' => (object)$this->postRepository->getStatistics(),
    'users' => (object)$this->userRepository->getStatistics(),
    'myPosts' => $this->postRepository->getPostsCountByUser(auth()->id())
];
//dd($statistics->posts->total_posts);

        return view('dashboard', compact('posts', 'statistics'));
    }
}