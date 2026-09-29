@extends('layouts.app')

@section('title', 'Register — Finclude-AI')

@section('header')
<header class="app-header">
    <a href="{{ route('login') }}" class="icon-btn" title="Back to Login">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div style="font-weight: 700; font-size: 1rem;">Create Account</div>
    <div class="header-actions">
        <button type="button" class="icon-btn" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
            <i class="fa-solid fa-sun theme-toggle-icon" style="color: var(--primary);"></i>
        </button>
    </div>
</header>
@endsection

@section('content')
<main class="app-body">
    <!-- Header Card -->
    <div class="f-card glow-primary animate-fade-in" style="display: flex; align-items: center; gap: 14px; padding: 18px;">
        <img src="{{ asset('images/finclude_logo.png') }}" alt="Logo" style="width: 44px; height: 44px; border-radius: 12px; object-fit: cover;">
        <div>
            <h1 class="title-md" style="font-size: 1.1rem;">Gig Worker Registration</h1>
            <p class="text-subtext" style="font-size: 0.8rem;">Unlock AI-evaluated alternative credit limits & open banking</p>
        </div>
    </div>

    <!-- Registration Form -->
    <div class="f-card animate-fade-in">
        <form action="{{ route('register.submit') }}" method="POST" id="registerForm">
            @csrf

            <!-- Full Name -->
            <div class="form-group">
                <label class="form-label" for="name">Full Name (as shown in PhilSys / Gov ID)</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-user input-icon"></i>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Juan C. Dela Cruz" value="{{ old('name') }}" required>
                </div>
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="juan@gmail.com" value="{{ old('email') }}" required>
                </div>
            </div>

            <!-- Phone Number -->
            <div class="form-group">
                <label class="form-label" for="phone_number">Mobile Phone (Philippine Mobile)</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-phone input-icon"></i>
                    <input type="tel" id="phone_number" name="phone_number" class="form-control" placeholder="+63 917 123 4567" value="{{ old('phone_number') }}">
                </div>
            </div>

            <!-- Valid Philippine ID Type -->
            <div class="form-group">
                <label class="form-label" for="id_type">Valid Philippine Government ID</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-id-card input-icon"></i>
                    <select id="id_type" name="id_type" class="form-control" required>
                        <option value="Philippine National ID (PhilSys)" {{ old('id_type') == 'Philippine National ID (PhilSys)' ? 'selected' : '' }}>Philippine National ID (PhilSys)</option>
                        <option value="UMID / SSS ID" {{ old('id_type') == 'UMID / SSS ID' ? 'selected' : '' }}>UMID / SSS ID</option>
                        <option value="Driver's License" {{ old('id_type') == "Driver's License" ? 'selected' : '' }}>Driver's License</option>
                        <option value="Passport" {{ old('id_type') == 'Passport' ? 'selected' : '' }}>Philippine Passport</option>
                        <option value="PRC ID" {{ old('id_type') == 'PRC ID' ? 'selected' : '' }}>PRC Professional ID</option>
                        <option value="Postal ID" {{ old('id_type') == 'Postal ID' ? 'selected' : '' }}>Postal ID</option>
                    </select>
                </div>
            </div>

            <!-- ID Number -->
            <div class="form-group">
                <label class="form-label" for="id_number">ID Number / Serial</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-hashtag input-icon"></i>
                    <input type="text" id="id_number" name="id_number" class="form-control" placeholder="1234-5678-9012-3456" value="{{ old('id_number') }}">
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password">Create Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" minlength="6" required oninput="checkStrength(this.value)">
                    <button type="button" class="input-action" onclick="togglePasswordVisibility('password', this)">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                <!-- Strength Indicator -->
                <div class="strength-meter">
                    <div id="strengthBar" class="strength-bar"></div>
                </div>
                <div id="strengthLabel" style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px;">At least 6 characters</div>
            </div>

            <!-- Password Confirmation -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-check-double input-icon"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="••••••••" minlength="6" required>
                    <button type="button" class="input-action" onclick="togglePasswordVisibility('password_confirmation', this)">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-primary" style="padding: 15px;">
                <span>Create Account & Continue</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 0.88rem; color: var(--text-muted);">
            Already have an account? 
            <a href="{{ route('login') }}" style="color: var(--text-main); font-weight: 700; text-decoration: underline; margin-left: 4px;">
                Sign In
            </a>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function checkStrength(pass) {
        const bar = document.getElementById('strengthBar');
        const label = document.getElementById('strengthLabel');
        let strength = 0;

        if (pass.length >= 6) strength += 30;
        if (pass.length >= 8) strength += 20;
        if (/[A-Z]/.test(pass)) strength += 20;
        if (/[0-9]/.test(pass)) strength += 15;
        if (/[^A-Za-z0-9]/.test(pass)) strength += 15;

        bar.style.width = strength + '%';

        if (strength < 40) {
            bar.style.backgroundColor = 'var(--danger)';
            label.innerText = 'Weak password (minimum 6 chars)';
            label.style.color = 'var(--danger)';
        } else if (strength < 75) {
            bar.style.backgroundColor = 'var(--warning)';
            label.innerText = 'Medium strength';
            label.style.color = 'var(--warning)';
        } else {
            bar.style.backgroundColor = 'var(--success)';
            label.innerText = 'Strong password';
            label.style.color = 'var(--success)';
        }
    }
</script>
@endpush
