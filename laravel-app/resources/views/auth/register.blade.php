@extends('layouts.traxtar')

@section('content')
<h1 class="text-2xl font-bold mb-6">Register</h1>

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

<form method="POST" action="{{ route('register') }}" class="max-w-md space-y-4">
  @csrf
  <div>
    <label class="label">Name</label>
    <input class="input" type="text" name="name"
           value="{{ old('name') }}" 
           required autocomplete="name">
  </div>
  <div>
    <label class="label">Email</label>
    <input class="input" type="email" name="email"
           value="{{ old('email') }}" 
           required autocomplete="email">
  </div>
  <div>
    <label class="label">Password</label>
    <input class="input" type="password" name="password"
           required autocomplete="new-password">
  </div>
  <div>
    <label class="label">Confirm Password</label>
    <input class="input" type="password" name="password_confirmation"
           required autocomplete="new-password">
  </div>
  <div>
    <label class="label">Role</label>
    <select class="input" name="role" required>
      <option value="customer" {{ old('role') === 'customer' ? 'selected' : '' }}>Customer</option>
      <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (for authorized users only)</option>
    </select>
  </div>
  <button class="btn-primary" type="submit">Create account</button>
</form>
@endsection
