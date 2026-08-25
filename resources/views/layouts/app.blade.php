<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - NTTI Attendance</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&family=Inter:wght@300;400;500;600;700;800&family=Kantumruy+Pro:wght@300;400;500;600;700&family=Suwannaphum:wght@300;400;700;900&family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">

    <!-- Phosphor Icons for modern slick icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    @php
        $primaryColor = \App\Models\Setting::getValue('primary_color', '#00d4a0');
        $defaultTheme = \App\Models\Setting::getValue('default_theme', 'dark');
        $fontSize     = \App\Models\Setting::getValue('font_size', '14');
        $iconSize     = \App\Models\Setting::getValue('global_icon_size', '1.1');
        $fontFamily   = \App\Models\Setting::getValue('font_family', 'Battambang');
        $borderRadius = \App\Models\Setting::getValue('border_radius', '0.5rem');
        $glassEnabled = \App\Models\Setting::getValue('enable_glassmorphism', 'on');
    @endphp
    <style>
        :root {
            --primary: {{ $primaryColor }};
            --font-family: '{{ $fontFamily }}', sans-serif;
            --border-radius: {{ $borderRadius }};
            @if($glassEnabled === 'on')
            --card-bg-glass: rgba(13, 17, 23, 0.65);
            --card-border-glass: rgba(255, 255, 255, 0.08);
            --backdrop-blur: blur(16px);
            @else
            --card-bg-glass: var(--bg-card);
            --card-border-glass: var(--border);
            --backdrop-blur: none;
            @endif
        }

        [data-theme="light"] {
            --primary: {{ $primaryColor }};
            --font-family: '{{ $fontFamily }}', sans-serif;
            --border-radius: {{ $borderRadius }};
            @if($glassEnabled === 'on')
            --card-bg-glass: rgba(255, 255, 255, 0.65);
            --card-border-glass: rgba(0, 0, 0, 0.06);
            --backdrop-blur: blur(16px);
            @else
            --card-bg-glass: var(--bg-card);
            --card-border-glass: var(--border);
            --backdrop-blur: none;
            @endif
        }

        body, button, input, select, textarea { font-family: var(--font-family) !important; }
        .card, .stat-card, .btn, .form-control, .insight-card, .glass-panel, .panel-header, .alert, .badge {
            border-radius: var(--border-radius) !important;
        }
        
        /* Force Native Date & Time Picker Icons: WHITE in Dark Mode, BLACK in Light Mode */
        input[type="date"],
        input[type="time"],
        input[type="datetime-local"],
        .form-control,
        .form-control-minimal {
            color-scheme: dark !important;
        }

        [data-theme="light"] input[type="date"],
        [data-theme="light"] input[type="time"],
        [data-theme="light"] input[type="datetime-local"],
        [data-theme="light"] .form-control,
        [data-theme="light"] .form-control-minimal {
            color-scheme: light !important;
        }

        /* Custom Date/Time Picker Icons */
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer !important;
            opacity: 1 !important;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%2300d4a0"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>') !important;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        input[type="time"]::-webkit-calendar-picker-indicator {
            cursor: pointer !important;
            opacity: 1 !important;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%2300d4a0"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/><path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>') !important;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        /* Global Glassmorphism for Panels */
        .card, .stat-card, .glass-panel {
            background: var(--card-bg-glass) !important;
            backdrop-filter: var(--backdrop-blur) !important;
            -webkit-backdrop-filter: var(--backdrop-blur) !important;
            border: 1px solid var(--card-border-glass) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .card:hover, .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        
        .table tbody tr {
            transition: all 0.2s;
        }
        @if($glassEnabled !== 'on')
        /* ── Glassmorphism DISABLED — flat/performance mode ── */
        body {
            background-image: none !important;
        }
        .sidebar,
        .main-content,
        .topbar,
        .glass-panel {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        /* Sidebar: solid flat panel, no blur, no transparency */
        .sidebar {
            background: var(--bg-card) !important;
            border-right: 2px solid var(--border) !important;
            box-shadow: none !important;
            margin: 0 !important;
            height: 100vh !important;
            border-radius: 0 !important;
        }
        /* Main content: flat, no glass panel */
        .main-content {
            background: var(--bg-dark) !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            border: none !important;
            margin: 0 !important;
        }
        /* Topbar: solid with a visible bottom border */
        .topbar {
            background: var(--bg-card) !important;
            border-bottom: 2px solid var(--border) !important;
            box-shadow: none !important;
        }
        /* Settings panels: solid instead of glassy */
        .glass-panel {
            background: var(--bg-card) !important;
            box-shadow: none !important;
        }
        @endif
        @keyframes livePulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(1.4); }
        }
    /* Modal styles handled in style.css */
/* Modal styles handled in style.css */

.insight-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    width: 95%;
    max-width: 450px;
    border-radius: 1.5rem;
    padding: 2rem;
    position: relative;
    box-shadow: 0 30px 60px rgba(0,0,0,0.5);
    animation: modalPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.insight-close {
    position: absolute;
    top: 1.25rem;
    right: 1.25rem;
    background: var(--bg-dark);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sidebar-logo-badge {
    width: 70px;
    height: 70px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
    transition: all 0.3s ease;
    transform: translateZ(0);
    outline: none !important;
    -webkit-tap-highlight-color: transparent;
}
.sidebar-logo-badge:focus, .sidebar-logo-badge:active {
    outline: none !important;
    border: none !important;
}

.sidebar.collapsed .sidebar-logo-badge {
    width: 48px;
    height: 48px;
}

.sidebar-logo-badge img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.sidebar-logo-badge:hover {
    transform: scale(1.05);
}

.insight-header { display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; }
.insight-header h2 { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem; color: var(--text-primary); }
.insight-header p { color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.75rem; }

.insight-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem; }
.insight-stat-box { background: var(--bg-dark); border: 1px solid var(--border); padding: 1rem; border-radius: 1rem; text-align: center; }
.is-label { display: block; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem; }
.is-value { display: block; font-size: 1.25rem; font-weight: 800; margin-bottom: 0.25rem; }
.is-value.primary { color: var(--primary); }
.is-value.danger { color: var(--danger); }
.is-meta { font-size: 0.6rem; color: var(--text-muted); }

.insight-section h4 { font-size: 0.85rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-primary); }
.insight-list { display: flex; flex-direction: column; gap: 0.75rem; }
.insight-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: var(--bg-dark); border: 1px solid var(--border); border-radius: 0.75rem; }
.ii-date { font-size: 0.8rem; font-weight: 600; color: var(--text-primary); }
.ii-times { font-size: 0.75rem; color: var(--text-secondary); }

