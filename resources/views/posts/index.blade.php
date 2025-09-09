@extends('layouts.app')

@section('title', 'Posts')
@section('page-title', 'Posts Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Manage your bulletin board posts</p>
    </div>
    <a href="{{ route('posts.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> New Post
    </a>
</div>

<div class="card">
    <div class="card-body">
        @if($posts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            @if(auth()->user()->isAdmin())
                            <th>Author</th>
                            @endif
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $post)
                        <tr>
                            <td>
                                <!-- Updated to show post detail in modal -->
                                <a href="#" onclick="showPostDetail({{ $post->id }})" class="text-decoration-none fw-bold">
                                    {{ Str::limit($post->title, 40) }}
                                </a>
                            </td>
                            <td>{{ Str::limit($post->description, 60) }}</td>
                            @if(auth()->user()->isAdmin())
                            <td>{{ $post->creator->name }}</td>
                            @endif
                            <td>
                                <span class="badge bg-{{ $post->isActive() ? 'success' : 'secondary' }}">
                                    {{ $post->isActive() ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $post->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showPostDetail({{ $post->id }})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if(auth()->user()->isAdmin() || $post->create_user_id === auth()->id())
                                    <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <!-- Updated delete button to use modal confirmation -->
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmPostDelete({{ $post->id }}, '{{ addslashes($post->title) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $posts->links() }}
            <div class="pagination-info">
    Showing {{ $posts->firstItem() }} to {{ $posts->lastItem() }} of {{ $posts->total() }} entries
</div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-file-post fs-1 text-muted"></i>
                <h4 class="text-muted mt-3">No Posts Found</h4>
                <p class="text-muted">Start by creating your first post</p>
                <a href="{{ route('posts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Create Post
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Added post detail modal -->
<div class="modal fade" id="postDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="postDetailTitle">Post Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="postDetailContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="postDetailActions"></div>
            </div>
        </div>
    </div>
</div>

<!-- Added post delete confirmation modal -->
<div class="modal fade" id="postDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="postDeleteMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmPostDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for post deletion -->
<form id="postDeleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function showPostDetail(postId) {
    const modal = new bootstrap.Modal(document.getElementById('postDetailModal'));
    
    // Reset modal content
    document.getElementById('postDetailContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    document.getElementById('postDetailActions').innerHTML = '';
    
    modal.show();
    
    // Fetch post details
    fetch(`/posts/${postId}/modal`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('postDetailTitle').textContent = data.title;
            document.getElementById('postDetailContent').innerHTML = `
                <div class="mb-3">
                    <h6 class="text-muted">Author</h6>
                    <p>${data.author}</p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted">Status</h6>
                    <span class="badge bg-${data.status === 1 ? 'success' : 'secondary'}">
                        ${data.status === 1 ? 'Active' : 'Inactive'}
                    </span>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted">Created</h6>
                    <p>${data.created_at}</p>
                </div>
                ${data.updated_at !== data.created_at ? `
                <div class="mb-3">
                    <h6 class="text-muted">Last Updated</h6>
                    <p>${data.updated_at} by ${data.updater}</p>
                </div>
                ` : ''}
                <div class="mb-3">
                    <h6 class="text-muted">Description</h6>
                    <div class="border p-3 rounded bg-light">
                        ${data.description.replace(/\n/g, '<br>')}
                    </div>
                </div>
            `;
            
            // Add action buttons if user can edit/delete
            if (data.can_edit) {
                document.getElementById('postDetailActions').innerHTML = `
                    <a href="/posts/${postId}/edit" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <button type="button" class="btn btn-outline-danger ms-2" 
                            onclick="confirmPostDelete(${postId}, '${data.title.replace(/'/g, "\\'")}')">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                `;
            }
        })
        .catch(error => {
            document.getElementById('postDetailContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> Error loading post details.
                </div>
            `;
        });
}

function confirmPostDelete(postId, postTitle) {
    // Close post detail modal if open
    const postDetailModal = bootstrap.Modal.getInstance(document.getElementById('postDetailModal'));
    if (postDetailModal) {
        postDetailModal.hide();
    }
    
    document.getElementById('postDeleteMessage').textContent = `Are you sure you want to delete "${postTitle}"? This action cannot be undone.`;
    
    document.getElementById('confirmPostDelete').onclick = function() {
        const form = document.getElementById('postDeleteForm');
        form.action = `/posts/${postId}`;
        form.submit();
    };
    
    new bootstrap.Modal(document.getElementById('postDeleteModal')).show();
}
</script>
@endsection
