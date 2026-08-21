<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Live Attendance Monitor') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @php
        $defaultTheme = \App\Models\Setting::getValue('default_theme', 'dark');
        $radarSize = \App\Models\Setting::getValue('live_radar_size', '360');
    @endphp
    <script>
        // Apply LIVE-specific theme (separate from admin system)
        const savedLiveTheme = localStorage.getItem('live_theme');
        const defaultTheme = '{{ $defaultTheme }}';

        if (savedLiveTheme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
        } else if (savedLiveTheme === 'dark') {
            document.documentElement.removeAttribute('data-theme');
        } else if (!savedLiveTheme && defaultTheme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    </script>

    {{-- Phosphor Icons --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    {{-- Main Stylesheet --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    
    <style>
        :root {
            --live-bg: linear-gradient(135deg, #0b0f14 0%, #161b22 100%);
            --live-card-bg: rgba(22, 27, 34, 0.85);
            --live-card-border: rgba(255, 255, 255, 0.05);
            --live-shadow: 0 25px 80px rgba(0,0,0,0.5);
            --live-history-bg: rgba(22, 27, 34, 0.6);
            --live-accent-glow: rgba(0, 212, 160, 0.15);
            --live-card-border: rgba(255, 255, 255, 0.08);
            --live-card-bg: rgba(13, 17, 23, 0.8);
        }

        [data-theme="light"] {
            --live-bg: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            --live-card-bg: rgba(255, 255, 255, 0.9);
            --live-card-border: rgba(0, 0, 0, 0.05);
            --live-shadow: 0 20px 50px rgba(0,0,0,0.1);
            --live-history-bg: rgba(255, 255, 255, 0.5);
            --live-accent-glow: rgba(0, 212, 160, 0.1);
            --text-primary: #1a202c;
            --text-secondary: #4a5568;
            --border: #cbd5e0;
        }

        body {
            margin: 0;
            padding: 0;
            overflow: hidden; 
            background: var(--live-bg);
            color: var(--text-primary);
            font-family: 'Kantumruy Pro', 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            height: 100vh;
            transition: background 0.5s ease;
        }
        
        /* Top Header — 4 equal columns */
        .live-header {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            align-items: stretch;
            gap: 1rem;
            padding: 1rem 2rem;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--live-shadow);
            z-index: 50;
        }

        /* ── Shared header-card token ── */
        /* Every box in the header uses these values so they all match */
        :root {
            --hcard-bg     : rgba(255,255,255,0.03);
            --hcard-border : rgba(255,255,255,0.08);
            --hcard-radius : 0.875rem;
            --hcard-shadow : 0 4px 20px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.05);
            --hcard-pad    : 0.75rem 1.25rem;
        }
        [data-theme="light"] {
            --hcard-bg     : rgba(255,255,255,0.7);
            --hcard-border : rgba(0,0,0,0.07);
            --hcard-shadow : 0 4px 20px rgba(0,0,0,0.08), inset 0 1px 0 rgba(255,255,255,0.9);
        }
        .hcard {
            background    : var(--hcard-bg);
            border        : 1px solid var(--hcard-border);
            border-radius : var(--hcard-radius);
            box-shadow    : var(--hcard-shadow);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            display       : flex;
            align-items   : center;
        }
        
        .time-widget {
            display: flex;
            flex-direction: column;
            align-items: center;        /* centered clock in equal-width card */
            justify-content: center;
            padding: var(--hcard-pad);
            border-radius: var(--hcard-radius);
            background: var(--hcard-bg);
            border: 1px solid var(--hcard-border);
            box-shadow: var(--hcard-shadow);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .clock-display {
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 1px;
            font-variant-numeric: tabular-nums;
            display: flex;
            align-items: baseline;
            line-height: 1;
            text-shadow: 0 0 15px var(--live-accent-glow);
        }

        .clock-display span.seconds {
            font-size: 1.4rem;
            opacity: 0.7;
            font-weight: 500;
            margin-left: 4px;
            color: var(--text-secondary);
        }

        /* Stats Dashboard */
        .live-stats {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 0.5rem;
            animation: slideDownIn 0.8s 0.2s both;
            width: 100%;
        }

        .stat-widget {
            flex: 1;
            background: var(--live-card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--live-card-border);
            padding: 1.1rem 1.5rem;
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-widget:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2), 0 0 15px var(--live-accent-glow);
        }

        .stat-widget::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.03) 0%, transparent 100%);
            pointer-events: none;
        }

        .stat-widget i {
            font-size: 1.8rem;
            width: 48px;
            height: 48px;
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-widget.late i { background: rgba(239, 68, 68, 0.1); color: #f87171; }
        .stat-widget.rem i  { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }

        .stat-info .s-val {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.1;
        }

        .stat-info .s-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.25rem;
        }

        /* Gauge */
        .gauge-container {
            width: 70px; height: 70px;
            position: relative;
            display: flex; align-items: center; justify-content: center;
        }
        .gauge-svg { transform: rotate(-90deg); }
        .gauge-bg { fill: none; stroke: rgba(255,255,255,0.05); stroke-width: 3; }
        .gauge-fill { fill: none; stroke: var(--primary); stroke-width: 3; stroke-dasharray: 100; stroke-dashoffset: 100; transition: stroke-dashoffset 1s ease-out; stroke-linecap: round; }
        .gauge-text { position: absolute; font-size: 0.85rem; font-weight: 800; color: var(--primary); }

        /* Shift Tracker Bar */
        .shift-tracker {
            position: absolute; bottom: 0; left: 0; width: 100%; height: 4px;
            background: rgba(255,255,255,0.05); overflow: hidden;
        }
        .shift-progress { height: 100%; background: var(--primary); width: 0%; transition: width 0.5s ease; box-shadow: 0 0 10px var(--primary); }

        .date-display {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-top: 0.4rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: 0;
            animation: fadeInRight 0.8s ease forwards;
            animation-delay: 0.2s;
        }

        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(15px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Main Content */
        .live-main {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 1.5rem 3rem 2.5rem;
            gap: 1.5rem;
            overflow: hidden;
        }

        .live-content-row {
            display: flex;
            flex: 1;
            gap: 2rem;
            min-height: 0;
        }

        /* Scan Popup Area */
        .scan-area {
            flex: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .scan-card {
            background: var(--live-card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 2px solid var(--live-card-border);
            border-radius: 1.5rem;
            padding: 4rem 3rem;
            width: 100%;
            max-width: 850px;
            text-align: center;
            box-shadow: var(--live-shadow);
            transform: scale(0.95) translateY(20px);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes glowPulseSuccess {
            0% { box-shadow: 0 0 30px rgba(63, 185, 80, 0.15); }
            50% { box-shadow: 0 0 90px rgba(63, 185, 80, 0.5); }
            100% { box-shadow: 0 0 30px rgba(63, 185, 80, 0.15); }
        }

        .scan-card.active {
            transform: scale(1) translateY(0);
            opacity: 1;
            border-color: var(--success);
            animation: glowPulseSuccess 2s infinite ease-in-out;
        }
        
        .scan-card.active.check-out {
            border-color: var(--success);
        }

        .scan-card.active.error {
            border-color: var(--danger);
            box-shadow: 0 0 80px rgba(239, 68, 68, 0.3);
            animation: none;
        }

        .scan-icon.check-in { color: var(--success); }
        .scan-icon.check-out { color: var(--success); }
        .scan-icon.error { color: var(--danger); }

        .teacher-photo-container {
            width: 180px;
            height: 180px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            border: 4px solid var(--primary);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            display: none; /* Shown only when photo exists */
            background: transparent;
        }

        .teacher-photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .teacher-photo-placeholder {
            width: 180px;
            height: 180px;
            margin: 0 auto 2rem;
            border-radius: 2rem;
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            border: 2px dashed rgba(var(--primary-rgb), 0.3);
        }

        .teacher-name {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            letter-spacing: -1px;
        }

        .teacher-dept {
            font-size: 2rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        .scan-time {
            font-size: 3rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .scan-time.check-in { color: var(--success); }
        .scan-time.check-out { color: var(--success); }

        /* History Panel */
        .history-panel {
            flex: 1;
            background: var(--live-history-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--live-card-border);
            border-radius: 1rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            box-shadow: var(--live-shadow);
        }
        
        .history-panel::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: linear-gradient(to top, var(--live-card-bg), transparent);
            pointer-events: none;
            border-radius: 0 0 1rem 1rem;
        }

        .history-header {
            padding: 1.25rem 1.5rem;
            background: rgba(0,212,160,0.04);
            border-bottom: 1px solid var(--live-card-border);
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--text-primary);
        }
        .history-header i { color: var(--primary); font-size: 1.2rem; }
        .history-header .h-count {
            margin-left: auto;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            background: rgba(255,255,255,0.06);
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            border: 1px solid var(--live-card-border);
        }

        .history-list {
            padding: 0.75rem;
            overflow-y: auto;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .history-item {
            padding: 0.85rem 1rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            animation: slideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            transition: all 0.2s;
            border: 1px solid var(--live-card-border);
            background: rgba(255,255,255,0.02);
        }
        .history-item:hover { background: rgba(255,255,255,0.04); }

        .history-item.check-in  { border-left: 3px solid var(--success); }
        .history-item.check-out { border-left: 3px solid var(--warning); }

        .h-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.85rem;
            flex-shrink: 0;
            color: #fff;
        }
        .check-in .h-avatar  { background: linear-gradient(135deg, #10b981, #059669); }
        .check-out .h-avatar { background: linear-gradient(135deg, #f59e0b, #d97706); }

        .h-info { flex: 1; min-width: 0; }
        .h-info .h-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .h-info .h-dept {
            font-size: 0.78rem;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .h-right { text-align: right; flex-shrink: 0; }
        .h-right .history-time {
            font-size: 1rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .h-right .h-badge {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.15rem 0.45rem;
            border-radius: 999px;
            display: inline-block;
            margin-top: 0.2rem;
        }
        .check-in .h-right .history-time { color: var(--success); }
        .check-out .h-right .history-time { color: var(--warning); }
        .check-in .h-badge  { background: rgba(16,185,129,0.12); color: var(--success); }
        .check-out .h-badge { background: rgba(245,158,11,0.12); color: var(--warning); }

        .history-empty {
            text-align: center; padding: 3rem 1rem; color: var(--text-muted);
        }
        .history-empty i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.4; }

        @keyframes slideIn {
            from { transform: translateX(20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideDownIn {
            from { transform: translateY(-40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* ── Radar Styles Base ── */
        .radar-container {
            position: relative;
            width: {{ $radarSize }}px;
            height: {{ $radarSize }}px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 0.75rem auto;
            border-radius: 1.5rem;
            transition: all 0.5s ease;
        }

        /* ── STYLE 1: HOLOGRAPHIC RADAR ── */
        .radar-container.style-radar {
            background: radial-gradient(circle, rgba(var(--primary-rgb), 0.05) 0%, transparent 70%);
            box-shadow: inset 0 0 40px rgba(var(--primary-rgb), 0.08);
            border: 1px dashed rgba(var(--primary-rgb), 0.2);
            overflow: hidden;
        }
        .radar-container.style-radar .radar-ring {
            position: absolute; inset: 0; border-radius: 1.5rem; border: 1px dashed rgba(var(--primary-rgb), 0.25); animation: spinRadar 12s linear infinite;
        }
        .radar-container.style-radar .radar-ring-inner {
            position: absolute; inset: 25px; border-radius: 1.1rem; border: 1px dashed rgba(var(--primary-rgb), 0.4); animation: spinRadar 6s linear infinite reverse;
        }
        .radar-container.style-radar .radar-sweep {
            position: absolute; inset: 0; border-radius: 1.5rem; background: conic-gradient(from 0deg, rgba(var(--primary-rgb), 0.35), transparent 75deg); animation: spinRadar 3s linear infinite;
        }
        .radar-container.style-radar .radar-pulse-wave {
            position: absolute; inset: 0; border-radius: 1.5rem; border: 2px solid var(--primary); opacity: 0; animation: radarPulse 3s cubic-bezier(0.16, 1, 0.3, 1) infinite;
        }
        .radar-container.style-radar .radar-pulse-wave:nth-child(2) { animation-delay: 1s; }
        .radar-container.style-radar .radar-pulse-wave:nth-child(3) { animation-delay: 2s; }

        /* ── STYLE 2: QUANTUM ENERGY PORTAL ── */
        .radar-container.style-quantum {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.08) 0%, rgba(59, 130, 246, 0.05) 100%);
            box-shadow: 0 0 50px rgba(168, 85, 247, 0.15), inset 0 0 30px rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(168, 85, 247, 0.2);
            overflow: hidden;
        }
        .radar-container.style-quantum .quantum-orb {
            position: absolute; width: 130px; height: 130px; border-radius: 1rem;
            background: radial-gradient(circle, #a855f7 0%, #3b82f6 60%, transparent 100%);
            filter: blur(10px); opacity: 0.7; animation: quantumPulse 2.5s ease-in-out infinite alternate;
        }
        .radar-container.style-quantum .quantum-orbit-1 {
            position: absolute; inset: 10px; border-radius: 1.3rem; border: 2px solid rgba(168, 85, 247, 0.6);
            border-left-color: transparent; border-right-color: transparent; animation: spinRadar 4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
        .radar-container.style-quantum .quantum-orbit-2 {
            position: absolute; inset: 30px; border-radius: 1rem; border: 2px solid rgba(59, 130, 246, 0.7);
            border-top-color: transparent; border-bottom-color: transparent; animation: spinRadar 2.5s linear infinite reverse;
        }
        @keyframes quantumPulse {
            0% { transform: scale(0.85); opacity: 0.5; filter: blur(12px); }
            100% { transform: scale(1.15); opacity: 0.9; filter: blur(6px); }
        }

        /* ── STYLE 3: BIOMETRIC LASER RETICLE ── */
        .radar-container.style-laser {
            background: rgba(16, 185, 129, 0.03);
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 1.5rem;
            box-shadow: inset 0 0 25px rgba(16, 185, 129, 0.08);
            overflow: hidden;
        }
        .radar-container.style-laser .laser-grid {
            position: absolute; inset: 0; border-radius: 1.5rem;
            background-image: linear-gradient(rgba(16, 185, 129, 0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(16, 185, 129, 0.1) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .radar-container.style-laser .laser-corner {
            position: absolute; width: 22px; height: 22px; border: 3px solid #10b981;
        }
        .radar-container.style-laser .laser-corner.tl { top: 14px; left: 14px; border-right: none; border-bottom: none; border-top-left-radius: 4px; }
        .radar-container.style-laser .laser-corner.tr { top: 14px; right: 14px; border-left: none; border-bottom: none; border-top-right-radius: 4px; }
        .radar-container.style-laser .laser-corner.bl { bottom: 14px; left: 14px; border-right: none; border-top: none; border-bottom-left-radius: 4px; }
        .radar-container.style-laser .laser-corner.br { bottom: 14px; right: 14px; border-left: none; border-top: none; border-bottom-right-radius: 4px; }
        
        .radar-container.style-laser .laser-beam {
            position: absolute; left: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, transparent, #10b981, #6ee7b7, #10b981, transparent);
            box-shadow: 0 0 15px #10b981, 0 0 30px #10b981;
            animation: laserScan 2.2s ease-in-out infinite alternate;
        }
        @keyframes laserScan {
            0% { top: 15px; }
            100% { top: {{ $radarSize - 30 }}px; }
        }

        /* ── STYLE 4: GLASSMORPHIC MINIMAL RIPPLE ── */
        .radar-container.style-ripple {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(var(--primary-rgb), 0.1);
        }
        .radar-container.style-ripple .glass-ripple-ring {
            position: absolute; inset: 0; border-radius: 1.5rem;
            background: rgba(var(--primary-rgb), 0.04);
            border: 1px solid rgba(var(--primary-rgb), 0.2);
            animation: glassRipple 3.5s cubic-bezier(0, 0.2, 0.8, 1) infinite;
        }
        .radar-container.style-ripple .glass-ripple-ring:nth-child(2) { animation-delay: 1.1s; }
        .radar-container.style-ripple .glass-ripple-ring:nth-child(3) { animation-delay: 2.2s; }

        @keyframes glassRipple {
            0% { transform: scale(0.2); opacity: 1; border-color: var(--primary); }
            100% { transform: scale(1.3); opacity: 0; border-color: transparent; }
        }

        /* Style Switcher Bar */
        .style-switcher-bar {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            border-radius: 30px;
            padding: 4px;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        .style-btn {
            background: transparent;
            border: none;
            outline: none;
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .style-btn i { font-size: 0.85rem; }
        .style-btn:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.08);
        }
        .style-btn.active {
            background: var(--primary);
            color: #0b0f19;
            box-shadow: 0 0 15px var(--live-accent-glow);
        }

        @keyframes radarPulse {
            0% { transform: scale(0.3); opacity: 0.8; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        @keyframes spinRadar {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        /* 3D Holographic Card Component inside Radar */
        .radar-3d-card {
            position: relative;
            z-index: 10;
            width: 120px;
            height: 76px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.9));
            border: 2px solid var(--primary);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 25px var(--live-accent-glow);
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            animation: cardFloatTap 3s ease-in-out infinite;
            backdrop-filter: blur(8px);
        }

        /* Holographic Glint Sweep line */
        .radar-3d-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent 45%,
                rgba(255, 255, 255, 0.25) 50%,
                transparent 55%
            );
            animation: cardGlint 4s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes cardFloatTap {
            0%, 100% { transform: translateY(0) rotateX(8deg) rotateY(-8deg) scale(1); }
            50%      { transform: translateY(-10px) rotateX(12deg) rotateY(4deg) scale(1.05); }
        }

        @keyframes cardGlint {
            0%   { transform: translateX(-100%) translateY(-100%); }
            30%  { transform: translateX(100%) translateY(100%); }
            100% { transform: translateX(100%) translateY(100%); }
        }

        .card-chip {
            width: 20px;
            height: 15px;
            background: linear-gradient(135deg, #fbbf24, #d97706);
            border-radius: 3px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: inset 0 0 2px rgba(0,0,0,0.4);
            position: relative;
        }
        .card-chip::after {
            content: '';
            position: absolute;
            inset: 3px;
            border: 1px solid rgba(0,0,0,0.3);
            border-radius: 1px;
        }

        .card-wave-lines {
            color: var(--primary);
            font-size: 1.4rem;
            opacity: 0.9;
            filter: drop-shadow(0 0 6px var(--primary));
            animation: wavePulse 1.5s ease-in-out infinite alternate;
        }

        @keyframes wavePulse {
            from { opacity: 0.4; transform: scale(0.9); }
            to   { opacity: 1;   transform: scale(1.1); }
        }

        .standby-text {
            color: var(--text-secondary);
            font-size: 1.8rem;
            font-weight: 300;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        /* ── Admin Scan Panel ── */
        .admin-scan-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 100;
            background: var(--primary);
            color: #000;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 20px rgba(0,212,160,0.3);
            transition: all 0.15s;
            letter-spacing: 0.05em;
        }
        .admin-scan-btn:hover { opacity: 0.88; transform: translateY(-2px); }

        .admin-panel-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            z-index: 200;
            justify-content: center;
            align-items: center;
        }
        .admin-panel-overlay.open { display: flex; }

        .admin-panel {
            background: #161b22;
            border: 1px solid rgba(0,212,160,0.3);
            padding: 2rem;
            width: 100%;
            max-width: 480px;
            position: relative;
            box-shadow: 0 0 60px rgba(0,212,160,0.15);
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        .admin-panel h2 { margin: 0 0 1.25rem; font-size: 1.25rem; }
        .panel-close {
            position: absolute; top: 1rem; right: 1rem;
            background: none; border: none; color: var(--text-secondary);
            font-size: 1.5rem; cursor: pointer;
        }
        .admin-teacher-search {
            position: relative; margin-bottom: 0.75rem;
        }
        .admin-teacher-search i {
            position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); pointer-events: none;
        }
        #adminTeacherInput {
            width: 100%; padding: 0.6rem 0.75rem 0.6rem 2.2rem;
            background: #0d1117; border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-primary); font-size: 0.9rem;
            box-sizing: border-box;
        }
        .admin-teacher-list {
            max-height: 240px; overflow-y: auto;
            border: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 1rem;
        }
        .admin-teacher-item {
            padding: 0.65rem 1rem; cursor: pointer;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            display: flex; align-items: center; gap: 0.75rem;
            transition: background 0.12s;
            font-size: 0.9rem;
        }
        .admin-teacher-item:hover    { background: rgba(0,212,160,0.08); }
        .admin-teacher-item.selected { background: rgba(0,212,160,0.15); border-left: 3px solid var(--primary); }
        .admin-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--primary); color: #000;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.85rem; flex-shrink: 0;
        }
        .btn-do-scan {
            width: 100%; padding: 0.85rem;
            background: var(--primary); color: #000;
            border: none; font-size: 1rem; font-weight: 700;
            cursor: pointer; letter-spacing: 0.04em;
            transition: opacity 0.15s;
        }
        .btn-do-scan:hover:not(:disabled) { opacity: 0.88; }
        .btn-do-scan:disabled { opacity: 0.35; cursor: not-allowed; }
        #adminResult {
            margin-top: 0.75rem; padding: 0.75rem 1rem;
            font-size: 0.85rem; display: none;
            border-left: 3px solid transparent;
        }
        #adminResult.success { border-color: var(--success); background: rgba(16,185,129,0.08); color: var(--success); }
        #adminResult.error   { border-color: var(--danger);  background: rgba(239,68,68,0.08);  color: var(--danger); }
        #adminResult.info    { border-color: var(--warning);  background: rgba(245,158,11,0.08); color: var(--warning); }

        /* ── Header Brand Card ── */
        .header-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: var(--hcard-pad);
        }
        .header-brand-text { text-align: left; }
        .header-brand-text h1 { margin: 0; font-size: 1.75rem; line-height: 1.2; }
        .header-badges { display: flex; align-items: center; gap: 0.75rem; margin-top: 0.35rem; }

        /* ── Hardware Status Card ── */
        .hw-badge-card {
            display: flex;
            align-items: center;
            justify-content: center;    /* center content in equal-width cell */
            gap: 0.75rem;
            padding: 0 1.25rem;
            transition: border-color 0.3s;
        }

        /* ── Header Center (grid cell wrapper) ── */
        .header-center {
            display: flex;
            align-items: stretch;
            justify-content: center;
        }

        /* Unified frosted-glass toolbar — fills entire center cell */
        .header-toolbar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            padding: 0.35rem;
            width: 100%;
        }

        /* Live status badge (replaces rate) */
        .toolbar-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.8rem;
            background: rgba(0,212,160,0.1);
            border-radius: 0.6rem;
        }
        .toolbar-status .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--primary);
            animation: livePulse 2s infinite;
        }
        .toolbar-status span {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
        }

        /* Icon action buttons inside toolbar */
        .toolbar-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 0.7rem;
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.16,1,0.3,1);
            font-size: 1.4rem;
            position: relative;
            flex-shrink: 0;
        }
        .toolbar-btn:hover {
            background: rgba(0,212,160,0.12);
            color: var(--primary);
            transform: scale(1.12);
            box-shadow: 0 0 12px rgba(0,212,160,0.2);
            text-decoration: none;
        }
        .toolbar-btn.active {
            background: rgba(0,212,160,0.18);
            color: var(--primary);
            box-shadow: 0 0 14px rgba(0,212,160,0.25);
        }
        /* Tooltip on hover */
        .toolbar-btn[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15,20,30,0.95);
            color: #e2e8f0;
            font-size: 0.68rem;
            font-weight: 600;
            white-space: nowrap;
            padding: 0.3rem 0.6rem;
            border-radius: 0.4rem;
            border: 1px solid rgba(255,255,255,0.08);
            pointer-events: none;
            z-index: 999;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.3px;
        }
        /* Divider inside toolbar */
        .toolbar-divider {
            width: 1px;
            height: 52px; /* match rate ring size */
            background: rgba(255,255,255,0.08);
            margin: 0 0.3rem;
            align-self: center; /* vertically center */
            flex-shrink: 0;
        }

        @keyframes livePulse {
            0%,100% { box-shadow: 0 0 6px rgba(0,212,160,0.5); }
            50%      { box-shadow: 0 0 14px rgba(0,212,160,0.9); }
        }

        /* ── Responsiveness ── */
        @media (max-width: 1200px) {
            .live-header { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 1024px) {
            .live-header { padding: 0.75rem 1.25rem; }
            .live-main { padding: 1.5rem 2rem; }
            .teacher-name { font-size: 3rem; }
        }

        @media (max-width: 768px) {
            .live-header { grid-template-columns: 1fr; padding: 1rem; gap: 0.75rem; }
            .header-brand { flex-direction: column; justify-content: center; text-align: center; }
            .header-brand-text { text-align: center; }
            .header-brand-text h1 { font-size: 1.4rem; margin-bottom: 0.25rem; line-height: 1.3; }
            .header-badges { justify-content: center; }
            .header-center { justify-content: center; }
            .hw-badge-card { justify-content: center; }
            .time-widget { align-items: center; }
            .clock-display { font-size: 2.4rem; justify-content: center; }
            .clock-display span.seconds { font-size: 1.2rem; }
            .date-display { font-size: 0.85rem; letter-spacing: 1px; }
            .teacher-name { font-size: 2.5rem; }
            .teacher-dept { font-size: 1.2rem; }
            .scan-card { padding: 3rem 1rem; }
            .scan-icon { font-size: 4rem; }
            .admin-scan-btn { bottom: 1rem; right: 1rem; padding: 0.6rem 1rem; font-size: 0.8rem; }
            body { overflow-y: auto; height: auto; min-height: 100vh; }
        }

        @media (max-width: 480px) {
            .live-header h1 { font-size: 1.5rem; }
            .clock-display { font-size: 2rem; }
            .teacher-name { font-size: 1.8rem; }
            .scan-time { font-size: 2rem; }
            .standby-text { font-size: 1.2rem; }
            .standby-icon { font-size: 4rem; }
            .radar-container { width: 180px; height: 180px; }
            .history-header { font-size: 1rem; padding: 1rem; }
            .history-info .h-name { font-size: 1.1rem; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="live-header">

        {{-- ① Brand Card --}}
        <div class="header-brand hcard">
            @php $logo = \App\Models\Setting::getAssetUrl('university_logo', '/images/ntti_logo.png'); @endphp
            @if($logo)
                <div style="width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; transform: translateZ(0);">
                    <img src="{{ $logo }}" alt="Logo" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                </div>
            @endif
            <div class="header-brand-text">
                <h1>{{ __(\App\Models\Setting::getValue('university_name', 'National Technical Training Institute')) }}</h1>
                <div class="header-badges">
                    <div style="color:var(--primary); font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:1px;">{{ __('Live Monitor') }}</div>
                    {{-- Language Switcher --}}
                    <div style="display:flex; background:rgba(255,255,255,0.05); border:1px solid var(--hcard-border); border-radius:0.5rem; overflow:hidden; padding:1px;">
                        <a href="{{ route('lang.switch.live', 'en') }}"
                           style="padding:0.2rem 0.6rem; font-size:0.65rem; font-weight:700; text-decoration:none; border-radius:0.4rem; transition:all 0.2s;
                                  background:{{ app()->getLocale()==='en' ? 'var(--primary)' : 'transparent' }};
                                  color:{{ app()->getLocale()==='en' ? '#000' : 'var(--text-secondary)' }};">EN</a>
                        <a href="{{ route('lang.switch.live', 'km') }}"
                           style="padding:0.2rem 0.6rem; font-size:0.65rem; font-weight:700; text-decoration:none; border-radius:0.4rem; transition:all 0.2s;
                                  background:{{ app()->getLocale()==='km' ? 'var(--primary)' : 'transparent' }};
                                  color:{{ app()->getLocale()==='km' ? '#000' : 'var(--text-secondary)' }};">KH</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ② Center Toolbar Card --}}
        <div class="header-center">
            <div class="header-toolbar hcard">

                {{-- Live Status Badge (replaces rate) --}}
                <div class="toolbar-status" title="{{ __('System status') }}">
                    <div class="status-dot"></div>
                    <span>{{ __('Live') }}</span>
                </div>
                
                {{-- Live Weather Widget --}}
                <div class="toolbar-status" style="margin-left: 0.5rem; background: rgba(245, 158, 11, 0.08); padding-right: 0.8rem; cursor: default; gap: 0.4rem;" title="{{ __('Weather') }}">
                    <i id="liveWeatherIcon" class="ph ph-sun-horizon" style="font-size: 1.1rem; color: #f59e0b; transition: all 0.3s;"></i>
                    <span id="liveWeatherText" style="font-size: 0.9rem; font-weight: 700; color: var(--text-primary);">--°C</span>
                </div>

                {{-- Dashboard Link --}}
                <a href="{{ route('dashboard') }}" class="toolbar-btn" style="width: auto; padding: 0 0.8rem; gap: 0.4rem; background: rgba(var(--primary-rgb), 0.15); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.3);" title="{{ __('Back to Dashboard') }}">
                    <i class="ph ph-arrow-left" style="font-size: 1.1rem;"></i>
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary);">{{ __('Dashboard') }}</span>
                </a>

                <div class="toolbar-divider"></div>

                {{-- Theme Toggle --}}
                <button id="themeToggleBtn" class="toolbar-btn" onclick="toggleTheme()" title="{{ __('Light mode') }}">
                    <i class="ph ph-sun" id="themeIcon"></i>
                </button>

                {{-- Fullscreen --}}
                <button id="fullscreenBtn" class="toolbar-btn" onclick="toggleFullscreen()" title="{{ __('Fullscreen') }}">
                    <i class="ph ph-arrows-out" id="fullscreenIcon"></i>
                </button>

            </div>
        </div>
        {{-- ═══════════════════════════════════════ --}}

        {{-- ③ Hardware Status Card --}}
        <div id="live-hw-badge" class="hcard hw-badge-card" style="transition:border-color 0.3s;">
            <span id="live-hw-dot" style="width:14px; height:14px; border-radius:50%; background:#94a3b8; flex-shrink:0;"></span>
            <div style="display:flex; flex-direction:column; gap:0.2rem;">
                <span id="live-hw-text" style="font-size:1rem; font-weight:800; text-transform:uppercase; color:var(--text-secondary);">{{ __('Checking...') }}</span>
                <span id="live-hw-meta" style="font-size:0.78rem; color:var(--text-muted);">{{ __('Initialing system') }}</span>
            </div>
            <i id="live-hw-icon" class="ph ph-broadcast" style="font-size:2.2rem; color:var(--text-muted); margin-left:0.5rem;"></i>
        </div>

        {{-- ④ Time / Shift Card --}}
        @php
            $sysOpen  = \App\Models\Setting::getValue('system_open_time', '06:30');
            $sysClose = \App\Models\Setting::getValue('system_close_time', '18:30');
        @endphp
        <div class="time-widget" style="min-width: 320px;">
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 6px; margin-bottom: 0.5rem;">
                <div id="shiftLabel" style="font-size: 0.72rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">{{ __('Loading Shift...') }}</div>
                
                <div id="systemHours" style="font-size: 0.72rem; font-weight: 700; color: var(--text-primary); background: rgba(0,212,160,0.1); border: 1px solid rgba(0,212,160,0.25); padding: 2px 8px; border-radius: 8px; display: flex; align-items: center; gap: 4px; white-space: nowrap;">
                    <i class="ph ph-clock-afternoon" style="font-size: 0.8rem; color: var(--primary);"></i>
                    <span style="color: var(--primary); text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.5px;">{{ __('System') }}:</span>
                    <span id="sysOpenDisplay" style="font-weight: 800;">{{ $sysOpen }}</span> - <span id="sysCloseDisplay" style="font-weight: 800;">{{ $sysClose }}</span>
                </div>

                <div id="shiftCutoff" style="font-size: 0.72rem; font-weight: 700; color: var(--text-secondary); background: rgba(255,255,255,0.06); padding: 2px 8px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08); white-space: nowrap;">{{ __('Cutoff') }}: --:--</div>
            </div>
            <div class="clock-display" id="clock">
                <span id="clock-main">00:00</span>
                <span id="clock-seconds" class="seconds">00</span>
            </div>
            @php
                $liveLocale = app()->getLocale();
            @endphp
            <div class="date-display" id="date-text">{{ now()->locale($liveLocale)->translatedFormat('l, j F Y') }}</div>
        </div>
    </header>

    <!-- Main -->
    <main class="live-main">
        
        <!-- Stats Dashboard (Now Inside Main) -->
        <div class="live-stats">
            <div class="stat-widget">
                <i class="ph ph-users-four"></i>
                <div class="stat-info">
                    <div class="s-val" id="statPresent">0</div>
                    <div class="s-label">{{ __('Present') }}</div>
                </div>
            </div>
            <div class="stat-widget late">
                <i class="ph ph-clock-user"></i>
                <div class="stat-info">
                    <div class="s-val" id="statLate">0</div>
                    <div class="s-label">{{ __('Late') }}</div>
                </div>
            </div>
            <div class="stat-widget rem">
                <i class="ph ph-user-minus"></i>
                <div class="stat-info">
                    <div class="s-val" id="statRemaining">0</div>
                    <div class="s-label">{{ __('Remaining') }}</div>
                </div>
            </div>
            <div class="stat-widget" style="flex: 0.6; min-width: 140px; justify-content: space-between;">
                <div class="stat-info">
                    <div class="s-val"><span id="statPresentNum">0</span>/<span id="statTotalNum">0</span></div>
                    <div class="s-label">{{ __('Present') }}</div>
                </div>
                <div class="gauge-container">
                    <svg class="gauge-svg" viewBox="0 0 36 36" width="60" height="60">
                        <path class="gauge-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                        <path id="gaugeFill" class="gauge-fill" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" style="stroke-dashoffset: 100; stroke-width: 3.5;"></path>
                    </svg>
                    <span class="gauge-text" id="gaugeText" style="font-size: 0.95rem;">0%</span>
                </div>
            </div>
        </div>

        <div class="live-content-row">
            <!-- Center Scan Display -->
            <div class="scan-area">
            
            <!-- Default standby view -->
            <div id="standbyView" style="text-align: center; animation: slideDownIn 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                <div class="radar-container style-radar" id="mainRadarContainer">
                    <!-- Style 1: Radar Elements -->
                    <div class="radar-ring style-elem-radar"></div>
                    <div class="radar-ring-inner style-elem-radar"></div>
                    <div class="radar-sweep style-elem-radar"></div>
                    <div class="radar-pulse-wave style-elem-radar"></div>
                    <div class="radar-pulse-wave style-elem-radar"></div>

                    <!-- Style 2: Quantum Elements -->
                    <div class="quantum-orb style-elem-quantum" style="display:none;"></div>
                    <div class="quantum-orbit-1 style-elem-quantum" style="display:none;"></div>
                    <div class="quantum-orbit-2 style-elem-quantum" style="display:none;"></div>

                    <!-- Style 3: Laser Reticle Elements -->
                    <div class="laser-grid style-elem-laser" style="display:none;"></div>
                    <div class="laser-corner tl style-elem-laser" style="display:none;"></div>
                    <div class="laser-corner tr style-elem-laser" style="display:none;"></div>
                    <div class="laser-corner bl style-elem-laser" style="display:none;"></div>
                    <div class="laser-corner br style-elem-laser" style="display:none;"></div>
                    <div class="laser-beam style-elem-laser" style="display:none;"></div>

                    <!-- Style 4: Glass Ripple Elements -->
                    <div class="glass-ripple-ring style-elem-ripple" style="display:none;"></div>
                    <div class="glass-ripple-ring style-elem-ripple" style="display:none;"></div>
                    <div class="glass-ripple-ring style-elem-ripple" style="display:none;"></div>
                    
                    {{-- 3D Holographic RFID Card (Shared) --}}
                    <div class="radar-3d-card">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div class="card-chip"></div>
                            <i class="ph ph-wave-waves card-wave-lines"></i>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: auto;">
                            <div style="font-size: 0.55rem; font-weight: 800; color: var(--primary); letter-spacing: 1px;">RFID SYSTEM</div>
                            <i class="ph ph-contactless-payment" style="color: rgba(255,255,255,0.7); font-size: 0.9rem;"></i>
                        </div>
                    </div>
                </div>


                <h2 class="standby-text" style="margin-top: 0.5rem;">
                    {{ __('Awaiting Scan...') }}
                </h2>
                <p style="color: var(--text-muted); font-size: 1rem; letter-spacing: 1px; margin-top: 0.5rem; text-transform: uppercase;">
                    {{ __('Please tap your card to scan') }}
                </p>
            </div>

            <!-- Active Scan Card (Hidden by default) -->
            <div class="scan-card" id="scanCard" style="display: none; position: relative; z-index: 10;">
                <div id="teacherPhotoContainer" class="teacher-photo-container">
                    <img id="teacherPhoto" src="" alt="">
                </div>
                <div id="teacherPlaceholder" class="teacher-photo-placeholder">
                    <i class="ph ph-user"></i>
                </div>

                <div id="scanIconContainer" style="position: absolute; top: 2rem; right: 2rem;">
                    <i class="ph ph-check-circle scan-icon check-in" id="scanIcon" style="font-size: 3rem; margin: 0;"></i>
                </div>
                
                <div class="teacher-name notranslate" id="scanName" translate="no">{{ __('Teacher Name') }}</div>
                <div class="teacher-dept" id="scanDept">{{ __('Department') }}</div>
                
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px dashed var(--border);">
                    <div style="font-size: 1.2rem; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.5rem;" id="scanLabel">
                        {{ __('Check In Time') }}
                    </div>
                    <div class="scan-time check-in" id="scanTime">00:00:00</div>
                </div>
            </div>

        </div>

        <!-- History Panel -->
        <div class="history-panel">
            <div class="history-header">
                <i class="ph ph-clock-counter-clockwise"></i>
                {{ __('Recent Scans') }}
                <span class="h-count" id="historyCount">0</span>
            </div>
            <div class="history-list" id="historyList">
                <div class="history-empty">
                    <i class="ph ph-scan"></i>
                    {{ __('No recent scans.') }}
                </div>
            </div>
            </div>
        </div>

    </main>

    <script>
        // Translation Map
        const deptMap = {
            @foreach($departments as $d)
            "{{ $d->name }}": "{{ app()->getLocale() == 'km' ? ($d->name_kh ?: $d->name) : $d->name }}",
            @endforeach
        };
        window.transDept = function(d) { 
            if (!d) return d;
            const entry = Object.entries(deptMap).find(([k]) => k.toLowerCase() === d.trim().toLowerCase());
            return entry ? entry[1] : d;
        };

        // --- 1. Clock Logic ---
        // Date is pre-rendered server-side via PHP Carbon translatedFormat.
        // We only update the clock here, and refresh the date string at midnight.
        let _lastDateDay = new Date().getDate();

        function updateClock() {
            const now = new Date();

            // Update time display
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock-main').textContent = `${h}:${m}`;
            document.getElementById('clock-seconds').textContent = s;

            // Refresh date only when the day rolls over (midnight)
            if (now.getDate() !== _lastDateDay) {
                _lastDateDay = now.getDate();
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                const locale = '{{ app()->getLocale() === "km" ? "km-KH" : "en-US" }}';
                document.getElementById('date-text').textContent = now.toLocaleDateString(locale, options);
            }
        }
        setInterval(updateClock, 1000);
        updateClock();


        // --- 2. Polling Logic & Audio ---
        let lastUpdatedAt = '';
        let initialSettingsVersion = null;
        let isInitialLoad = true;
        let scanClearTimeout;
        let scanAlertDuration = 15; // Default 15s
        let seenScans = new Map(); // Store ID -> updatedAt

        // Audio objects
        const audioSuccess = new Audio('https://assets.mixkit.co/active_storage/sfx/2354/2354-preview.mp3');
        const audioError = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');

        function playSound(type) {
            if (type === 'success') audioSuccess.play().catch(e => console.log('Audio disabled by browser'));
            else audioError.play().catch(e => console.log('Audio disabled by browser'));
        }

        async function pollLatest() {
            try {
                // If it's the very first poll, we just get the latest list to populate history, but don't flash the big card.
                const url = lastUpdatedAt ? `/api-live/latest?last_updated_at=${encodeURIComponent(lastUpdatedAt)}` : `/api-live/latest`;
                
                const response = await fetch(url);
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                
                // --- Settings Auto-Reload ---
                if (initialSettingsVersion === null && data.settings_updated_at !== undefined) {
                    initialSettingsVersion = data.settings_updated_at;
                } else if (initialSettingsVersion !== null && data.settings_updated_at && data.settings_updated_at > initialSettingsVersion) {
                    console.log("Settings changed! Reloading Live Monitor...");
                    window.location.reload();
                    return;
                }
                
                if (data.scan_alert_duration) {
                    scanAlertDuration = data.scan_alert_duration;
                }
                
                lastUpdatedAt = data.server_time; // Update timestamp for next poll

                if (data.scans && data.scans.length > 0) {
                    // Filter out truly seen scans (same ID AND same updatedAt)
                    const newScans = data.scans.filter(s => {
                        const lastTime = seenScans.get(s.id);
                        return !lastTime || lastTime !== s.updated_at;
                    });
                    
                    // Update seen map
                    data.scans.forEach(s => seenScans.set(s.id, s.updated_at));

                    // Populate History List
                    renderHistory(data.scans);

                    // If it's NOT the initial load, it means NEW scans just happened!
                    if (!isInitialLoad && newScans.length > 0) {
                        const latestScan = newScans[0];
                        
                        // Show the big popup for the most recent one
                        showScanPopup(latestScan);
                        
                        if (latestScan.status === 'error') {
                            playSound('error');
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: '{{ __("Unauthorized Scan") }}',
                                    text: `${latestScan.message} (${window.transDept(latestScan.department)})`,
                                    icon: 'error',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: scanAlertDuration * 1000,
                                    background: 'var(--live-card-bg)',
                                    color: 'var(--text-primary)'
                                });
                            }
                        } else {
                            playSound('success');
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: latestScan.teacher_name_kh || latestScan.teacher_name,
                                    text: `${latestScan.type === 'check-in' ? '{{ __("Checked In") }}' : '{{ __("Checked Out") }}'} {{ __("at") }} ${latestScan.time}`,
                                    icon: 'success',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: scanAlertDuration * 1000,
                                    timerProgressBar: true,
                                    background: 'var(--live-card-bg)',
                                    color: 'var(--text-primary)'
                                });
                            } else {
                                window.showToast(`${latestScan.teacher_name}: ${latestScan.type === 'check-in' ? '{{ __("Checked In") }}' : '{{ __("Checked Out") }}'}`, 'success');
                            }
                        }
                    }
                }
                
                isInitialLoad = false;

                // Update Stats
                if (data.stats) {
                    updateStats(data.stats);
                }
                
                if (data.shift) {
                    updateShiftInfo(data.shift);
                }

            } catch (error) {
                console.error("Polling error:", error);
            }
        }

        function renderHistory(scans) {
            const list = document.getElementById('historyList');
            const countEl = document.getElementById('historyCount');
            list.innerHTML = '';
            countEl.textContent = scans.length;
            
            if (scans.length === 0) {
                list.innerHTML = '<div class="history-empty"><i class="ph ph-scan"></i>{{ __("No recent scans.") }}</div>';
                return;
            }

            scans.forEach((scan, idx) => {
                const isCheckIn = scan.type === 'check-in';
                const initial = scan.teacher_name ? scan.teacher_name.charAt(0).toUpperCase() : '?';
                const badgeText = isCheckIn ? '{{ __("IN") }}' : '{{ __("OUT") }}';
                const icon = isCheckIn ? '<i class="ph ph-sign-in"></i>' : '<i class="ph ph-sign-out"></i>';
                
                const item = document.createElement('div');
                item.className = `history-item ${scan.type}`;
                item.style.animationDelay = `${idx * 0.05}s`;
                item.innerHTML = `
                    <div class="h-avatar">${initial}</div>
                    <div class="h-info">
                        <div class="h-name-kh notranslate" translate="no" style="color:var(--primary); font-size:0.95rem; font-weight:700;">${scan.teacher_name_kh || ''}</div>
                        <div class="h-name notranslate" translate="no" style="font-size:0.8rem; opacity:0.8;">${scan.teacher_name}</div>
                        <div class="h-dept">${window.transDept(scan.department)}</div>
                    </div>
                    <div class="h-right">
                        <div class="history-time">${scan.time}</div>
                        <div class="h-badge">${icon} ${badgeText}</div>
                    </div>
                `;
                list.appendChild(item);
            });
        }

        function showScanPopup(scan) {
            const standby = document.getElementById('standbyView');
            const card = document.getElementById('scanCard');
            const icon = document.getElementById('scanIcon');
            const name = document.getElementById('scanName');
            const dept = document.getElementById('scanDept');
            const label = document.getElementById('scanLabel');
            const time = document.getElementById('scanTime');
            const photoCont = document.getElementById('teacherPhotoContainer');
            const photoImg  = document.getElementById('teacherPhoto');
            const placeholder = document.getElementById('teacherPlaceholder');

            // Setup content
            name.innerHTML = `
                <div style="color:var(--primary); font-size:4.5rem; line-height:1.1; margin-bottom:0.5rem;">${scan.teacher_name_kh || ''}</div>
                <div style="font-size:2.8rem; opacity:0.8;">${scan.teacher_name}</div>
            `;
            dept.textContent = window.transDept(scan.department);
            time.textContent = scan.time;

            // Photo logic
            if (scan.photo) {
                photoImg.src = scan.photo;
                photoCont.style.display = 'block';
                placeholder.style.display = 'none';
            } else {
                photoCont.style.display = 'none';
                placeholder.style.display = 'flex';
            }

            const isCheckIn = scan.type === 'check-in';
            const isError   = scan.type === 'error';
            const typeClass = isError ? 'error' : (isCheckIn ? 'check-in' : 'check-out');
            
            // Remove old classes, add new
            card.className = 'scan-card active ' + typeClass;
            icon.className = 'ph scan-icon ' + typeClass + (isError ? ' ph-warning-octagon' : (isCheckIn ? ' ph-check-circle' : ' ph-sign-out'));
            time.className = 'scan-time ' + typeClass;
            
            if (isError) {
                label.textContent = scan.message || '{{ __("Unauthorized Scan") }}';
                label.style.color = 'var(--danger)';
            } else {
                label.style.color = '';
                label.textContent = isCheckIn 
                    ? '{{ __("Checked In") }}' 
                    : '{{ __("Checked Out") }}';
            }

            // Show card, hide standby
            standby.style.display = 'none';
            card.style.display = 'block';
            card.classList.add('active'); // Added to trigger animation

            // Reset timer to hide the card after setting
            clearTimeout(scanClearTimeout);
            scanClearTimeout = setTimeout(() => {
                card.classList.remove('active');
                setTimeout(() => {
                    card.style.display = 'none';
                    standby.style.display = 'block';
                }, 500); 
            }, scanAlertDuration * 1000);
        }

        function updateStats(stats) {
            const animateVal = (id, val) => {
                const el = document.getElementById(id);
                const current = parseInt(el.textContent) || 0;
                if (current === val) return;
                
                // Simple animation
                let start = current;
                const duration = 1000;
                const stepTime = Math.abs(Math.floor(duration / (val - current)));
                
                const timer = setInterval(() => {
                    start += (val > current ? 1 : -1);
                    el.textContent = start;
                    if (start == val) clearInterval(timer);
                }, stepTime || 50);
            };

            animateVal('statPresent', stats.present);
            animateVal('statLate', stats.late);
            animateVal('statRemaining', stats.remaining);
            
            // Update absolute numbers
            document.getElementById('statPresentNum').textContent = stats.present;
            document.getElementById('statTotalNum').textContent = stats.total;

            // Update Gauge
            const gauge = document.getElementById('gaugeFill');
            const gaugeText = document.getElementById('gaugeText');
            if (gauge) {
                const offset = 100 - stats.rate;
                gauge.style.strokeDashoffset = offset;
                gaugeText.textContent = stats.rate + '%';
            }
        }

        function updateShiftInfo(shift) {
            if (!shift) return;
            document.getElementById('shiftLabel').textContent = shift.label;
            document.getElementById('shiftCutoff').textContent = '{{ __("Cutoff") }}: ' + shift.cutoff;
            if (shift.open_time && document.getElementById('sysOpenDisplay')) {
                document.getElementById('sysOpenDisplay').textContent = shift.open_time;
            }
            if (shift.close_time && document.getElementById('sysCloseDisplay')) {
                document.getElementById('sysCloseDisplay').textContent = shift.close_time;
            }
        }

        // ── Radar Animation Style Switcher ──────────────────────────────────────
        function setRadarStyle(style) {
            const container = document.getElementById('mainRadarContainer');
            if (!container) return;

            // Remove existing style classes
            container.classList.remove('style-radar', 'style-quantum', 'style-laser', 'style-ripple');
            container.classList.add('style-' + style);

            // Toggle element visibility
            document.querySelectorAll('.style-elem-radar').forEach(el => el.style.display = (style === 'radar' ? 'block' : 'none'));
            document.querySelectorAll('.style-elem-quantum').forEach(el => el.style.display = (style === 'quantum' ? 'block' : 'none'));
            document.querySelectorAll('.style-elem-laser').forEach(el => el.style.display = (style === 'laser' ? 'block' : 'none'));
            document.querySelectorAll('.style-elem-ripple').forEach(el => el.style.display = (style === 'ripple' ? 'block' : 'none'));

            // Update button active states
            document.querySelectorAll('.style-btn').forEach(btn => btn.classList.remove('active'));
            const activeBtn = document.getElementById('btnStyle' + style.charAt(0).toUpperCase() + style.slice(1));
            if (activeBtn) activeBtn.classList.add('active');

            // Save preference to LocalStorage permanently
            localStorage.setItem('live_radar_style', style);
        }

        // Initialize saved radar style & start polling on page load
        document.addEventListener('DOMContentLoaded', () => {
            const savedStyle = localStorage.getItem('live_radar_style') || 'radar';
            setRadarStyle(savedStyle);

            pollLatest();
            setInterval(pollLatest, 5000);
        });

        // ── Theme Toggle (Live-specific, independent from admin) ──────────────────────────────
        function toggleTheme() {
            const html = document.documentElement;
            const isLight = html.getAttribute('data-theme') === 'light';
            const btn = document.getElementById('themeToggleBtn');
            if (isLight) {
                html.removeAttribute('data-theme');
                localStorage.setItem('live_theme', 'dark');
                document.getElementById('themeIcon').className = 'ph ph-sun';
                btn.title = '{{ __("Light mode") }}';
                btn.classList.remove('active');
            } else {
                html.setAttribute('data-theme', 'light');
                localStorage.setItem('live_theme', 'light');
                document.getElementById('themeIcon').className = 'ph ph-moon';
                btn.title = '{{ __("Dark mode") }}';
                btn.classList.add('active');
            }
        }

        // Sync theme button state on load
        (function syncThemeBtn() {
            const isLight = document.documentElement.getAttribute('data-theme') === 'light';
            const btn = document.getElementById('themeToggleBtn');
            if (isLight) {
                document.getElementById('themeIcon').className = 'ph ph-moon';
                btn.title = '{{ __("Dark mode") }}';
                btn.classList.add('active');
            }
        })();

        // ── Fullscreen Toggle ─────────────────────────
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen().catch(() => {});
            }
        }

        document.addEventListener('fullscreenchange', () => {
            const inFS  = !!document.fullscreenElement;
            const icon  = document.getElementById('fullscreenIcon');
            const btn   = document.getElementById('fullscreenBtn');
            icon.className = inFS ? 'ph ph-arrows-in' : 'ph ph-arrows-out';
            btn.title = inFS ? '{{ __("Exit fullscreen") }}' : '{{ __("Fullscreen") }}';
            btn.classList.toggle('active', inFS);
        });

        // Header toolbar no longer shows rate; no extra JS needed.

        // ── Admin Scan Panel ─────────────────────────
        let selectedAdminTeacherId = null;

        function openAdminPanel() {
            document.getElementById('adminOverlay').classList.add('open');
            document.getElementById('adminTeacherInput').focus();
        }

        function closeAdminPanel() {
            document.getElementById('adminOverlay').classList.remove('open');
            document.getElementById('adminResult').style.display = 'none';
        }

        function selectAdminTeacher(el) {
            document.querySelectorAll('.admin-teacher-item').forEach(i => i.classList.remove('selected'));
            el.classList.add('selected');
            selectedAdminTeacherId = el.dataset.id;
            document.getElementById('doScanBtn').disabled = false;
        }

        function filterAdminList(q) {
            q = q.toLowerCase();
            document.querySelectorAll('.admin-teacher-item').forEach(item => {
                const match = item.dataset.name.toLowerCase().includes(q) ||
                              item.dataset.dept.toLowerCase().includes(q);
                item.style.display = match ? '' : 'none';
            });
        }

        async function doAdminScan() {
            if (!selectedAdminTeacherId) return;

            const btn = document.getElementById('doScanBtn');
            const resultEl = document.getElementById('adminResult');
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-circle-notch" style="animation:spin 1s linear infinite;display:inline-block;"></i> {{ __("Scanning...") }}';
            resultEl.style.display = 'none';

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const res  = await fetch('{{ route("api.attendance.admin-scan") }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type':  'application/json',
                        'X-CSRF-TOKEN':  csrf,
                        'Accept':        'application/json',
                    },
                    body: JSON.stringify({ teacher_id: parseInt(selectedAdminTeacherId) })
                });
                const data = await res.json();

                // Show result in panel
                const cls = data.status === 'success' ? 'success' : data.status === 'info' ? 'info' : 'error';
                resultEl.className = cls;
                resultEl.textContent = data.message || '{{ __("Done") }}';
                resultEl.style.display = 'block';

                // Show the big scan popup on the live screen
                if (data.status === 'success' || data.status === 'info') {
                    playSound(data.status === 'success' ? 'success' : 'info');
                    const popupType = data.action === 'check-in' ? 'check-in' : 'check-out';
                    showScanPopup({
                        teacher_name: data.teacher_name || '',
                        teacher_name_kh: data.teacher_name_kh || '',
                        department:   data.department   || '',
                        time:         data.time         || new Date().toLocaleTimeString(),
                        type:         popupType,
                        photo:        data.photo        || null
                    });
                    // Refresh history list immediately
                    lastUpdatedAt = '';
                    isInitialLoad = true;
                    pollLatest();

                    // Auto close panel after 2s on success
                    if (data.status === 'success') {
                        setTimeout(closeAdminPanel, 2000);
                    }
                } else {
                    playSound('error');
                }
            } catch(e) {
                resultEl.className = 'error';
                resultEl.textContent = e.message || '{{ __("Network error") }}';
                resultEl.style.display = 'block';
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-scan"></i> {{ __("Scan Selected Teacher") }}';
            }
        }

        // ── Hardware Status Polling ──────────────────
        async function checkDeviceStatus() {
            try {
                const res = await fetch('/api/device-status');
                const data = await res.json();
                
                const dot = document.getElementById('live-hw-dot');
                const text = document.getElementById('live-hw-text');
                const meta = document.getElementById('live-hw-meta');
                const icon = document.getElementById('live-hw-icon');
                const badge = document.getElementById('live-hw-badge');
                
                if (data.online) {
                    dot.style.background = 'var(--primary)';
                    dot.style.boxShadow = '0 0 10px rgba(0,212,160,0.5)';
                    dot.style.animation = 'livePulse 2s infinite';
                    text.textContent = '{{ __("Scanner Online") }}';
                    text.style.color = 'var(--primary)';
                    meta.textContent = `RSSI: ${data.rssi} dBm · ${data.last_seen_ago}`;
                    icon.style.color = 'var(--primary)';
                    badge.style.borderColor = 'rgba(0,212,160,0.3)';
                } else {
                    dot.style.background = 'var(--danger)';
                    dot.style.boxShadow = '0 0 10px rgba(239,68,68,0.5)';
                    dot.style.animation = 'none';
                    text.textContent = '{{ __("Scanner Offline") }}';
                    text.style.color = 'var(--danger)';
                    meta.textContent = `Last seen: ${data.last_seen_ago}`;
                    icon.style.color = 'var(--danger)';
                    badge.style.borderColor = 'rgba(239,68,68,0.3)';
                }
            } catch (e) {
                console.error("Hardware status error:", e);
            }
        }
        checkDeviceStatus();
        setInterval(checkDeviceStatus, 10000);


        // ── Modern Alert/Confirm/Toast System (Synced from app layout) ──────────────────
        
        // 1. Toast UI logic
        const toastContainer = document.createElement('div');
        toastContainer.style = "position:fixed; top:20px; right:20px; z-index:9999; display:flex; flex-direction:column; gap:10px; pointer-events:none;";
        document.body.appendChild(toastContainer);

        window.showToast = (msg, type = 'success') => {
            const toast = document.createElement('div');
            const color = type === 'success' ? 'var(--primary)' : (type === 'error' ? 'var(--danger)' : 'var(--warning)');
            const icon = type === 'success' ? 'ph ph-check-circle' : (type === 'error' ? 'ph ph-x-circle' : 'ph ph-warning');
            
            toast.style.cssText = `
                min-width: 280px; background: var(--live-card-bg); border-left: 4px solid ${color}; color: var(--text-primary);
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

        // 2. Override window.alert for Admin Panel errors
        const alertModal = document.createElement('div');
        alertModal.style = "position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); z-index:10000; display:none; align-items:center; justify-content:center; padding:20px;";
        alertModal.innerHTML = `
            <div style="background:var(--live-card-bg); border:1px solid var(--border); width:100%; max-width:400px; border-radius:8px; overflow:hidden; animation:modalPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                <div style="padding:1.5rem; text-align:center;">
                    <i class="ph ph-warning-circle" style="font-size:3rem; color:var(--primary); margin-bottom:1rem; display:block;"></i>
                    <p id="customAlertMsg" style="font-size:1rem; font-weight:500; color:var(--text-primary); margin:0; line-height:1.5;"></p>
                </div>
                <div style="padding:1rem; border-top:1px solid var(--border); display:flex; justify-content:center; background:rgba(0,0,0,0.2);">
                    <button id="customAlertBtn" class="admin-scan-btn" style="position:static; width:120px; cursor: pointer; justify-content:center;">OK</button>
                </div>
            </div>
        `;
        document.body.appendChild(alertModal);

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

        // Keyboard: Escape = close panel
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeAdminPanel();
        });

        // Live Monitor Weather Logic
        async function fetchLiveMonitorWeather() {
            try {
                const res = await fetch('https://api.open-meteo.com/v1/forecast?latitude=11.5564&longitude=104.9282&current_weather=true');
                const data = await res.json();
                const weather = data.current_weather;
                if(weather) {
                    const textEl = document.getElementById('liveWeatherText');
                    if (textEl) textEl.innerHTML = `${Math.round(weather.temperature)}°C`;
                    
                    const icon = document.getElementById('liveWeatherIcon');
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
                console.error('Live monitor weather fetch failed', e);
            }
        }
        fetchLiveMonitorWeather();
        setInterval(fetchLiveMonitorWeather, 30 * 60 * 1000); // Update every 30 minutes
    </script>
</body>
</html>
