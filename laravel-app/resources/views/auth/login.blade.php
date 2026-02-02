@extends('layouts.traxtar')

@section('content')
<h1 class="text-2xl font-bold mb-6">Login</h1>

@if(session('error'))
  <div class="alert mb-4">{{ session('error') }}</div>
@endif

@if($errors->any())
  <div class="alert mb-4">
    <ul class="list-disc list-inside">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('login') }}" class="max-w-md space-y-4">
  @csrf
  <div>
    <label class="label">Email</label>
    <input class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
  </div>
  <div>
    <label class="label">Password</label>
    <input class="input" type="password" name="password" required autocomplete="current-password">
  </div>
  <button class="btn-primary" type="submit">Sign in</button>
</form>
@endsection
