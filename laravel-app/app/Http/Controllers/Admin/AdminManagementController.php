<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\UpdateAdminRoleRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminManagementController extends Controller
{
    use LogsActivity;

    /**
     * Display a listing of all admins.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::admins()->with('invitedBy');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            }
        }

        // Filter by role
        if ($request->has('role') && $request->role !== 'all') {
            if ($request->role === 'super_admin') {
                $query->superAdmins();
            } elseif ($request->role === 'admin') {
                $query->regularAdmins();
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = $query->latest()->paginate(15);

        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for inviting a new admin.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.admins.invite');
    }

    /**
     * Deactivate an admin.
     */
    public function deactivate(User $admin): RedirectResponse
    {
        $this->authorize('deactivate', $admin);

        if ($admin->is_active) {
            $admin->update(['is_active' => false]);

            // Defensive security: Invalidate all sessions and tokens when admin is deactivated
            // Revoke all Sanctum tokens
            $admin->tokens()->delete();
            
            // Delete all sessions from database (if using database sessions)
            DB::table('sessions')
                ->where('user_id', $admin->id)
                ->delete();

            // Log deactivation
            $this->logActivity(
                'admin.deactivated',
                $admin,
                'Admin Management',
                'warning',
                "Admin '{$admin->name}' ({$admin->email}) was deactivated. All sessions and tokens revoked.",
                [
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'admin_email' => $admin->email,
                    'role' => $admin->role,
                    'sessions_revoked' => true,
                    'tokens_revoked' => true,
                ]
            );

            return redirect()->route('admin.admins.index')
                ->with('success', 'Admin deactivated successfully. All active sessions have been terminated.');
        }

        return redirect()->route('admin.admins.index')
            ->with('error', 'Admin is already inactive.');
    }

    /**
     * Activate an admin.
     */
    public function activate(User $admin): RedirectResponse
    {
        $this->authorize('activate', $admin);

        if (!$admin->is_active) {
            $admin->update(['is_active' => true]);

            // Log activation
            $this->logActivity(
                'admin.activated',
                $admin,
                'Admin Management',
                'info',
                "Admin '{$admin->name}' ({$admin->email}) was activated",
                [
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'admin_email' => $admin->email,
                    'role' => $admin->role,
                ]
            );

            return redirect()->route('admin.admins.index')
                ->with('success', 'Admin activated successfully.');
        }

        return redirect()->route('admin.admins.index')
            ->with('error', 'Admin is already active.');
    }

    /**
     * Update an admin's role.
     */
    public function updateRole(UpdateAdminRoleRequest $request, User $admin): RedirectResponse
    {
        $this->authorize('updateRole', $admin);

        $oldRole = $admin->role;
        $newRole = $request->validated()['role'];

        if ($oldRole !== $newRole) {
            $admin->update(['role' => $newRole]);

            // Log role change
            $this->logActivity(
                'admin.role.updated',
                $admin,
                'Admin Management',
                'warning',
                "Admin '{$admin->name}' role changed from {$oldRole} to {$newRole}",
                [
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'old_role' => $oldRole,
                    'new_role' => $newRole,
                ]
            );

            return redirect()->route('admin.admins.index')
                ->with('success', 'Admin role updated successfully.');
        }

        return redirect()->route('admin.admins.index')
            ->with('info', 'No changes made.');
    }
}
