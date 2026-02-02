@extends('layouts.traxtar')

@section('content')
<div class="py-6">
    <h1 class="text-2xl font-bold mb-6">Verify Your Email Address</h1>

    <div class="card p-6 max-w-md">
        <div class="mb-4 text-sm text-neutral-600">
            Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 border border-green-200 rounded p-3">
                A new verification link has been sent to the email address you provided in your profile settings.
            </div>
        @endif

        <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-primary">
                    Resend Verification Email
                </button>
            </form>

            <div class="flex items-center gap-4">
                <a href="{{ route('customer.profile') }}" class="text-sm text-neutral-600 hover:text-neutral-900 underline">
                    Edit Profile
                </a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-neutral-600 hover:text-neutral-900 underline">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
