@extends('layouts.app')

@section('title', $post->title)
@section('page-title', 'Post Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{{ $post->title }}</h5>
                    <small class="text-muted">
                        By {{ $post->creator->name }} • {{ $post->created_at->format('M d, Y H:i') }}
                    </small>
                </div>
                <span class="badge bg-{{ $post->isActive() ? 'success' : 'secondary' }}">
                    {{ $post->isActive() ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    {!! nl2br(e($post->description)) !!}
                </div>

                @if($post->updated_at != $post->created_at && $post->updater)
                <div class="border-top pt-3">
                    <small class="text-muted">
                        Last updated by {{ $post->updater->name }} on {{ $post->updated_at->format('M d, Y H:i') }}
                    </small>
                </div>
                @endif
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('posts.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Posts
                    </a>
                    
                    @if(auth()->user()->isAdmin() || $post->create_user_id === auth()->id())
                    <div>
                        <a href="{{ route('posts.edit', $post) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline ms-2" 
                              onsubmit="return confirm('Are you sure you want to delete this post?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
