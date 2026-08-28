<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Teacher Portal Login') }} | {{ __(\App\Models\Setting::getValue('university_name', 'National Technical Training Institute')) }}</title>
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&family=Inter:wght@300;400;500;600;700;800&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Phosphor Icons --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        :root {
            --primary: {{ \App\Models\Setting::getValue('primary_color', '#00d4a0') }};
            --primary-rgb: 0, 212, 160;
            --bg: #f0f4f8;
            --card: rgba(255, 255, 255, 0.7);
            --text-main: #0f172a;
            --text-sub: #64748b;
            --border: rgba(15, 23, 42, 0.1);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow: 0 20px 50px rgba(0,0,0,0.1);
        }

        :root[data-theme="dark"] {
            --bg: #020617;
            --card: rgba(15, 23, 42, 0.7);
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --border: rgba(255, 255, 255, 0.05);
            --shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        * { box-sizing: border-box; }
        
        html, body {
            overflow-x: hidden;
            width: 100%;
            height: 100%;
            background-color: var(--bg);
        }

        body {
            font-family: 'Battambang', 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background 0.3s ease;
            position: relative;
        }

        /* Animated Blobs */
        .blob-bg {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            filter: blur(80px);
            opacity: 0.6;
            overflow: hidden;
            transition: opacity 0.3s ease;
        }
        [data-theme="dark"] .blob-bg {
            opacity: 0.15;
            filter: blur(100px);
        }
        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(var(--primary-rgb), 0.3);
            animation: move 20s infinite alternate;
        }
        .blob-2 {
            width: 400px;
            height: 400px;
            background: rgba(59, 130, 246, 0.2);
            right: -10%;
            top: -10%;
            animation-delay: -5s;
        }
        .blob-3 {
            width: 300px;
            height: 300px;
            background: rgba(139, 92, 246, 0.2);
            bottom: -5%;
            left: 20%;
            animation-delay: -10s;
        }

        @keyframes move {
            from { transform: translate(-10%, -10%) rotate(0deg); }
            to { transform: translate(10%, 10%) rotate(360deg); }
        }

        .container {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            padding: 3rem 1.25rem;
            box-sizing: border-box;
            z-index: 1;
        }

        header {
            text-align: center;
            margin-bottom: 2.5rem;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .logo-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            overflow: hidden;
            animation: fadeIn 1s ease;
        }
        .logo-img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        h1 { font-size: 2rem; margin: 0; font-weight: 800; letter-spacing: -0.03em; }
        p.subtitle { color: var(--text-sub); font-size: 1rem; margin-top: 0.5rem; }

        .search-card {
            background: var(--card);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 2.5rem;
            padding: 2.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            position: relative;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.75rem; font-weight: 700; font-size: 0.85rem; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.1em; }
        
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--primary); font-size: 1.25rem; }
        
        .form-control {
            width: 100%;
            padding: 1.1rem 1.25rem 1.1rem 3.2rem;
            border: 2px solid var(--border);
            border-radius: 1.25rem;
            font-size: 16px;
            background: rgba(0, 0, 0, 0.04);
            color: var(--text-main);
            box-sizing: border-box;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            font-weight: 600;
        }
        [data-theme="dark"] .form-control {
            background: rgba(255, 255, 255, 0.05);
        }
        .form-control::placeholder {
            color: var(--text-sub);
            opacity: 0.8;
        }
        .form-control:focus { outline: none; border-color: var(--primary); background: rgba(0, 0, 0, 0.07); box-shadow: 0 0 0 8px rgba(var(--primary-rgb), 0.1); }
        [data-theme="dark"] .form-control:focus { background: rgba(255, 255, 255, 0.1); }

        .btn-check {
            width: 100%;
            padding: 1.1rem;
            background: linear-gradient(135deg, var(--primary), #00b894);
            color: #fff;
            border: none;
            border-radius: 1.25rem;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 10px 25px rgba(var(--primary-rgb), 0.3);
        }
        .btn-check:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 20px 35px rgba(var(--primary-rgb), 0.4); }
        .btn-check:active { transform: translateY(-1px) scale(1); }

        .error-msg { 
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 1.25rem;
            border-radius: 1.5rem;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 700;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .campus-widget {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 0.4rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-main);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .campus-widget i { font-size: 1rem; color: var(--primary); }

        @media (max-width: 480px) {
            .container { padding: 2rem 1rem; }
            .search-card { padding: 1.75rem; border-radius: 2rem; }
        }
    </style>
</head>
<body>
    <div class="blob-bg">
        <div class="blob"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="container">
        <header>
            @php 
                $uName = \App\Models\Setting::getValue('university_name', 'National Technical Training Institute');
                $uLogo = \App\Models\Setting::getAssetUrl('university_logo', '/images/ntti_logo.png'); 
            @endphp
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div class="campus-widget">
                    <i id="weatherIcon" class="ph ph-sun-horizon" style="color: #f59e0b;"></i>
                    <span>{{ __('Phnom Penh') }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="display: flex; background: var(--card); border: 1px solid var(--border); border-radius: 0.75rem; overflow: hidden; padding: 2px; backdrop-filter: blur(10px);">
                        <a href="{{ route('lang.switch.portal', 'en') }}" 
                           style="padding: 0.4rem 0.85rem; font-size: 0.72rem; font-weight: 700; text-decoration: none; border-radius: 0.6rem; transition: all 0.2s;
                                  background: {{ app()->getLocale() === 'en' ? 'var(--primary)' : 'transparent' }};
                                  color: {{ app()->getLocale() === 'en' ? '#000' : 'var(--text-sub)' }};">EN</a>
                        <a href="{{ route('lang.switch.portal', 'km') }}" 
                           style="padding: 0.4rem 0.85rem; font-size: 0.72rem; font-weight: 700; text-decoration: none; border-radius: 0.6rem; transition: all 0.2s;
                                  background: {{ app()->getLocale() === 'km' ? 'var(--primary)' : 'transparent' }};
                                  color: {{ app()->getLocale() === 'km' ? '#000' : 'var(--text-sub)' }};">KH</a>
                    </div>
                    <button id="portalThemeBtn" onclick="togglePortalTheme()" title="Dark Mode"
                            style="background: var(--card); border: 1px solid var(--border); backdrop-filter: blur(10px); color: var(--text-sub); width: 34px; height: 34px; border-radius: 0.75rem; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: all 0.2s; flex-shrink: 0;"
                            onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)';"
                            onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-sub)';">
                        <i id="portalThemeIcon" class="ph ph-moon"></i>
                    </button>
                </div>
            </div>

            @if($uLogo)
                <div class="logo-wrapper">
                    <img src="{{ $uLogo }}" class="logo-img" alt="Logo">
                </div>
            @endif

            <h1 style="font-size: 1.4rem; margin: 0 0 0.5rem; font-weight: 700;">{{ __('Teacher Portal') }}</h1>
            <p class="subtitle" style="margin-top: 0;">{{ __('Secure Access') }}</p>
        </header>

        <div class="search-card">
            @if(session('error') || $errors->any())
                <div class="error-msg">
                    <i class="ph ph-warning-circle" style="font-size:1.2rem; vertical-align:middle; margin-right:0.3rem;"></i>
                    {{ session('error') ?? $errors->first() }}
                </div>
            @endif

            <form action="{{ route('portal.login.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>{{ __('Employee ID') }}</label>
                    <div class="input-wrapper">
                        <i class="ph ph-identification-badge"></i>
                        <input type="text" id="employee_id" name="employee_id" class="form-control" placeholder="{{ __('e.g. T0005') }}" required value="{{ old('employee_id') }}" autocomplete="off" autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ __('6-digit PIN') }}</label>
                    <div class="input-wrapper">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" id="pin" name="pin" class="form-control" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;" required pattern="\d{6}" maxlength="6" inputmode="numeric" autocomplete="off">
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:1rem; margin-top: 2rem;">
                    <button type="submit" class="btn-check">
                        <i class="ph ph-sign-in"></i>
                        {{ __('View Attendance') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Portal-specific theme (separate from admin system and live monitor)
        const savedPortalTheme = localStorage.getItem('portal_theme');
        const sysDefault = '{{ \App\Models\Setting::getValue("default_theme", "dark") }}';
        if (savedPortalTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else if (savedPortalTheme === 'light') {
            document.documentElement.removeAttribute('data-theme');
        } else if (!savedPortalTheme && sysDefault === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }

        function togglePortalTheme() {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'dark';
            const btn = document.getElementById('portalThemeBtn');
            const icon = document.getElementById('portalThemeIcon');
            if (isDark) {
                html.removeAttribute('data-theme');
                localStorage.setItem('portal_theme', 'light');
                icon.className = 'ph ph-moon';
                btn.title = 'Dark Mode';
            } else {
                html.setAttribute('data-theme', 'dark');
                localStorage.setItem('portal_theme', 'dark');
                icon.className = 'ph ph-sun';
                btn.title = 'Light Mode';
            }
        }

        // Sync button icon on load
        (function() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const icon = document.getElementById('portalThemeIcon');
            if (icon) icon.className = isDark ? 'ph ph-sun' : 'ph ph-moon';
        })();

        // ── Live Lockout Countdown ──
        document.addEventListener("DOMContentLoaded", function() {
            const errorBoxes = document.querySelectorAll('.error-msg');
            errorBoxes.forEach(box => {
                let match = box.innerHTML.match(/in\s+(\d+)\s+seconds/i);
                if (match) {
                    let seconds = parseInt(match[1]);
                    const btn = document.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        btn.style.opacity = '0.5';
                        btn.style.cursor = 'not-allowed';
                    }
                    
                    let interval = setInterval(() => {
                        seconds--;
                        if (seconds <= 0) {
                            clearInterval(interval);
                            box.innerHTML = box.innerHTML.replace(/in\s+\d+\s+seconds/i, "now. You can try again.");
                            if (btn) {
                                btn.disabled = false;
                                btn.style.opacity = '1';
                                btn.style.cursor = 'pointer';
                            }
                        } else {
                            box.innerHTML = box.innerHTML.replace(/in\s+\d+\s+seconds/i, `in ${seconds} seconds`);
                        }
                    }, 1000);
                }
            });
        });
    </script>
</body>
</html>
