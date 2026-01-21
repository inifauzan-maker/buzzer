<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Menu SIVMI Marketing</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@400;600;700&display=swap');
            :root {
                --bg-1: #0d0507;
                --bg-2: #2a0b10;
                --ink: #f8f5f1;
                --muted: rgba(248, 245, 241, 0.65);
                --glow: 0 18px 40px rgba(255, 91, 153, 0.25);
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                font-family: "Space Grotesk", "Trebuchet MS", sans-serif;
                background: radial-gradient(circle at top, #321014 0%, var(--bg-1) 45%, #050202 100%);
                color: var(--ink);
                display: grid;
                place-items: center;
                padding: 32px 18px 48px;
            }
            .menu-shell {
                width: min(980px, 100%);
                text-align: center;
                position: relative;
            }
            .menu-actions {
                position: absolute;
                top: 0;
                right: 0;
                display: flex;
                gap: 10px;
                align-items: center;
                font-size: 13px;
                color: var(--muted);
            }
            .menu-actions form {
                margin: 0;
            }
            .menu-actions button {
                background: transparent;
                color: var(--ink);
                border: 1px solid rgba(248, 245, 241, 0.3);
                padding: 6px 12px;
                border-radius: 999px;
                cursor: pointer;
                font-family: inherit;
                font-size: 12px;
            }
            .menu-title {
                font-family: "Bebas Neue", "Impact", sans-serif;
                font-size: clamp(36px, 5vw, 56px);
                letter-spacing: 0.12em;
                text-transform: uppercase;
                margin: 12px 0 8px;
            }
            .menu-subtitle {
                color: var(--muted);
                font-size: 14px;
                margin-bottom: 28px;
            }
            .menu-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
                gap: 18px;
                justify-items: center;
            }
            .menu-card {
                width: 140px;
                aspect-ratio: 1 / 1;
                border-radius: 16px;
                display: grid;
                place-items: center;
                text-transform: uppercase;
                font-size: 12px;
                letter-spacing: 0.08em;
                font-weight: 700;
                text-align: center;
                padding: 14px;
                color: #fff;
                text-decoration: none;
                box-shadow: var(--glow);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            .menu-card:hover {
                transform: translateY(-4px) scale(1.02);
                box-shadow: 0 20px 50px rgba(64, 255, 220, 0.3);
            }
            .card-buzzer {
                background: linear-gradient(145deg, #ff6aa8, #ff7a57);
            }
            .card-konten {
                background: linear-gradient(145deg, #5f6bff, #2ac7ff);
            }
            .card-ads {
                background: linear-gradient(145deg, #f725ff, #6d5bff);
            }
            .card-sosial {
                background: linear-gradient(145deg, #ff6f6f, #ffd65a);
            }
            .card-ghost {
                background: linear-gradient(145deg, #7b2cff, #36d1ff);
                opacity: 0.75;
            }
            .menu-card.disabled {
                cursor: default;
                opacity: 0.6;
                pointer-events: none;
                box-shadow: 0 16px 30px rgba(0, 0, 0, 0.4);
            }
            @media (max-width: 720px) {
                .menu-actions {
                    position: static;
                    justify-content: center;
                    margin-bottom: 12px;
                }
                .menu-card {
                    width: 120px;
                    font-size: 11px;
                }
            }
        </style>
    </head>
    <body>
        <div class="menu-shell">
            <div class="menu-actions">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>

            <div class="menu-title">SIVMI - MARKETING</div>
            <div class="menu-subtitle">Pilih modul kerja Anda.</div>

            <div class="menu-grid">
                <a class="menu-card card-buzzer" href="{{ route('dashboard') }}">Buzzer Marketing</a>
                <div class="menu-card card-konten disabled">Konten Marketing</div>
                <div class="menu-card card-ads disabled">Ads Iklan</div>
                <div class="menu-card card-sosial disabled">Media Sosial</div>
                <div class="menu-card card-ghost disabled">&nbsp;</div>
                <div class="menu-card card-ghost disabled">&nbsp;</div>
                <div class="menu-card card-ghost disabled">&nbsp;</div>
                <div class="menu-card card-ghost disabled">&nbsp;</div>
            </div>
        </div>
    </body>
</html>
