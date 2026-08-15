<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
@php
    $uName = __(\App\Models\Setting::getValue('university_name', 'National Technical Training Institute'));
    $uLogo = \App\Models\Setting::getAssetUrl('university_logo', '/images/ntti_logo.png');
    $loginBg = \App\Models\Setting::getAssetUrl('login_bg', '/images/bg-login.jpg');
    $primaryColor = \App\Models\Setting::getValue('primary_color', '#00d4a0');
    $defaultTheme = \App\Models\Setting::getValue('default_theme', 'dark');
    
    // Hex to RGB for CSS variables
    list($r, $g, $b) = sscanf($primaryColor, "#%02x%02x%02x");
    $primaryRgb = "$r, $g, $b";
@endphp
    <title>Sign In — {{ $uName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root, [data-theme="light"] { 
            --primary: {{ $primaryColor }}; 
            --primary-rgb: {{ $primaryRgb }};
        }
        
        .auth-container {
            position: relative;
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            overflow: hidden;
            background: #0a0f1e;
        }

        .auth-bg-layer {
            position: absolute;
            inset: 0;
            background: linear-gradient(rgba(10, 15, 30, 0.4), rgba(10, 15, 30, 0.8)),
                        url('{{ $loginBg }}') center/cover no-repeat;
            animation: kenBurns 20s ease-in-out infinite alternate;
            z-index: 1;
        }

        @keyframes kenBurns {
            from { transform: scale(1); }
            to   { transform: scale(1.15); }
        }

        .auth-branding {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 5rem;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.15) 0%, transparent 60%);
        }

        .auth-branding h1 {
            font-size: 3.5rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.05;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
            text-shadow: 0 10px 30px rgba(0,0,0,0.5);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .auth-branding p {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.6);
            max-width: 420px;
            line-height: 1.7;
            font-weight: 300;
            letter-spacing: 0.3px;
            animation: slideUp 0.8s 0.1s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .auth-panel {
            position: relative;
            z-index: 3;
            width: 520px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(10, 15, 25, 0.65);
            backdrop-filter: blur(35px) saturate(200%);
            -webkit-backdrop-filter: blur(35px) saturate(200%);
            border-left: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: inset 1px 0 0 rgba(255, 255, 255, 0.05),
                        -30px 0 80px rgba(0,0,0,0.6);
            padding: 4.5rem 4rem;
        }

        .auth-panel-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2.5rem;
            animation: slideUp 0.8s 0.2s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .auth-panel h2 {
            font-size: 2.3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,0.6) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
            text-align: center;
            animation: slideUp 0.8s 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .auth-subtitle {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.88rem;
            margin-bottom: 2.5rem;
            font-weight: 400;
            letter-spacing: 0.2px;
            text-align: center;
            animation: slideUp 0.8s 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* ── Form Styling ── */
        .form-group {
            animation: slideUp 0.8s 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .form-group label {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.5) !important;
            margin-bottom: 0.75rem !important;
            display: block;
            transition: color 0.3s ease;
        }

        .form-group:focus-within label {
            color: var(--primary) !important;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper > i {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.25rem;
            color: rgba(255,255,255,0.25) !important;
            transition: all 0.3s ease;
            z-index: 1;
        }

        .input-icon-wrapper:focus-within > i {
            color: var(--primary) !important;
        }

        .form-control {
            border-radius: 1rem !important;
            background: rgba(255,255,255,0.03) !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            height: 56px;
            color: #fff !important;
            font-size: 0.95rem !important;
            font-weight: 500 !important;
            letter-spacing: 0.3px;
            padding-left: 3.2rem !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-control::placeholder {
            color: rgba(255,255,255,0.2) !important;
            font-weight: 400 !important;
        }

        .form-control:focus {
            background: rgba(var(--primary-rgb), 0.05) !important;
            border-color: rgba(var(--primary-rgb), 0.5) !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1),
                        0 0 0 4px rgba(var(--primary-rgb), 0.1),
                        0 10px 30px rgba(var(--primary-rgb), 0.15) !important;
            transform: translateY(-2px);
            outline: none !important;
        }

        /* ── Login Extras ── */
        .login-extras {
            animation: slideUp 0.8s 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .login-extras label {
            font-size: 0.82rem !important;
            font-weight: 500 !important;
            color: rgba(255,255,255,0.4) !important;
            transition: color 0.3s ease;
        }

        .login-extras label:hover {
            color: rgba(255,255,255,0.7) !important;
        }

        /* ── Button ── */
        .btn-primary {
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
            border-radius: 1rem !important;
            height: 58px;
            font-size: 0.9rem !important;
            font-weight: 700 !important;
            letter-spacing: 2px;
            text-transform: uppercase;
            background: linear-gradient(135deg, var(--primary), color-mix(in srgb, var(--primary), #fff 20%)) !important;
            box-shadow: 0 10px 30px rgba(var(--primary-rgb), 0.4),
                        inset 0 1px 0 rgba(255,255,255,0.3);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        }

        /* Khmer Language Adjustments */
        html[lang="km"] .btn-primary {
            letter-spacing: 0 !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
            font-family: 'Kantumruy Pro', sans-serif !important;
            text-transform: none;
        }
        
        html[lang="km"] .form-group label {
            letter-spacing: 0 !important;
            font-size: 0.85rem !important;
            font-family: 'Kantumruy Pro', sans-serif !important;
            text-transform: none;
        }

        html[lang="km"] .auth-branding h1 {
            line-height: 1.4;
            letter-spacing: 0;
            font-family: 'Kantumruy Pro', sans-serif !important;
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            top: -50%; left: -60%; width: 20%; height: 200%;
            background: rgba(255,255,255,0.3);
            transform: rotate(30deg);
            transition: none;
            animation: shimmer 4s infinite;
        }

        .btn-primary:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 15px 40px rgba(var(--primary-rgb), 0.5),
                        0 0 20px rgba(var(--primary-rgb), 0.2),
                        inset 0 1px 0 rgba(255,255,255,0.4);
            filter: brightness(1.15);
        }

        .btn-primary:active {
            transform: translateY(0) scale(0.98);
        }

        /* ── Panel Top Decoration ── */
        .auth-panel-decor {
            position: relative;
            width: 100%;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            animation: slideUp 0.8s 0.1s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .orbit-ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(var(--primary-rgb), 0.12);
        }

        .orbit-ring-1 {
            width: 100px; height: 100px;
            animation: orbitSpin 12s linear infinite;
        }
        .orbit-ring-2 {
            width: 140px; height: 140px;
            border-style: dashed;
            border-color: rgba(var(--primary-rgb), 0.08);
            animation: orbitSpin 18s linear infinite reverse;
        }
        .orbit-ring-3 {
            width: 170px; height: 170px;
            border-style: dotted;
            border-color: rgba(255,255,255,0.04);
            animation: orbitSpin 25s linear infinite;
        }

        .orbit-dot {
            position: absolute;
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--primary);
            box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.6);
        }
        .orbit-dot-1 { top: 0; left: 50%; transform: translate(-50%, -50%); }
        .orbit-dot-2 { bottom: 0; right: 0; transform: translate(50%, 50%); }

        .decor-logo {
            position: relative;
            z-index: 1;
            width: 65px; height: 65px;
            border-radius: 50%;
            overflow: hidden;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3), 0 0 15px rgba(var(--primary-rgb), 0.15);
        }

        .decor-logo img {
            width: 90%; height: 90%;
            object-fit: contain;
        }

        @keyframes orbitSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* ── Radar Animation ── */
        .radar-sweep {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(from 0deg, transparent 70%, rgba(var(--primary-rgb), 0.6) 100%);
            animation: radarSpin 2.5s linear infinite;
        }

        .radar-grid-1 {
            position: absolute;
            width: 50%; height: 50%;
            border-radius: 50%;
            border: 1px solid rgba(var(--primary-rgb), 0.2);
        }
        
        .radar-grid-2 {
            position: absolute;
            width: 100%; height: 100%;
            border-radius: 50%;
            border: 1px solid rgba(var(--primary-rgb), 0.4);
        }

        @keyframes radarSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* ── RFID Card Animation ── */
        .rfid-card {
            animation: cardTap 3s ease-in-out infinite;
        }
        .rfid-waves {
            animation: emitWaves 3s ease-in-out infinite;
        }

        @keyframes cardTap {
            0%, 100% { transform: translateY(-10px) rotate(-10deg); color: rgba(255,255,255,0.6); }
            50% { transform: translateY(5px) rotate(0deg); color: var(--primary); filter: drop-shadow(0 0 10px rgba(var(--primary-rgb), 0.6)); }
        }
        @keyframes emitWaves {
            0%, 20%, 80%, 100% { opacity: 0; transform: translateY(-5px) scale(0.8); }
            50% { opacity: 1; transform: translateY(-20px) scale(1.2); }
        }

        /* ── Divider Line ── */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.8rem 0;
            animation: slideUp 0.8s 0.65s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .auth-divider::before, .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
        }
        .auth-divider span {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.2);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes shimmer {
            0% { left: -60%; }
            20% { left: 120%; }
            100% { left: 120%; }
        }

        /* ── Floating Blobs ── */
        .blob {
            position: absolute;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.35), rgba(var(--primary-rgb), 0.05));
            filter: blur(90px);
            border-radius: 50%;
            z-index: 2;
            pointer-events: none;
            animation: float 25s infinite alternate;
            mix-blend-mode: screen;
        }
        .blob-1 { width: 600px; height: 600px; top: -15%; left: -10%; animation-delay: 0s; }
        .blob-2 { width: 550px; height: 550px; bottom: -15%; right: 10%; background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(147, 51, 234, 0.2)); animation-delay: -5s; }
        .blob-3 { width: 450px; height: 450px; top: 30%; left: 35%; background: linear-gradient(135deg, rgba(236, 72, 153, 0.15), rgba(244, 63, 94, 0.15)); animation: float-reverse 20s infinite alternate; }
        
        @keyframes float {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            33% { transform: translate(40px, -60px) scale(1.15) rotate(10deg); }
            66% { transform: translate(-30px, 30px) scale(0.9) rotate(-10deg); }
            100% { transform: translate(0, 0) scale(1) rotate(0deg); }
        }
        @keyframes float-reverse {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            50% { transform: translate(-50px, -30px) scale(1.2) rotate(-15deg); }
            100% { transform: translate(0, 0) scale(1) rotate(0deg); }
        }

        .mobile-top-header {
            display: none;
        }

        /* ── Mobile Responsive ── */
        @media (max-width: 768px) {
            .mobile-top-header {
                display: block;
                animation: slideUp 0.8s ease both;
            }

            .auth-branding,
            .auth-bg-layer,
            .blob { display: none !important; }

            .auth-container {
                background: linear-gradient(160deg, #0a0f1e 0%, #111827 50%, #0a0f1e 100%);
            }

            .auth-panel {
                width: 100%;
                min-height: 100vh;
                border-left: none;
                padding: 2rem 1.5rem;
                background: transparent;
                backdrop-filter: none;
                -webkit-backdrop-filter: none;
                box-shadow: none;
            }

            .auth-panel h2 {
                font-size: 1.6rem;
            }

            .auth-panel-decor {
                height: 100px;
                margin-bottom: 1rem;
            }

            .orbit-ring-3 { width: 140px; height: 140px; }
            .orbit-ring-2 { width: 115px; height: 115px; }
            .orbit-ring-1 { width: 85px; height: 85px; }
            .decor-logo { width: 55px; height: 55px; }
        }
    </style>
    <script>
        const _fs = parseInt(localStorage.getItem('font_size'));
        if (_fs && _fs >= 11 && _fs <= 20) document.documentElement.style.fontSize = _fs + 'px';

        const savedTheme = localStorage.getItem('theme');
        const defaultTheme = '{{ $defaultTheme }}';
        if (savedTheme === 'light' || (!savedTheme && defaultTheme === 'light')) {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    </script>
</head>
<body style="display:block; min-height:100vh;">

<div class="auth-container">
    {{-- Animated Background Layer --}}
    <div class="auth-bg-layer"></div>
    
    {{-- Decorative Blobs --}}
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    {{-- Left: Branding panel --}}
    <div class="auth-branding">
        <div style="display:flex; align-items:center; gap:1rem; margin-bottom:3rem; animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;">
            @if($uLogo)
                <div style="width: 85px; height: 85px; border-radius: 50%; overflow: hidden; background: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(0,0,0,0.3);">
                    <img src="{{ to_asset_url($uLogo) }}" alt="Logo" style="width: 92%; height: 92%; object-fit: contain;">
                </div>
            @endif
            <span class="school-name-display" style="font-size:1.4rem; font-weight:800; color:#fff; letter-spacing:1px; text-transform:uppercase; opacity:0.9; text-shadow: 0 4px 10px rgba(0,0,0,0.4);">{{ $uName }}</span>
        </div>
        <h1>{{ __('NTTI Teacher') }}<br>{{ __('Attendance') }}<br><span style="color:var(--primary);">{{ __('Management System') }}</span></h1>
        <p>{{ __('A professional institution deserve professional tools. Track, manage and report attendance with one simple click.') }}</p>
    </div>

    {{-- Right: Login panel --}}
    <div class="auth-panel">
        {{-- Mobile Top Spacer/Header --}}
        <div class="mobile-top-header" style="text-align: center; margin-bottom: auto; padding-bottom: 2rem;">
            <span style="font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 2px;">{{ __('System Administration Portal') }}</span>
        </div>

        {{-- Animated Logo Decoration --}}
        <div class="auth-panel-decor">
            <div class="orbit-ring orbit-ring-3"></div>
            <div class="orbit-ring orbit-ring-2"></div>
            <div class="orbit-ring orbit-ring-1">
                <div class="orbit-dot orbit-dot-1"></div>
                <div class="orbit-dot orbit-dot-2"></div>
            </div>
            @if($uLogo)
                <div class="decor-logo">
                    <img src="{{ to_asset_url($uLogo) }}" alt="{{ $uName }} Logo">
                </div>
            @endif
        </div>

        {{-- Institution Name --}}
        <div style="text-align:center; margin-bottom:2rem; animation: slideUp 0.8s 0.2s cubic-bezier(0.16, 1, 0.3, 1) both;">
            <span class="school-name-display" style="font-size:1.5rem; font-weight:900; color:var(--primary); letter-spacing:0.5px; text-transform:uppercase; text-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.2);">{{ $uName }}</span>
        </div>

        <h2>{{ __('Welcome back') }}</h2>
        <p class="auth-subtitle">{{ __('Sign in to your administrator account') }}</p>

        {{-- Error / flash --}}
        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom:1.5rem; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.15); color:#f87171; border-radius:0.75rem; padding:0.9rem 1.2rem; font-size:0.88rem; display:flex; align-items:center; gap:0.6rem;">
                <i class="ph ph-warning-circle" style="font-size:1.2rem;"></i> {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="email">{{ __('Email Address') }}</label>
                <div class="input-icon-wrapper">
                    <i class="ph ph-at"></i>
                    <input type="text" id="email" name="email"
                           class="form-control"
                           value="{{ old('email') }}" required autofocus
                           placeholder="admin@ntti.edu.kh">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="password">{{ __('Security Password') }}</label>
                <div class="input-icon-wrapper">
                    <i class="ph ph-lock-key"></i>
                    <input type="password" id="password" name="password"
                           class="form-control" required placeholder="••••••••" style="padding-right: 3rem !important;">
                    <i class="ph ph-eye" style="left: auto !important; right: 1.25rem !important; cursor: pointer; pointer-events: auto;" onclick="
                        const pwd = document.getElementById('password');
                        if (pwd.type === 'password') {
                            pwd.type = 'text';
                            this.classList.remove('ph-eye');
                            this.classList.add('ph-eye-slash');
                        } else {
                            pwd.type = 'password';
                            this.classList.remove('ph-eye-slash');
                            this.classList.add('ph-eye');
                        }
                    "></i>
                </div>
            </div>

            <div class="login-extras" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem;">
                <label style="display:flex; align-items:center; gap:0.6rem; cursor:pointer;">
                    <input type="checkbox" name="remember" style="accent-color:var(--primary); width:16px; height:16px;"> {{ __('Stay signed in') }}
                </label>
                {{-- Language Switcher on Login --}}
                <div class="lang-switcher" style="display:flex; gap:0; border:1px solid rgba(255,255,255,0.1); border-radius:0.5rem; overflow:hidden;">
                    <a href="{{ route('lang.switch', 'en') }}" style="padding:0.4rem 0.85rem; font-size:0.72rem; font-weight:700; letter-spacing:0.5px; background:{{ app()->getLocale()==='en' ? 'var(--primary)' : 'transparent' }}; color:{{ app()->getLocale()==='en' ? '#000' : 'rgba(255,255,255,0.4)' }}; text-decoration:none; transition: all 0.2s ease;">EN</a>
                    <a href="{{ route('lang.switch', 'km') }}" style="padding:0.4rem 0.85rem; font-size:0.72rem; font-weight:700; letter-spacing:0.5px; background:{{ app()->getLocale()==='km' ? 'var(--primary)' : 'transparent' }}; color:{{ app()->getLocale()==='km' ? '#000' : 'rgba(255,255,255,0.4)' }}; text-decoration:none; border-left:1px solid rgba(255,255,255,0.1); transition: all 0.2s ease;">KH</a>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; border:none; color:#000; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.6rem;">
                <i class="ph-bold ph-sign-in" style="font-size:1.1rem;"></i> {{ __('AUTHENTICATE') }}
            </button>
        </form>

        <div class="auth-divider"><span>NTTI</span></div>

        <div style="display: flex; justify-content: center; align-items: center; gap: 2rem; margin: 1.5rem auto 0; animation: slideUp 0.8s 0.75s cubic-bezier(0.16, 1, 0.3, 1) both;">
            {{-- Radar/Sonar Animation --}}
            <div class="radar-animation" style="position: relative; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 50%; background: rgba(var(--primary-rgb), 0.05); box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.1);">
                <div class="radar-sweep"></div>
                <div class="radar-grid-1"></div>
                <div class="radar-grid-2"></div>
                <i class="ph-bold ph-broadcast" style="font-size: 24px; color: var(--primary); z-index: 2; filter: drop-shadow(0 0 5px rgba(var(--primary-rgb), 0.8));"></i>
            </div>

            {{-- Divider --}}
            <div style="height: 30px; width: 1px; background: rgba(255,255,255,0.1);"></div>

            {{-- RFID Card Animation --}}
            <div class="rfid-animation" style="position: relative; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                <i class="ph-bold ph-wifi-high rfid-waves" style="font-size: 30px; color: var(--primary); position: absolute; top: -15px; opacity: 0; z-index: 1;"></i>
                <i class="ph-fill ph-identification-card rfid-card" style="font-size: 38px; color: rgba(255,255,255,0.8); z-index: 2;"></i>
                <div class="rfid-reader" style="position: absolute; bottom: 0; width: 50px; height: 6px; background: rgba(255,255,255,0.05); border: 1px solid rgba(var(--primary-rgb), 0.2); border-radius: 3px; box-shadow: 0 0 8px rgba(var(--primary-rgb), 0.2);"></div>
            </div>
        </div>

        <div class="mobile-bottom-footer" style="margin-top: auto; padding-top: 2rem;">
            <p style="text-align:center; font-size:0.75rem; color:rgba(255,255,255,0.4); letter-spacing:0.5px; animation: slideUp 0.8s 0.8s cubic-bezier(0.16, 1, 0.3, 1) both; margin-bottom: 0.5rem;">
                {{ __('Secure access for authorized personnel only') }}
            </p>
            <p style="text-align:center; font-size:0.7rem; color:rgba(255,255,255,0.2); letter-spacing:1px; animation: slideUp 0.8s 0.9s cubic-bezier(0.16, 1, 0.3, 1) both;">
                v1.0.0 &copy; {{ date('Y') }}
            </p>
        </div>

    </div>

</div>

<script>
    // ── Toast System ──
    const toastContainer = document.createElement('div');
    toastContainer.style = "position:fixed; top:20px; right:20px; z-index:9999; display:flex; flex-direction:column; gap:10px; pointer-events:none;";
    document.body.appendChild(toastContainer);

    window.showToast = (msg, type = 'success') => {
        const toast = document.createElement('div');
        const color = type === 'success' ? 'var(--primary)' : '#f87171';
        const icon = type === 'success' ? 'ph ph-check-circle' : 'ph ph-x-circle';
        
        toast.style = `
            min-width: 300px; background: rgba(20,25,40,0.9); backdrop-filter:blur(10px); border-left: 4px solid ${color}; color: #fff;
            padding: 1.25rem; box-shadow: 0 15px 40px rgba(0,0,0,0.5); display: flex; align-items: center; gap: 1rem;
            transform: translateX(120%); transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            pointer-events: auto; border-radius: 0.5rem;
        `;
        toast.innerHTML = `<i class="${icon}" style="font-size:1.5rem; color:${color};"></i> <span style="font-size:0.95rem; font-weight:600;">${msg}</span>`;
        
        toastContainer.appendChild(toast);
        setTimeout(() => toast.style.transform = 'translateX(0)', 10);
        
        const remove = () => {
            toast.style.transform = 'translateX(120%)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        };
        
        setTimeout(remove, 5000);
        toast.onclick = remove;
    };

    @if(session('error')) window.showToast("{{ session('error') }}", 'error'); @endif
    @if(session('success')) window.showToast("{{ session('success') }}", 'success'); @endif

    // ── Instant Language Switcher ──
    const langDict = {
        'en': {
            'school_name': {!! json_encode(__(\App\Models\Setting::getValue('university_name', 'National Technical Training Institute'), [], 'en')) !!},
            'NTTI Teacher': 'NTTI Teacher',
            'Attendance': 'Attendance',
            'Management System': 'Management System',
            'desc': 'A professional institution deserve professional tools. Track, manage and report attendance with one simple click.',
            'portal': 'System Administration Portal',
            'welcome': 'Welcome back',
            'signin': 'Sign in to your administrator account',
            'email': 'Email Address',
            'password': 'Security Password',
            'remember': 'Stay signed in',
            'auth': 'AUTHENTICATE',
            'secure': 'Secure access for authorized personnel only'
        },
        'km': {
            'school_name': {!! json_encode(__(\App\Models\Setting::getValue('university_name', 'National Technical Training Institute'), [], 'km')) !!},
            'NTTI Teacher': 'ប្រព័ន្ធគ្រប់គ្រង',
            'Attendance': 'វត្តមាន',
            'Management System': 'គ្រូបង្រៀន NTTI',
            'desc': 'ស្ថាប័នអាជីពសមនឹងទទួលបានឧបករណ៍អាជីព។ តាមដាន គ្រប់គ្រង និងរាយការណ៍វត្តមានដោយគ្រាន់តែចុចមួយ។',
            'portal': 'ផតថលរដ្ឋបាលប្រព័ន្ធ',
            'welcome': 'សូមស្វាគមន៍ការត្រឡប់មកវិញ',
            'signin': 'ចូលទៅក្នុងគណនីអ្នកគ្រប់គ្រងរបស់អ្នក',
            'email': 'អាសយដ្ឋានអ៊ីមែល',
            'password': 'ពាក្យសម្ងាត់សុវត្ថិភាព',
            'remember': 'រក្សាការចូល',
            'auth': 'ផ្ទៀងផ្ទាត់',
            'secure': 'សិទ្ធិចូលដំណើរការសម្រាប់តែបុគ្គលិកដែលមានការអនុញ្ញាតប៉ុណ្ណោះ'
        }
    };

    document.querySelectorAll('.lang-switcher a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            const lang = url.split('/').pop();
            
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            
            document.documentElement.lang = lang;
            
            const links = this.parentElement.querySelectorAll('a');
            links.forEach(l => {
                l.style.background = 'transparent';
                l.style.color = 'rgba(255,255,255,0.4)';
            });
            this.style.background = 'var(--primary)';
            this.style.color = '#000';

            document.querySelectorAll('.school-name-display').forEach(el => {
                el.innerText = langDict[lang]['school_name'];
            });

            document.querySelector('.auth-branding h1').innerHTML = `${langDict[lang]['NTTI Teacher']}<br>${langDict[lang]['Attendance']}<br><span style="color:var(--primary);">${langDict[lang]['Management System']}</span>`;
            document.querySelector('.auth-branding p').innerText = langDict[lang]['desc'];
            document.querySelector('.mobile-top-header span').innerText = langDict[lang]['portal'];
            document.querySelector('.auth-panel h2').innerText = langDict[lang]['welcome'];
            document.querySelector('.auth-subtitle').innerText = langDict[lang]['signin'];
            document.querySelector('label[for="email"]').innerText = langDict[lang]['email'];
            document.querySelector('label[for="password"]').innerText = langDict[lang]['password'];
            
            const rememberLabel = document.querySelector('input[name="remember"]').parentElement;
            if (rememberLabel && rememberLabel.childNodes[2]) {
                rememberLabel.childNodes[2].nodeValue = ' ' + langDict[lang]['remember'];
            }

            document.querySelector('.btn-primary').innerHTML = `<i class="ph-bold ph-sign-in" style="font-size:1.1rem;"></i> ${langDict[lang]['auth']}`;
            document.querySelector('.mobile-bottom-footer p:first-child').innerText = langDict[lang]['secure'];
        });
    });
</script>

</body>
</html>
