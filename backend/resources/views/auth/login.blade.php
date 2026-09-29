@extends('layouts.app')

@section('title', 'Sign In — Finclude-AI')

@section('header')
<header class="app-header" style="background: transparent; border-bottom: none; padding-top: 18px;">
    <div></div>

    <div class="header-actions">
        <button
            type="button"
            class="icon-btn"
            onclick="toggleTheme()"
            title="Toggle Dark/Light Mode"
        >
            <i
                class="fa-solid fa-sun theme-toggle-icon"
                style="color: var(--primary);"
            ></i>
        </button>
    </div>
</header>
@endsection


@section('content')

<main
    class="app-body"
    style="justify-content: center; padding-top: 0;"
>

    <div
        class="f-card glow-primary animate-fade-in"
        style="padding: 28px 24px; border-radius: 28px;"
    >

        <!-- Logo and Branding -->
        <div
            style="
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                margin-bottom: 24px;
            "
        >

            <div
                style="
                    background: var(--card-sub-bg);
                    padding: 12px;
                    border-radius: 20px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
                    margin-bottom: 14px;
                    border: 1px solid var(--border-color);
                "
            >
                <img
                    src="{{ asset('images/finclude_logo.png') }}"
                    alt="Finclude-AI"
                    style="
                        width: 52px;
                        height: 52px;
                        border-radius: 12px;
                        object-fit: cover;
                    "
                >
            </div>

            <h1
                class="title-xl"
                style="font-size: 1.85rem; margin-bottom: 6px;"
            >
                Finclude-AI
            </h1>

            <p
                class="text-subtext"
                style="
                    font-size: 0.88rem;
                    max-width: 320px;
                    line-height: 1.4;
                "
            >
                Alternative AI Credit Scoring & Micro-Lending
                <br>
                for Filipino Gig Workers
            </p>

        </div>


        <!-- Login Form -->
        <form
            id="loginForm"
            action="{{ route('login.submit') }}"
            method="POST"
        >
            @csrf

            <div class="form-group">

                <label
                    class="form-label"
                    for="identifier"
                >
                    Email or Username
                </label>

                <div class="input-wrapper">

                    <i class="fa-regular fa-user input-icon"></i>

                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        class="form-control"
                        placeholder="e.g. juan.delacruz@finclude.ph"
                        value="{{ old('identifier') }}"
                        required
                        autocomplete="username"
                    >

                </div>

            </div>


            <div
                class="form-group"
                style="margin-bottom: 6px;"
            >

                <label
                    class="form-label"
                    for="password"
                >
                    Password
                </label>

                <div class="input-wrapper">

                    <i class="fa-solid fa-lock input-icon"></i>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >

                    <button
                        type="button"
                        class="input-action"
                        onclick="togglePasswordVisibility('password', this)"
                        title="Show/Hide Password"
                    >
                        <i class="fa-regular fa-eye"></i>
                    </button>

                </div>

            </div>


            <!-- Forgot Password -->
            <div
                style="
                    display: flex;
                    justify-content: flex-end;
                    margin-bottom: 20px;
                "
            >

                <button
                    type="button"
                    onclick="openModal('forgotPasswordModal')"
                    style="
                        background: transparent;
                        border: none;
                        color: var(--primary);
                        font-size: 0.82rem;
                        font-weight: 600;
                        cursor: pointer;
                        text-decoration: none;
                    "
                >
                    Forgot password?
                </button>

            </div>


            <!-- Login Button -->
            <button
                type="submit"
                id="submitBtn"
                class="btn-primary"
                style="padding: 15px; font-size: 1rem;"
            >
                <span id="btnText">
                    Sign In to Dashboard
                </span>

                <i
                    id="btnSpinner"
                    class="fa-solid fa-circle-notch fa-spin"
                    style="display: none;"
                ></i>
            </button>

        </form>


        <!-- Registration Link -->
        <div
            style="
                text-align: center;
                margin-top: 20px;
                font-size: 0.88rem;
                color: var(--text-muted);
            "
        >

            Don't have an account yet?

            <a
                href="{{ route('register') }}"
                style="
                    color: var(--text-main);
                    font-weight: 700;
                    text-decoration: underline;
                    margin-left: 4px;
                "
            >
                Create Account
            </a>

        </div>

    </div>

</main>


<!-- Forgot Password / Verification Modal -->
<div
    id="forgotPasswordModal"
    class="modal-overlay"
