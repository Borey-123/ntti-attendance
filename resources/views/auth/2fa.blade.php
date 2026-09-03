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
    
    list($r, $g, $b) = sscanf($primaryColor, "#%02x%02x%02x");
    $primaryRgb = "$r, $g, $b";
@endphp
    <title>2FA Security Verification — {{ $uName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&family=Inter:wght@300;400;500;600;700;800&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            box-shadow: inset 1px 0 0 rgba(255, 255, 255, 0.05), -30px 0 80px rgba(0,0,0,0.6);
            padding: 4.5rem 4rem;
        }

        .otp-display-box {
            background: rgba(168, 85, 247, 0.1);
            border: 1px dashed rgba(168, 85, 247, 0.4);
            border-radius: 1rem;
            padding: 1.25rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 2.2rem;
            font-weight: 900;
            letter-spacing: 8px;
            color: #c084fc;
            text-shadow: 0 0 12px rgba(192, 132, 252, 0.5);
            margin: 0.5rem 0;
        }

        .form-control {
            border-radius: 1rem !important;
            background: rgba(255,255,255,0.03) !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            height: 58px;
            color: #fff !important;
            font-size: 1.5rem !important;
            font-weight: 800 !important;
            letter-spacing: 6px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .form-control:focus {
            background: rgba(var(--primary-rgb), 0.05) !important;
            border-color: rgba(var(--primary-rgb), 0.5) !important;
            box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1) !important;
            outline: none !important;
        }

        .btn-primary {
            border-radius: 1rem !important;
            height: 58px;
            font-size: 0.9rem !important;
            font-weight: 700 !important;
            letter-spacing: 2px;
            text-transform: uppercase;
            background: linear-gradient(135deg, var(--primary), color-mix(in srgb, var(--primary), #fff 20%)) !important;
            box-shadow: 0 10px 30px rgba(var(--primary-rgb), 0.4);
            border: none; color: #000; cursor: pointer; width: 100%;
        }

        @media (max-width: 768px) {
            .auth-branding { display: none !important; }
            .auth-panel { width: 100%; min-height: 100vh; padding: 2rem 1.5rem; background: transparent; backdrop-filter: none; }
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-bg-layer"></div>

    <div class="auth-branding">
        <div style="display:flex; align-items:center; gap:1rem; margin-bottom:3rem;">
            @if($uLogo)
                <div style="width: 85px; height: 85px; border-radius: 50%; overflow: hidden;">
                    <img src="{{ to_asset_url($uLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            @endif
            <span style="font-size:1.4rem; font-weight:800; color:#fff; text-transform:uppercase;">{{ $uName }}</span>
        </div>
        <h1>{{ __('Two-Factor') }}<br><span style="color:#c084fc;">{{ __('Authentication') }}</span></h1>
        <p>{{ __('Verify your 6-digit security code to proceed to the administrative dashboard.') }}</p>
    </div>

    <div class="auth-panel">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 70px; height: 70px; margin: 0 auto 1.5rem; border-radius: 50%; background: rgba(168, 85, 247, 0.15); border: 2px solid rgba(168, 85, 247, 0.4); display: flex; align-items: center; justify-content: center;">
                <i class="ph-bold ph-shield-check" style="font-size: 2.2rem; color: #c084fc;"></i>
            </div>
            <h2 style="font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem;">{{ __('2FA Security Check') }}</h2>
            <p style="color: rgba(255,255,255,0.5); font-size: 0.9rem;">{{ __('Account') }}: <strong style="color: #fff;">{{ $user->name }}</strong> ({{ $user->email }})</p>
        </div>

@php
    $botUsername = \App\Models\Setting::getValue('telegram_bot_username');
    $tgToken = session('2fa_tg_token');
    $tgParam = $tgToken ? "link_{$tgToken}" : "admin_{$user->id}";
    $tgDeepLink = $botUsername ? "https://t.me/{$botUsername}?start={$tgParam}" : "#";
@endphp

        {{-- Telegram OTP Delivery Notification Box --}}
        <div class="telegram-delivery-box" style="background: rgba(14, 165, 233, 0.12); border: 1px solid rgba(14, 165, 233, 0.35); border-radius: 1rem; padding: 1.25rem; text-align: center; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 0.6rem; color: #38bdf8; font-weight: 800; font-size: 1rem; margin-bottom: 0.5rem;">
                <i class="ph-bold ph-paper-plane-tilt" style="font-size: 1.4rem;"></i> {{ __('Telegram Bot OTP Verification') }}
            </div>
            <p style="font-size: 0.85rem; color: rgba(255,255,255,0.75); margin: 0 0 1rem 0; line-height: 1.5;">
                {{ __('A 6-digit OTP code is sent directly to your Telegram Bot conversation.') }}
            </p>

            @if($botUsername)
            <a href="{{ $tgDeepLink }}" target="_blank" class="btn-tg-link" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.6rem; background: #0088cc; color: #fff; font-weight: 700; font-size: 0.88rem; padding: 0.75rem 1.25rem; border-radius: 0.75rem; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0, 136, 204, 0.4); margin-bottom: 0.75rem; width: 100%;">
                <i class="ph-bold ph-telegram-logo" style="font-size: 1.2rem;"></i> 
                {{ empty($user->telegram_chat_id) ? __('Connect Telegram Bot to Get OTP') : __('Open Telegram Bot (@' . $botUsername . ')') }}
            </a>
            @endif

            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.45); display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                <i class="ph ph-clock" style="color: #38bdf8;"></i> {{ __('Code expires in 10 minutes') }}
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:1.5rem; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2); color:#f87171; border-radius:0.75rem; padding:0.9rem 1.2rem; font-size:0.88rem; display:flex; align-items:center; gap:0.6rem;">
                <i class="ph ph-warning-circle" style="font-size:1.2rem;"></i> 
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.2fa.post') }}">
            @csrf
            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="display:block; text-align:center; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:rgba(255,255,255,0.5); margin-bottom:0.75rem;">
                    {{ __('Enter 6-Digit Code') }}
                </label>
                <input type="text" name="code" class="form-control" placeholder="000000" maxlength="6" autofocus required autocomplete="off">
            </div>

            <button type="submit" class="btn-primary" style="margin-bottom: 1.5rem; display:flex; align-items:center; justify-content:center; gap:0.6rem;">
                <i class="ph-bold ph-lock-key-open" style="font-size:1.2rem;"></i> {{ __('VERIFY & ACCESS SYSTEM') }}
            </button>
        </form>

        <form method="POST" action="{{ route('login.2fa.cancel') }}" style="text-align: center;">
            @csrf
            <button type="submit" style="background: none; border: none; color: rgba(255,255,255,0.4); font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: underline;">
                <i class="ph ph-arrow-left"></i> {{ __('Cancel & Return to Sign In') }}
            </button>
        </form>
    </div>
</div>

</body>
</html>
