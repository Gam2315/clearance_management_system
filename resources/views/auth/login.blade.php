<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{config('app.name')}}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{asset('assets/images/logo/spup-favicon.ico')}}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Core css -->
    <link href="{{asset('assets/css/app.min.css')}}" rel="stylesheet">

    <!-- Login Page Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: url('{{asset('assets/images/others/bg-osa.jpeg')}}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .login-container {
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            padding: 50px 40px;
            text-align: center;
        }

        .university-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .login-subtitle {
            font-size: 1.1rem;
            color: #10b981;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-description {
            font-size: 0.95rem;
            color: #6b7280;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: white;
            color: #111827;
        }

        .form-control:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .login-btn {
            width: 100%;
            background: #10b981;
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            background: #059669;
        }

        .login-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }

        .footer-text {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 20px;
        }

        .alert {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-danger {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        @media (max-width: 640px) {
            .login-card {
                padding: 40px 30px;
            }

            .login-title {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <img src="{{asset('assets/images/logo/screen-logo.png')}}" alt="SPUP Logo" class="university-logo">

            <h1 class="login-title">Welcome Back</h1>
            <h2 class="login-subtitle">Clearance Management System</h2>
            <p class="login-description">
                Access your clearance management system account.
            </p>

            <!-- Alert Messages -->
            @include('alert.alert_message')
            @include('alert.alert_danger')

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- User ID Field -->
                <div class="form-group">
                    <label for="login" class="form-label">Enter User ID</label>
                    <input type="text"
                           name="login"
                           id="login"
                           class="form-control"
                           placeholder="Enter User ID"
                           required
                           autofocus
                           autocomplete="username">
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password"
                           name="password"
                           id="password"
                           class="form-control"
                           placeholder="Enter your password"
                           required
                           autocomplete="current-password">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="login-btn">
                    Sign In
                </button>
            </form>

            <!-- Footer -->
            <p class="footer-text">
                © {{now()->year}} St. Paul University Philippines. All rights reserved.
            </p>
        </div>
    </div>
    <!-- Core Vendors JS -->
    <script src="{{asset('assets/js/vendors.min.js')}}"></script>

    <!-- Core JS -->
    <script src="{{asset('assets/js/app.min.js')}}"></script>

</body>

</html>