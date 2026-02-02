@extends('layouts.traxtar')

@section('content')
<a class="btn mb-4 inline-block" href="{{ route('admin.admins.index') }}">← Back to Admins</a>

<div class="max-w-lg card p-6">
    <h2 class="text-xl font-bold mb-4">Invite New Admin</h2>

    <form method="POST" action="{{ route('admin.admins.invite.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="label">Email Address</label>
            <input 
                type="email" 
                id="email"
                name="email" 
                value="{{ old('email') }}"
                class="input @error('email') border-red-500 @enderror" 
                required 
                autofocus>
            @error('email')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-sm text-neutral-500 mt-1">An invitation link will be sent to this email address.</p>
        </div>

        <div>
            <label for="role" class="label">Role</label>
            <select 
                id="role"
                name="role" 
                class="input @error('role') border-red-500 @enderror" 
                required>
                <option value="">Select Role</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            </select>
            @error('role')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-sm text-neutral-500 mt-1">
                <strong>Admin:</strong> Can manage products and orders.<br>
                <strong>Super Admin:</strong> Can manage products, orders, and other admins.
            </p>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Send Invitation</button>
            <a href="{{ route('admin.admins.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