>

    <div class="modal-sheet">

        <!-- Modal Header -->
        <div class="modal-header">

            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 12px;
                "
            >

                <div
                    style="
                        background: rgba(0, 229, 255, 0.15);
                        color: var(--primary);
                        padding: 10px;
                        border-radius: 12px;
                        font-size: 1.2rem;
                    "
                >
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <div>

                    <h3 class="title-md">
                        Reset Password
                    </h3>

                    <p
                        class="text-subtext"
                        style="font-size: 0.78rem;"
                    >
                        Verify via Gmail or SMS Code
                    </p>

                </div>

            </div>


            <button
                type="button"
                class="modal-close-btn"
                onclick="closeModal('forgotPasswordModal')"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <!-- Step 1: Request Verification Code -->
        <form
            id="requestCodeForm"
            onsubmit="handleRequestCode(event)"
        >

            @csrf

            <div class="form-group">

                <label
                    class="form-label"
                    for="reset_identifier"
                >
                    Registered Email or Mobile Number
                </label>

                <div class="input-wrapper">

                    <i class="fa-regular fa-envelope input-icon"></i>

                    <input
                        type="text"
                        id="reset_identifier"
                        class="form-control"
                        placeholder="e.g. juan@finclude.ph or 09123456789"
                        required
                    >

                </div>

            </div>


            <div
                style="
                    display: flex;
                    gap: 10px;
                    margin-top: 20px;
                "
            >

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="closeModal('forgotPasswordModal')"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    id="sendCodeBtn"
                    class="btn-primary"
                >
                    Send Verification Code
                </button>

            </div>

        </form>


        <!-- Step 2: Verify Code and Update Password -->
        <form
            id="verifyAndUpdateForm"
            onsubmit="handleVerifyAndUpdate(event)"
            style="display: none;"
        >

            @csrf

            <input
                type="hidden"
                id="verified_identifier"
            >


            <!-- Verification Code -->
            <div class="form-group">

                <label
                    class="form-label"
                    for="verification_code"
                >
                    Enter Verification Code
                </label>

                <div class="input-wrapper">

                    <i class="fa-solid fa-key input-icon"></i>

                    <input
                        type="text"
                        id="verification_code"
                        class="form-control"
                        placeholder="Enter 6-digit code"
                        maxlength="6"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        required
                    >

                </div>

                <small
                    class="text-subtext"
                    style="font-size: 0.75rem;"
                >
                    Check your Gmail or SMS for the verification code.
                </small>

            </div>


            <!-- New Password -->
            <div class="form-group">

                <label
                    class="form-label"
                    for="new_password"
                >
                    New Password
                </label>

                <div class="input-wrapper">

                    <i class="fa-solid fa-lock input-icon"></i>

                    <input
                        type="password"
                        id="new_password"
                        class="form-control"
                        placeholder="Minimum 6 characters"
                        minlength="6"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="button"
                        class="input-action"
                        onclick="togglePasswordVisibility('new_password', this)"
                        title="Show/Hide Password"
                    >
                        <i class="fa-regular fa-eye"></i>
                    </button>

                </div>

            </div>


            <!-- Confirm New Password -->
            <div
                class="form-group"
                style="margin-bottom: 20px;"
            >

                <label
                    class="form-label"
                    for="confirm_password"
                >
                    Confirm New Password
                </label>

                <div class="input-wrapper">

                    <i class="fa-solid fa-check-double input-icon"></i>

                    <input
                        type="password"
                        id="confirm_password"
                        class="form-control"
                        placeholder="Re-type new password"
                        minlength="6"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="button"
                        class="input-action"
                        onclick="togglePasswordVisibility('confirm_password', this)"
                        title="Show/Hide Password"
                    >
                        <i class="fa-regular fa-eye"></i>
                    </button>

                </div>

            </div>


            <!-- Verification Buttons -->
            <div
                style="
                    display: flex;
                    gap: 10px;
                "
            >

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="resetPasswordStep()"
                >
                    Back
                </button>

                <button
                    type="submit"
                    id="updateBtn"
                    class="btn-primary"
                >
                    Verify & Update Password
                </button>

            </div>

        </form>

    </div>

</div>

@endsection


@push('scripts')

