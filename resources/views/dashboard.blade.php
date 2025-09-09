@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $statistics->posts->total_posts }}</h4>
                        <p class="mb-0">Total Posts</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-file-post fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
    <div class="col-md-4 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $statistics->users->total_users }}</h4>
                        <p class="mb-0">Total Users</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-md-4 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $statistics->myPosts }}</h4>
                        <p class="mb-0">My Posts</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-person-check fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Posts -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Posts</h5>
                <a href="{{ route('posts.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus"></i> New Post
                </a>
            </div>
            <div class="card-body">
                @if($posts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($posts as $post)
                                <tr>
                                    <td>
                                        <a href="{{ route('posts.show', $post) }}" class="text-decoration-none">
                                            {{ Str::limit($post->title, 50) }}
                                        </a>
                                    </td>
                                    <td>{{ $post->creator->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $post->isActive() ? 'success' : 'secondary' }}">
                                            {{ $post->isActive() ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $post->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(auth()->user()->isAdmin() || $post->create_user_id === auth()->id())
                                        <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $posts->links() }}
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-post fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No posts available</p>
                        <a href="{{ route('posts.create') }}" class="btn btn-primary">Create First Post</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