@keyframes modalPop { from { opacity:0; transform:scale(0.9); } to { opacity:1; transform:scale(1); } }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.animate-spin { animation: spin 1s linear infinite; display: inline-block; }
</style>
    
    <script>
        // Apply font size immediately to prevent flash
        const _fs = parseInt(localStorage.getItem('font_size')) || {{ $fontSize }};
        if (_fs && _fs >= 11 && _fs <= 20) document.documentElement.style.fontSize = _fs + 'px';

        // Apply theme immediately to prevent flash
        const savedTheme = localStorage.getItem('theme');
        const defaultTheme = '{{ $defaultTheme }}';

        if (savedTheme === 'light' || (!savedTheme && defaultTheme === 'light')) {
            document.documentElement.setAttribute('data-theme', 'light');
        } else if (savedTheme === 'dark' || (!savedTheme && defaultTheme === 'dark')) {
            document.documentElement.removeAttribute('data-theme');
        }



        // Apply global icon size immediately to prevent flash
        const _is = localStorage.getItem('global_icon_size') || '{{ $iconSize }}';
        if (_is) document.documentElement.style.setProperty('--icon-size', _is + 'rem');
    </script>
    @stack('styles')
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay" style="cursor: pointer;"></div>
    <aside class="sidebar" id="mainSidebar">
        <div class="sidebar-header" style="flex-direction: column; text-align: center; gap: 0.5rem; position: relative;">
            <button id="sidebarCloseMobile" class="hide-desktop" style="position: absolute; right: 1.25rem; top: 1.25rem; background: rgba(255,255,255,0.05); border: none; color: var(--text-primary); font-size: 1.75rem; width: 44px; height: 44px; border-radius: 50%; align-items: center; justify-content: center; cursor: pointer; z-index: 110;">
                <i class="ph ph-x"></i>
            </button>
            @php
                $uName = __(\App\Models\Setting::getValue('university_name', 'National Technical Training Institute'));
                $uLogo = \App\Models\Setting::getAssetUrl('university_logo', '/images/ntti_logo.png');
                $uWeb  = \App\Models\Setting::getValue('university_website', '#');
                $uFb   = \App\Models\Setting::getValue('university_facebook', '#');
            @endphp
            @if($uLogo)
                <div class="sidebar-logo-badge" onclick="openSchoolInfo()" title="{{ __('School Info') }}">
                    <img src="{{ $uLogo }}" class="brand-logo" alt="Logo">
                </div>
            @endif
            <h2 style="font-size: 1.25rem; cursor: pointer;" onclick="openSchoolInfo()">{{ $uName }}</h2>
        </div>
        <nav class="nav-links">
            <a href="{{ route('dashboard') }}" data-title="{{ __('Dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="ph ph-squares-four nav-icon"></i> <span class="nav-text">{{ __('Dashboard') }}</span>
            </a>
            <a href="{{ route('scan.index') }}" data-title="{{ __('Scan Station') }}" class="nav-item {{ request()->routeIs('scan.*') ? 'active' : '' }}">
                <i class="ph ph-scan nav-icon"></i> <span class="nav-text">{{ __('Scan Station') }}</span>
            </a>
            <a href="{{ route('teachers.index') }}" data-title="{{ __('Teacher Directory') }}" class="nav-item {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                <i class="ph ph-users nav-icon"></i> <span class="nav-text">{{ __('Teacher Directory') }}</span>
            </a>
            <a href="{{ route('rfid.index') }}" data-title="{{ __('RFID Cards') }}" class="nav-item {{ request()->routeIs('rfid.*') ? 'active' : '' }}">
                <i class="ph ph-identification-badge nav-icon"></i> <span class="nav-text">{{ __('RFID Cards') }}</span>
            </a>
            <a href="{{ route('departments.index') }}" data-title="{{ __('Departments') }}" class="nav-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                <i class="ph ph-buildings nav-icon"></i> <span class="nav-text">{{ __('Departments') }}</span>
            </a>
            <a href="{{ route('reports.index') }}" data-title="{{ __('Reports') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="ph ph-chart-bar nav-icon"></i> <span class="nav-text">{{ __('Reports') }}</span>
            </a>
            <a href="{{ route('security.index') }}" data-title="{{ __('Security Logs') }}" class="nav-item {{ request()->routeIs('security.*') ? 'active' : '' }}">
                <i class="ph ph-shield-checkered nav-icon"></i> <span class="nav-text">{{ __('Security Logs') }}</span>
            </a>
            <a href="{{ route('settings.index') }}" data-title="{{ __('Settings') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="ph ph-gear nav-icon"></i> <span class="nav-text">{{ __('Settings') }}</span>
            </a>
        </nav>

        {{-- Mobile Additional Actions --}}
        <div class="hide-desktop" style="padding: 1.5rem 1rem 1rem; border-top: 1px solid var(--border); display:flex; flex-direction:column; gap:0.75rem; margin-top: auto;">
            <a href="{{ route('live.monitor') }}" target="_blank" class="btn btn-secondary" style="border-color: rgba(239,68,68,0.5); color: var(--danger); justify-content: center; gap: 0.5rem; background: rgba(239,68,68,0.05);">
                <span style="width:6px;height:6px;border-radius:50%;background:var(--danger);animation:livePulse 1.2s ease-in-out infinite;"></span> {{ __('LIVE MONITOR') }}
            </a>
            <a href="{{ route('portal.index') }}" target="_blank" class="btn btn-secondary" style="border-color: rgba(var(--primary-rgb),0.5); color: var(--primary); justify-content: center; gap: 0.5rem; background: rgba(var(--primary-rgb),0.05);">
                <i class="ph ph-identification-card"></i> {{ __('TEACHER PORTAL') }}
            </a>
            <div style="display: flex; border: 1px solid var(--border); border-radius: 0.5rem; overflow: hidden; margin-top: 0.5rem;">
                <a href="{{ route('lang.switch', 'en') }}" style="flex:1; text-align:center; padding:0.5rem; background:{{ app()->getLocale()==='en' ? 'var(--primary)' : 'transparent' }}; color:{{ app()->getLocale()==='en' ? '#000' : 'var(--text-secondary)' }}; font-weight:600; text-decoration:none;">English</a>
                <a href="{{ route('lang.switch', 'km') }}" style="flex:1; text-align:center; padding:0.5rem; border-left:1px solid var(--border); background:{{ app()->getLocale()==='km' ? 'var(--primary)' : 'transparent' }}; color:{{ app()->getLocale()==='km' ? '#000' : 'var(--text-secondary)' }}; font-weight:600; text-decoration:none;">ខ្មែរ</a>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="date-header d-flex align-center gap-2">
                <button id="sidebarToggle" style="background:none; border:none; color:var(--text-primary); font-size:1.5rem; cursor:pointer; display:flex; align-items:center;">
                    <i class="ph ph-list"></i>
                </button>
                <span id="header-date" style="color: var(--text-secondary); margin-left: 0.5rem;" class="hide-mobile">{{ now()->locale(app()->getLocale())->translatedFormat('l, j F Y') }}</span>
                <div style="display: flex; align-items: center; gap: 0.8rem; background: rgba(var(--primary-rgb), 0.08); padding: 0.4rem 1.2rem; border-radius: 2rem; border: 1px solid rgba(var(--primary-rgb), 0.2); margin-left: 0.75rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary); box-shadow: 0 0 12px var(--primary); animation: livePulse 2s ease-in-out infinite;"></span>
                    <span id="header-clock" style="font-weight: 800; color: var(--primary); font-family: 'JetBrains Mono', 'Roboto Mono', monospace; font-size: 1.15rem; letter-spacing: 0.5px;">{{ now()->format('H:i:s') }}</span>
                </div>
                
                {{-- Weather Widget --}}
                <div class="hide-mobile" style="display: flex; align-items: center; gap: 0.5rem; background: rgba(0,0,0,0.02); padding: 0.4rem 0.85rem; border-radius: 2rem; border: 1px solid var(--border); margin-left: 0.5rem; cursor: default;">
                    <i id="sysWeatherIcon" class="ph ph-sun-horizon" style="font-size: 1.1rem; color: #f59e0b; transition: all 0.3s;"></i>
                    <span id="sysWeatherText" style="font-weight: 700; color: var(--text-primary); font-size: 0.9rem;">{{ __('Phnom Penh') }} &nbsp;--°C</span>
                </div>
            </div>

            {{-- Global Search Center --}}
            <div class="topbar-search hide-mobile">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="globalSearchInput" placeholder="{{ __('Search anything...') }}">
                <div class="search-kbd">⌘K</div>

                {{-- Search Results Dropdown --}}
                <div id="searchDropdown" style="display: none; position: absolute; top: 110%; left: 0; right: 0; background: var(--bg-card); border: 1px solid var(--border); border-radius: 1rem; box-shadow: 0 15px 45px rgba(0,0,0,0.5); z-index: 1000; max-height: 450px; overflow-y: auto; padding: 0.5rem; backdrop-filter: blur(20px);">
                    <div id="searchResults" style="display: flex; flex-direction: column; gap: 0.25rem;"></div>
                </div>
            </div>

            <div class="user-profile" style="gap: 0.5rem;">
                {{-- Hardware Status Indicator --}}
                <div id="hardware-status-badge" class="d-flex align-center gap-1" style="padding:0.3rem 0.6rem; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:4px; transition:all 0.3s; cursor:help;" title="{{ __('Scanner Health Status') }}">
                    <span id="hw-status-dot" style="width:7px; height:7px; border-radius:50%; background:#94a3b8; box-shadow:0 0 6px rgba(148,163,184,0.5);"></span>
                    <span id="hw-status-text" style="font-size:0.7rem; font-weight:800; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.01em;">{{ __('Checking...') }}</span>
                    <i id="hw-status-icon" class="ph ph-wifi-high" style="font-size:0.85rem; color:var(--text-muted); margin-left:0.1rem;"></i>
                </div>

                

                {{-- Live Monitor Button --}}
                <a href="{{ route('live.monitor') }}" target="_blank" class="hide-mobile"
                   style="display:flex; align-items:center; gap:0.3rem; padding:0.3rem 0.6rem; background:rgba(239,68,68,0.12); border:1px solid var(--danger); color:var(--danger); text-decoration:none; font-size:0.75rem; font-weight:800; letter-spacing:0.02em; transition:all 0.15s;"
                   onmouseover="this.style.background='var(--danger)';this.style.color='#fff';"
                   onmouseout="this.style.background='rgba(239,68,68,0.12)';this.style.color='var(--danger)';"
                   data-title="{{ __('Open Live Monitor') }}">
                    <span style="width:6px;height:6px;border-radius:50%;background:var(--danger);animation:livePulse 1.2s ease-in-out infinite;display:inline-block;"></span>
                    {{ __('LIVE') }}
                </a>

                {{-- Teacher Portal Button --}}
                <a href="{{ route('portal.index') }}" target="_blank" class="hide-mobile"
                   style="display:flex; align-items:center; gap:0.3rem; padding:0.3rem 0.6rem; background:rgba(var(--primary-rgb), 0.12); border:1px solid var(--primary); color:var(--primary); text-decoration:none; font-size:0.75rem; font-weight:800; letter-spacing:0.02em; transition:all 0.15s;"
                   onmouseover="this.style.background='var(--primary)';this.style.color='#000';"
                   onmouseout="this.style.background='rgba(var(--primary-rgb), 0.12)';this.style.color='var(--primary)';"
                   data-title="{{ __('Open Teacher Portal') }}">
                    <i class="ph ph-identification-card" style="font-size:0.9rem;"></i>
                    {{ __('PORTAL') }}
                </a>

                {{-- Language Switcher --}}
                <div class="d-flex hide-mobile" style="border: 1px solid var(--border); overflow: hidden;">
                    <a href="{{ route('lang.switch', 'en') }}"
                       style="padding:0.35rem 0.6rem; font-size:0.75rem; font-weight:600; background:{{ app()->getLocale()==='en' ? 'var(--primary)' : 'transparent' }}; color:{{ app()->getLocale()==='en' ? '#000' : 'var(--text-secondary)' }}; text-decoration:none; transition:all 0.15s;"
                       data-title="English">EN</a>
                    <a href="{{ route('lang.switch', 'km') }}"
                       style="padding:0.35rem 0.6rem; font-size:0.75rem; font-weight:600; background:{{ app()->getLocale()==='km' ? 'var(--primary)' : 'transparent' }}; color:{{ app()->getLocale()==='km' ? '#000' : 'var(--text-secondary)' }}; text-decoration:none; border-left:1px solid var(--border); transition:all 0.15s;"
                       data-title="ខ្មែរ">KH</a>
                </div>

                {{-- Pending Corrections Button --}}
                @php
                    $pendingCorrections = \App\Models\AttendanceCorrection::where('status', 'pending')->count();
                @endphp
                <button onclick="localStorage.setItem('settings_tab', 'section-corrections'); window.location.href='{{ route('settings.index') }}'" title="{{ __('Pending Corrections') }}" class="hide-mobile" style="background:none; border:1px solid var(--border); color:#f59e0b; padding:0.4rem 0.5rem; cursor:pointer; display:flex; align-items:center; font-size:1rem; transition:all 0.15s; position: relative; margin-right: 0.2rem;">
                    <i class="ph ph-clipboard-text"></i>
                    @if($pendingCorrections > 0)
                        <span style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; font-size: 0.6rem; font-weight: 800; padding: 0.1rem 0.3rem; border-radius: 10px; animation: livePulse 2s infinite;">{{ $pendingCorrections }}</span>
                    @endif
                </button>

                {{-- Telegram Chats Button --}}
                <button onclick="openTelegramChatsModal()" title="{{ __('View Recent Chat IDs') }}" class="hide-mobile" style="background:none; border:1px solid var(--border); color:#0088cc; padding:0.4rem 0.5rem; cursor:pointer; display:flex; align-items:center; font-size:1rem; transition:all 0.15s; position: relative;">
                    <i class="ph ph-telegram-logo"></i>
                    <span id="telegram-chat-count" style="display: none; position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; font-size: 0.6rem; font-weight: 800; padding: 0.1rem 0.3rem; border-radius: 10px;">0</span>
                </button>

                <button id="themeToggle" title="{{ __('Toggle Theme') }}" style="background:none; border:1px solid var(--border); color:var(--text-secondary); padding:0.4rem 0.5rem; cursor:pointer; display:flex; align-items:center; font-size:1rem; transition:all 0.15s;">
                    <i class="ph ph-moon" id="themeIcon"></i>
                </button>
                <div onclick="openAdminProfile()" style="display: flex; align-items: center; gap: 0.75rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border); padding: 0.25rem 0.25rem 0.25rem 0.25rem; border-radius: 2rem; margin-left: 0.5rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.06)';this.style.borderColor='var(--primary)';" onmouseout="this.style.background='rgba(255,255,255,0.03)';this.style.borderColor='var(--border)';">

                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), #00b894); color: #000; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; border: 2px solid rgba(255,255,255,0.1); box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0; display: flex;" onclick="event.stopPropagation()">
                        @csrf
                        <button type="submit" style="background: rgba(239,68,68,0.08); border: none; color: #ef4444; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; outline: none;" 
                                onmouseover="this.style.background='#ef4444';this.style.color='#fff';" 
                                onmouseout="this.style.background='rgba(239,68,68,0.08)';this.style.color='#ef4444';" 
                                title="{{ __('Sign Out') }}">
                            <i class="ph ph-power" style="font-size: 1.05rem; font-weight: 800;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <div class="content-wrapper">
            @yield('content')
        </div>

        <footer class="main-footer" style="justify-content: center; gap: 1rem;">
            <span class="class-badge">IT08B2</span>
            <span class="copyright">&copy; {{ date('Y') }} {{ __(\App\Models\Setting::getValue('university_name', 'National Technical Training Institute')) }}. {{ __('All rights reserved.') }}</span>
        </footer>
    </main>

    <script>
        // Update header clock
        function updateClock() {
            const now = new Date();
            document.getElementById('header-clock').textContent = now.toLocaleTimeString('en-US', { hour12: false });
        }
        setInterval(updateClock, 1000);

        // System Weather Logic
        async function fetchSystemWeather() {
            try {
                const res = await fetch('https://api.open-meteo.com/v1/forecast?latitude=11.5564&longitude=104.9282&current_weather=true');
                const data = await res.json();
                const weather = data.current_weather;
                if(weather) {
                    const textEl = document.getElementById('sysWeatherText');
                    if (textEl) textEl.innerHTML = `{{ __('Phnom Penh') }} &nbsp;${Math.round(weather.temperature)}°C`;
                    
                    const icon = document.getElementById('sysWeatherIcon');
                    if (icon) {
                        const code = weather.weathercode;
                        const isDay = weather.is_day;
                        
                        let iconClass = 'ph-sun';
                        let iconColor = '#f59e0b';
                        
                        if (code === 0) {
                            iconClass = isDay ? 'ph-sun' : 'ph-moon';
                            iconColor = isDay ? '#f59e0b' : '#94a3b8';
                        } else if (code >= 1 && code <= 3) {
                            iconClass = isDay ? 'ph-cloud-sun' : 'ph-cloud-moon';
                            if (code === 3) iconClass = 'ph-cloud';
                            iconColor = '#94a3b8';
                        } else if (code >= 45 && code <= 48) {
                            iconClass = 'ph-cloud-fog';
                            iconColor = '#94a3b8';
                        } else if ((code >= 51 && code <= 67) || (code >= 80 && code <= 82)) {
                            iconClass = 'ph-cloud-rain';
                            iconColor = '#3b82f6';
                        } else if (code >= 95) {
                            iconClass = 'ph-cloud-lightning';
                            iconColor = '#8b5cf6';
                        }
                        
                        icon.className = `ph ${iconClass}`;
                        icon.style.color = iconColor;
                    }
                }
            } catch (e) {
                console.error('System weather fetch failed', e);
            }
        }
        fetchSystemWeather();
        setInterval(fetchSystemWeather, 30 * 60 * 1000); // Update every 30 minutes

        // Theme Toggle Logic
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        
        function updateThemeIcon() {
            if (document.documentElement.getAttribute('data-theme') === 'light') {
                themeIcon.className = 'ph ph-sun';
                themeIcon.style.color = '#f59e0b';
            } else {
                themeIcon.className = 'ph ph-moon';
                themeIcon.style.color = '#94a3b8';
            }
        }

        themeToggleBtn.addEventListener('click', () => {
            if (document.documentElement.getAttribute('data-theme') === 'light') {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
            updateThemeIcon();
        });
        
        // Initial setup
        updateThemeIcon();

        // Sidebar Toggle Logic
        const sidebar = document.getElementById('mainSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarCloseMobile');
        
        function openSidebar() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }

        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                // Mobile behavior: Slide in/out
                if (sidebar.classList.contains('active')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            } else {
                // Desktop behavior: Collapse/Expand
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
            }
        });

        // Ensure sidebar starts closed on mobile regardless of desktop saved state
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('active');
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeSidebar);
            closeBtn.addEventListener('touchstart', (e) => { e.preventDefault(); closeSidebar(); });
        }
        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
            overlay.addEventListener('touchstart', (e) => { e.preventDefault(); closeSidebar(); });
        }

        // Initial Desktop state
        if (window.innerWidth > 768 && localStorage.getItem('sidebar_collapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        // ── Modern Alert/Confirm/Toast System ──────────────────
        
        // 1. Toast UI logic
        const toastContainer = document.createElement('div');
        toastContainer.style = "position:fixed; top:20px; right:20px; z-index:9999; display:flex; flex-direction:column; gap:10px; pointer-events:none;";
        document.body.appendChild(toastContainer);

        window.showToast = (msg, type = 'success') => {
            const toast = document.createElement('div');
            const color = type === 'success' ? 'var(--primary)' : (type === 'error' ? 'var(--danger)' : 'var(--warning)');
            const icon = type === 'success' ? 'ph ph-check-circle' : (type === 'error' ? 'ph ph-x-circle' : 'ph ph-warning');
            
            toast.style.cssText = `
                min-width: 280px; background: var(--bg-card); border-left: 4px solid ${color}; color: var(--text-primary);
                padding: 1rem 1.25rem; box-shadow: 0 10px 25px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 0.75rem;
                transform: translateX(120%); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                pointer-events: auto; border-radius: 4px; cursor: pointer;
            `;
            toast.innerHTML = `<i class="${icon}" style="font-size:1.25rem; color:${color};"></i> <span style="font-size:0.88rem; font-weight:500;">${msg}</span>`;
            
            toastContainer.appendChild(toast);
            setTimeout(() => toast.style.transform = 'translateX(0)', 10);
            
            const remove = () => {
                toast.style.transform = 'translateX(120%)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 400);
            };
            
            setTimeout(remove, 4000);
            toast.onclick = remove;
        };

        // 2. Override window.alert
        const alertModal = document.createElement('div');
        alertModal.style = "position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); z-index:10000; display:none; align-items:center; justify-content:center; padding:20px;";
        alertModal.innerHTML = `
            <div style="background:var(--bg-card); border:1px solid var(--border); width:100%; max-width:400px; border-radius:8px; overflow:hidden; animation:modalPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                <div style="padding:1.5rem; text-align:center;">
                    <i class="ph ph-warning-circle" style="font-size:3rem; color:var(--primary); margin-bottom:1rem; display:block;"></i>
                    <p id="customAlertMsg" style="font-size:1rem; font-weight:500; color:var(--text-primary); margin:0; line-height:1.5;"></p>
                </div>
                <div style="padding:1rem; border-top:1px solid var(--border); display:flex; justify-content:center; background:var(--bg-dark);">
                    <button id="customAlertBtn" class="btn btn-primary" style="width:120px; cursor: pointer;">{{ __('OK') }}</button>
                </div>
            </div>
        `;
        document.body.appendChild(alertModal);

        const style = document.createElement('style');
        style.innerHTML = "@keyframes modalPop { from { opacity:0; transform:scale(0.9); } to { opacity:1; transform:scale(1); } }";
        document.head.appendChild(style);

        window.alert = (msg) => {
            return new Promise((resolve) => {
                document.getElementById('customAlertMsg').innerText = msg;
                alertModal.style.display = 'flex';
                const btn = document.getElementById('customAlertBtn');
                const close = () => {
                    alertModal.style.display = 'none';
                    btn.removeEventListener('click', close);
                    resolve();
                };
                btn.addEventListener('click', close);
            });
        };

        // 3. Override window.confirm
        const confirmModal = document.createElement('div');
        confirmModal.style = "position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); z-index:10000; display:none; align-items:center; justify-content:center; padding:20px;";
        confirmModal.innerHTML = `
            <div style="background:var(--bg-card); border:1px solid var(--border); width:100%; max-width:400px; border-radius:8px; overflow:hidden; animation:modalPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                <div style="padding:1.5rem; text-align:center;">
                    <i class="ph ph-question" style="font-size:3rem; color:var(--warning); margin-bottom:1rem; display:block;"></i>
                    <p id="customConfirmMsg" style="font-size:1rem; font-weight:500; color:var(--text-primary); margin:0; line-height:1.5;"></p>
                </div>
                <div style="padding:1rem; border-top:1px solid var(--border); display:flex; gap:10px; justify-content:center; background:var(--bg-dark);">
                    <button id="confirmCancelBtn" class="btn btn-secondary" style="width:100px;">{{ __('Cancel') }}</button>
                    <button id="confirmOkBtn" class="btn btn-primary" style="width:100px;">{{ __('Confirm') }}</button>
                </div>
            </div>
        `;
        document.body.appendChild(confirmModal);

        window.confirm = (msg) => {
            return new Promise((resolve) => {
                document.getElementById('customConfirmMsg').innerText = msg;
                confirmModal.style.display = 'flex';
                
                const okBtn = document.getElementById('confirmOkBtn');
                const cancelBtn = document.getElementById('confirmCancelBtn');
                
                const onOk = () => { cleanup(); resolve(true); };
                const onCancel = () => { cleanup(); resolve(false); };
                const cleanup = () => {
                    confirmModal.style.display = 'none';
                    okBtn.removeEventListener('click', onOk);
                    cancelBtn.removeEventListener('click', onCancel);
                };
                
                okBtn.addEventListener('click', onOk);
                cancelBtn.addEventListener('click', onCancel);
            });
        };

        // Utility for AJAX requests (csrf token setup)
        window.fetchApi = async (url, options = {}) => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            options.headers = {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                ...options.headers
            };
            
            // If body is NOT FormData, set JSON content type
            if (options.body && !(options.body instanceof FormData)) {
                options.headers['Content-Type'] = 'application/json';
            }

            try {
                const response = await fetch(url, options);
                const contentType = response.headers.get('content-type');
                const isJson = contentType && contentType.includes('application/json');
                
                if (!response.ok) {
                    let errorMessage = `Server Error (${response.status})`;
                    if (isJson) {
                        const errorData = await response.json().catch(() => ({}));
                        if (response.status === 422 && errorData.errors) {
                            errorMessage = Object.values(errorData.errors).flat().join('\n');
                        } else {
                            errorMessage = errorData.message || errorMessage;
                        }
                    }
                    throw new Error(errorMessage);
                }

                return isJson ? response.json() : response.text();
            } catch (err) {
                const cleanMsg = err.message.includes('Unexpected token') 
                    ? 'Invalid server response (HTML instead of JSON)' 
                    : err.message;
                window.showToast(cleanMsg, 'error');
                throw err;
            }
        };

        // ── Hardware Heartbeat Monitor ──────────────────
        function updateHardwareStatus() {
            fetch('/api/device-status')
                .then(res => res.json())
                .then(data => {
                    const dot = document.getElementById('hw-status-dot');
                    const text = document.getElementById('hw-status-text');
                    const badge = document.getElementById('hardware-status-badge');
                    const icon = document.getElementById('hw-status-icon');
                    
                        if (data.online) {
                            dot.style.background = 'var(--primary)';
                            dot.style.boxShadow = '0 0 10px rgba(var(--primary-rgb), 0.6)';
                            dot.style.animation = 'livePulse 2s infinite';
                            text.textContent = '{{ __("Scanner Online") }}';
                            text.style.color = 'var(--primary)';
                            badge.style.borderColor = 'rgba(var(--primary-rgb), 0.3)';
                            
                            // RSSI Icon logic
                            let signalIcon = 'ph-wifi-high';
                            if (data.rssi <= -80) signalIcon = 'ph-wifi-low';
                            else if (data.rssi <= -65) signalIcon = 'ph-wifi-medium';
                            
                            icon.className = 'ph ' + signalIcon;
                            icon.style.color = 'var(--primary)';
                            badge.title = `Scanner Healthy\nIP: ${data.ip}\nSignal: ${data.rssi} dBm\nLast seen: ${data.last_seen_ago}`;
                        } else {
                            dot.style.background = 'var(--danger)';
                            dot.style.boxShadow = '0 0 10px rgba(239,68,68,0.6)';
                            dot.style.animation = 'none';
                            text.textContent = '{{ __("Scanner Offline") }}';
                            text.style.color = 'var(--danger)';
                            badge.style.borderColor = 'rgba(239,68,68,0.3)';
                            icon.className = 'ph ph-wifi-slash';
                            icon.style.color = 'var(--danger)';
                            badge.title = `Scanner Disconnected\nLast IP: ${data.ip}\nLast seen: ${data.last_seen_ago}`;
                        }
                    })
                    .catch(() => {
                        document.getElementById('hw-status-text').textContent = '{{ __("Link Lost") }}';
                    });
            }
            updateHardwareStatus();
            setInterval(updateHardwareStatus, 10000); // Check every 10s

        // Handle Laravel session messages
        @if(session('error')) window.showToast("{{ session('error') }}", 'error'); @endif

        // ── Teacher Insights Global Logic ────────────────
        window.openTeacherInsights = async function(id) {
            console.log('🔍 Global Click Teacher ID:', id);
            const modal = document.getElementById('insightModal');
            if (!modal) return;
            
            modal.classList.add('active');
            document.getElementById('insight-name').textContent = '{{ __("Loading...") }}';
            document.getElementById('insight-recent-list').innerHTML = '<div style="text-align:center; padding:2rem;"><i class="ph ph-circle-notch animate-spin"></i> {{ __("Loading data...") }}</div>';

            try {
                const data = await window.fetchApi(`/api-web/teachers/${id}/insights`);
                const t = data.teacher;
                const stats = data.stats;
                
                document.getElementById('insight-name-kh').textContent = t.name_kh || '';
                document.getElementById('insight-name').textContent = t.name;
                document.getElementById('insight-dept').textContent = window.transDept ? window.transDept(t.department) : (t.department || 'General');
                
                const badge = document.getElementById('insight-score-badge');
                badge.textContent = `{{ __('Score') }}: ${stats.score}%`;
                badge.className = 'badge-pill ' + (stats.score > 80 ? 'success' : (stats.score > 60 ? 'warning' : 'danger'));

                document.getElementById('is-present').textContent = stats.present_days;
                document.getElementById('is-late').textContent = stats.late_days;
                document.getElementById('is-score').textContent = stats.score + '%';

                const avatarContainer = document.getElementById('insight-avatar-container');
                const initial = t.name ? t.name.charAt(0).toUpperCase() : '?';
                avatarContainer.innerHTML = t.photo 
                    ? `<div class="teacher-avatar" style="width:80px; height:80px; border-radius:50%; overflow:hidden; cursor:pointer; transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onclick="document.getElementById('imageViewerImg').src='${t.photo}'; document.getElementById('imageViewerModal').classList.add('active');"><img src="${t.photo}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;"></div>`
                    : `<div class="avatar-placeholder" style="width:80px; height:80px; font-size:2rem; border-radius:50%; display:flex; align-items:center; justify-content:center; background:rgba(var(--primary-rgb),0.1); color:var(--primary);">${initial}</div>`;

                const list = document.getElementById('insight-recent-list');
                list.innerHTML = data.recent.map(r => `
                    <div class="insight-item">
                        <div><div class="ii-date">${r.date}</div><div class="ii-times">${r.morning} / ${r.afternoon}</div></div>
                        <span class="badge-pill ${r.status === 'late' ? 'warning' : 'success'}" style="font-size:0.6rem;">${r.status.toUpperCase()}</span>
                    </div>
                `).join('') || `<div style="text-align:center; color:var(--text-muted); font-size:0.8rem; padding:1rem;">{{ __('No recent records') }}</div>`;

            } catch (e) {
                console.error(e);
                window.closeTeacherInsights();
            }
        };

        window.closeTeacherInsights = function() {
            document.getElementById('insightModal').classList.remove('active');
        };

        window.openAdminProfile = function() {
            document.getElementById('adminProfileModal').classList.add('active');
        };
        window.closeAdminProfile = function() {
            document.getElementById('adminProfileModal').classList.remove('active');
        };

        window.openSchoolInfo = function() {
            document.getElementById('schoolInfoModal').classList.add('active');
        };
        window.closeSchoolInfo = function() {
            document.getElementById('schoolInfoModal').classList.remove('active');
        };

        window.openTelegramChatsModal = function() {
            document.getElementById('telegramChatsModal').classList.add('active');
            refreshTelegramChats();
            // Clear unread badge
            const countBadge = document.getElementById('telegram-chat-count');
            if (countBadge) countBadge.style.display = 'none';
            // Save current time to localStorage
            localStorage.setItem('last_telegram_view_time', Math.floor(Date.now() / 1000));
        };
        window.closeTelegramChatsModal = function() {
            document.getElementById('telegramChatsModal').classList.remove('active');
        };

        window.refreshTelegramChats = async function() {
            const list = document.getElementById('telegram-chats-list');
            list.innerHTML = `
                <div style="text-align: center; padding: 2rem 0; color: var(--text-secondary);">
                    <i class="ph ph-spinner-gap animate-spin" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    {{ __('Fetching recent chats...') }}
                </div>
            `;

            try {
                const response = await fetch(`{{ route('settings.telegram.chats') }}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (data.status === 'success') {
                    if (data.chats.length === 0) {
                        list.innerHTML = `
                            <div style="text-align: center; padding: 2rem 0; color: var(--text-secondary);">
                                <i class="ph ph-telegram-logo" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                {{ __('No recent messages found. Ask your teachers to message your bot first!') }}
                            </div>
                        `;
                        return;
                    }

                    list.innerHTML = '';
                    data.chats.forEach(chat => {
                        const chatRow = document.createElement('div');
                        chatRow.style.cssText = 'background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border); padding: 1rem; border-radius: 1rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem;';
                        chatRow.innerHTML = `
                            <div style="flex: 1; text-align: left;">
                                <div style="font-weight: 700; color: var(--text-primary);">${chat.name || 'Unknown User'}</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                    <span style="color: #0088cc;">${chat.username}</span> · Message: "${chat.last_message}"
                                </div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">${chat.date}</div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                                <span style="font-family: monospace; font-weight: 600; color: var(--primary); font-size: 0.85rem; background: rgba(0, 212, 160, 0.05); padding: 0.25rem 0.5rem; border-radius: 0.5rem; border: 1px solid rgba(0, 212, 160, 0.1);">${chat.id}</span>
                                <button type="button" class="btn btn-secondary" onclick="copyTelegramId('${chat.id}', this)" style="padding: 0.35rem 0.6rem; border-radius: 0.5rem; margin: 0; min-height: unset; width: auto; font-size: 0.85rem;">
                                    <i class="ph ph-copy"></i>
                                </button>
                            </div>
                        `;
                        list.appendChild(chatRow);
                    });
                } else {
                    list.innerHTML = `<div style="color: var(--danger); text-align: center; padding: 2rem 0;">${data.message}</div>`;
                }
            } catch (e) {
                list.innerHTML = `<div style="color: var(--danger); text-align: center; padding: 2rem 0;">Failed to fetch chats: ${e.message}</div>`;
            }
        };

        window.copyTelegramId = function(id, btn) {
            const doCopy = () => {
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(id);
                } else {
                    let textArea = document.createElement("textarea");
                    textArea.value = id;
                    textArea.style.position = "fixed";
                    textArea.style.left = "-999999px";
                    textArea.style.top = "-999999px";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        textArea.remove();
                        return Promise.resolve();
                    } catch (err) {
                        textArea.remove();
                        return Promise.reject(err);
                    }
                }
            };

            doCopy().then(() => {
                if (btn) {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="ph ph-check" style="color: var(--success);"></i>';
                    setTimeout(() => { btn.innerHTML = originalHTML; }, 2000);
                }
            }).catch(err => {
                console.error("Failed to copy ID: ", err);
            });
        };

        // Fetch Telegram Chats Count on load
        async function fetchTelegramChatsCount() {
            try {
                const response = await fetch(`{{ route('settings.telegram.chats') }}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) return;
                const data = await response.json();
                if (data.status === 'success' && data.chats && data.chats.length > 0) {
                    const lastViewTime = parseInt(localStorage.getItem('last_telegram_view_time') || '0');
                    let unreadCount = 0;
                    data.chats.forEach(chat => {
                        if (chat.timestamp > lastViewTime) unreadCount++;
                    });

                    const countBadge = document.getElementById('telegram-chat-count');
                    if (countBadge && unreadCount > 0) {
                        countBadge.textContent = unreadCount;
                        countBadge.style.display = 'block';
                    } else if (countBadge) {
                        countBadge.style.display = 'none';
                    }
                }
            } catch (e) {
                console.error("Failed to fetch telegram chats count", e);
            }
        }
        
        fetchTelegramChatsCount();

        // Global Search Logic
        let searchTimeout;
        const searchInput = document.getElementById('globalSearchInput');
        const searchDropdown = document.getElementById('searchDropdown');
        const searchResults = document.getElementById('searchResults');

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.trim();
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    searchDropdown.style.display = 'none';
                    return;
                }
                
                searchTimeout = setTimeout(async () => {
                    try {
                        const data = await window.fetchApi(`/api-web/teachers?search=${encodeURIComponent(query)}`);
                        renderSearchResults(data);
                    } catch (err) {
                        console.error('Search error:', err);
                    }
                }, 300);
            });

            function renderSearchResults(data) {
                const query = searchInput.value.toLowerCase().trim();
                const pages = [
                    { title: '{{ __("Dashboard") }}', url: '{{ route("dashboard") }}', icon: 'ph-house', keywords: 'home' },
                    { title: '{{ __("Teachers List") }}', url: '{{ route("teachers.index") }}', icon: 'ph-users', keywords: 'staff register' },
                    { title: '{{ __("RFID Management") }}', url: '{{ route("rfid.index") }}', icon: 'ph-identification-card', keywords: 'cards tags' },
                    { title: '{{ __("Attendance Scan") }}', url: '{{ route("scan.index") }}', icon: 'ph-scan', keywords: 'checkin checkout' },
                    { title: '{{ __("Daily Reports") }}', url: '{{ route("reports.index") }}', icon: 'ph-file-pdf', keywords: 'logs export' },
                    { title: '{{ __("Departments") }}', url: '{{ route("departments.index") }}', icon: 'ph-buildings', keywords: 'org groups' },
                    { title: '{{ __("Security & Audit") }}', url: '{{ route("security.index") }}', icon: 'ph-shield-check', keywords: 'logs cache integrity' },
                    { title: '{{ __("System Settings") }}', url: '{{ route("settings.index") }}', icon: 'ph-gear', keywords: 'config appearance backup' },
                    { title: '{{ __("Live Monitor") }}', url: '{{ route("live.monitor") }}', icon: 'ph-desktop', keywords: 'tv screen monitor' },
                ];

                const matchedPages = pages.filter(p => 
                    p.title.toLowerCase().includes(query) || p.keywords.includes(query)
                );

                if (!data.length && !matchedPages.length) {
                    searchResults.innerHTML = `<div style="padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">{{ __('No results found') }}</div>`;
                } else {
                    let html = '';

                    if (matchedPages.length) {
                        html += `<div style="padding: 0.5rem 0.75rem 0.25rem; font-size: 0.65rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Navigation') }}</div>`;
                        matchedPages.forEach(p => {
                            html += `
                                <a href="${p.url}" class="search-result-item" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; border-radius: 0.75rem; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; text-decoration: none;">
                                    <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="ph ${p.icon}" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.85rem;">${p.title}</div>
                                        <div style="font-size: 0.7rem; color: var(--text-muted);">{{ __('System Page') }}</div>
                                    </div>
                                    <i class="ph ph-arrow-right" style="color: var(--text-secondary); font-size: 0.9rem; margin-right: 0.5rem; transition: transform 0.2s ease, color 0.2s ease;"></i>
                                </a>
                            `;
                        });
                    }

                    if (data.length) {
                        html += `<div style="padding: 1rem 0.75rem 0.25rem; font-size: 0.65rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Teachers & Staff') }}</div>`;
                        data.slice(0, 6).forEach(teacher => {
                            html += `
                                <div class="search-result-item teacher-result" onclick="openTeacherInsights(${teacher.id})" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; border-radius: 0.75rem; cursor: pointer; transition: all 0.2s; border: 1px solid transparent;">
                                    <div style="width: 38px; height: 38px; border-radius: 10px; overflow: hidden; background: rgba(255,255,255,0.05); flex-shrink: 0; border: 1px solid var(--border);">
                                        ${teacher.photo ? `<img src="${teacher.photo}" style="width:100%; height:100%; object-fit:cover;">` : `<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--primary); font-weight:800; font-size:0.9rem;">${teacher.name.charAt(0)}</div>`}
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.85rem; line-height: 1.2;">${teacher.name_kh || ''}</div>
                                        <div style="font-weight: 600; color: var(--text-secondary); font-size: 0.8rem;">${teacher.name}</div>
                                        <div style="font-size: 0.65rem; color: var(--primary); text-transform: uppercase; font-weight: 800; margin-top: 2px;">${teacher.department}</div>
                                    </div>
                                    <i class="ph ph-caret-right" style="color: var(--text-secondary); font-size: 1rem; margin-right: 0.5rem; transition: transform 0.2s ease, color 0.2s ease;"></i>
                                </div>
                            `;
                        });
                    }

                    searchResults.innerHTML = html;

                    // Re-apply hover effects
                    document.querySelectorAll('.search-result-item').forEach(item => {
                        item.addEventListener('mouseover', () => {
                            item.style.background = 'rgba(var(--primary-rgb), 0.08)';
                            item.style.borderColor = 'rgba(var(--primary-rgb), 0.2)';
                            const icon = item.querySelector('.ph-arrow-right, .ph-caret-right');
                            if (icon) {
                                icon.style.transform = 'translateX(4px)';
                                icon.style.color = 'var(--primary)';
                            }
                        });
                        item.addEventListener('mouseout', () => {
                            item.style.background = 'transparent';
                            item.style.borderColor = 'transparent';
                            const icon = item.querySelector('.ph-arrow-right, .ph-caret-right');
                            if (icon) {
                                icon.style.transform = 'translateX(0)';
                                icon.style.color = 'var(--text-secondary)';
                            }
                        });
                    });
                }
                searchDropdown.style.display = 'block';
            }

            // Close dropdown on click outside
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                    searchDropdown.style.display = 'none';
                }
            });
        }

        // Global Search Shortcut (Cmd+K or Ctrl+K)
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('globalSearchInput').focus();
            }
        });
    </script>
    {{-- Telegram Chats Modal --}}
    <div class="modal-overlay" id="telegramChatsModal" onclick="if(event.target===this) closeTelegramChatsModal()">
        <div class="modal-content" style="max-width: 600px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 1.5rem; padding: 2rem;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph ph-telegram-logo" style="color: #0088cc; font-size: 1.5rem;"></i>
                    {{ __('Recent Bot Messages & Chat IDs') }}
                </h3>
                <button class="modal-close" onclick="closeTelegramChatsModal()" style="background: none; border: none; font-size: 1.5rem; color: var(--text-secondary); cursor: pointer;">&times;</button>
            </div>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.5;">
                {{ __('To see a teacher here, ask them to search for your bot and press "Start" or send a message. Then click "Refresh" to see their name and Chat ID.') }}
            </p>
            <div id="telegram-chats-list" style="max-height: 350px; overflow-y: auto; margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem; padding-right: 0.5rem;">
                <div style="text-align: center; padding: 2rem 0; color: var(--text-secondary);">
                    <i class="ph ph-spinner-gap animate-spin" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    {{ __('Fetching recent chats...') }}
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeTelegramChatsModal()" style="border-radius: 1rem; padding: 0.75rem 1.5rem; font-weight: 700;">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary" onclick="refreshTelegramChats()" style="width: auto; border-radius: 1rem; padding: 0.75rem 1.5rem; font-weight: 700; background: #0088cc; border-color: #0088cc; display: inline-flex; align-items: center; gap: 0.5rem; color: white;">
                    <i class="ph ph-arrows-clockwise"></i> {{ __('Refresh') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Global Admin Profile Modal --}}
    <div id="adminProfileModal" class="modal-overlay" onclick="if(event.target == this) closeAdminProfile()">
        <div class="modal-content" style="max-width: 400px; padding: 2.5rem; border-radius: 2.5rem;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), #00b894); color: #000; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 2.2rem; margin: 0 auto 1.5rem; border: 4px solid rgba(255,255,255,0.05); box-shadow: 0 12px 24px rgba(0,0,0,0.3);">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <h2 style="margin: 0; font-weight: 800; color: var(--text-primary); font-size: 1.5rem;">{{ auth()->user()->name }}</h2>
                <p style="color: var(--primary); font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; margin: 0.4rem 0 1.5rem;">{{ __('System Administrator') }}</p>
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 1rem; background: rgba(var(--primary-rgb), 0.08); border-radius: 2rem; color: var(--primary); font-size: 0.75rem; font-weight: 700;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary); animation: livePulse 2s infinite;"></span>
                    {{ __('Account Active') }}
                </div>
            </div>

            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 1.5rem; padding: 1.5rem; margin-bottom: 2rem;">
                <div style="margin-bottom: 1.25rem; display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 36px; height: 36px; border-radius: 0.75rem; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                        <i class="ph ph-envelope-simple" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.1rem;">{{ __('Email Address') }}</label>
                        <div style="font-weight: 600; color: var(--text-primary); font-size: 0.9rem;">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 36px; height: 36px; border-radius: 0.75rem; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                        <i class="ph ph-calendar-blank" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.1rem;">{{ __('Member Since') }}</label>
                        <div style="font-weight: 600; color: var(--text-primary); font-size: 0.9rem;">{{ auth()->user()->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                <a href="{{ route('settings.index') }}" class="btn btn-secondary" style="width: 100%; border-radius: 1.25rem; gap: 0.75rem; justify-content: center; padding: 0.85rem;">
                    <i class="ph ph-gear" style="font-size: 1.2rem;"></i> {{ __('Account Settings') }}
                </a>
                <button class="btn btn-primary" onclick="closeAdminProfile()" style="width: 100%; border-radius: 1.25rem; padding: 0.85rem; font-weight: 800;">
                    {{ __('Dismiss') }}
                </button>
            </div>
        </div>
    </div>
    {{-- Global Teacher Insight Modal --}}
    <div id="insightModal" class="modal-overlay" onclick="if(event.target == this) closeTeacherInsights()">
        <div class="insight-card">
            <button class="insight-close" onclick="closeTeacherInsights()"><i class="ph ph-x"></i></button>
            <div class="insight-header">
                <div id="insight-avatar-container"></div>
                <div class="insight-header-text">
                    <h2 id="insight-name-kh" style="color:var(--primary); margin:0; line-height:1.1; font-weight: 800;"></h2>
                    <h3 id="insight-name" style="margin:0; font-size:1.1rem; font-weight:700; opacity:0.8;">Loading...</h3>
                    <p id="insight-dept" style="margin-top: 0.25rem;">---</p>
                    <div id="insight-score-badge" class="badge-pill">{{ __('Score') }}: --</div>
                </div>
            </div>
            <div class="insight-stats-grid">
                <div class="insight-stat-box"><span class="is-label">{{ __('Present') }}</span><span class="is-value" id="is-present">0</span><span class="is-meta">{{ __('Days') }}</span></div>
                <div class="insight-stat-box"><span class="is-label">{{ __('Late') }}</span><span class="is-value danger" id="is-late">0</span><span class="is-meta">{{ __('Times') }}</span></div>
                <div class="insight-stat-box"><span class="is-label">{{ __('Punctuality') }}</span><span class="is-value primary" id="is-score">0%</span><span class="is-meta">{{ __('Score') }}</span></div>
            </div>
            <div class="insight-section">
                <h4><i class="ph ph-clock-counter-clockwise"></i> {{ __('Recent Records') }}</h4>
                <div id="insight-recent-list" class="insight-list"></div>
            </div>
            <div class="insight-footer" style="margin-top:2rem;">
                <button class="btn btn-secondary" style="width:100%;" onclick="closeTeacherInsights()">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
    
    {{-- Global Image Viewer Modal --}}
    <div id="imageViewerModal" class="modal-overlay" style="z-index: 1000000; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px);" onclick="if(event.target == this) this.classList.remove('active')">
        <div style="position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; padding: 2rem;">
            <button class="modal-close" style="position: absolute; top: 2rem; right: 2rem; background: rgba(255,255,255,0.1); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; cursor: pointer; transition: 0.2s;" onclick="document.getElementById('imageViewerModal').classList.remove('active')" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                <i class="ph ph-x"></i>
            </button>
            <img id="imageViewerImg" src="" style="max-width: 90%; max-height: 90vh; object-fit: contain; border-radius: 1rem; box-shadow: 0 15px 40px rgba(0,0,0,0.5); background: var(--bg-card);">
        </div>
    </div>
    {{-- Global School Info Modal --}}
    <div id="schoolInfoModal" class="modal-overlay" onclick="if(event.target == this) closeSchoolInfo()">
        <div class="modal-content" style="max-width: 450px; padding: 2.5rem; border-radius: 2.5rem; text-align: center;">
            <div style="margin-bottom: 2rem;">
                @if($uLogo)
                    <div style="width: 120px; height: 120px; margin: 0 auto 1.5rem; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; transform: translateZ(0);">
                        <img src="{{ to_asset_url($uLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    </div>
                @endif
                <h2 style="margin: 0; font-weight: 800; color: var(--text-primary); font-size: 1.6rem; line-height: 1.2;">{{ $uName }}</h2>
                <p style="color: var(--primary); font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; margin: 0.5rem 0 2rem;">{{ __('Institution Profile') }}</p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2.5rem;">
                <a href="{{ $uWeb }}" target="_blank" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: rgba(var(--primary-rgb), 0.08); border: 1px solid rgba(var(--primary-rgb), 0.2); border-radius: 1.25rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='rgba(var(--primary-rgb), 0.15)';this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(var(--primary-rgb), 0.08)';this.style.transform='translateY(0)';">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: #000; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="ph ph-globe" style="font-size: 1.4rem;"></i>
                    </div>
                    <div style="text-align: left;">
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">{{ __('Official Website') }}</span>
                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;">{{ $uWeb != '#' ? str_replace(['https://', 'http://'], '', $uWeb) : __('Not set') }}</div>
                    </div>
                    <i class="ph ph-arrow-square-out" style="margin-left: auto; color: var(--primary); font-size: 1.2rem;"></i>
                </a>

                <a href="{{ $uFb }}" target="_blank" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: rgba(24, 119, 242, 0.08); border: 1px solid rgba(24, 119, 242, 0.2); border-radius: 1.25rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='rgba(24, 119, 242, 0.15)';this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(24, 119, 242, 0.08)';this.style.transform='translateY(0)';">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #1877F2; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="ph ph-facebook-logo" style="font-size: 1.4rem;"></i>
                    </div>
                    <div style="text-align: left;">
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">{{ __('Facebook Page') }}</span>
                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;">{{ $uFb != '#' ? 'Facebook' : __('Not set') }}</div>
                    </div>
                    <i class="ph ph-arrow-square-out" style="margin-left: auto; color: #1877F2; font-size: 1.2rem;"></i>
                </a>
            </div>

            <button class="btn btn-primary" onclick="closeSchoolInfo()" style="width: 100%; border-radius: 1.25rem; padding: 1rem; font-weight: 800; box-shadow: 0 10px 20px rgba(var(--primary-rgb), 0.2);">
                {{ __('Close') }}
            </button>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