<script>

    /*
    |--------------------------------------------------------------------------
    | Password Visibility
    |--------------------------------------------------------------------------
    */

    function togglePasswordVisibility(fieldId, btn) {

        const input = document.getElementById(fieldId);
        const icon = btn.querySelector('i');

        if (!input || !icon) {
            return;
        }

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


    /*
    |--------------------------------------------------------------------------
    | Step 1: Send Verification Code
    |--------------------------------------------------------------------------
    */

    async function handleRequestCode(e) {

        e.preventDefault();

        const identifier =
            document
                .getElementById('reset_identifier')
                .value
                .trim();

        const sendBtn =
            document.getElementById('sendCodeBtn');


        if (!identifier) {

            alert(
                'Please enter your registered email, username, name, or mobile number.'
            );

            return;
        }


        sendBtn.disabled = true;
        sendBtn.innerText = 'Sending Code...';


        try {

            const res = await fetch(
                "{{ route('password.code.send') }}",
                {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',

                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,

                        'Accept': 'application/json'
                    },

                    body: JSON.stringify({
                        identifier: identifier
                    })
                }
            );


            const data = await res.json();


            if (res.ok) {

                document.getElementById(
                    'verified_identifier'
                ).value = identifier;


                document.getElementById(
                    'requestCodeForm'
                ).style.display = 'none';


                document.getElementById(
                    'verifyAndUpdateForm'
                ).style.display = 'block';


                showToast(
                    data.message ||
                    'Verification code sent to your Gmail/SMS!',
                    'success'
                );


            } else {

                if (data.errors) {

                    const errors =
                        Object.values(data.errors)
                            .flat()
                            .join('\n');

                    alert(errors);

                } else {

                    alert(
                        data.message ||
                        'Account not found or invalid identifier.'
                    );
                }
            }


        } catch (err) {

            console.error(
                'Password reset code error:',
                err
            );

            alert(
                'Network error while sending verification code.'
            );


        } finally {

            sendBtn.disabled = false;
            sendBtn.innerText =
                'Send Verification Code';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Step 2: Verify Code and Update Password
    |--------------------------------------------------------------------------
    */

    async function handleVerifyAndUpdate(e) {

        e.preventDefault();


        const identifier =
            document
                .getElementById('verified_identifier')
                .value
                .trim();

        const code =
            document
                .getElementById('verification_code')
                .value
                .trim();

        const newPass =
            document.getElementById('new_password').value;

        const confirmPass =
            document.getElementById('confirm_password').value;


        /*
        |--------------------------------------------------------------------------
        | Frontend Validation
        |--------------------------------------------------------------------------
        */

        if (!identifier) {

            alert(
                'Missing account identifier. Please request a new verification code.'
            );

            return;
        }


        if (!/^\d{6}$/.test(code)) {

            alert(
                'Please enter the 6-digit verification code.'
            );

            return;
        }


        if (newPass.length < 6) {

            alert(
                'Password must be at least 6 characters.'
            );

            return;
        }


        if (newPass !== confirmPass) {

            alert(
                'Passwords do not match. Please ensure both fields are identical.'
            );

            return;
        }


        const updateBtn =
            document.getElementById('updateBtn');

        updateBtn.disabled = true;
        updateBtn.innerText = 'Verifying...';


        try {

            const res = await fetch(
                "{{ route('password.code.verify') }}",
                {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',

                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,

                        'Accept': 'application/json'
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | IMPORTANT:
                    | Laravel's "confirmed" validation rule requires
                    | password_confirmation.
                    |--------------------------------------------------------------------------
                    */

                    body: JSON.stringify({

                        identifier: identifier,

                        code: code,

                        password: newPass,

                        password_confirmation: confirmPass
                    })
                }
            );


            const data = await res.json();


            if (res.ok) {

                closeModal(
                    'forgotPasswordModal'
                );


                showToast(
                    data.message ||
                    'Password successfully updated!',
                    'success'
                );


                /*
                |--------------------------------------------------------------------------
                | Put identifier and new password
                | into the login form
                |--------------------------------------------------------------------------
                */

                const loginIdentifier =
                    document.getElementById(
                        'identifier'
                    );

                const loginPassword =
                    document.getElementById(
                        'password'
                    );


                if (loginIdentifier) {

                    loginIdentifier.value =
                        identifier;
                }


                if (loginPassword) {

                    loginPassword.value =
                        newPass;
                }


                resetPasswordStep();


            } else {

                /*
                |--------------------------------------------------------------------------
                | Handle Laravel Validation Errors
                |--------------------------------------------------------------------------
                */

                if (data.errors) {

                    const errors =
                        Object.values(data.errors)
                            .flat()
                            .join('\n');

                    alert(errors);

                } else {

                    alert(
                        data.message ||
                        'Invalid or expired verification code.'
                    );
                }
            }


        } catch (err) {

            console.error(
                'Password verification error:',
                err
            );

            alert(
                'Network error during verification.'
            );


        } finally {

            updateBtn.disabled = false;

            updateBtn.innerText =
                'Verify & Update Password';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Password Modal to Step 1
    |--------------------------------------------------------------------------
    */

    function resetPasswordStep() {

        const requestForm =
            document.getElementById(
                'requestCodeForm'
            );

        const verifyForm =
            document.getElementById(
                'verifyAndUpdateForm'
            );


        if (requestForm) {

            requestForm.style.display =
                'block';
        }


        if (verifyForm) {

            verifyForm.style.display =
                'none';
        }


        const resetIdentifier =
            document.getElementById(
                'reset_identifier'
            );

        const verifiedIdentifier =
            document.getElementById(
                'verified_identifier'
            );

        const verificationCode =
            document.getElementById(
                'verification_code'
            );

        const newPassword =
            document.getElementById(
                'new_password'
            );

        const confirmPassword =
            document.getElementById(
                'confirm_password'
            );


        if (resetIdentifier) {

            resetIdentifier.value = '';
        }


        if (verifiedIdentifier) {

            verifiedIdentifier.value = '';
        }


        if (verificationCode) {

            verificationCode.value = '';
        }


        if (newPassword) {

            newPassword.value = '';
        }


        if (confirmPassword) {

            confirmPassword.value = '';
        }
    }

</script>

@endpush