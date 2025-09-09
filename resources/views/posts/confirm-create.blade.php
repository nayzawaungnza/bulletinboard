@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Post Creation
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Please review the post information below before creating.
                    </div>

                    <form method="POST" action="{{ route('posts.confirm-store') }}">
                        @csrf
                        
                        <!-- Hidden fields to preserve data -->
                        @foreach($data as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach

                        <div class="mb-3">
                            <label class="form-label fw-bold">Title:</label>
                            <input type="text" class="form-control" value="{{ $data['title'] }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description:</label>
                            <textarea class="form-control" rows="5" disabled>{{ $data['description'] }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status:</label>
                            <input type="text" class="form-control" 
                                   value="{{ $data['status'] == 1 ? 'Active' : 'Inactive' }}" disabled>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('posts.create') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Edit
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>Confirm & Create Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
