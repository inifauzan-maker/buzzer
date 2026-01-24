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
            .menu-shell::before,
            .menu-shell::after {
                content: "";
                position: absolute;
                width: 180px;
                height: 180px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(255, 106, 168, 0.25), transparent 70%);
                filter: blur(2px);
                z-index: -1;
                animation: drift 8s ease-in-out infinite;
            }
            .menu-shell::before {
                top: -40px;
                left: -30px;
            }
            .menu-shell::after {
                bottom: -50px;
                right: -40px;
                animation-delay: -3s;
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
                animation: fade-up 0.8s ease both;
            }
            .menu-subtitle {
                color: var(--muted);
                font-size: 14px;
                margin-bottom: 28px;
                animation: fade-up 0.8s ease both;
                animation-delay: 0.12s;
            }
            .menu-grid {
                display: grid;
                grid-template-columns: repeat(6, minmax(130px, 140px));
                gap: 18px;
                justify-content: center;
                justify-items: center;
                animation: fade-up 0.8s ease both;
                animation-delay: 0.2s;
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
                animation: card-rise 0.7s ease both;
            }
            .menu-card:hover {
                transform: translateY(-4px) scale(1.02);
                box-shadow: 0 20px 50px rgba(64, 255, 220, 0.3);
            }
            .menu-card:focus-visible {
                outline: 2px solid rgba(248, 245, 241, 0.9);
                outline-offset: 4px;
            }
            .menu-card:nth-child(1) { animation-delay: 0.25s; }
            .menu-card:nth-child(2) { animation-delay: 0.32s; }
            .menu-card:nth-child(3) { animation-delay: 0.39s; }
            .menu-card:nth-child(4) { animation-delay: 0.46s; }
            .menu-card:nth-child(5) { animation-delay: 0.53s; }
            .menu-card:nth-child(6) { animation-delay: 0.6s; }
            .menu-card:nth-child(7) { animation-delay: 0.67s; }
            .menu-card:nth-child(8) { animation-delay: 0.74s; }
            .card-content {
                display: grid;
                gap: 8px;
                place-items: center;
            }
            .card-label {
                display: block;
                line-height: 1.1;
                text-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            }
            .card-icon {
                width: 34px;
                height: 34px;
                filter: drop-shadow(0 6px 10px rgba(0, 0, 0, 0.35));
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
            @keyframes fade-up {
                0% { opacity: 0; transform: translateY(12px); }
                100% { opacity: 1; transform: translateY(0); }
            }
            @keyframes card-rise {
                0% { opacity: 0; transform: translateY(16px) scale(0.96); }
                100% { opacity: 1; transform: translateY(0) scale(1); }
            }
            @keyframes drift {
                0% { transform: translate(0, 0); }
                50% { transform: translate(12px, -10px); }
                100% { transform: translate(0, 0); }
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
            @media (max-width: 980px) {
                .menu-grid {
                    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
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
                @php
                    $userRole = auth()->user()->role ?? 'guest';
                    $isAdmin = $userRole === 'superadmin';
                    $canProduk = in_array($userRole, ['superadmin', 'leader', 'staff'], true);
                    $canDataSiswa = $userRole === 'superadmin';
                @endphp
                @if ($isAdmin)
                    <div class="menu-card card-buzzer disabled" title="Segera hadir" aria-disabled="true">
                        <span class="card-content">
                            <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="white" d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z"/>
                            </svg>
                            <span>Dashboard</span>
                        </span>
                    </div>
                    <a class="menu-card card-buzzer" href="{{ route('dashboard') }}">
                        <span class="card-content">
                            <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="white" d="M3 10v4a1 1 0 0 0 1 1h2l4 3v-6l-4 3H4v-6h2l4 3V6l-4 3H4a1 1 0 0 0-1 1Zm12-2.5 5.5-2A1 1 0 0 1 22 6.4v11.2a1 1 0 0 1-1.5.9l-5.5-2V7.5Z"/>
                            </svg>
                            <span>Buzzer Marketing</span>
                        </span>
                    </a>
                    @if ($canProduk)
                        <a class="menu-card card-konten" href="{{ route('produk.index') }}">
                            <span class="card-content">
                                <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="white" d="M6 2h12a2 2 0 0 1 2 2v5h-2V4H6v16h7v2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm10 8 6 3-6 3v-2h-6v-2h6v-2Z"/>
                                </svg>
                                <span>Produk</span>
                            </span>
                        </a>
                    @else
                        <div class="menu-card card-konten disabled" title="Segera hadir" aria-disabled="true">
                            <span class="card-content">
                                <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="white" d="M6 2h12a2 2 0 0 1 2 2v5h-2V4H6v16h7v2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm10 8 6 3-6 3v-2h-6v-2h6v-2Z"/>
                                </svg>
                                <span>Produk</span>
                            </span>
                        </div>
                    @endif
                    @if ($canDataSiswa)
                        <a class="menu-card card-konten" href="{{ route('data-siswa.index') }}">
                            <span class="card-content">
                                <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="white" d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/>
                                </svg>
                                <span>Data Siswa</span>
                            </span>
                        </a>
                    @else
                        <div class="menu-card card-konten disabled" title="Segera hadir" aria-disabled="true">
                            <span class="card-content">
                                <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="white" d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/>
                                </svg>
                                <span>Data Siswa</span>
                            </span>
                        </div>
                    @endif
                    <div class="menu-card card-ads disabled" title="Segera hadir" aria-disabled="true">
                        <span class="card-content">
                            <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="white" d="M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20Zm0 4a6 6 0 1 0 0 12 6 6 0 0 0 0-12Zm0 3a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z"/>
                            </svg>
                            <span class="card-label">Ads</span>
                            <span class="card-label">Iklan</span>
                        </span>
                    </div>
                    <div class="menu-card card-sosial disabled" title="Segera hadir" aria-disabled="true">
                        <span class="card-content">
                            <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="white" d="M7 6a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm10-2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm-5 10a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm-5-4 5 4m5-6-5 4m-4 2 5-4"/>
                            </svg>
                            <span>Media Sosial</span>
                        </span>
                    </div>
                    <div class="menu-card card-konten disabled" title="Segera hadir" aria-disabled="true">
                        <span class="card-content">
                            <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="white" d="M4 4h12l4 4v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm11 1v4h4l-4-4ZM6 12h12v2H6v-2Zm0 4h8v2H6v-2Z"/>
                            </svg>
                            <span>Konten Marketing</span>
                        </span>
                    </div>
                    <div class="menu-card card-konten disabled" title="Segera hadir" aria-disabled="true">
                        <span class="card-content">
                            <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="white" d="M4 6h10v2H4V6Zm0 5h16v2H4v-2Zm0 5h12v2H4v-2Z"/>
                            </svg>
                            <span>Leads</span>
                        </span>
                    </div>
                    <div class="menu-card card-sosial disabled" title="Segera hadir" aria-disabled="true">
                        <span class="card-content">
                            <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="white" d="M7 2h2v2h6V2h2v2h3a2 2 0 0 1 2 2v3H2V6a2 2 0 0 1 2-2h3V2Zm15 9v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9h20Zm-11 3H7v4h4v-4Zm6 0h-4v4h4v-4Z"/>
                            </svg>
                            <span>Event</span>
                        </span>
                    </div>
                @else
                    <a class="menu-card card-buzzer" href="{{ route('dashboard') }}">
                        <span class="card-content">
                            <svg class="card-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="white" d="M3 10v4a1 1 0 0 0 1 1h2l4 3v-6l-4 3H4v-6h2l4 3V6l-4 3H4a1 1 0 0 0-1 1Zm12-2.5 5.5-2A1 1 0 0 1 22 6.4v11.2a1 1 0 0 1-1.5.9l-5.5-2V7.5Z"/>
                            </svg>
                            <span>Buzzer Marketing</span>
                        </span>
                    </a>
                @endif
            </div>
        </div>
    </body>
</html>
