<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Barang Masuk</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #1f2937;
            background:
                radial-gradient(circle at top left, rgba(153, 27, 27, 0.14), transparent 32%),
                linear-gradient(135deg, #fff7f7 0%, #f8fafc 45%, #ffffff 100%);
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        .login-wrapper::before,
        .login-wrapper::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            filter: blur(10px);
            opacity: 0.65;
            z-index: 0;
        }

        .login-wrapper::before {
            width: 280px;
            height: 280px;
            background: rgba(153, 27, 27, 0.10);
            top: -90px;
            right: -80px;
        }

        .login-wrapper::after {
            width: 220px;
            height: 220px;
            background: rgba(190, 18, 60, 0.08);
            bottom: -70px;
            left: -60px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(229, 231, 235, 0.9);
            border-radius: 24px;
            padding: 36px;
            box-shadow: 0 24px 70px rgba(31, 41, 55, 0.12);
            backdrop-filter: blur(16px);
            position: relative;
            z-index: 1;
            animation: fadeUp 0.45s ease-out;
        }

        .brand-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 18px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #7f1d1d, #be123c);
            color: #ffffff;
            box-shadow: 0 14px 30px rgba(127, 29, 29, 0.25);
        }

        .login-title {
            margin: 0;
            font-size: 26px;
            line-height: 1.25;
            font-weight: 800;
            text-align: center;
            letter-spacing: -0.03em;
            color: #111827;
        }

        .login-subtitle {
            margin: 10px 0 28px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error ul {
            margin: 0;
            padding-left: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #374151;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .form-input {
            width: 100%;
            height: 48px;
            padding: 12px 14px 12px 44px;
            border: 1px solid #d1d5db;
            border-radius: 14px;
            background: #ffffff;
            color: #111827;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .form-input:focus {
            border-color: #be123c;
            box-shadow: 0 0 0 4px rgba(190, 18, 60, 0.10);
            background: #fffafa;
        }

        .input-wrapper:focus-within .input-icon {
            color: #be123c;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 4px 0 24px;
        }

        .remember-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #4b5563;
            font-size: 14px;
            cursor: pointer;
            user-select: none;
        }

        .remember-label input {
            width: 16px;
            height: 16px;
            accent-color: #991b1b;
            cursor: pointer;
        }

        .login-button {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #7f1d1d, #be123c);
            color: #ffffff;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.01em;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(127, 29, 29, 0.22);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .login-button:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 18px 36px rgba(127, 29, 29, 0.28);
        }

        .login-button:active {
            transform: translateY(0);
            box-shadow: 0 10px 20px rgba(127, 29, 29, 0.20);
        }

        .login-footer {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .login-wrapper {
                padding: 18px;
            }

            .login-card {
                padding: 28px 22px;
                border-radius: 20px;
            }

            .login-title {
                font-size: 23px;
            }

            .login-subtitle {
                font-size: 13px;
                margin-bottom: 24px;
            }
        }
    </style>
</head>
<body>
    <main class="login-wrapper">
        <div class="login-card">
            <div class="brand-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 7.5L12 3L20 7.5V16.5L12 21L4 16.5V7.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M12 12L20 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M12 12V21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M12 12L4 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>

            <h1 class="login-title">
                Aplikasi Barang Masuk
            </h1>

            <p class="login-subtitle">
                Sistem pencatatan barang masuk<br>
                PT. Samsudi Indoniaga Sedaya
            </p>

            @if ($errors->any())
                <div class="alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.process') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="username" class="form-label">
                        Username
                    </label>

                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M20 21C20 17.6863 16.4183 15 12 15C7.58172 15 4 17.6863 4 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M12 12C14.4853 12 16.5 9.98528 16.5 7.5C16.5 5.01472 14.4853 3 12 3C9.51472 3 7.5 5.01472 7.5 7.5C7.5 9.98528 9.51472 12 12 12Z" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </span>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            placeholder="Masukkan username"
                            class="form-input"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        Password
                    </label>

                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 10V8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8V10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M6.5 10H17.5C18.6046 10 19.5 10.8954 19.5 12V19C19.5 20.1046 18.6046 21 17.5 21H6.5C5.39543 21 4.5 20.1046 4.5 19V12C4.5 10.8954 5.39543 10 6.5 10Z" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M12 15V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="Masukkan password"
                            class="form-input"
                        >
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" value="1">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="login-button">
                    Login
                </button>
            </form>

            <div class="login-footer">
                © {{ date('Y') }} PT. Samsudi Indoniaga Sedaya
            </div>
        </div>
    </main>
</body>
</html>