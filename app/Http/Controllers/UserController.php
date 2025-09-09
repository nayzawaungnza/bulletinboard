<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->middleware('auth');
        $this->middleware('admin')->except(['profile', 'updateProfile', 'changePassword']);
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        $filters = [];
        
        if ($request->filled('search_name')) {
            $filters['name'] = $request->search_name;
        }
        
        if ($request->filled('search_email')) {
            $filters['email'] = $request->search_email;
        }
        
        if ($request->filled('search_role')) {
            $filters['role'] = $request->search_role;
        }
        
        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->date_from;
        }
        
        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->date_to;
        }

        $users = $this->userRepository->getPaginatedWithFilters(10, $filters);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(CreateUserRequest $request)
    {
        $userData = $request->validated();
        
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $userData['profile_path'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $this->userRepository->create($userData);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $user = $this->userRepository->findById($user->id);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $userData = $request->validated();
        
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($user->profile_path) {
                Storage::disk('public')->delete($user->profile_path);
            }
            $userData['profile_path'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $this->userRepository->update($user->id, $userData);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete yourself!');
        }

        DB::beginTransaction();
        
        try {
            // Delete profile image if exists
            if ($user->profile_path) {
                Storage::disk('public')->delete($user->profile_path);
            }

            // Soft delete user's posts first
            DB::table('posts')->where('user_id', $user->id)->update([
                'deleted_by' => auth()->id(),
                'deleted_at' => now()
            ]);

            // Delete the user
            $this->userRepository->delete($user->id);

            DB::commit();
            return redirect()->route('users.index')->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')->with('error', 'Failed to delete user. Please try again.');
        }
    }

    public function toggleLock(User $user)
    {
        if ($user->is_locked) {
            $this->userRepository->unlockAccount($user->id);
            $status = 'unlocked';
        } else {
            $this->userRepository->lockAccount($user->id);
            $status = 'locked';
        }

        return redirect()->route('users.index')->with('success', "User {$status} successfully!");
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:unlock,delete',
            'user_ids' => 'required|json'
        ]);

        $userIds = json_decode($request->user_ids, true);
        $currentUserId = auth()->id();
        
        // Remove current user from bulk actions
        $userIds = array_filter($userIds, function($id) use ($currentUserId) {
            return $id != $currentUserId;
        });

        if (empty($userIds)) {
            return redirect()->route('users.index')->with('error', 'No valid users selected for bulk action.');
        }

        DB::beginTransaction();
        
        try {
            $count = 0;
            foreach ($userIds as $userId) {
                if ($request->action === 'unlock') {
                    $this->userRepository->unlockAccount($userId);
                    $count++;
                } elseif ($request->action === 'delete') {
                    // Handle related data cleanup for each user
                    $user = $this->userRepository->findById($userId);
                    if ($user) {
                        // Delete profile image if exists
                        if ($user->profile_path) {
                            Storage::disk('public')->delete($user->profile_path);
                        }
                        
                        // Soft delete user's posts
                        DB::table('posts')->where('user_id', $userId)->update([
                            'deleted_by' => auth()->id(),
                            'deleted_at' => now()
                        ]);
                        
                        $this->userRepository->delete($userId);
                        $count++;
                    }
                }
            }

            DB::commit();
            $actionText = $request->action === 'unlock' ? 'unlocked' : 'deleted';
            return redirect()->route('users.index')->with('success', "{$count} users {$actionText} successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')->with('error', 'Bulk action failed. Please try again.');
        }
    }

    public function profile()
    {
        return view('users.profile');
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        $userData = $request->validated();
        
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($user->profile_path) {
                Storage::disk('public')->delete($user->profile_path);
            }
            $userData['profile_path'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $this->userRepository->updateProfile($user->id, $userData);

        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->userRepository->changePassword(auth()->id(), $request->password);

        return redirect()->route('profile')->with('success', 'Password changed successfully!');
    }
}
