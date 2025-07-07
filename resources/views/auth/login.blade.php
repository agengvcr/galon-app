@extends('layouts.app')

@section('title', 'Login - Galon App')

@section('styles')
<style>
    body {
        background: #f8fafc;
    }
    .login-simple-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-simple-box {
        width: 100%;
        max-width: 350px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 32px 24px 24px 24px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.10), 0 1.5px 4px rgba(0,0,0,0.06);
    }
    .login-simple-box h1 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 8px;
        text-align: center;
    }
    .login-simple-box p {
        text-align: center;
        color: #6b7280;
        margin-bottom: 24px;
        font-size: 0.95rem;
    }
    .form-group {
        margin-bottom: 16px;
    }
    .form-label {
        display: block;
        margin-bottom: 6px;
        font-size: 0.97rem;
        color: #374151;
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 1rem;
        background: #f9fafb;
    }
    .form-control:focus {
        border-color: #2563eb;
        outline: none;
        background: #fff;
    }
    .form-check {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
    }
    .form-check-input {
        margin-right: 8px;
    }
    .btn-login {
        width: 100%;
        padding: 10px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-weight: 500;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-login:hover {
        background: #1d4ed8;
    }
    .alert {
        border-radius: 5px;
        padding: 10px 14px;
        margin-bottom: 16px;
        font-size: 0.97rem;
    }
    .alert-danger {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .alert-success {
        background: #d1fae5;
        color: #047857;
        border: 1px solid #6ee7b7;
    }
</style>
@endsection

@section('content')
<div class="login-simple-container">
    <div class="login-simple-box">
        <h1>Galon App</h1>
        <p>Aero Tripandawa</p>

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email"
                       name="email" 
                       placeholder="Email"
                       value="{{ old('email') }}" 
                       required 
                       autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password"
                       name="password" 
                       placeholder="Password"
                       required>
            </div>

            <div class="form-check">
                <input type="checkbox" 
                       class="form-check-input" 
                       id="remember" 
                       name="remember" 
                       {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">
                    Ingat Saya
                </label>
            </div>

            <button type="submit" class="btn-login">
                Masuk
            </button>
        </form>
    </div>
</div>
@endsection