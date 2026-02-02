<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'SIVMI Buzzer Marketing')</title>
        <style>
            :root {
                --bg: #f1f1f1;
                --ink: #1f2937;
                --muted: #6b7280;
                --primary: #0a0a5c;
                --secondary: #c20f31;
                --accent-yellow: #c6bb0c;
                --accent-orange: #ff7e24;
                --accent: var(--primary);
                --accent-dark: var(--secondary);
                --sidebar: #3f475d;
                --sidebar-dark: #32394d;
                --card: #ffffff;
                --border: #e2e8f0;
                --shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
                color: var(--ink);
                background: linear-gradient(140deg, #f6f2ea, #e3f1f0);
                min-height: 100vh;
            }
            a { color: inherit; text-decoration: none; }
            .app-shell {
                display: grid;
                grid-template-columns: 240px 1fr;
                min-height: 100vh;
            }
            .sidebar {
                background: linear-gradient(180deg, var(--sidebar), var(--sidebar-dark));
                color: #e8edf2;
                padding: 20px 18px;
                display: flex;
                flex-direction: column;
                gap: 18px;
            }
            .brand-block {
                text-transform: uppercase;
                letter-spacing: 0.2em;
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
            }
            .brand-title {
                font-weight: 700;
                font-size: 14px;
            }
            .brand-sub {
                font-size: 10px;
                color: rgba(232, 237, 242, 0.6);
            }
            .profile {
                background: rgba(255, 255, 255, 0.08);
                border-radius: 16px;
                padding: 16px;
                display: grid;
                gap: 10px;
                justify-items: center;
                text-align: center;
            }
            .avatar {
                width: 72px;
                height: 72px;
                border-radius: 50%;
                background: var(--accent);
                display: grid;
                place-items: center;
                font-weight: 700;
                color: #fff;
                font-size: 22px;
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
            }
            .badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 10px;
                border-radius: 999px;
                font-size: 11px;
                background: rgba(198, 187, 12, 0.25);
                color: #fff6c3;
                border: 1px solid rgba(198, 187, 12, 0.6);
            }
            .side-nav {
                display: grid;
                gap: 6px;
            }
            .nav-link {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 12px;
                border-radius: 10px;
                font-size: 14px;
                color: rgba(232, 237, 242, 0.85);
                transition: all 0.2s ease;
            }
            .nav-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: rgba(232, 237, 242, 0.4);
            }
            .nav-link.active,
            .nav-link:hover {
                background: rgba(10, 10, 92, 0.25);
                color: #ffffff;
            }
            .nav-link.active .nav-dot,
            .nav-link:hover .nav-dot {
                background: var(--accent);
            }
            .nav-badge {
                margin-left: auto;
                min-width: 18px;
                height: 18px;
                padding: 0 5px;
                border-radius: 999px;
                background: var(--secondary);
                color: #fff;
                font-size: 10px;
                font-weight: 700;
                display: grid;
                place-items: center;
                box-shadow: 0 0 0 2px rgba(63, 71, 93, 0.6);
            }
            .sidebar-footer {
                margin-top: auto;
                font-size: 12px;
                color: rgba(232, 237, 242, 0.6);
            }
            .main {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }
            .topbar {
                background: #ffffff;
                border-bottom: 1px solid var(--border);
                padding: 16px 26px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
            }
            .search {
                flex: 1;
                max-width: 420px;
                display: flex;
                align-items: center;
                gap: 8px;
                border: 1px solid var(--border);
                border-radius: 12px;
                padding: 6px 12px;
                background: #f9fafb;
            }
            .search input {
                border: none;
                outline: none;
                background: transparent;
                width: 100%;
                font-family: inherit;
                font-size: 14px;
            }
            .top-actions {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 13px;
                color: var(--muted);
            }
            .top-actions a {
                color: inherit;
                text-decoration: none;
            }
            .top-actions .profile-link {
                padding: 6px 10px;
                border-radius: 999px;
                border: 1px solid var(--border);
                background: #f9fafb;
                font-weight: 600;
            }
            .notif-label {
                font-size: 12px;
                color: #0b5f57;
                font-weight: 600;
            }
            .notif-btn {
                position: relative;
                width: 34px;
                height: 34px;
                border-radius: 50%;
                border: 1px solid var(--border);
                display: grid;
                place-items: center;
                background: #ffffff;
            }
            .notif-count {
                position: absolute;
                top: -6px;
                right: -6px;
                min-width: 18px;
                height: 18px;
                padding: 0 4px;
                border-radius: 999px;
                background: var(--secondary);
                color: #fff;
                font-size: 10px;
                font-weight: 700;
                display: grid;
                place-items: center;
                box-shadow: 0 0 0 2px #fff;
            }
            .subnav {
                background: #e9edf3;
                padding: 10px 26px;
                display: flex;
                flex-wrap: wrap;
                gap: 14px;
                font-size: 13px;
                color: #3f475d;
                justify-content: center;
                text-align: center;
            }
            .subnav a,
            .subnav span {
                font-weight: 600;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #3f475d;
            }
            .subnav a.active {
                color: var(--accent-dark);
            }
            .subnav .disabled {
                color: rgba(63, 71, 93, 0.5);
                cursor: default;
                pointer-events: none;
            }
            .content {
                padding: 26px;
                max-width: 1200px;
                width: 100%;
                margin: 0 auto;
            }
            .grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 16px;
            }
            .card {
                background: var(--card);
                padding: 18px;
                border-radius: 16px;
                box-shadow: var(--shadow);
                border: 1px solid rgba(15, 118, 110, 0.08);
            }
            .card h3 {
                margin: 0 0 6px;
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
            }
            .card p {
                margin: 0;
                color: var(--muted);
            }
            h1, h2 {
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
            }
            h1 { margin-top: 0; }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 14px;
            }
            th, td {
                text-align: left;
                padding: 10px 8px;
                border-bottom: 1px solid var(--border);
            }
            th {
                font-size: 12px;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: var(--muted);
            }
            .form {
                display: grid;
                gap: 12px;
                max-width: 720px;
            }
            label {
                font-size: 13px;
                color: var(--muted);
            }
            input, select, textarea {
                padding: 10px 12px;
                border-radius: 10px;
                border: 1px solid var(--border);
                font-family: inherit;
                font-size: 14px;
            }
            .button {
                background: var(--accent);
                color: white;
                border: none;
                padding: 10px 16px;
                border-radius: 10px;
                font-weight: 600;
                cursor: pointer;
            }
            .button:hover { background: var(--accent-dark); }
            .button-outline {
                background: transparent;
                color: var(--accent-dark);
                border: 1px solid var(--accent-dark);
            }
            .pagination {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin-top: 12px;
            }
            .pagination-links {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }
            .pagination-summary {
                font-size: 12px;
                color: var(--muted);
            }
            .page {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 32px;
                height: 32px;
                padding: 0 10px;
                border-radius: 10px;
                border: 1px solid var(--border);
                background: #fff;
                font-size: 12px;
                color: var(--ink);
                text-decoration: none;
            }
            .page.current {
                background: var(--accent);
                border-color: var(--accent);
                color: #fff;
                font-weight: 700;
            }
            .page.disabled {
                background: #f1f5f9;
                border-color: #e2e8f0;
                color: var(--muted);
                cursor: default;
            }
            .page.dots {
                border: none;
                background: transparent;
                padding: 0 4px;
                min-width: auto;
            }
            .status {
                padding: 6px 12px;
                border-radius: 999px;
                font-size: 12px;
                display: inline-block;
            }
            .status.pending { background: #fef3c7; color: #92400e; }
            .status.verified { background: #d1fae5; color: #065f46; }
            .status.rejected { background: #fee2e2; color: #991b1b; }
            .status.reviewed { background: #dbeafe; color: #1e3a8a; }
            .flash {
                padding: 12px 16px;
                border-radius: 12px;
                background: rgba(255, 126, 36, 0.12);
                color: var(--secondary);
                margin-bottom: 16px;
                border: 1px solid rgba(255, 126, 36, 0.35);
            }
            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }
            .muted {
                color: var(--muted);
                font-size: 13px;
            }
            @media (max-width: 980px) {
                .app-shell { grid-template-columns: 200px 1fr; }
                .content { padding: 22px; }
            }
            @media (max-width: 820px) {
                .app-shell { grid-template-columns: 1fr; }
                .sidebar { position: sticky; top: 0; z-index: 10; }
                .topbar { flex-direction: column; align-items: stretch; }
                .search { max-width: 100%; }
                .subnav {
                    overflow-x: auto;
                    justify-content: flex-start;
                    text-align: left;
                }
            }
            @media (max-width: 720px) {
                .sidebar { padding: 14px; gap: 12px; }
                .profile {
                    grid-template-columns: 52px 1fr;
                    justify-items: start;
                    text-align: left;
                    align-items: center;
                }
                .avatar { width: 52px; height: 52px; font-size: 16px; }
                .side-nav {
                    grid-auto-flow: column;
                    grid-auto-columns: max-content;
                    overflow-x: auto;
                    padding-bottom: 6px;
                }
                .nav-link { white-space: nowrap; }
                .top-actions { flex-wrap: wrap; justify-content: flex-start; gap: 8px; }
                .notif-label { display: none; }
                .content { padding: 16px; }
                .card { padding: 14px; }
                table { display: block; overflow-x: auto; }
                th, td { white-space: nowrap; }
                .form { max-width: 100%; }
            }
        </style>
    </head>
    <body>
        <div class="app-shell">
                @auth
                    <aside class="sidebar">
                    <div class="brand-block">
                        <div class="brand-title">SIVMI</div>
                        <div class="brand-sub">Buzzer Marketing</div>
                    </div>
                    <div class="profile">
                        <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        <div>
                            <div>{{ auth()->user()->name }}</div>
                            <div class="badge">{{ strtoupper(auth()->user()->role) }}</div>
                        </div>
                    </div>
                    @php
                        $adsRoles = ['admin', 'campaign_planner', 'ads_specialist', 'analyst', 'management'];
                        $role = strtolower(auth()->user()->role ?? '');
                        $module = 'buzzer';

                        if (request()->routeIs('produk.*')) {
                            $module = 'produk';
                        } elseif (request()->routeIs('data-siswa.*')) {
                            $module = 'data-siswa';
                        } elseif (request()->routeIs('akademik.*')) {
                            $module = 'akademik';
                        } elseif (request()->routeIs('keuangan.*')) {
                            $module = 'keuangan';
                        } elseif (request()->routeIs('ads.*')) {
                            $module = 'ads';
                        } elseif (request()->routeIs('leads.*')) {
                            $module = 'leads';
                        }
                    @endphp
                    <nav class="side-nav">
                        <a href="{{ route('menu') }}" class="nav-link">
                            <span class="nav-dot"></span> Kembali ke Menu Utama
                        </a>
                        @if ($module === 'buzzer')
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Dashboard
                            </a>
                            <a href="{{ route('leaderboard') }}" class="nav-link {{ request()->routeIs('leaderboard') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Leaderboard
                            </a>
                            @if (in_array($role, array_merge(['superadmin'], $adsRoles), true))
                                <a href="{{ route('ads.index') }}" class="nav-link {{ request()->routeIs('ads.*') ? 'active' : '' }}">
                                    <span class="nav-dot"></span> Ads/Iklan
                                </a>
                            @endif
                            @if ($role === 'leader')
                                <a href="{{ route('targets.index') }}" class="nav-link {{ request()->routeIs('targets.*') ? 'active' : '' }}">
                                    <span class="nav-dot"></span> Target Tim
                                </a>
                            @endif
                            @if (in_array($role, ['leader', 'staff'], true))
                                <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                                    <span class="nav-dot"></span> Chat
                                    @if (!empty($chatUnreadCount))
                                        <span class="nav-badge">{{ $chatUnreadCount }}</span>
                                    @endif
                                </a>
                            @endif
                            @if ($role === 'superadmin')
                                <a href="{{ route('teams.index') }}" class="nav-link {{ request()->routeIs('teams.*') ? 'active' : '' }}">
                                    <span class="nav-dot"></span> Tim
                                </a>
                                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <span class="nav-dot"></span> User
                                </a>
                                <a href="{{ route('targets.admin') }}" class="nav-link {{ request()->routeIs('targets.admin') ? 'active' : '' }}">
                                    <span class="nav-dot"></span> Target Tim
                                </a>
                            @endif
                            <a href="{{ route('activities.index') }}" class="nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Aktivitas
                            </a>
                            <a href="{{ route('conversions.index') }}" class="nav-link {{ request()->routeIs('conversions.*') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Konversi
                            </a>
                            @if ($role === 'superadmin')
                                <a href="{{ route('settings.points') }}" class="nav-link {{ request()->routeIs('settings.points*') ? 'active' : '' }}">
                                    <span class="nav-dot"></span> Settings Poin
                                </a>
                                <a href="{{ route('settings.maintenance') }}" class="nav-link {{ request()->routeIs('settings.maintenance*') ? 'active' : '' }}">
                                    <span class="nav-dot"></span> Maintenance
                                </a>
                                <a href="{{ route('activity-logs.index') }}" class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                                    <span class="nav-dot"></span> Log Aktivitas
                                </a>
                            @endif
                        @elseif ($module === 'produk')
                            <a href="{{ route('produk.index') }}" class="nav-link {{ request()->routeIs('produk.*') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Produk
                            </a>
                        @elseif ($module === 'data-siswa')
                            <a href="{{ route('data-siswa.dashboard') }}" class="nav-link {{ request()->routeIs('data-siswa.dashboard') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Dashboard
                            </a>
                            <a href="{{ route('data-siswa.index') }}" class="nav-link {{ request()->routeIs('data-siswa.index') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Data Siswa
                            </a>
                            <a href="{{ route('data-siswa.index') }}#data-siswa-tools" class="nav-link">
                                <span class="nav-dot"></span> Import CSV
                            </a>
                            <a href="{{ route('data-siswa.export.csv') }}" class="nav-link">
                                <span class="nav-dot"></span> Export CSV
                            </a>
                        @elseif ($module === 'akademik')
                            <a href="{{ route('akademik.index', ['section' => 'komposisi']) }}" class="nav-link {{ request('section') === 'komposisi' ? 'active' : '' }}">
                                <span class="nav-dot"></span> Komposisi Kelas
                            </a>
                            <a href="{{ route('akademik.index', ['section' => 'kalender']) }}" class="nav-link {{ request('section') === 'kalender' ? 'active' : '' }}">
                                <span class="nav-dot"></span> Kalender Akademik
                            </a>
                            <a href="{{ route('akademik.index', ['section' => 'silabus']) }}" class="nav-link {{ request('section') === 'silabus' ? 'active' : '' }}">
                                <span class="nav-dot"></span> Silabus
                            </a>
                            <a href="{{ route('akademik.index') }}" class="nav-link {{ request()->routeIs('akademik.index') && ! request('section') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Ringkasan Akademik
                            </a>
                            <a href="{{ route('akademik.index', ['section' => 'pelajaran']) }}" class="nav-link {{ request('section') === 'pelajaran' ? 'active' : '' }}">
                                <span class="nav-dot"></span> Daftar Pelajaran &amp; Pengajar
                            </a>
                            <a href="{{ route('akademik.index', ['section' => 'jadwal']) }}" class="nav-link {{ request('section') === 'jadwal' ? 'active' : '' }}">
                                <span class="nav-dot"></span> Jadwal KBM
                            </a>
                            <a href="{{ route('akademik.index', ['section' => 'absensi']) }}" class="nav-link {{ request('section') === 'absensi' ? 'active' : '' }}">
                                <span class="nav-dot"></span> Data Absensi &amp; Nilai
                            </a>
                            <a href="{{ route('akademik.index', ['section' => 'laporan']) }}" class="nav-link {{ request('section') === 'laporan' ? 'active' : '' }}">
                                <span class="nav-dot"></span> Laporan Kemajuan Siswa
                            </a>
                        @elseif ($module === 'keuangan')
                            <a href="{{ route('keuangan.index') }}" class="nav-link {{ request()->routeIs('keuangan.*') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Keuangan
                            </a>
                        @elseif ($module === 'ads')
                            <a href="{{ route('ads.index') }}" class="nav-link {{ request()->routeIs('ads.*') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Ads/Iklan
                            </a>
                        @elseif ($module === 'leads')
                            <a href="{{ route('leads.index') }}" class="nav-link {{ request()->routeIs('leads.index') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Pipeline Leads
                            </a>
                            <a href="{{ route('leads.followups') }}" class="nav-link {{ request()->routeIs('leads.followups') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Follow Up
                            </a>
                            <a href="{{ route('leads.analytics') }}" class="nav-link {{ request()->routeIs('leads.analytics') ? 'active' : '' }}">
                                <span class="nav-dot"></span> Analitik
                            </a>
                            <a href="{{ route('leads.whatsapp') }}" class="nav-link {{ request()->routeIs('leads.whatsapp') ? 'active' : '' }}">
                                <span class="nav-dot"></span> WhatsApp (Stub)
                            </a>
                        @endif
                    </nav>
                    <div class="sidebar-footer">SIVMI panel v1.0</div>
                </aside>
            @endauth
            <div class="main">
                @auth
                    <header class="topbar">
                        <div class="search">
                            <input type="text" placeholder="Cari tim, aktivitas, atau user">
                        </div>
                    <div class="top-actions">
                        @php
                            $maintenanceEnabled = false;
                            $maintenanceMessage = '';
                            if (auth()->user()->role === 'superadmin') {
                                $maintenanceEnabled = filter_var(\App\Models\AppSetting::getValue('maintenance_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
                                $maintenanceMessage = \App\Models\AppSetting::getValue('maintenance_message', '');
                            }
                        @endphp
                        @if (auth()->user()->role === 'superadmin')
                            <form method="POST" action="{{ route('settings.maintenance.update') }}" style="display: inline-flex;">
                                @csrf
                                <input type="hidden" name="enabled" value="{{ $maintenanceEnabled ? 0 : 1 }}">
                                <input type="hidden" name="message" value="{{ $maintenanceMessage }}">
                                <button class="button-outline" type="submit">
                                    {{ $maintenanceEnabled ? 'Nonaktifkan Maintenance' : 'Aktifkan Maintenance' }}
                                </button>
                            </form>
                        @endif
                        <span>{{ auth()->user()->email }}</span>
                        <a class="profile-link" href="{{ route('profile.show') }}">My Profil</a>
                            <a class="notif-btn" title="Notifikasi" href="{{ route('notifications.index') }}">
                                @if (!empty($notifCount))
                                    <span class="notif-count">{{ $notifCount }}</span>
                                @endif
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="#3f475d" d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Z"/>
                                </svg>
                            </a>
                            @if (!empty($notifCount))
                                <a class="notif-label" href="{{ route('notifications.index') }}">Notifikasi baru</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="button button-outline" type="submit">Logout</button>
                            </form>
                        </div>
                    </header>
                    @php
                        $role = auth()->user()->role;
                        $adsRoles = ['admin', 'campaign_planner', 'ads_specialist', 'analyst', 'management'];
                    @endphp
                    @if (in_array($role, array_merge(['superadmin', 'leader', 'akademik', 'keuangan'], $adsRoles), true))
                        <nav class="subnav">
                            @if ($role === 'superadmin')
                                <span class="disabled">Dashboard</span>
                                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                    Buzzer Marketing
                                </a>
                                <a href="{{ route('produk.index') }}" class="{{ request()->routeIs('produk.*') ? 'active' : '' }}">
                                    Produk
                                </a>
                                <a href="{{ route('data-siswa.index') }}" class="{{ request()->routeIs('data-siswa.*') ? 'active' : '' }}">
                                    Data Siswa
                                </a>
                                <a href="{{ route('akademik.index') }}" class="{{ request()->routeIs('akademik.*') ? 'active' : '' }}">
                                    Akademik
                                </a>
                                <a href="{{ route('keuangan.index') }}" class="{{ request()->routeIs('keuangan.*') ? 'active' : '' }}">
                                    Keuangan
                                </a>
                                <a href="{{ route('ads.index') }}" class="{{ request()->routeIs('ads.*') ? 'active' : '' }}">
                                    Ads/Iklan
                                </a>
                                <a href="{{ route('media-sosial.index') }}" class="{{ request()->routeIs('media-sosial.*') ? 'active' : '' }}">
                                    Media Sosial
                                </a>
                                <a href="{{ route('konten-marketing.index') }}" class="{{ request()->routeIs('konten-marketing.*') ? 'active' : '' }}">
                                    Konten Marketing
                                </a>
                                <a href="{{ route('leads.index') }}" class="{{ request()->routeIs('leads.*') ? 'active' : '' }}">
                                    Leads
                                </a>
                                <a href="{{ route('event.index') }}" class="{{ request()->routeIs('event.*') ? 'active' : '' }}">
                                    Event
                                </a>
                            @elseif ($role === 'leader')
                                <span class="disabled">Dashboard</span>
                                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                    Buzzer Marketing
                                </a>
                                <a href="{{ route('produk.index') }}" class="{{ request()->routeIs('produk.*') ? 'active' : '' }}">
                                    Produk
                                </a>
                                <a href="{{ route('data-siswa.index') }}" class="{{ request()->routeIs('data-siswa.*') ? 'active' : '' }}">
                                    Data Siswa
                                </a>
                                <a href="{{ route('akademik.index') }}" class="{{ request()->routeIs('akademik.*') ? 'active' : '' }}">
                                    Akademik
                                </a>
                                <a href="{{ route('keuangan.index') }}" class="{{ request()->routeIs('keuangan.*') ? 'active' : '' }}">
                                    Keuangan
                                </a>
                                <a href="{{ route('ads.index') }}" class="{{ request()->routeIs('ads.*') ? 'active' : '' }}">
                                    Ads/Iklan
                                </a>
                                <a href="{{ route('media-sosial.index') }}" class="{{ request()->routeIs('media-sosial.*') ? 'active' : '' }}">
                                    Media Sosial
                                </a>
                                <a href="{{ route('konten-marketing.index') }}" class="{{ request()->routeIs('konten-marketing.*') ? 'active' : '' }}">
                                    Konten Marketing
                                </a>
                                <a href="{{ route('leads.index') }}" class="{{ request()->routeIs('leads.*') ? 'active' : '' }}">
                                    Leads
                                </a>
                                <a href="{{ route('event.index') }}" class="{{ request()->routeIs('event.*') ? 'active' : '' }}">
                                    Event
                                </a>
                            @elseif ($role === 'akademik')
                                <a href="{{ route('akademik.index') }}" class="{{ request()->routeIs('akademik.*') ? 'active' : '' }}">
                                    Akademik
                                </a>
                            @elseif ($role === 'keuangan')
                                <a href="{{ route('keuangan.index') }}" class="{{ request()->routeIs('keuangan.*') ? 'active' : '' }}">
                                    Keuangan
                                </a>
                            @elseif (in_array($role, $adsRoles, true))
                                <a href="{{ route('ads.index') }}" class="{{ request()->routeIs('ads.*') ? 'active' : '' }}">
                                    Ads/Iklan
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                    Buzzer Marketing
                                </a>
                            @endif
                        </nav>
                    @endif
                @endauth
                <main class="content">
                    @if (session('status'))
                        <div class="flash">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="flash">
                            <strong>Validasi gagal:</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
