@extends('layouts.app')

@section('title', 'Login - Galon App')

@section('styles')
<style>
    body {
        background-color: #f0f2f5;
        background-image: 
            radial-gradient(circle at 100% 100%, #ffffff33 0, #ffffff33 3px, transparent 3px),
            radial-gradient(circle at 0 0, #ffffff33 0, #ffffff33 3px, transparent 3px);
        background-size: 40px 40px;
        position: relative;
        overflow: hidden;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #0061f233 0%, #00ba8833 100%);
        z-index: 0;
    }

    /* Decorative Elements */
    .background-shapes::before {
        content: '';
        position: fixed;
        width: 400px;
        height: 400px;
        background: #0061f2;
        border-radius: 50%;
        top: -200px;
        right: -200px;
        opacity: 0.1;
    }

    .background-shapes::after {
        content: '';
        position: fixed;
        width: 300px;
        height: 300px;
        background: #00ba88;
        border-radius: 50%;
        bottom: -150px;
        left: -150px;
        opacity: 0.1;
    }

    .login-section {
        min-height: calc(100vh - 180px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
        z-index: 1;
    }

    .login-container {
        width: 100%;
        max-width: 420px;
        margin: auto;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .login-header {
        background: linear-gradient(135deg, #0061f2 0%, #0056d6 100%);
        padding: 30px;
        text-align: center;
        color: white;
    }

    .login-header h1 {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .login-header p {
        margin: 5px 0 0;
        opacity: 0.9;
    }

    .login-body {
        padding: 30px;
    }

    .form-floating {
        margin-bottom: 20px;
    }

    .form-floating > .form-control {
        padding: 15px;
        height: 60px;
        border: 2px solid rgba(233, 236, 239, 0.8);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.9);
    }

    .form-floating > label {
        padding: 15px;
    }

    .form-floating > .form-control:focus {
        border-color: #0061f2;
        box-shadow: 0 0 0 4px rgba(0, 97, 242, 0.1);
        background: white;
    }

    .form-check {
        margin: 20px 0;
    }

    .form-check-input:checked {
        background-color: #0061f2;
        border-color: #0061f2;
    }

    .btn-login {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #0061f2 0%, #0056d6 100%);
        border: none;
        border-radius: 12px;
        font-weight: 500;
        font-size: 16px;
        transition: all 0.3s ease;
        color: white;
    }

    .btn-login:hover {
        background: linear-gradient(135deg, #0056d6 0%, #004cbd 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 97, 242, 0.2);
    }

    .alert {
        border: none;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
        backdrop-filter: blur(10px);
    }

    .alert-danger {
        background: rgba(229, 62, 62, 0.1);
        color: #e53e3e;
        border: 1px solid rgba(229, 62, 62, 0.2);
    }

    .alert-success {
        background: rgba(56, 161, 105, 0.1);
        color: #38a169;
        border: 1px solid rgba(56, 161, 105, 0.2);
    }

    /* Animation */
    .login-card {
        animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
        .login-card {
            margin: 10px;
        }
        
        .login-header {
            padding: 20px;
        }

        .login-body {
            padding: 20px;
        }
    }
</style>
@endsection

@section('content')
<div class="background-shapes"></div>
<section class="login-section">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Galon App</h1>
                <p>Aero Tripandawa</p>
            </div>

            <div class="login-body">
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
                    <div class="form-floating">
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email"
                               name="email" 
                               placeholder="Email"
                               value="{{ old('email') }}" 
                               required 
                               autofocus>
                        <label for="email">Email</label>
                    </div>

                    <div class="form-floating">
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password"
                               name="password" 
                               placeholder="Password"
                               required>
                        <label for="password">Password</label>
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

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection