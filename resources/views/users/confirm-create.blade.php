@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm User Creation
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Please review the user information below before creating the account.
                    </div>

                    <form method="POST" action="{{ route('users.confirm-store') }}">
                        @csrf
                        
                        <!-- Hidden fields to preserve data -->
                        @foreach($data as $key => $value)
                            @if($key !== 'password_confirmation')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Full Name:</label>
                                    <input type="text" class="form-control" value="{{ $data['name'] }}" disabled>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email Address:</label>
                                    <input type="email" class="form-control" value="{{ $data['email'] }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Role:</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $data['role'] == 0 ? 'Admin' : 'User' }}" disabled>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Phone Number:</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $data['phone'] ?? 'Not provided' }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Address:</label>
                            <textarea class="form-control" rows="3" disabled>{{ $data['address'] ?? 'Not provided' }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Date of Birth:</label>
                            <input type="text" class="form-control" 
                                   value="{{ $data['date_of_birth'] ?? 'Not provided' }}" disabled>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('users.create') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Edit
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>Confirm & Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
