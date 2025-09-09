<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Repositories\Interfaces\PostRepositoryInterface;

class PostController extends Controller
{
    protected PostRepositoryInterface $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->middleware('auth');
        
        $this->postRepository = $postRepository;
    }

    public function index()
    {
        $this->authorize('viewAny', Post::class);
        $filters = [];
        
        // Non-admin users can only see their own posts
        if (auth()->user()->role !== 0) {
            $filters['create_user_id'] = auth()->id(); // Also changed key to match your model
        }

        $posts = $this->postRepository->getPaginated(10, $filters);

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        $this->authorize('create', Post::class);
        return view('posts.create');
    }

    public function store(CreatePostRequest $request)
    {
        $this->authorize('create',Post::class);
        $this->postRepository->create($request->validated());

        return redirect()->route('posts.index')->with('success', 'Post created successfully!');
    }

    public function show(Post $post)
    {
         $this->authorize('view', $post);
        $this->postRepository->incrementViews($post->id);
        $post = $this->postRepository->findById($post->id); // Refresh with updated view count
        
        return view('posts.show', compact('post'));
    }

    public function modal(Post $post)
    {
        $user = auth()->user();
        $canEdit = $user->isAdmin() || $post->create_user_id === $user->id;
        
        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'author' => $post->creator->name,
            'status' => $post->status,
            'created_at' => $post->created_at->format('M d, Y H:i'),
            'updated_at' => $post->updated_at->format('M d, Y H:i'),
            'updater' => $post->updater ? $post->updater->name : $post->creator->name,
            'can_edit' => $canEdit
        ]);
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        // Authorization is handled in UpdatePostRequest
        return view('posts.edit', compact('post'));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        Log::debug('Update attempt', [
        'user_id' => auth()->id(),
        'post_creator' => $post->create_user_id,
        'is_admin' => auth()->user()->isAdmin(),
        'policy_result' => auth()->user()->can('update', $post)
    ]);

        $this->authorize('update', $post);
        $this->postRepository->update($post->id, $request->validated());

        return redirect()->route('posts.index')->with('success', 'Post updated successfully!');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        
        

        $this->postRepository->delete($post->id);

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully!');
    }
}