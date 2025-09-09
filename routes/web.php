<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DataController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => true]);

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Posts
    Route::resource('posts', PostController::class);
    Route::get('/posts/{post}/modal', [PostController::class, 'modal'])->name('posts.modal');
    
    // Data Handling Routes
    Route::get('/data/csv-upload', [DataController::class, 'showCsvUpload'])->name('data.csv-upload');
    Route::post('/data/upload-csv', [DataController::class, 'uploadCsv'])->name('data.upload-csv');
    Route::get('/data/download-excel', [DataController::class, 'downloadExcel'])->name('data.download-excel');
    Route::get('/data/sample-csv', function() {
        $csvContent = "title,description,status\n";
        $csvContent .= "\"Sample Post 1\",\"This is a sample post description\",\"1\"\n";
        $csvContent .= "\"Sample Post 2\",\"Another sample post description\",\"active\"\n";
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="sample_posts.csv"');
    })->name('data.sample-csv');
    
    // Profile
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    
    // Admin Only Routes
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-lock', [UserController::class, 'toggleLock'])->name('users.toggle-lock');
        Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
    });
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');