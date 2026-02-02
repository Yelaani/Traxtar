@extends('layouts.traxtar')

@section('content')
<div class="py-6">
    @if(auth()->user()->isCustomer())
        <a class="btn mb-4 inline-block" href="{{ route('customer.dashboard') }}">← Back to dashboard</a>
    @endif
    <h1 class="text-2xl font-bold mb-6">Profile</h1>

    <div class="space-y-6">
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            @livewire('profile.update-profile-information-form')

            <div class="border-t border-neutral-200 my-6"></div>
        @endif

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            <div>
                @livewire('profile.update-password-form')
            </div>

            <div class="border-t border-neutral-200 my-6"></div>
        @endif

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <div>
                @livewire('profile.two-factor-authentication-form')
            </div>

            <div class="border-t border-neutral-200 my-6"></div>
        @endif

        <div>
            @livewire('profile.logout-other-browser-sessions-form')
        </div>

        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
            <div class="border-t border-neutral-200 my-6"></div>

            <div>
                @livewire('profile.delete-user-form')
            </div>
        @endif
    </div>
</div>
@endsection
