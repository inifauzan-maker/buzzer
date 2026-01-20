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
                --accent: #0f766e;
                --card: #fff8f0;
                --border: #e2e8f0;
                --shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: "Georgia", "Times New Roman", serif;
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
                border: 1px solid rgba(15, 118, 110, 0.1);
                width: min(420px, 100%);
            }
            h1 {
                margin-top: 0;
                font-family: "Trebuchet MS", "Lucida Grande", sans-serif;
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
                    <input id="password" name="password" type="password" required>
                </div>
                <label>
                    <input type="checkbox" name="remember" value="1"> Ingat saya
                </label>
                <button class="button" type="submit">Masuk</button>
            </form>
        </div>
    </body>
</html>
