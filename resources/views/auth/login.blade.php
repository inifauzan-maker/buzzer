<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login - SIVMI</title>
        <style>
            :root {
                --bg: #f4f1ea;
                --ink: #1f2937;
                --muted: #6b7280;
                --primary: #0a0a5c;
                --secondary: #c20f31;
                --accent-yellow: #c6bb0c;
                --accent-orange: #ff7e24;
                --accent: var(--primary);
                --card: #fff8f0;
                --border: #e2e8f0;
                --shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
                background: radial-gradient(circle at top, #f9f2e6, #d7efe8);
                color: var(--ink);
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 24px;
            }
            .card {
                background: var(--card);
                padding: 28px;
                border-radius: 18px;
                box-shadow: var(--shadow);
                border: 1px solid rgba(10, 10, 92, 0.18);
                width: min(420px, 100%);
            }
            h1 {
                margin-top: 0;
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
            }
            .muted { color: var(--muted); font-size: 14px; }
            label { font-size: 13px; color: var(--muted); }
            input {
                width: 100%;
                padding: 10px 12px;
                margin-top: 6px;
                border-radius: 10px;
                border: 1px solid var(--border);
                font-family: inherit;
                font-size: 14px;
            }
            .input-wrap {
                position: relative;
            }
            .input-wrap input {
                padding-right: 42px;
            }
            .toggle-password {
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                border: none;
                background: transparent;
                cursor: pointer;
                padding: 4px;
                color: var(--muted);
            }
            .toggle-password svg {
                width: 18px;
                height: 18px;
            }
            .form {
                display: grid;
                gap: 14px;
                margin-top: 16px;
            }
            .button {
                background: var(--accent);
                color: #fff;
                border: none;
                padding: 10px 16px;
                border-radius: 10px;
                font-weight: 600;
                cursor: pointer;
            }
            .error {
                background: #fee2e2;
                color: #991b1b;
                border-radius: 10px;
                padding: 10px 12px;
                font-size: 13px;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Login SIVMI</h1>
            <p class="muted">Masuk untuk mengelola aktivitas tim buzzer marketing.</p>

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form class="form" method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div>
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input id="password" name="password" type="password" required>
                        <button class="toggle-password" type="button" data-toggle-password aria-label="Lihat password">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="currentColor" d="M12 5c5 0 9.27 3.11 11 7-1.73 3.89-6 7-11 7S2.73 15.89 1 12c1.73-3.89 6-7 11-7Zm0 2C8.13 7 4.59 9.27 3.06 12 4.59 14.73 8.13 17 12 17s7.41-2.27 8.94-5C19.41 9.27 15.87 7 12 7Zm0 2.5A2.5 2.5 0 1 1 9.5 12 2.5 2.5 0 0 1 12 9.5Z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <label>
                    <input type="checkbox" name="remember" value="1"> Ingat saya
                </label>
                <button class="button" type="submit">Masuk</button>
            </form>
        </div>
        <script>
            const toggleButton = document.querySelector('[data-toggle-password]');
            const passwordInput = document.getElementById('password');
            if (toggleButton && passwordInput) {
                toggleButton.addEventListener('click', () => {
                    const isHidden = passwordInput.type === 'password';
                    passwordInput.type = isHidden ? 'text' : 'password';
                    toggleButton.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Lihat password');
                });
            }
        </script>
    </body>
</html>
