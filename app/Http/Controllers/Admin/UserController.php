<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Apply filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);
        $roles = ['admin', 'security', 'staff', 'student'];

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = ['admin', 'security', 'staff', 'student'];
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:admin,security,staff,student'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_user',
            'subject_type' => 'user',
            'subject_id' => $user->id,
            'details' => ['role' => $user->role],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = ['admin', 'security', 'staff', 'student'];
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'string', 'in:admin,security,staff,student'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $oldRole = $user->role;
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();

        if ($oldRole !== $user->role) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_user_role',
                'subject_type' => 'user',
                'subject_id' => $user->id,
                'details' => [
                    'old_role' => $oldRole,
                    'new_role' => $user->role,
                ],
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Log the action before deletion
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_user',
            'subject_type' => 'user',
            'subject_id' => $user->id,
            'details' => ['role' => $user->role],
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Show user details and activity.
     */
    public function show(User $user)
    {
        $user->load(['reports', 'comments', 'auditLogs']);
        
        $activityStats = [
            'total_reports' => $user->reports()->count(),
            'resolved_reports' => $user->reports()->where('status', 'resolved')->count(),
            'total_comments' => $user->comments()->count(),
            'last_active' => $user->last_active_at ?? $user->updated_at,
        ];

        return view('admin.users.show', compact('user', 'activityStats'));
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'toggle_user_status',
            'subject_type' => 'user',
            'subject_id' => $user->id,
            'details' => ['new_status' => $user->is_active ? 'active' : 'inactive'],
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully.");
    }

    /**
     * Display the roles and permissions management page.
     */
    public function roles()
    {
        Log::info('Accessed admin.users.roles route.');

        $roles = [
            'admin' => [
                'name' => 'Administrator',
                'description' => 'Full system access with all permissions',
                'permissions' => [
                    'manage_users' => true,
                    'manage_roles' => true,
                    'manage_reports' => true,
                    'manage_security' => true,
                    'view_analytics' => true,
                    'manage_settings' => true,
                ]
            ],
            'security' => [
                'name' => 'Security Personnel',
                'description' => 'Access to security operations and reports',
                'permissions' => [
                    'manage_reports' => true,
                    'manage_security' => true,
                    'view_analytics' => true,
                    'manage_users' => false,
                    'manage_roles' => false,
                    'manage_settings' => false,
                ]
            ],
            'staff' => [
                'name' => 'Staff',
                'description' => 'Basic access to submit and view reports',
                'permissions' => [
                    'submit_reports' => true,
                    'view_reports' => true,
                    'manage_reports' => false,
                    'manage_security' => false,
                    'view_analytics' => false,
                    'manage_settings' => false,
                ]
            ],
            'student' => [
                'name' => 'Student',
                'description' => 'Limited access to submit reports only',
                'permissions' => [
                    'submit_reports' => true,
                    'view_reports' => false,
                    'manage_reports' => false,
                    'manage_security' => false,
                    'view_analytics' => false,
                    'manage_settings' => false,
                ]
            ]
        ];

        // Fetch all users and group them by role
        $users = User::all()->groupBy('role');

        Log::info('Fetched and grouped users by role.', ['roles_found' => $users->keys()->toArray()]);

        return view('admin.users.roles', compact('roles', 'users'));
    }
} 