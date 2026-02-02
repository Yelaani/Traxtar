@extends('layouts.traxtar')

@section('content')
<a class="btn mb-4 inline-block" href="{{ route('admin.dashboard') }}">← Back to Dashboard</a>

<h1 class="text-2xl font-bold mb-4">Analytics Dashboard</h1>

@livewire('analytics-dashboard')
@endsection
