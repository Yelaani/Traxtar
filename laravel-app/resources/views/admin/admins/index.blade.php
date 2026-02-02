@extends('layouts.traxtar')

@section('content')
<a class="btn mb-4 inline-block" href="{{ route('admin.dashboard') }}">← Back to Dashboard</a>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Admin Management</h1>
    @can('create', \App\Models\User::class)
        <a href="{{ route('admin.admins.invite') }}" class="btn-primary">Invite New Admin</a>
    @endcan
</div>

@if(session('success'))
    <div class="bg-green-500 text-white p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-500 text-white p-4 rounded mb-4">
        {{ session('error') }}
    </div>
@endif

@if(session('info'))
    <div class="bg-blue-500 text-white p-4 rounded mb-4">
        {{ session('info') }}
    </div>
@endif

<!-- Filters -->
<div class="card p-4 mb-6">
    <form method="GET" action="{{ route('admin.admins.index') }}" class="grid md:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
            <label for="search" class="block text-sm font-medium text-neutral-700 mb-2">Search</label>
            <input 
                type="text" 
                id="search"
                name="search" 
                value="{{ request('search') }}"
                placeholder="Name or email..."
                class="w-full px-4 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Status Filter -->
        <div>
            <label for="status" class="block text-sm font-medium text-neutral-700 mb-2">Status</label>
            <select 
                id="status"
                name="status" 
                class="w-full px-4 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Role Filter -->
        <div>
            <label for="role" class="block text-sm font-medium text-neutral-700 mb-2">Role</label>
            <select 
                id="role"
                name="role" 
                class="w-full px-4 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="all" {{ request('role') === 'all' ? 'selected' : '' }}>All Roles</option>
                <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="flex items-end">
            <button type="submit" class="btn w-full">Filter</button>
        </div>
    </form>
</div>

<!-- Admins Table -->
@if($admins->count() > 0)
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead class="bg-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Invited By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-neutral-200">
                @foreach($admins as $admin)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-neutral-900">{{ $admin->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-neutral-600">{{ $admin->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($admin->isSuperAdmin())
                                <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">Super Admin</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Admin</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($admin->is_active)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-neutral-600">
                                @if($admin->invitedBy)
                                    {{ $admin->invitedBy->name }}
                                @else
                                    <span class="text-neutral-400">N/A</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-neutral-600">{{ $admin->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex gap-2">
                                @can('updateRole', $admin)
                                    <form method="POST" action="{{ route('admin.admins.updateRole', $admin) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" onchange="this.form.submit()" class="text-xs border rounded px-2 py-1">
                                            <option value="admin" {{ $admin->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="super_admin" {{ $admin->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                        </select>
                                    </form>
                                @endcan
                                
                                @can('deactivate', $admin)
                                    @if($admin->is_active)
                                        <form method="POST" action="{{ route('admin.admins.deactivate', $admin) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-xs" onclick="return confirm('Are you sure you want to deactivate this admin?')">Deactivate</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.admins.activate', $admin) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900 text-xs">Activate</button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $admins->links() }}
    </div>
@else
    <div class="card p-8 text-center">
        <p class="text-neutral-600 mb-4">No admins found.</p>
        @can('create', \App\Models\User::class)
            <a href="{{ route('admin.admins.invite') }}" class="btn-primary inline-block">Invite First Admin</a>
        @endcan
    </div>
@endif
@endsection
