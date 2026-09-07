<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Smart Kiosk Station') }} — NTTI</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700;900&family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700;800&family=Kantumruy+Pro:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Phosphor Icons --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    {{-- Canvas Confetti --}}
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    {{-- HTML5 QR Code & QRCode JS --}}
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    @php
        $uLogo = \App\Models\Setting::getAssetUrl('university_logo', '/images/ntti_logo.png');
        $uName = \App\Models\Setting::getValue('university_name', 'វិទ្យាស្ថានជាតិបណ្តុះបណ្តាលបច្ចេកទេស');
    @endphp

    <style>
        :root {
            --bg-kiosk: radial-gradient(circle at 50% 15%, #0f172a 0%, #020617 100%);
            --card-bg: rgba(15, 23, 42, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.35);
            --cyan: #06b6d4;
            --cyan-glow: rgba(6, 182, 212, 0.35);
            --amber: #f59e0b;
            --danger: #ef4444;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --font-kh: 'Battambang', 'Kantumruy Pro', sans-serif;
            --font-en: 'Inter', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            user-select: none;
        }

        body {
            background: var(--bg-kiosk);
            color: var(--text-main);
            font-family: var(--font-en), var(--font-kh);
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* ── Top Header Bar ── */
        .kiosk-header {
            height: 76px;
            background: rgba(10, 15, 29, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.75rem;
            z-index: 100;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .brand-logo-wrap {
            position: relative;
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .brand-logo-wrap img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .brand-text h1 {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .brand-text .tagline {
            font-size: 0.72rem;
            color: var(--cyan);
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .header-center {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .clock-badge {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--card-border);
            padding: 0.45rem 1.25rem;
            border-radius: 9999px;
        }

        .clock-time {
            font-family: var(--font-mono);
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 1px;
        }

        .clock-date {
            font-size: 0.8rem;
            color: var(--text-sub);
            font-family: var(--font-kh);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            padding-left: 0.85rem;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .status-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.9rem;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid var(--primary);
            color: var(--primary);
            transition: all 0.3s;
        }

        .status-pill.offline {
            background: rgba(245, 158, 11, 0.15);
            border-color: var(--amber);
            color: var(--amber);
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            animation: pulseGlow 1.5s infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        .icon-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .icon-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        .icon-btn.active {
            background: rgba(16, 185, 129, 0.2);
            border-color: var(--primary);
            color: var(--primary);
        }

        /* ── Main Kiosk Body (2 Columns) ── */
        .kiosk-main {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 440px;
            gap: 1.5rem;
            padding: 1.25rem 1.75rem;
            overflow: hidden;
            position: relative;
        }

        /* ── Left Column: Multi-Scanner Bay ── */
        .scanner-panel {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 1.5rem;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
        }

        .glow-blob {
            position: absolute;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            filter: blur(60px);
            top: -100px;
            left: -100px;
            pointer-events: none;
            z-index: 0;
        }

        /* Scanner Tabs */
        .scanner-tabs {
            display: flex;
            gap: 0.75rem;
            background: rgba(2, 6, 23, 0.6);
            padding: 0.35rem;
            border-radius: 1rem;
            border: 1px solid var(--card-border);
            z-index: 2;
        }

        .tab-btn {
            flex: 1;
            padding: 0.65rem 1rem;
            border-radius: 0.75rem;
            border: none;
            background: transparent;
            color: var(--text-sub);
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.25s;
        }

        .tab-btn i { font-size: 1.1rem; }

        .tab-btn.active {
            background: rgba(16, 185, 129, 0.15);
            color: #fff;
            border: 1px solid rgba(16, 185, 129, 0.4);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
        }

        /* Viewport Container */
        .scanner-viewport {
            flex: 1;
            background: #020617;
            border-radius: 1.25rem;
            border: 1.5px solid var(--card-border);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        /* HTML5 QR Camera Container */
        #kioskQrReader {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #kioskQrReader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 1.25rem;
        }

        #kioskQrReader__scan_region {
            width: 100% !important;
            height: 100% !important;
            min-height: 100% !important;
        }

        /* High-tech Sci-Fi HUD Overlay */
        .hud-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 10;
        }

        .hud-reticle-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 260px;
            height: 260px;
            border: 1px dashed rgba(6, 182, 212, 0.3);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(6, 182, 212, 0.15), inset 0 0 30px rgba(6, 182, 212, 0.1);
        }

        .hud-corner {
            position: absolute;
            width: 24px;
            height: 24px;
            border-color: var(--cyan);
            border-style: solid;
            filter: drop-shadow(0 0 8px var(--cyan));
        }

        .corner-tl { top: -2px; left: -2px; border-width: 3px 0 0 3px; border-top-left-radius: 12px; }
        .corner-tr { top: -2px; right: -2px; border-width: 3px 3px 0 0; border-top-right-radius: 12px; }
        .corner-bl { bottom: -2px; left: -2px; border-width: 0 0 3px 3px; border-bottom-left-radius: 12px; }
        .corner-br { bottom: -2px; right: -2px; border-width: 0 3px 3px 0; border-bottom-right-radius: 12px; }

        .hud-laser {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent 0%, var(--cyan) 50%, transparent 100%);
            box-shadow: 0 0 15px var(--cyan);
            animation: laserSweep 2.4s ease-in-out infinite alternate;
        }

        @keyframes laserSweep {
            0% { top: 8%; opacity: 0.7; }
            100% { top: 92%; opacity: 1; }
        }

        /* RFID Mode Screen */
        .rfid-view {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            width: 100%;
            height: 100%;
            padding: 2rem;
            text-align: center;
        }

        .rfid-illustration {
            position: relative;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: var(--primary);
            box-shadow: 0 0 40px var(--primary-glow);
            animation: waveRipple 2s infinite ease-out;
        }

        @keyframes waveRipple {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
            70% { box-shadow: 0 0 0 30px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Rotating Screen QR Mode */
        .screen-qr-view {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            width: 100%;
            height: 100%;
            padding: 2rem;
            text-align: center;
        }

        .qr-canvas-box {
            background: #fff;
            padding: 1.25rem;
            border-radius: 1.5rem;
            box-shadow: 0 0 40px rgba(16, 185, 129, 0.35);
            border: 2px solid var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Scanner Bottom Prompt */
        .scanner-prompt {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1.25rem;
            background: rgba(2, 6, 23, 0.5);
            border: 1px solid var(--card-border);
            border-radius: 1rem;
            z-index: 2;
        }

        .prompt-text {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-sub);
        }

        .prompt-text i { font-size: 1.25rem; color: var(--primary); }

        /* ── Right Column: Live Statistics & Stream ── */
        .feed-panel {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            overflow: hidden;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 1rem;
            padding: 1rem 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            position: relative;
            overflow: hidden;
        }

        .stat-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-sub);
            letter-spacing: 0.05em;
        }

        .stat-value {
            font-family: var(--font-mono);
            font-size: 1.5rem;
            font-weight: 900;
            line-height: 1.1;
            color: #fff;
        }

        .stat-card.present .stat-value { color: var(--primary); }
        .stat-card.ontime .stat-value { color: var(--cyan); }
        .stat-card.late .stat-value { color: var(--amber); }

        /* Stream Container */
        .stream-card {
            flex: 1;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 1.25rem;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .stream-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--card-border);
        }

        .stream-title {
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stream-list {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            padding-right: 0.25rem;
        }

        .stream-list::-webkit-scrollbar { width: 4px; }
        .stream-list::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); border-radius: 4px; }

        /* Teacher Scan Item */
        .scan-item {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            background: rgba(2, 6, 23, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 0.75rem;
            border-radius: 0.85rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            animation: slideInDown 0.4s ease-out;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .scan-item.highlight {
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.25);
            background: rgba(16, 185, 129, 0.08);
        }

        .item-avatar {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .item-info {
            flex: 1;
            min-width: 0;
        }

        .item-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-name-kh {
            font-size: 0.8rem;
            color: var(--text-sub);
            font-family: var(--font-kh);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-dept {
            font-size: 0.7rem;
            color: var(--text-sub);
        }

        .item-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
        }

        .item-time {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
        }

        .item-badge {
            font-size: 0.65rem;
            font-weight: 800;
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-ontime { background: rgba(16, 185, 129, 0.2); color: var(--primary); }
        .badge-late { background: rgba(245, 158, 11, 0.2); color: var(--amber); }

        /* ── Bottom Announcements Marquee ── */
        .kiosk-footer {
            height: 44px;
            background: rgba(10, 15, 29, 0.95);
            border-top: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            overflow: hidden;
            z-index: 50;
        }

        .marquee-label {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            color: #020617;
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0 1.25rem;
            white-space: nowrap;
            z-index: 2;
        }

        .marquee-content {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            box-sizing: border-box;
            position: relative;
        }

        .marquee-track {
            display: inline-block;
            padding-left: 100%;
            animation: marqueeScroll 28s linear infinite;
            font-size: 0.85rem;
            font-family: var(--font-kh);
            color: #e2e8f0;
        }

        @keyframes marqueeScroll {
            0% { transform: translate(0, 0); }
            100% { transform: translate(-100%, 0); }
        }

        /* ── Fullscreen Holographic Recognition HUD Modal ── */
        .hud-modal {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(25px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .hud-modal.show {
            display: flex;
            opacity: 1;
        }

        .hud-card {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.9) 100%);
            border: 2px solid var(--primary);
            box-shadow: 0 0 60px rgba(16, 185, 129, 0.35), 0 30px 80px rgba(0, 0, 0, 0.8);
            border-radius: 2rem;
            padding: 3rem 4rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            max-width: 580px;
            width: 90%;
            position: relative;
            animation: popIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes popIn {
            0% { transform: scale(0.85); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .hud-avatar-frame {
            position: relative;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            padding: 6px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--cyan) 100%);
            box-shadow: 0 0 30px var(--primary-glow);
        }

        .hud-avatar-frame img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            background: #020617;
        }

        .hud-check-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary);
            color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            border: 4px solid #0f172a;
            box-shadow: 0 0 15px var(--primary);
        }

        .hud-teacher-name-kh {
            font-family: var(--font-kh);
            font-size: 2rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.2;
        }

        .hud-teacher-name-en {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--text-sub);
            letter-spacing: 0.02em;
        }

        .hud-action-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.5rem;
            border-radius: 9999px;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .hud-details-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            color: var(--text-sub);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .hud-details-row span {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .hud-details-row i { color: var(--cyan); }
    </style>
</head>
<body>

    {{-- Top Header --}}
    <header class="kiosk-header">
        <div class="brand-section">
            <div class="brand-logo-wrap">
                <img src="{{ $uLogo }}" onerror="this.src='{{ asset('images/ntti_logo.png') }}'; this.onerror=null;" alt="NTTI">
            </div>
            <div class="brand-text">
                <h1>{{ $uName }} <span style="color: var(--primary); font-size: 0.75rem; border: 1px solid var(--primary); border-radius: 4px; padding: 2px 6px;">NTTI</span></h1>
                <div class="tagline">SMART ATTENDANCE KIOSK STATION · ស្ថានីយស្កេនវៃឆ្លាត</div>
            </div>
        </div>

        <div class="header-center">
            <div class="clock-badge">
                <i class="ph ph-clock" style="font-size: 1.3rem; color: var(--cyan);"></i>
                <div class="clock-time" id="kioskClock">00:00:00</div>
                <div class="clock-date" id="kioskDate">កំពុងដំណើរការ...</div>
            </div>
        </div>

        <div class="header-actions">
            <div class="status-pill" id="networkStatusPill">
                <div class="pulse-dot"></div>
                <span id="networkStatusText">ONLINE</span>
            </div>

            <button class="icon-btn active" id="voiceToggleBtn" onclick="toggleVoice()" title="{{ __('Voice Greeting') }}">
                <i class="ph ph-speaker-high" id="voiceIcon"></i>
            </button>

            <button class="icon-btn" onclick="toggleFullscreen()" title="Fullscreen (F11)">
                <i class="ph ph-arrows-out-simple"></i>
            </button>

            <a href="{{ route('dashboard') }}" class="icon-btn" title="Back to Dashboard">
                <i class="ph ph-x"></i>
            </a>
        </div>
    </header>

    {{-- Main Kiosk Area --}}
    <main class="kiosk-main">
        {{-- Left: Multi-Scanner Bay --}}
        <section class="scanner-panel">
            <div class="glow-blob"></div>

            {{-- Scanner Mode Switcher Tabs --}}
            <div class="scanner-tabs">
                <button class="tab-btn active" onclick="switchScanTab('camera')" id="tabCamera">
                    <i class="ph ph-camera"></i>
                    <span>{{ __('Camera Scanner') }}</span>
                </button>
                <button class="tab-btn" onclick="switchScanTab('rfid')" id="tabRfid">
                    <i class="ph ph-identification-card"></i>
                    <span>{{ __('Card Reader') }}</span>
                </button>
                <button class="tab-btn" onclick="switchScanTab('screen_qr')" id="tabScreenQr">
                    <i class="ph ph-qr-code"></i>
                    <span>{{ __('Dynamic Screen QR') }}</span>
                </button>
            </div>

            {{-- Scanner Viewport --}}
            <div class="scanner-viewport">
                {{-- 1. Camera QR Scanner --}}
                <div id="cameraViewWrap" style="width: 100%; height: 100%; position: relative;">
                    <div id="kioskQrReader"></div>
                    <div class="hud-overlay">
                        <div class="hud-reticle-box">
                            <div class="hud-corner corner-tl"></div>
                            <div class="hud-corner corner-tr"></div>
                            <div class="hud-corner corner-bl"></div>
                            <div class="hud-corner corner-br"></div>
                            <div class="hud-laser"></div>
                        </div>
                    </div>
                </div>

                {{-- 2. USB RFID Card Reader View --}}
                <div class="rfid-view" id="rfidViewWrap">
                    <div class="rfid-illustration">
                        <i class="ph ph-wave-sine"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;">សូមដាក់កាត RFID លើឧបករណ៍ស្កេន</h2>
                        <p style="color: var(--text-sub); font-size: 0.95rem;">Please tap your physical RFID badge onto the USB reader.</p>
                    </div>
                </div>

                {{-- 3. Live Rotating Screen QR View --}}
                <div class="screen-qr-view" id="screenQrViewWrap">
                    <div class="qr-canvas-box">
                        <div id="dynamicQrCanvas"></div>
                    </div>
                    <div style="max-width: 380px;">
                        <h3 style="font-size: 1.35rem; font-weight: 800; color: #fff; margin-bottom: 0.35rem;">ស្កេន QR នេះដោយទូរសព្ទដៃ</h3>
                        <p style="color: var(--cyan); font-size: 0.9rem; font-weight:600; margin-bottom:0.6rem;">Point any smartphone camera or Teacher Portal at this QR</p>
                        <div style="background:rgba(255,255,255,0.06); border:1px dashed var(--card-border); padding:0.5rem 1rem; border-radius:10px; font-size:0.82rem; color:var(--text-sub); display:flex; align-items:center; justify-content:center; gap:0.5rem;">
                            <i class="ph ph-arrows-clockwise" style="color:var(--primary);"></i>
                            <span>កូដផ្លាស់ប្តូរស្វ័យប្រវត្តរៀងរាល់ <strong id="qrCountdown" style="color:var(--primary); font-family:var(--font-mono);">20</strong> វិនាទី</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Prompt Bar --}}
            <div class="scanner-prompt">
                <div class="prompt-text">
                    <i class="ph ph-check-circle" id="scannerPromptIcon"></i>
                    <span id="scannerStatusPrompt">{{ __('Point your Teacher ID Card QR code or Dynamic QR code at the camera') }}</span>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="icon-btn" style="width: 36px; height: 36px; font-size: 1rem;" onclick="switchCamera()" title="Switch Camera">
                        <i class="ph ph-arrows-clockwise"></i>
                    </button>
                </div>
            </div>
        </section>

        {{-- Right: Live Feed & Stats --}}
        <aside class="feed-panel">
            {{-- Stat KPI Cards --}}
            <div class="stats-grid">
                <div class="stat-card present">
                    <div class="stat-label">{{ __('Present Today') }}</div>
                    <div class="stat-value" id="kioskPresentCount">{{ $presentCount }}<span style="font-size: 0.9rem; color: var(--text-sub); font-weight: 500;">/{{ $totalTeachers }}</span></div>
                </div>
                <div class="stat-card ontime">
                    <div class="stat-label">{{ __('On-Time Rate') }}</div>
                    <div class="stat-value" id="kioskRate">{{ $rate }}%</div>
                </div>
                <div class="stat-card late">
                    <div class="stat-label">{{ __('Late Count') }}</div>
                    <div class="stat-value" id="kioskLateCount">{{ $lateCount }}</div>
                </div>
            </div>

            {{-- Live Activity Stream --}}
            <div class="stream-card">
                <div class="stream-header">
                    <div class="stream-title">
                        <div class="pulse-dot" style="background: var(--primary);"></div>
                        <span>{{ __('Recent Check-Ins') }}</span>
                    </div>
                    <span style="font-size: 0.7rem; color: var(--text-sub); font-weight: 700; font-family: var(--font-mono);">LIVE FEED</span>
                </div>

                <div class="stream-list" id="kioskStreamList">
                    @forelse($recentScans as $scan)
                        @php
                            $teacher = $scan->teacher;
                            $photo = $teacher && $teacher->photo ? to_asset_url($teacher->photo) : asset('images/default-avatar.png');
                            $time = $scan->morning_in ?? $scan->afternoon_in ?? 'N/A';
                            $status = ($scan->morning_status === 'late' || $scan->afternoon_status === 'late') ? 'late' : 'ontime';
                            $statusText = $status === 'late' ? 'Late' : 'On Time';
                        @endphp
                        <div class="scan-item" data-scan-id="{{ $scan->id }}">
                            <img src="{{ $photo }}" class="item-avatar" alt="Avatar" onerror="this.src='/images/default-avatar.png';">
                            <div class="item-info">
                                <div class="item-name">{{ $teacher->name ?? 'Teacher' }}</div>
                                <div class="item-name-kh">{{ $teacher->name_kh ?? '' }}</div>
                                <div class="item-dept">{{ $teacher->department ?? 'Faculty' }}</div>
                            </div>
                            <div class="item-meta">
                                <div class="item-time">{{ $time }}</div>
                                <div class="item-badge {{ $status === 'late' ? 'badge-late' : 'badge-ontime' }}">{{ $statusText }}</div>
                            </div>
                        </div>
                    @empty
                        <div id="kioskEmptyFeed" style="text-align: center; color: var(--text-sub); padding: 3rem 1rem; font-size: 0.85rem;">
                            {{ __('No scans recorded today yet.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </aside>
    </main>

    {{-- Bottom Campus Announcement Marquee --}}
    <footer class="kiosk-footer">
        <div class="marquee-label">
            <i class="ph ph-megaphone"></i>
            <span>{{ __('Campus Announcements') }}</span>
        </div>
        <div class="marquee-content">
            <div class="marquee-track" id="kioskMarquee">
                វិទ្យាស្ថានជាតិបណ្តុះបណ្តាលបច្ចេកទេស (NTTI) — សូមស្វាគមន៍មកកាន់ប្រព័ន្ធគ្រប់គ្រងវត្តមានវៃឆ្លាត។ សូមលោកគ្រូ-អ្នកគ្រូចុះវត្តមានឱ្យបានទាន់ពេលវេលា។
            </div>
        </div>
    </footer>

    {{-- Instant Holographic Recognition HUD Modal --}}
    <div class="hud-modal" id="hudModal">
        <div class="hud-card">
            <div class="hud-avatar-frame">
                <img id="hudPhoto" src="{{ asset('images/default-avatar.png') }}" alt="Teacher">
                <div class="hud-check-badge">
                    <i class="ph ph-check-bold" id="hudCheckIcon"></i>
                </div>
            </div>

            <div>
                <div class="hud-teacher-name-kh" id="hudNameKh">ឈ្មោះគ្រូ</div>
                <div class="hud-teacher-name-en" id="hudNameEn">Teacher Name</div>
            </div>

            <div class="hud-action-pill" id="hudActionPill">
                <i class="ph ph-check-circle" id="hudActionIcon"></i>
                <span id="hudActionText">ចូលបង្រៀនជោគជ័យ (Check-In)</span>
            </div>

            <div class="hud-details-row">
                <span><i class="ph ph-clock"></i> <strong id="hudTime">08:00 AM</strong></span>
                <span><i class="ph ph-buildings"></i> <strong id="hudDept">Department</strong></span>
                <span><i class="ph ph-tag"></i> <strong id="hudMethod">QR Code</strong></span>
            </div>
        </div>
    </div>

    {{-- Audio Chimes & Synthesis Elements --}}
    <audio id="audioSuccess" preload="auto">
        <source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg">
    </audio>
    <audio id="audioError" preload="auto">
        <source src="https://assets.mixkit.co/active_storage/sfx/2874/2874-preview.mp3" type="audio/mpeg">
    </audio>

    <script>
        // ── Kiosk Configuration & Global State ──
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let voiceEnabled = localStorage.getItem('kiosk_voice') !== 'false';
        let isProcessingScan = false;
        let qrScanner = null;
        let currentFacingMode = 'environment';
        let dynamicQrInterval = null;
        let qrCountdownTimer = null;
        let qrSecondsLeft = 20;

        // ── IndexedDB Offline Buffer Engine ──
        const DB_NAME = 'NTTI_Kiosk_DB';
        const DB_VERSION = 1;
        let db = null;

        function initOfflineDB() {
            const req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = function(e) {
                const d = e.target.result;
                if (!d.objectStoreNames.contains('offline_scans')) {
                    d.createObjectStore('offline_scans', { keyPath: 'id', autoIncrement: true });
                }
            };
            req.onsuccess = function(e) {
                db = e.target.result;
                updateOfflineCount();
                if (navigator.onLine) {
                    syncOfflineScans();
                }
            };
            req.onerror = function(e) {
                console.warn('IndexedDB error:', e);
            };
        }

        function saveOfflineScan(payload) {
            if (!db) return;
            const tx = db.transaction('offline_scans', 'readwrite');
            const store = tx.objectStore('offline_scans');
            payload.scanned_at = new Date().toISOString();
            store.add(payload);
            tx.oncomplete = () => {
                updateOfflineCount();
            };
        }

        function updateOfflineCount() {
            if (!db) return;
            const tx = db.transaction('offline_scans', 'readonly');
            const store = tx.objectStore('offline_scans');
            const countReq = store.count();
            countReq.onsuccess = function() {
                const count = countReq.result;
                const pill = document.getElementById('networkStatusPill');
                const txt = document.getElementById('networkStatusText');
                if (!navigator.onLine) {
                    pill.className = 'status-pill offline';
                    txt.textContent = `OFFLINE (${count} BUFFERED)`;
                } else {
                    if (count > 0) {
                        pill.className = 'status-pill offline';
                        txt.textContent = `SYNCING (${count})...`;
                    } else {
                        pill.className = 'status-pill';
                        txt.textContent = 'ONLINE';
                    }
                }
            };
        }

        async function syncOfflineScans() {
            if (!db || !navigator.onLine) return;
            const tx = db.transaction('offline_scans', 'readonly');
            const store = tx.objectStore('offline_scans');
            const req = store.getAll();

            req.onsuccess = async function() {
                const scans = req.result;
                if (!scans || scans.length === 0) return;

                try {
                    const res = await fetch('/api/kiosk/sync-offline', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ scans: scans })
                    });
                    const json = await res.json();
                    if (json.success) {
                        const clearTx = db.transaction('offline_scans', 'readwrite');
                        clearTx.objectStore('offline_scans').clear();
                        clearTx.oncomplete = () => {
                            updateOfflineCount();
                            console.log(`[KIOSK] Successfully synced ${json.processed_count} offline scans.`);
                        };
                    }
                } catch (err) {
                    console.warn('[KIOSK] Offline sync retry error:', err);
                }
            };
        }

        window.addEventListener('online', () => {
            updateOfflineCount();
            syncOfflineScans();
        });

        window.addEventListener('offline', () => {
            updateOfflineCount();
        });

        // ── Real-Time Digital Clock & Khmer Solar Calendar ──
        function updateKioskClock() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;

            const timeStr = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')} ${ampm}`;
            document.getElementById('kioskClock').textContent = timeStr;

            const khDays = ['អាទិត្យ', 'ចន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍'];
            const khMonths = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
            const toKhmerNum = num => String(num).replace(/[0-9]/g, d => "០១២៣៤៥៦៧៨៩"[d]);

            const dayName = khDays[now.getDay()];
            const dateNum = toKhmerNum(now.getDate());
            const monthName = khMonths[now.getMonth()];
            const yearNum = toKhmerNum(now.getFullYear());

            document.getElementById('kioskDate').textContent = `ថ្ងៃ${dayName} ទី${dateNum} ខែ${monthName} ឆ្នាំ${yearNum}`;
        }
        setInterval(updateKioskClock, 1000);
        updateKioskClock();

        // ── Voice Speech Synthesis & Audio Chimes ──
        function toggleVoice() {
            voiceEnabled = !voiceEnabled;
            localStorage.setItem('kiosk_voice', voiceEnabled);
            const btn = document.getElementById('voiceToggleBtn');
            const icon = document.getElementById('voiceIcon');
            if (voiceEnabled) {
                btn.classList.add('active');
                icon.className = 'ph ph-speaker-high';
                speakText('សំឡេងស្វាគមន៍ត្រូវបានបើក', 'km');
            } else {
                btn.classList.remove('active');
                icon.className = 'ph ph-speaker-slash';
                if (window.speechSynthesis) window.speechSynthesis.cancel();
            }
        }

        function speakText(text, lang = 'km') {
            if (!voiceEnabled || !text) return;

            // Try backend TTS proxy first for high-fidelity Khmer voice
            const audio = new Audio(`/api-live/tts?lang=${encodeURIComponent(lang)}&text=${encodeURIComponent(text)}`);
            audio.play().catch(() => {
                // Fallback to browser SpeechSynthesis
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                    const utter = new SpeechSynthesisUtterance(text);
                    utter.lang = lang === 'km' ? 'km-KH' : 'en-US';
                    utter.rate = 0.95;
                    window.speechSynthesis.speak(utter);
                }
            });
        }

        function playSound(type) {
            try {
                const el = document.getElementById(type === 'success' ? 'audioSuccess' : 'audioError');
                if (el) {
                    el.currentTime = 0;
                    el.play().catch(() => {});
                }
            } catch (e) {}
        }

        // ── Fullscreen Toggle ──
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                if (document.exitFullscreen) document.exitFullscreen().catch(() => {});
            }
        }

        // ── Global RFID Card Keystroke Interceptor ──
        let rfidKeyBuffer = '';
        let rfidLastKeyTime = 0;

        window.addEventListener('keydown', function(e) {
            if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;

            const now = Date.now();
            if (now - rfidLastKeyTime > 150) {
                rfidKeyBuffer = '';
            }
            rfidLastKeyTime = now;

            if (e.key === 'Enter') {
                if (rfidKeyBuffer.length >= 4) {
                    const cardUid = rfidKeyBuffer.trim();
                    rfidKeyBuffer = '';
                    handleScanProcess({ rfid_uid: cardUid, method: 'rfid' });
                }
            } else if (e.key.length === 1) {
                rfidKeyBuffer += e.key;
            }
        });

        // ── Camera QR Code Scanner (Html5Qrcode) ──
        function initCameraScanner() {
            if (qrScanner) {
                try { qrScanner.stop(); } catch (e) {}
            }

            qrScanner = new Html5Qrcode("kioskQrReader");
            const config = {
                fps: 15,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
            };

            qrScanner.start(
                { facingMode: currentFacingMode },
                config,
                onQrCodeDetected,
                () => {}
            ).catch(err => {
                console.warn('[KIOSK CAMERA] Camera start warning:', err);
                document.getElementById('scannerStatusPrompt').textContent = '⚠️ មិនអាចបើកកាមេរ៉ាបានទេ (សូមអនុញ្ញាត Camera Permission ឬប្រើកាត RFID)';
            });
        }

        function switchCamera() {
            currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
            initCameraScanner();
        }

        function onQrCodeDetected(decodedText) {
            if (isProcessingScan || !decodedText) return;
            handleScanProcess({ qr_data: decodedText, method: 'camera' });
        }

        // ── Scanner Mode Tabs ──
        function switchScanTab(mode) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('cameraViewWrap').style.display = 'none';
            document.getElementById('rfidViewWrap').style.display = 'none';
            document.getElementById('screenQrViewWrap').style.display = 'none';

            if (mode === 'camera') {
                document.getElementById('tabCamera').classList.add('active');
                document.getElementById('cameraViewWrap').style.display = 'block';
                document.getElementById('scannerStatusPrompt').textContent = '{{ __("Point your Teacher ID Card QR code or Dynamic QR code at the camera") }}';
                initCameraScanner();
                clearInterval(dynamicQrInterval);
                clearInterval(qrCountdownTimer);
            } else if (mode === 'rfid') {
                document.getElementById('tabRfid').classList.add('active');
                document.getElementById('rfidViewWrap').style.display = 'flex';
                document.getElementById('scannerStatusPrompt').textContent = '{{ __("Or tap your physical RFID card on the reader below") }}';
                if (qrScanner) {
                    try { qrScanner.stop(); } catch(e) {}
                }
                clearInterval(dynamicQrInterval);
                clearInterval(qrCountdownTimer);
            } else if (mode === 'screen_qr') {
                document.getElementById('tabScreenQr').classList.add('active');
                document.getElementById('screenQrViewWrap').style.display = 'flex';
                document.getElementById('scannerStatusPrompt').textContent = 'ស្កេនកូដ QR លើអេក្រង់នេះដោយទូរសព្ទដៃរបស់អ្នក (Point your phone at this QR)';
                if (qrScanner) {
                    try { qrScanner.stop(); } catch(e) {}
                }
                loadDynamicScreenQr();
                dynamicQrInterval = setInterval(loadDynamicScreenQr, 20000);
            }
        }

        // ── Dynamic Rotating Screen QR Loader ──
        async function loadDynamicScreenQr() {
            try {
                const res = await fetch('/api-web/attendance/dynamic-qr-token');
                const json = await res.json();
                if (json.token) {
                    const canvasBox = document.getElementById('dynamicQrCanvas');
                    canvasBox.innerHTML = '';
                    
                    // Encode full checkin URL so standard camera apps open it directly
                    const qrPayload = json.url || json.token;
                    
                    new QRCode(canvasBox, {
                        text: qrPayload,
                        width: 200,
                        height: 200,
                        colorDark: "#020617",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.M
                    });

                    qrSecondsLeft = json.expires_in || 20;
                    startQrCountdown();
                }
            } catch(e) {
                console.warn('[KIOSK] loadDynamicScreenQr error:', e);
            }
        }

        function startQrCountdown() {
            clearInterval(qrCountdownTimer);
            const countEl = document.getElementById('qrCountdown');
            if (countEl) countEl.textContent = qrSecondsLeft;
            
            qrCountdownTimer = setInterval(() => {
                qrSecondsLeft--;
                if (countEl) countEl.textContent = Math.max(0, qrSecondsLeft);
                if (qrSecondsLeft <= 0) {
                    clearInterval(qrCountdownTimer);
                    loadDynamicScreenQr();
                }
            }, 1000);
        }

        // ── Real-Time Campus-Wide Scan Polling Engine ──
        // Listens for scans triggered from ANY device (mobile phone scanning screen QR, card, admin scan)
        let seenScanSignatures = new Set();
        let isFirstPoll = true;

        async function pollKioskLiveStream() {
            try {
                const res = await fetch('/api-live/latest');
                if (!res.ok) return;
                const data = await res.json();

                if (data.scans && data.scans.length > 0) {
                    if (isFirstPoll) {
                        // Seed seen scans from initial page load
                        data.scans.forEach(s => seenScanSignatures.add(s.id + '_' + s.updated_at));
                        isFirstPoll = false;
                        return;
                    }

                    // Identify new incoming scans
                    const newScans = data.scans.filter(s => !seenScanSignatures.has(s.id + '_' + s.updated_at));

                    if (newScans.length > 0) {
                        newScans.forEach(s => seenScanSignatures.add(s.id + '_' + s.updated_at));

                        const latest = newScans[0];
                        playSound('success');
                        triggerConfetti();

                        // Immediate Holographic Modal Popup!
                        showHudModal({
                            teacher_name: latest.teacher_name,
                            teacher_name_kh: latest.teacher_name_kh,
                            photo: latest.photo,
                            department: latest.department,
                            time: latest.time,
                            action: latest.type || 'check-in',
                            attendance_status: latest.status === 'late' ? 'late' : 'present',
                        });

                        // Prepend to Live Stream with Slide-In Animation!
                        prependStreamFeed({
                            teacher_name: latest.teacher_name,
                            teacher_name_kh: latest.teacher_name_kh,
                            photo: latest.photo,
                            department: latest.department,
                            time: latest.time,
                            action: latest.type || 'check-in',
                            attendance_status: latest.status === 'late' ? 'late' : 'present',
                        });

                        // Announce in Khmer with Voice Speech!
                        const actionKh = (latest.type === 'check-out') ? 'ចេញពីបង្រៀន' : 'ចូលបង្រៀន';
                        const nameToSpeak = latest.teacher_name_kh || latest.teacher_name;
                        speakText(`សូមស្វាគមន៍ ${nameToSpeak} ${actionKh}`, 'km');

                        // Refresh KPI Counters
                        if (data.stats) {
                            const countEl = document.getElementById('kioskPresentCount');
                            if (countEl) {
                                countEl.innerHTML = `${data.stats.present}<span style="font-size: 0.9rem; color: var(--text-sub); font-weight: 500;">/${data.stats.total}</span>`;
                            }
                            const rateEl = document.getElementById('kioskRate');
                            if (rateEl) rateEl.textContent = `${data.stats.rate}%`;
                            const lateEl = document.getElementById('kioskLateCount');
                            if (lateEl) lateEl.textContent = data.stats.late;
                        }
                    }
                }
            } catch (err) {
                // Ignore transient network errors
            }
        }
        setInterval(pollKioskLiveStream, 2000);

        // ── Local Kiosk Scan Processing (Camera / USB RFID) ──
        async function handleScanProcess(payload) {
            if (isProcessingScan) return;
            isProcessingScan = true;

            if (qrScanner) {
                try { qrScanner.pause(); } catch (e) {}
            }

            try {
                if (!navigator.onLine) {
                    saveOfflineScan(payload);
                    playSound('success');
                    showHudModal({
                        teacher_name: 'Teacher (Offline)',
                        teacher_name_kh: 'កត់ត្រាទុកក្នុងម៉ាស៊ីនរួចរាល់',
                        action: 'check-in',
                        shift: 'Current',
                        time: new Date().toLocaleTimeString(),
                        department: 'Offline Buffered',
                        method: payload.method || 'Offline',
                        photo: '/images/default-avatar.png',
                        is_offline: true
                    });
                    return;
                }

                const res = await fetch('/api/kiosk/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json();

                if (json.status === 'success' || json.status === 'info') {
                    playSound('success');
                    triggerConfetti();
                    showHudModal(json);
                    prependStreamFeed(json);
                    updateStatsCounters(json);

                    if (json.audio_text_kh) {
                        speakText(json.audio_text_kh, 'km');
                    }
                } else {
                    playSound('error');
                    showErrorNotification(json.message || 'Scan unrecognized.');
                }
            } catch (err) {
                console.warn('[KIOSK SCAN ERROR]', err);
                saveOfflineScan(payload);
                playSound('success');
                showHudModal({
                    teacher_name: 'Teacher',
                    teacher_name_kh: 'កត់ត្រាទុកក្នុងម៉ាស៊ីន (Offline)',
                    action: 'check-in',
                    time: new Date().toLocaleTimeString(),
                    department: 'Saved Locally',
                    method: 'Offline Buffer',
                    photo: '/images/default-avatar.png',
                    is_offline: true
                });
            } finally {
                setTimeout(() => {
                    isProcessingScan = false;
                    if (qrScanner) {
                        try { qrScanner.resume(); } catch (e) {}
                    }
                }, 3500);
            }
        }

        // ── Instant Recognition HUD Modal ──
        let hudTimeout = null;
        function showHudModal(data) {
            clearTimeout(hudTimeout);
            const modal = document.getElementById('hudModal');

            document.getElementById('hudPhoto').src = data.photo || '/images/default-avatar.png';
            document.getElementById('hudNameKh').textContent = data.teacher_name_kh || data.teacher_name || 'គ្រូបង្រៀន';
            document.getElementById('hudNameEn').textContent = data.teacher_name || 'Teacher';
            document.getElementById('hudTime').textContent = data.time || new Date().toLocaleTimeString();
            document.getElementById('hudDept').textContent = data.department || 'General';

            const isCheckIn = data.action === 'check-in';
            const actionPill = document.getElementById('hudActionPill');
            const actionText = document.getElementById('hudActionText');
            const actionIcon = document.getElementById('hudActionIcon');

            if (isCheckIn) {
                const isLate = data.attendance_status === 'late';
                actionPill.style.background = isLate ? 'rgba(245, 158, 11, 0.2)' : 'rgba(16, 185, 129, 0.2)';
                actionPill.style.borderColor = isLate ? 'var(--amber)' : 'var(--primary)';
                actionPill.style.color = isLate ? 'var(--amber)' : 'var(--primary)';
                actionIcon.className = isLate ? 'ph ph-warning-circle' : 'ph ph-check-circle';
                actionText.textContent = isLate ? 'ចូលបង្រៀនយឺត (Late Check-In)' : 'ចូលបង្រៀនជោគជ័យ (Check-In)';
            } else {
                actionPill.style.background = 'rgba(6, 182, 212, 0.2)';
                actionPill.style.borderColor = 'var(--cyan)';
                actionPill.style.color = 'var(--cyan)';
                actionIcon.className = 'ph ph-sign-out';
                actionText.textContent = 'ចេញពីបង្រៀន (Check-Out)';
            }

            modal.classList.add('show');

            hudTimeout = setTimeout(() => {
                modal.classList.remove('show');
            }, 3200);
        }

        function triggerConfetti() {
            if (typeof confetti === 'function') {
                confetti({
                    particleCount: 50,
                    spread: 70,
                    origin: { y: 0.6 }
                });
            }
        }

        function showErrorNotification(msg) {
            playSound('error');
            const prompt = document.getElementById('scannerStatusPrompt');
            prompt.innerHTML = `<span style="color: var(--danger); font-weight: 800;">⚠️ ${msg}</span>`;

            // Display error prominently on the HUD modal
            const modal = document.getElementById('hudModal');
            document.getElementById('hudPhoto').src = '{{ asset("images/default-avatar.png") }}';
            document.getElementById('hudNameKh').textContent = 'មិនអាចកត់ត្រាវត្តមានបានទេ';
            document.getElementById('hudNameEn').textContent = msg;
            document.getElementById('hudTime').textContent = new Date().toLocaleTimeString();
            document.getElementById('hudDept').textContent = 'System Alert';

            const actionPill = document.getElementById('hudActionPill');
            actionPill.style.background = 'rgba(239, 68, 68, 0.2)';
            actionPill.style.borderColor = 'var(--danger)';
            actionPill.style.color = 'var(--danger)';
            document.getElementById('hudActionIcon').className = 'ph ph-x-circle';
            document.getElementById('hudActionText').textContent = 'ការស្កេនបរាជ័យ (Scan Failed)';

            modal.classList.add('show');
            setTimeout(() => {
                modal.classList.remove('show');
                prompt.textContent = '{{ __("Point your Teacher ID Card QR code or Dynamic QR code at the camera") }}';
            }, 3200);
        }

        function prependStreamFeed(data) {
            const list = document.getElementById('kioskStreamList');
            const emptyNotice = document.getElementById('kioskEmptyFeed');
            if (emptyNotice) emptyNotice.remove();

            const item = document.createElement('div');
            item.className = 'scan-item highlight';

            const statusClass = data.attendance_status === 'late' ? 'badge-late' : 'badge-ontime';
            const statusLabel = data.attendance_status === 'late' ? 'Late' : (data.action === 'check-out' ? 'Out' : 'On Time');

            item.innerHTML = `
                <img src="${data.photo || '/images/default-avatar.png'}" class="item-avatar" alt="Avatar" onerror="this.src='/images/default-avatar.png';">
                <div class="item-info">
                    <div class="item-name">${data.teacher_name || 'Teacher'}</div>
                    <div class="item-name-kh">${data.teacher_name_kh || ''}</div>
                    <div class="item-dept">${data.department || 'Faculty'}</div>
                </div>
                <div class="item-meta">
                    <div class="item-time">${data.time || new Date().toLocaleTimeString()}</div>
                    <div class="item-badge ${statusClass}">${statusLabel}</div>
                </div>
            `;

            list.insertBefore(item, list.firstChild);

            setTimeout(() => {
                item.classList.remove('highlight');
            }, 4000);

            while (list.children.length > 15) {
                list.removeChild(list.lastChild);
            }
        }

        function updateStatsCounters(data) {
            if (data.action === 'check-in') {
                const countEl = document.getElementById('kioskPresentCount');
                if (countEl) {
                    const parts = countEl.textContent.split('/');
                    const curr = parseInt(parts[0]) || 0;
                    const total = parts[1] || '';
                    countEl.innerHTML = `${curr + 1}<span style="font-size: 0.9rem; color: var(--text-sub); font-weight: 500;">/${total}</span>`;
                }
                if (data.attendance_status === 'late') {
                    const lateEl = document.getElementById('kioskLateCount');
                    if (lateEl) {
                        lateEl.textContent = (parseInt(lateEl.textContent) || 0) + 1;
                    }
                }
            }
        }

        // ── Fetch Active Announcements Marquee ──
        async function loadAnnouncementsMarquee() {
            try {
                const res = await fetch('/api-web/announcements/active');
                const list = await res.json();
                if (list && list.length > 0) {
                    const texts = list.map(a => `📌 [${a.priority.toUpperCase()}] ${a.title_kh || a.title}: ${a.content_kh || a.content}`).join('   ·   ');
                    document.getElementById('kioskMarquee').textContent = texts;
                }
            } catch(e) {}
        }
        loadAnnouncementsMarquee();
        setInterval(loadAnnouncementsMarquee, 60000);

        // ── Device Heartbeat Ping ──
        function pingHeartbeat() {
            fetch('/api/device/ping', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ device_code: 'KIOSK_ENTRANCE_01', device_name: 'Main Entrance Kiosk Station' })
            }).catch(() => {});
        }
        setInterval(pingHeartbeat, 30000);
        pingHeartbeat();

        // ── Initialize on Page Load ──
        document.addEventListener('DOMContentLoaded', () => {
            initOfflineDB();
            initCameraScanner();
            if (!voiceEnabled) {
                document.getElementById('voiceToggleBtn').classList.remove('active');
                document.getElementById('voiceIcon').className = 'ph ph-speaker-slash';
            }
        });
    </script>
</body>
</html>
