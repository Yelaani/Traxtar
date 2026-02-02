@extends('layouts.traxtar')

@section('content')
<div class="max-w-lg mx-auto card p-6">
    <h2 class="text-xl font-bold mb-4">Accept Admin Invitation</h2>

    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
        <p class="text-sm text-blue-800">
            <strong>You've been invited as:</strong> {{ $invitation->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
        </p>
        <p class="text-sm text-blue-800 mt-1">
            <strong>Email:</strong> {{ $invitation->email }}
        </p>
        <p class="text-sm text-blue-600 mt-2">
            This invitation expires on {{ $invitation->expires_at->format('F j, Y \a\t g:i A') }}
        </p>
    </div>

    <form method="POST" action="{{ route('admin.invitations.accept.post', $invitation->token) }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="label">Full Name</label>
            <input 
                type="text" 
                id="name"
                name="name" 
                value="{{ old('name') }}"
                class="input @error('name') border-red-500 @enderror" 
                required 
                autofocus>
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input 
                type="password" 
                id="password"
                name="password" 
                class="input @error('password') border-red-500 @enderror" 
                required>
            @error('password')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-sm text-neutral-500 mt-1">Minimum 8 characters</p>
        </div>

        <div>
            <label for="password_confirmation" class="label">Confirm Password</label>
            <input 
                type="password" 
                id="password_confirmation"
                name="password_confirmation" 
                class="input" 
                required>
        </div>

        <button type="submit" class="btn-primary w-full">Create Account & Accept Invitation</button>
    </form>
</div>
@endsection
