<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Teacher Portal') }} | {{ __(\App\Models\Setting::getValue('university_name', 'National Technical Training Institute')) }}</title>
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&family=Inter:wght@300;400;500;600;700;800&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Phosphor Icons --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    
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
        
        html[lang="km"] body,
        html[lang="km"] button,
        html[lang="km"] input,
        html[lang="km"] select,
        html[lang="km"] textarea {
            font-family: 'Battambang', 'Inter', sans-serif !important;
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
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform: translateZ(0);
            outline: none !important;
            -webkit-tap-highlight-color: transparent;
        }
        .logo-wrapper:focus, .logo-wrapper:active {
            outline: none !important;
            border: none !important;
        }
        .logo-wrapper:hover { transform: scale(1.1) translateY(-5px); }
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
        /* Dark mode select & option styling */
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
            cursor: pointer;
        }
        [data-theme="dark"] select.form-control {
            background-color: rgba(15, 23, 42, 0.9);
            color: #f8fafc;
        }
        [data-theme="dark"] select.form-control option {
            background-color: #0f172a;
            color: #f8fafc;
        }
        [data-theme="dark"] textarea.form-control {
            background-color: rgba(15, 23, 42, 0.6);
            color: #f8fafc;
        }
        [data-theme="dark"] input[type="date"].form-control {
            background-color: rgba(15, 23, 42, 0.6);
            color: #f8fafc;
            color-scheme: dark;
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

        .btn-back {
            width: 100%;
            padding: 1.1rem;
            background: rgba(0,0,0,0.02);
            color: var(--text-sub);
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            text-decoration: none;
            box-shadow: none;
        }
        [data-theme="dark"] .btn-back {
            background: rgba(255,255,255,0.05);
        }
        .btn-back:hover {
            background: rgba(0,0,0,0.05);
            color: var(--text-main);
            transform: translateY(-2px);
        }
        [data-theme="dark"] .btn-back:hover {
            background: rgba(255,255,255,0.1);
        }

        .teacher-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .teacher-photo-wrapper { position: relative; margin-bottom: 1rem; }
        .teacher-photo { 
            width: 90px; 
            height: 90px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 4px solid var(--primary); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .teacher-meta h2 { margin: 0.5rem 0 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; }
        .teacher-meta span { font-size: 0.9rem; color: var(--text-sub); font-weight: 600; }

        .stats-summary {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        
        .progress-ring-container {
            position: relative;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .progress-ring-text {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .progress-ring-text .percent { font-size: 1.5rem; font-weight: 800; color: var(--text-main); line-height: 1; }
        .progress-ring-text .label { font-size: 0.65rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-top: 2px; }

        .stat-badges {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.75rem;
        }
        .s-badge {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(0,0,0,0.03);
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            border: 1px solid var(--border);
        }
        [data-theme="dark"] .s-badge {
            background: rgba(255,255,255,0.05);
        }
        .s-badge i { font-size: 1.1rem; }
        .s-badge .val { font-weight: 800; font-size: 1.1rem; }
        .s-badge .lab { font-size: 0.75rem; font-weight: 600; color: var(--text-sub); }

        .history-title { 
            font-weight: 800; 
            font-size: 1.1rem; 
            margin-bottom: 1.25rem; 
            display: flex; 
            align-items: center; 
            gap: 0.75rem; 
            padding-left: 0.5rem;
        }
        .history-list { display: flex; flex-direction: column; gap: 0.85rem; }
        .history-card {
            background: rgba(0,0,0,0.02);
            padding: 1.25rem;
            border-radius: 1.5rem;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }
        [data-theme="dark"] .history-card { background: rgba(255,255,255,0.05); }
        .history-card:hover { transform: scale(1.02); border-color: var(--primary); }
        [data-theme="dark"] .history-card:hover { background: rgba(255,255,255,0.08); }
        
        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

        .hist-date-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
        .hist-date { font-weight: 800; font-size: 1rem; color: var(--text-main); }
        .hist-day { font-size: 0.8rem; color: var(--text-sub); font-weight: 600; margin-left: 0.4rem; }
        
        .hist-status-pill { 
            font-size: 0.65rem; 
            font-weight: 800; 
            padding: 0.25rem 0.6rem; 
            border-radius: 0.5rem; 
            background: rgba(245, 158, 11, 0.15); 
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .session-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        .session-box { 
            background: rgba(0,0,0,0.03); 
            padding: 0.75rem; 
            border-radius: 1rem; 
            border: 1px solid var(--border);
        }
        .session-box.late { border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.05); }
        .session-box .label { display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-sub); text-transform: uppercase; margin-bottom: 2px; }
        .session-box .time { font-size: 0.95rem; font-weight: 700; color: var(--text-main); }

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

        @media (max-width: 480px) {
            .container { padding: 2rem 1rem; }
            .search-card { padding: 1.75rem; border-radius: 2rem; }
            .stats-summary { flex-direction: column; align-items: center; }
            .stat-badges { width: 100%; flex-direction: row; flex-wrap: wrap; justify-content: center; gap: 0.5rem; }
            .s-badge { flex: 1; min-width: 30%; justify-content: center; flex-direction: column; gap: 0.2rem; padding: 0.75rem 0.5rem; text-align: center; }
        }

        
        /* Announcement Bar */
        .announcement-bar {
            background: var(--primary);
            color: #000;
            padding: 0.75rem;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 800;
            position: relative;
            z-index: 100;
            overflow: hidden;
        }
        .announcement-text {
            display: inline-block;
            white-space: nowrap;
            animation: marquee 20s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999999;
            padding: 1rem;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .modal-overlay.active { display: flex; animation: fadeInModal 0.3s ease; }
        .modal-content {
            background: var(--bg);
            border: 1px solid var(--border);
            width: 100%;
            max-width: 450px;
            border-radius: 2.5rem;
            padding: 2.5rem;
            position: relative;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes fadeInModal { from { opacity: 0; } to { opacity: 1; } }
        @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .btn-modal-close {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: #000;
            border: none;
            border-radius: 1.25rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 10px 20px rgba(var(--primary-rgb), 0.2);
        }
        .btn-modal-close:hover { filter: brightness(1.1); transform: translateY(-2px); }

        /* Calendar and Shift Info Styles */
        .shift-info-card {
            background: rgba(var(--primary-rgb), 0.05);
            border: 1px solid rgba(var(--primary-rgb), 0.2);
            border-radius: 1.5rem;
            padding: 1.25rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        .shift-info-item h4 { margin: 0; font-size: 0.75rem; color: var(--text-sub); text-transform: uppercase; }
        .shift-info-item p { margin: 0.25rem 0 0; font-weight: 800; color: var(--primary); font-size: 1.1rem; }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .btn-secondary {
            padding: 1rem;
            background: var(--card);
            color: var(--text-main);
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            transition: all 0.3s;
            text-decoration: none;
        }
        .btn-secondary i { font-size: 1.5rem; color: var(--primary); }
        .btn-secondary:hover { border-color: var(--primary); transform: translateY(-2px); background: rgba(var(--primary-rgb), 0.05); }

        .calendar-wrapper { margin-bottom: 2.5rem; }
        .calendar-header { font-weight: 800; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem; font-size: 1.1rem; }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
        }
        .calendar-day-header { text-align: center; font-size: 0.7rem; font-weight: 800; color: var(--text-sub); padding-bottom: 0.5rem; text-transform: uppercase; }
        .calendar-cell {
            aspect-ratio: 1;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            position: relative;
        }
        .cal-none { background: rgba(0,0,0,0.03); color: var(--text-sub); border: 1px solid var(--border); }
        [data-theme="dark"] .cal-none { background: rgba(255,255,255,0.05); }
        .cal-future { background: transparent; color: var(--text-sub); opacity: 0.3; border: 1px dashed var(--border); }
        .cal-weekend { background: rgba(0, 0, 0, 0.05); color: var(--text-sub); opacity: 0.7; border: 1px dashed var(--border); }
        [data-theme="dark"] .cal-weekend { background: rgba(255, 255, 255, 0.05); }
        .cal-holiday { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; border: 1px dashed rgba(139, 92, 246, 0.4); }
        .cal-present { background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }
        .cal-late { background: rgba(245, 158, 11, 0.15); color: var(--warning); border: 1px solid rgba(245, 158, 11, 0.3); }
        .cal-absent { background: rgba(239, 68, 68, 0.15); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.3); }
        .cal-today::after { content: ''; position: absolute; bottom: 4px; width: 4px; height: 4px; border-radius: 50%; background: var(--primary); }

        /* New Enhancements CSS */
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
        
        .pulse-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 10px;
            height: 10px;
            background-color: var(--success);
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .floating-faq-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), #00b894);
            color: #fff;
            border: none;
            border-radius: 50%;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(var(--primary-rgb), 0.4);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 1000;
        }
        .floating-faq-btn:hover {
            transform: scale(1.15) rotate(15deg);
        }
        
        /* Input hover glow */
        .form-control:hover {
            border-color: rgba(var(--primary-rgb), 0.5);
        }
    </style>
</head>
<body>

    @php
        $announcement = app()->getLocale() == 'km' ? \App\Models\Setting::getValue('portal_announcement_km') : \App\Models\Setting::getValue('portal_announcement_en');
        $phone = \App\Models\Setting::getValue('portal_phone');
        $email = \App\Models\Setting::getValue('portal_email');
        $fb = \App\Models\Setting::getValue('portal_facebook');
        $tg = \App\Models\Setting::getValue('portal_telegram');
    @endphp

    @if($announcement)
        <div class="announcement-bar">
            <div class="announcement-text">{{ $announcement }}</div>
        </div>
    @endif

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
                $uWeb = \App\Models\Setting::getValue('university_website', '#');
                $uFb = \App\Models\Setting::getValue('university_facebook', '#');
            @endphp
            
            {{-- Top Controls Row: Weather | Lang + Theme --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div class="campus-widget">
                    <i id="weatherIcon" class="ph ph-sun-horizon" style="color: #f59e0b; transition: all 0.3s;"></i>
                    <span id="weatherText">{{ __('Phnom Penh') }} &nbsp;--°C</span>
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

            {{-- 1. PWA Install Prompt Banner --}}
            <div id="pwaInstallBanner" style="display:none; background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.15), rgba(59, 130, 246, 0.15)); border: 1px solid rgba(var(--primary-rgb), 0.3); border-radius: 1.25rem; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; justify-content: space-between; align-items: center; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i class="ph ph-device-mobile" style="font-size: 1.6rem; color: var(--primary);"></i>
                    <div style="text-align: left;">
                        <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-main);">{{ __('Install Teacher Portal App') }}</div>
                        <div style="font-size: 0.72rem; color: var(--text-sub);">{{ __('Add to home screen for instant 1-tap access') }}</div>
                    </div>
                </div>
                <button id="pwaInstallBtn" style="background: var(--primary); color: #000; border: none; padding: 0.4rem 0.9rem; border-radius: 0.75rem; font-weight: 800; font-size: 0.78rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; flex-shrink: 0;">
                    <i class="ph ph-download-simple"></i> {{ __('Install') }}
                </button>
            </div>

            {{-- Logo --}}
            @if($uLogo)
                <div class="logo-wrapper" onclick="openSchoolInfo()" title="{{ __('School Info') }}">
                    <img src="{{ $uLogo }}" class="logo-img" alt="Logo">
                </div>
            @endif

            {{-- Live Clock Hero --}}
            <div id="liveClock" style="font-size: 3rem; font-weight: 800; color: var(--primary); font-variant-numeric: tabular-nums; letter-spacing: 2px; word-spacing: 4px; line-height: 1; margin-bottom: 0.4rem;">--:--:--</div>
            <h1 id="greetingText" style="font-size: 1.4rem; margin: 0 0 0.5rem; font-weight: 700;">{{ __('Teacher Portal') }}</h1>
            <p class="subtitle" style="margin-top: 0;">{{ __('Check your attendance records instantly.') }}</p>
        </header>


        <div class="search-card">
            @if($error)
                <div class="error-msg" style="display:block; border-radius:0.5rem; padding:0.75rem; background:rgba(239,68,68,0.1); border:1px solid var(--danger);">
                    <strong>{{ __('Error:') }}</strong> {{ $error }}
                    <div style="font-size:0.75rem; margin-top:0.4rem; opacity:0.8;">{{ __('Tip: Verify your ID in the teacher list.') }}</div>
                </div>
            @endif

            {{-- Auth Controls --}}
            <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 2rem;">
                <button onclick="downloadQrCode('{{ $teacher->employee_id }}', '{{ addslashes($teacher->name) }}')" style="background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.3); color: #a855f7; padding: 0.6rem 1rem; border-radius: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph ph-qr-code"></i> {{ __('My QR Code') }}
                </button>
                <button onclick="openLeaveModal()" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #3b82f6; padding: 0.6rem 1rem; border-radius: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph ph-calendar-plus"></i> {{ __('Apply Leave') }}
                </button>
                <button onclick="openPortalFaceRegisterModal()" style="background: rgba(236, 72, 153, 0.1); border: 1px solid rgba(236, 72, 153, 0.3); color: #ec4899; padding: 0.6rem 1rem; border-radius: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                    @if($teacher->face_descriptor)
                    <i class="ph-fill ph-bounding-box"></i> {{ __('Update Face ID') }}
                    @else
                    <i class="ph ph-bounding-box"></i> {{ __('Register Face ID') }}
                    @endif
                </button>
                <button onclick="openChangePinModal()" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); padding: 0.6rem 1rem; border-radius: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph ph-key"></i> {{ __('Change PIN') }}
                </button>
                <form action="{{ route('portal.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--danger); padding: 0.6rem 1rem; border-radius: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ph ph-sign-out"></i> {{ __('Logout') }}
                    </button>
                </form>
            </div>

                    <div class="teacher-header" style="margin-bottom: 1rem;">
                        <div class="teacher-photo-wrapper" style="position: relative; cursor: pointer;" onclick="document.getElementById('portalPhotoInput').click()" title="{{ __('Change Profile Picture') }}">
                            @if($teacher->photo)
                                <img src="{{ to_asset_url($teacher->photo) }}" class="teacher-photo" id="portal-profile-img" alt="{{ $teacher->name }}">
                            @else
                                <div class="teacher-photo" style="display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; background:var(--primary); font-size:2rem;">{{ substr($teacher->name, 0, 1) }}</div>
                            @endif
                            <div style="position: absolute; bottom: 5px; right: 5px; background: var(--primary); color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.2); z-index: 10;">
                                <i class="ph ph-camera"></i>
                            </div>
                        </div>
                        <form id="portalPhotoForm" style="display: none;">
                            <input type="file" id="portalPhotoInput" accept="image/*" onchange="initPortalCropper(this)">
                        </form>
                        <div class="teacher-meta">
                            <h2 style="color:var(--primary); margin-bottom: 0.25rem;">{{ $teacher->name_kh ?: '' }}</h2>
                            <h3 style="margin:0; font-size:1.1rem; font-weight: 700; opacity:0.8;">{{ $teacher->name }}</h3>
                            @php
                                $deptObj = $departments->firstWhere('name', $teacher->department);
                                $deptLabel = $deptObj ? (app()->getLocale() == 'km' ? ($deptObj->name_kh ?: $deptObj->name) : $deptObj->name) : $teacher->department;
                            @endphp
                            <span>{{ $deptLabel }}</span>
                        </div>
                    </div> <!-- Close teacher-header -->

                    @if($todayRecord)
                        <div style="background: rgba(var(--primary-rgb), 0.05); border: 1px solid var(--primary); border-radius: 1.5rem; padding: 1.25rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <h4 style="margin: 0; font-size: 0.9rem; font-weight: 800; color: var(--primary);">{{ __("Today's Status") }}</h4>
                                <p style="margin: 0.25rem 0 0; font-size: 0.8rem; color: var(--text-main); font-weight: 600;">
                                    @if($todayRecord->morning_in)
                                        {{ __('Checked in at') }} {{ substr($todayRecord->morning_in, 0, 5) }}
                                        @if($todayRecord->morning_out) - {{ __('Checked out at') }} {{ substr($todayRecord->morning_out, 0, 5) }} @else - <span style="color: var(--success); font-weight: 700;">{{ __('On Duty') }}</span> @endif
                                    @elseif($todayRecord->afternoon_in)
                                        {{ __('Checked in at') }} {{ substr($todayRecord->afternoon_in, 0, 5) }}
                                        @if($todayRecord->afternoon_out) - {{ __('Checked out at') }} {{ substr($todayRecord->afternoon_out, 0, 5) }} @else - <span style="color: var(--success); font-weight: 700;">{{ __('On Duty') }}</span> @endif
                                    @else
                                        <span style="color: var(--warning);">{{ __('Not checked in yet') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div style="font-size: 2rem; color: var(--primary);">
                                <i class="ph ph-calendar-star"></i>
                            </div>
                        </div>
                    @else
                        <div style="background: rgba(239, 68, 68, 0.05); border: 1px dashed rgba(239, 68, 68, 0.3); border-radius: 1.5rem; padding: 1.25rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <h4 style="margin: 0; font-size: 0.9rem; font-weight: 800; color: var(--danger);">{{ __("Today's Status") }}</h4>
                                <p style="margin: 0.25rem 0 0; font-size: 0.8rem; color: var(--danger); font-weight: 600;">{{ __('Not checked in yet') }}</p>
                            </div>
                            <div style="font-size: 2rem; color: var(--danger);">
                                <i class="ph ph-warning-circle"></i>
                            </div>
                        </div>
                    @endif

                    @php
                        $total = 30;
                        $present = $stats['present'];
                        $rate = round(($present / $total) * 100);
                        $offset = 283 - (283 * $rate / 100);
                    @endphp

                    <div class="stats-summary">
                        <div class="progress-ring-container">
                            <svg width="100" height="100">
                                <circle cx="50" cy="50" r="45" fill="transparent" stroke="rgba(255,255,255,0.1)" stroke-width="8" />
                                <circle cx="50" cy="50" r="45" fill="transparent" stroke="var(--primary)" stroke-width="8" 
                                        stroke-dasharray="283" stroke-dashoffset="{{ $offset }}" stroke-linecap="round" 
                                        style="transition: stroke-dashoffset 1.5s ease-out;" />
                            </svg>
                            <div class="progress-ring-text">
                                <span class="percent">{{ $rate }}%</span>
                                <span class="label">{{ __('Rate') }}</span>
                            </div>
                        </div>
                        <div class="stat-badges">
                            <div class="s-badge">
                                <i class="ph ph-calendar-check" style="color: var(--success);"></i>
                                <div>
                                    <div class="val">{{ $stats['present'] }}</div>
                                    <div class="lab">{{ __('Present') }}</div>
                                </div>
                            </div>
                            <div class="s-badge">
                                <i class="ph ph-clock-user" style="color: var(--warning);"></i>
                                <div>
                                    <div class="val">{{ $stats['late'] }}</div>
                                    <div class="lab">{{ __('Late') }}</div>
                                </div>
                            </div>
                            <div class="s-badge">
                                <i class="ph ph-x-circle" style="color: var(--danger);"></i>
                                <div>
                                    <div class="val">{{ $stats['absent'] }}</div>
                                    <div class="lab">{{ __('Absent') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. KPI Metrics Grid (Worked Hours, Avg Arrival, Punctual Streak) --}}
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 2rem;">
                        <div style="background: rgba(0,0,0,0.02); border: 1px solid var(--border); padding: 0.85rem 0.5rem; border-radius: 1.25rem; text-align: center;">
                            @php
                                $kpiHours = floor(($totalWorkedMinutes ?? 0) / 60);
                                $kpiMins = ($totalWorkedMinutes ?? 0) % 60;
                            @endphp
                            <i class="ph ph-clock" style="font-size: 1.3rem; color: var(--primary); margin-bottom: 0.2rem; display: block;"></i>
                            <div style="font-weight: 800; font-size: 1.05rem; color: var(--text-main);">{{ $kpiHours }}h {{ $kpiMins }}m</div>
                            <div style="font-size: 0.65rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-top: 2px;">{{ __('Worked Hours') }}</div>
                        </div>
                        <div style="background: rgba(0,0,0,0.02); border: 1px solid var(--border); padding: 0.85rem 0.5rem; border-radius: 1.25rem; text-align: center;">
                            <i class="ph ph-sun-horizon" style="font-size: 1.3rem; color: #f59e0b; margin-bottom: 0.2rem; display: block;"></i>
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">{{ $avgArrivalTime ?? '—' }}</div>
                            <div style="font-size: 0.65rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-top: 2px;">{{ __('Avg Arrival') }}</div>
                        </div>
                        <div style="background: rgba(0,0,0,0.02); border: 1px solid var(--border); padding: 0.85rem 0.5rem; border-radius: 1.25rem; text-align: center;">
                            <i class="ph ph-fire" style="font-size: 1.3rem; color: #ef4444; margin-bottom: 0.2rem; display: block;"></i>
                            <div style="font-weight: 800; font-size: 1.05rem; color: var(--text-main);">{{ $onTimeStreak ?? 0 }} {{ __('Days') }}</div>
                            <div style="font-size: 0.65rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-top: 2px;">{{ __('Punctual Streak') }}</div>
                        </div>
                    </div>

                    {{-- 3. Today's Teaching Schedule Widget --}}
                    @if(isset($todaySchedules) && $todaySchedules->count() > 0)
                        <div style="background: rgba(139, 92, 246, 0.05); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 1.5rem; padding: 1.25rem; margin-bottom: 2rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                                <h4 style="margin: 0; font-size: 0.9rem; font-weight: 800; color: #8b5cf6; display: flex; align-items: center; gap: 0.4rem;">
                                    <i class="ph ph-chalkboard-teacher" style="font-size: 1.2rem;"></i> {{ __("Today's Teaching Schedule") }}
                                </h4>
                                <span style="font-size: 0.72rem; font-weight: 800; color: #8b5cf6; background: rgba(139, 92, 246, 0.15); padding: 0.2rem 0.6rem; border-radius: 0.5rem;">
                                    {{ now()->format('l') }}
                                </span>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                                @foreach($todaySchedules as $sch)
                                    <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.02); padding: 0.65rem 0.85rem; border-radius: 0.85rem; border: 1px solid var(--border);">
                                        <div>
                                            <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-main);">{{ $sch->subject_name }}</div>
                                            <div style="font-size: 0.75rem; color: var(--text-sub); display: flex; align-items: center; gap: 0.3rem; margin-top: 2px;">
                                                <i class="ph ph-door" style="color: var(--primary);"></i> {{ __('Room') }}: <strong>{{ $sch->room_number ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                        <div style="font-size: 0.82rem; font-weight: 700; color: #8b5cf6; background: rgba(139, 92, 246, 0.1); padding: 0.3rem 0.6rem; border-radius: 0.6rem;">
                                            {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="shift-info-card">
                        <div class="shift-info-item">
                            <h4>{{ __('Morning Shift') }}</h4>
                            <p>{{ \App\Models\Setting::getValue('morning_shift_start', '05:00') }} - {{ \App\Models\Setting::getValue('morning_shift_end', '12:00') }}</p>
                            <div style="font-size: 0.7rem; color: var(--warning); margin-top: 4px;">{{ __('Late after') }} {{ \App\Models\Setting::getValue('morning_late_cutoff', '07:45') }}</div>
                        </div>
                        <div style="width: 1px; background: var(--border);"></div>
                        <div class="shift-info-item">
                            <h4>{{ __('Afternoon Shift') }}</h4>
                            <p>{{ \App\Models\Setting::getValue('afternoon_shift_start', '12:00') }} - {{ \App\Models\Setting::getValue('afternoon_shift_end', '17:30') }}</p>
                            <div style="font-size: 0.7rem; color: var(--warning); margin-top: 4px;">{{ __('Late after') }} {{ \App\Models\Setting::getValue('afternoon_late_cutoff', '14:15') }}</div>
                        </div>
                    </div>

                    @php
                        $botUser = \App\Models\Setting::getValue('telegram_bot_username');
                    @endphp
                    @if($botUser && empty($teacher->telegram_chat_id))
                        <div style="background: rgba(0, 136, 204, 0.08); border: 1px solid rgba(0, 136, 204, 0.2); border-radius: 1.5rem; padding: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; text-align: left;">
                            <div style="width: 45px; height: 45px; border-radius: 50%; background: #0088cc; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.3rem; box-shadow: 0 4px 10px rgba(0, 136, 204, 0.3);">
                                <i class="ph ph-telegram-logo"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0; font-size: 0.9rem; font-weight: 800; color: var(--text-main);">{{ __('Enable Telegram Alerts') }}</h4>
                                <p style="margin: 0.2rem 0 0; font-size: 0.75rem; color: var(--text-sub); line-height: 1.4;">{{ __('Receive instant notifications on check-in & check-out.') }}</p>
                            </div>
                            <a href="https://t.me/{{ $botUser }}" target="_blank" class="btn-check" style="width: auto; padding: 0.5rem 1rem; font-size: 0.8rem; font-weight: 700; margin: 0; background: #0088cc; border-radius: 0.75rem; flex-shrink: 0; text-decoration: none; color: white; display: flex; align-items: center; gap: 0.25rem; box-shadow: 0 4px 12px rgba(0, 136, 204, 0.2);">
                                {{ __('Connect') }} <i class="ph ph-arrow-square-out"></i>
                            </a>
                        </div>
                    @elseif($botUser && !empty($teacher->telegram_chat_id))
                        <div style="background: rgba(var(--primary-rgb), 0.05); border: 1px solid rgba(var(--primary-rgb), 0.15); border-radius: 1.5rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; text-align: left;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary); color: #000; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;">
                                <i class="ph ph-bell-ringing"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0; font-size: 0.85rem; font-weight: 800; color: var(--text-main);">{{ __('Telegram Alerts Active') }}</h4>
                                <p style="margin: 0.1rem 0 0; font-size: 0.7rem; color: var(--text-sub);">{{ __('Your account is connected to @') }}{{ $botUser }}</p>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.35rem;">
                                <span style="font-size: 0.7rem; font-weight: 700; color: var(--primary); background: rgba(var(--primary-rgb), 0.1); padding: 0.2rem 0.5rem; border-radius: 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <i class="ph ph-check-circle"></i> {{ __('Active') }}
                                </span>
                                <a href="https://t.me/{{ $botUser }}" target="_blank" style="font-size: 0.7rem; font-weight: 700; color: var(--text-main); background: rgba(0,0,0,0.05); padding: 0.25rem 0.5rem; border-radius: 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem; text-decoration: none; border: 1px solid var(--border); transition: all 0.2s;">
                                    <i class="ph ph-arrows-left-right"></i> {{ __('Change') }}
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- 4. Action Buttons with Print Slip --}}
                    <div class="action-buttons" style="grid-template-columns: repeat(3, 1fr); gap: 0.6rem;">
                        <a href="{{ route('portal.export', ['employee_id' => $teacher->employee_id]) }}" class="btn-secondary" style="padding: 0.75rem 0.4rem; font-size: 0.8rem;">
                            <i class="ph ph-file-csv"></i>
                            {{ __('Export CSV') }}
                        </a>
                        <button onclick="printMonthlySlip()" class="btn-secondary" style="padding: 0.75rem 0.4rem; font-size: 0.8rem;">
                            <i class="ph ph-printer"></i>
                            {{ __('Print Slip') }}
                        </button>
                        <button onclick="openCorrectionModal()" class="btn-secondary" style="padding: 0.75rem 0.4rem; font-size: 0.8rem;">
                            <i class="ph ph-shield-warning"></i>
                            {{ __('Dispute') }}
                        </button>
                    </div>

                    <div class="calendar-wrapper">
                        <div class="calendar-header" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="ph ph-calendar-blank" style="color: var(--primary);"></i>
                                {{ $calendarLabel }}
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                @php
                                    $prevMonth = $calendarMonth - 1;
                                    $prevYear = $calendarYear;
                                    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
                                    $nextMonth = $calendarMonth + 1;
                                    $nextYear = $calendarYear;
                                    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                                @endphp
                                <a href="{{ route('portal.index', ['employee_id' => $teacher->employee_id, 'month' => $prevMonth, 'year' => $prevYear]) }}" style="background: var(--card); border: 1px solid var(--border); border-radius: 0.5rem; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; color: var(--text-main); text-decoration: none;"><i class="ph ph-caret-left"></i></a>
                                <a href="{{ route('portal.index', ['employee_id' => $teacher->employee_id, 'month' => $nextMonth, 'year' => $nextYear]) }}" style="background: var(--card); border: 1px solid var(--border); border-radius: 0.5rem; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; color: var(--text-main); text-decoration: none;"><i class="ph ph-caret-right"></i></a>
                            </div>
                        </div>
                        <div class="calendar-grid">
                            @php
                                $dayHeaders = app()->getLocale() == 'km' ? ['ច','អ','ព','ព្រ','សុ','ស','អា'] : ['Mo','Tu','We','Th','Fr','Sa','Su'];
                            @endphp
                            @foreach($dayHeaders as $d)
                                <div class="calendar-day-header">{{ $d }}</div>
                            @endforeach
                            
                            @php
                                $targetDate = \Carbon\Carbon::createFromDate($calendarYear, $calendarMonth, 1);
                                $firstDayOfMonth = $targetDate->copy()->startOfMonth();
                                $dayOfWeek = $firstDayOfMonth->dayOfWeekIso; // 1 = Monday, 7 = Sunday
                            @endphp
                            
                            {{-- Empty cells for start of month --}}
                            @for($i = 1; $i < $dayOfWeek; $i++)
                                <div class="calendar-cell" style="background:transparent; border:none;"></div>
                            @endfor

                            {{-- Actual Days --}}
                            @foreach($calendar as $calDay)
                                <div class="calendar-cell cal-{{ $calDay->status }} {{ $calDay->is_today ? 'cal-today' : '' }}" title="{{ $calDay->date }} - {{ ucfirst($calDay->status) }}">
                                    {{ $calDay->day }}
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- Calendar Legend --}}
                        <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1.5rem; font-size: 0.75rem; font-weight: 600; color: var(--text-sub); justify-content: center;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;"><span style="width:12px; height:12px; border-radius:3px; background:rgba(59, 130, 246, 0.15); border:1px solid rgba(59, 130, 246, 0.3);"></span> {{ __('Present') }}</div>
                            <div style="display: flex; align-items: center; gap: 0.4rem;"><span style="width:12px; height:12px; border-radius:3px; background:rgba(245, 158, 11, 0.15); border:1px solid rgba(245, 158, 11, 0.3);"></span> {{ __('Late') }}</div>
                            <div style="display: flex; align-items: center; gap: 0.4rem;"><span style="width:12px; height:12px; border-radius:3px; background:rgba(239, 68, 68, 0.15); border:1px solid rgba(239, 68, 68, 0.3);"></span> {{ __('Absent') }}</div>
                            <div style="display: flex; align-items: center; gap: 0.4rem;"><span style="width:12px; height:12px; border-radius:3px; background:rgba(139, 92, 246, 0.15); border:1px dashed rgba(139, 92, 246, 0.4);"></span> {{ __('Holiday') }}</div>
                        </div>
                    </div>

                    @if($upcomingHolidays->count() > 0)
                    <div style="margin-bottom: 2.5rem;">
                        <div class="history-title">
                            <i class="ph ph-confetti" style="color: #8b5cf6;"></i>
                            {{ __('Upcoming Holidays') }}
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            @foreach($upcomingHolidays as $holiday)
                                <div style="background: rgba(139, 92, 246, 0.05); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 1rem; padding: 1rem; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 800; color: var(--text-main); font-size: 0.95rem;">{{ app()->getLocale() == 'km' ? ($holiday->name_kh ?: $holiday->name) : $holiday->name }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-sub); font-weight: 600; margin-top: 2px;">{{ \Carbon\Carbon::parse($holiday->date)->format('l, F j, Y') }}</div>
                                    </div>
                                    <div style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; padding: 0.4rem 0.8rem; border-radius: 0.75rem; font-size: 0.8rem; font-weight: 800;">
                                        {{ \Carbon\Carbon::parse($holiday->date)->diffForHumans() }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @if(isset($leaveRequestsHistory) && $leaveRequestsHistory->count() > 0)
                    <div style="margin-bottom: 2.5rem;">
                        <div class="history-title">
                            <i class="ph ph-calendar-plus" style="color: #3b82f6;"></i>
                            {{ __('My Leave Request History') }}
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            @foreach($leaveRequestsHistory as $lReq)
                                <div style="background: rgba(0,0,0,0.02); border: 1px solid var(--border); border-radius: 1rem; padding: 0.85rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 800; color: var(--text-main); font-size: 0.9rem;">
                                            {{ ucfirst($lReq->leave_type) }} {{ __('Leave') }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-sub); margin-top: 2px;">
                                            {{ \Carbon\Carbon::parse($lReq->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($lReq->end_date)->format('M d, Y') }}
                                        </div>
                                    </div>
                                    <div>
                                        @if($lReq->status === 'approved')
                                            <span style="font-size: 0.7rem; font-weight: 800; background: rgba(16,185,129,0.15); color: var(--success); padding: 0.25rem 0.6rem; border-radius: 0.5rem; border: 1px solid rgba(16,185,129,0.3);">{{ __('Approved') }}</span>
                                        @elseif($lReq->status === 'rejected')
                                            <span style="font-size: 0.7rem; font-weight: 800; background: rgba(239,68,68,0.15); color: var(--danger); padding: 0.25rem 0.6rem; border-radius: 0.5rem; border: 1px solid rgba(239,68,68,0.3);">{{ __('Rejected') }}</span>
                                        @else
                                            <span style="font-size: 0.7rem; font-weight: 800; background: rgba(245,158,11,0.15); color: var(--warning); padding: 0.25rem 0.6rem; border-radius: 0.5rem; border: 1px solid rgba(245,158,11,0.3);">{{ __('Pending') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="history-title">
                        <i class="ph ph-clock-counter-clockwise" style="color: var(--primary);"></i>
                        {{ __('Recent 30 Days') }}
                    </div>

                    <div class="history-list">
                        @forelse($history as $idx => $item)
                            <div class="history-card" style="animation: slideIn 0.5s ease {{ $idx * 0.05 }}s backwards;">
                                <div class="hist-date-row">
                                    <div class="hist-date">{{ $item->date }}<span class="hist-day">({{ $item->day }})</span></div>
                                    @if($item->has_late)
                                        <span class="hist-status-pill">{{ __('LATE') }}</span>
                                    @endif
                                </div>
                                <div class="session-row">
                                    <div class="session-box {{ $item->morning_late ? 'late' : '' }}">
                                        <span class="label">{{ __('Morning') }}</span>
                                        <div class="time">{!! $item->morning !!}</div>
                                    </div>
                                    <div class="session-box {{ $item->afternoon_late ? 'late' : '' }}">
                                        <span class="label">{{ __('Afternoon') }}</span>
                                        <div class="time">{!! $item->afternoon !!}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div style="text-align:center; padding:3rem; color:var(--text-sub); border:2px dashed var(--border); border-radius:2rem; background: rgba(0,0,0,0.02);">
                                <i class="ph ph-calendar-x" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                                {{ __('No records found in last 30 days.') }}
                            </div>
                        @endforelse
                    </div>

                </div>
        </div>

        <footer style="text-align: center; margin-top: 3rem; padding-bottom: 2rem;">
            <div style="display: flex; justify-content: center; gap: 1.5rem; margin-bottom: 1.5rem;">
                @if($fb)
                    <a href="{{ $fb }}" target="_blank" style="color: var(--text-sub); font-size: 1.5rem; transition: color 0.3s;"><i class="ph ph-facebook-logo"></i></a>
                @endif
                @if($tg)
                    <a href="{{ $tg }}" target="_blank" style="color: var(--text-sub); font-size: 1.5rem; transition: color 0.3s;"><i class="ph ph-telegram-logo"></i></a>
                @endif
                @if($email)
                    <a href="mailto:{{ $email }}" style="color: var(--text-sub); font-size: 1.5rem; transition: color 0.3s;"><i class="ph ph-envelope-simple"></i></a>
                @endif
                @if($phone)
                    <a href="tel:{{ $phone }}" style="color: var(--text-sub); font-size: 1.5rem; transition: color 0.3s;"><i class="ph ph-phone"></i></a>
                @endif
            </div>
            <div style="display: flex; justify-content: center; align-items: center; gap: 0.75rem;">
                <span style="font-size: 0.7rem; font-weight: 800; background: rgba(var(--primary-rgb), 0.15); color: var(--primary); padding: 0.2rem 0.5rem; border-radius: 0.5rem; border: 1px solid rgba(var(--primary-rgb), 0.3);">IT08B2</span>
                <p style="font-size: 0.8rem; color: var(--text-sub); margin: 0;">&copy; {{ date('Y') }} {{ __(\App\Models\Setting::getValue('university_name', 'National Technical Training Institute')) }}. {{ __('All rights reserved.') }}</p>
            </div>
        </footer>
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
    </script>
</body>

<script>
    function openSchoolInfo() {
        document.getElementById('schoolInfoModal').classList.add('active');
    }
    function closeSchoolInfo() {
        document.getElementById('schoolInfoModal').classList.remove('active');
    }
</script>

{{-- Global School Info Modal --}}
<div id="schoolInfoModal" class="modal-overlay" onclick="if(event.target == this) closeSchoolInfo()">
    <div class="modal-content">
        <div style="margin-bottom: 2rem; text-align: center;">
            @if($uLogo)
                <div style="width: 100px; height: 100px; margin: 0 auto 1.5rem; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; transform: translateZ(0);">
                    <img src="{{ to_asset_url($uLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
            @endif
            <h2 style="margin: 0; font-weight: 800; color: var(--text-main); font-size: 1.6rem; line-height: 1.2;">{{ $uName }}</h2>
            <p style="color: var(--primary); font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; margin: 0.5rem 0 2rem;">{{ __('Institution Profile') }}</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2.5rem;">
            <a href="{{ $uWeb }}" target="_blank" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: rgba(var(--primary-rgb), 0.08); border: 1px solid rgba(var(--primary-rgb), 0.2); border-radius: 1.25rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='rgba(var(--primary-rgb), 0.15)';this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(var(--primary-rgb), 0.08)';this.style.transform='translateY(0)';">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: #000; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="ph ph-globe" style="font-size: 1.4rem;"></i>
                </div>
                <div style="text-align: left;">
                    <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-sub); text-transform: uppercase; letter-spacing: 1px;">{{ __('Official Website') }}</span>
                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">{{ $uWeb != '#' ? str_replace(['https://', 'http://'], '', $uWeb) : __('Not set') }}</div>
                </div>
                <i class="ph ph-arrow-square-out" style="margin-left: auto; color: var(--primary); font-size: 1.2rem;"></i>
            </a>

            <a href="{{ $uFb }}" target="_blank" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: rgba(24, 119, 242, 0.08); border: 1px solid rgba(24, 119, 242, 0.2); border-radius: 1.25rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='rgba(24, 119, 242, 0.15)';this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(24, 119, 242, 0.08)';this.style.transform='translateY(0)';">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #1877F2; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="ph ph-facebook-logo" style="font-size: 1.4rem;"></i>
                </div>
                <div style="text-align: left;">
                    <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-sub); text-transform: uppercase; letter-spacing: 1px;">{{ __('Facebook Page') }}</span>
                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">{{ $uFb != '#' ? 'Facebook' : __('Not set') }}</div>
                </div>
                <i class="ph ph-arrow-square-out" style="margin-left: auto; color: #1877F2; font-size: 1.2rem;"></i>
            </a>
        </div>

        <button class="btn-modal-close" onclick="closeSchoolInfo()">
            {{ __('Close') }}
        </button>
    </div>
</div>
<script>
    function openCorrectionModal() {
        document.getElementById('correctionModal').classList.add('active');
    }
    function closeCorrectionModal() {
        document.getElementById('correctionModal').classList.remove('active');
    }

    // FAQ Modal Logic
    function openFaqModal() {
        document.getElementById('faqModal').classList.add('active');
    }
    function closeFaqModal() {
        document.getElementById('faqModal').classList.remove('active');
    }

    // Live Clock & Greeting Logic
    function updateClock() {
        const clockEl = document.getElementById('liveClock');
        const greetingEl = document.getElementById('greetingText');
        if (!clockEl) return; // Only runs on front page

        const now = new Date();
        clockEl.textContent = now.toLocaleTimeString();

        const hour = now.getHours();
        let greeting = '{{ __("Good Evening") }}';
        if (hour >= 5 && hour < 12) greeting = '{{ __("Good Morning") }}';
        else if (hour >= 12 && hour < 17) greeting = '{{ __("Good Afternoon") }}';
        
        if (greetingEl) {
            greetingEl.textContent = greeting + ", {{ __('Teachers') }}";
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Live Weather Logic (Phnom Penh)
    async function fetchWeather() {
        try {
            const res = await fetch('https://api.open-meteo.com/v1/forecast?latitude=11.5564&longitude=104.9282&current_weather=true');
            const data = await res.json();
            const weather = data.current_weather;
            if(weather) {
                document.getElementById('weatherText').innerHTML = `{{ __('Phnom Penh') }} &nbsp;${Math.round(weather.temperature)}°C`;
                const icon = document.getElementById('weatherIcon');
                const code = weather.weathercode;
                const isDay = weather.is_day;
                
                let iconClass = 'ph-sun';
                let iconColor = '#f59e0b';
                
                if (code === 0) {
                    iconClass = isDay ? 'ph-sun' : 'ph-moon';
                    iconColor = isDay ? '#f59e0b' : '#64748b';
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
        } catch (e) {
            console.error('Weather fetch failed', e);
        }
    }
    fetchWeather();
    setInterval(fetchWeather, 30 * 60 * 1000); // Update every 30 minutes

    // Carousel Logic
    document.addEventListener("DOMContentLoaded", () => {
        const track = document.getElementById("noticeTrack");
        if (!track) return;
        
        const slides = track.querySelectorAll(".carousel-slide");
        if (slides.length <= 1) return;

        let currentIndex = 0;
        setInterval(() => {
            currentIndex = (currentIndex + 1) % slides.length;
            track.style.transform = `translateX(-${currentIndex * 100}%)`;
        }, 4000);
    });

    async function submitCorrection(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Submitting...';

        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            data.employee_id = '{{ $teacher ? $teacher->employee_id : "" }}';

            const response = await fetch('{{ route("portal.correction.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (response.ok && result.status === 'success') {
                alert(result.message);
                closeCorrectionModal();
                window.location.reload();
            } else {
                alert(`Error: ${result.message}`);
            }
        } catch (err) {
            console.error(err);
            alert('{{ __('A system error occurred.') }}');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>

{{-- Correction Request Modal --}}
<div id="correctionModal" class="modal-overlay" onclick="if(event.target == this) closeCorrectionModal()">
    <div class="modal-content" style="max-width: 500px; padding: 2rem;">
        <h3 style="margin-top:0; display:flex; align-items:center; gap:0.5rem; color:var(--text-main);">
            <i class="ph ph-shield-warning" style="color:var(--warning);"></i> 
            {{ __('Attendance Dispute') }}
        </h3>
        <p style="font-size:0.85rem; color:var(--text-sub); margin-bottom:1.5rem;">
            {{ __('Submit a request to fix missing or incorrect attendance records. Your request will be reviewed by an administrator.') }}
        </p>

        <form onsubmit="submitCorrection(event)">
            <div class="form-group">
                <label>{{ __('Date') }}</label>
                <input type="date" name="date" class="form-control" required style="padding: 0.8rem 1rem;">
            </div>
            <div class="form-group">
                <label>{{ __('Shift') }}</label>
                <select name="shift" class="form-control" required style="padding: 0.8rem 1rem;">
                    <option value="morning">{{ __('Morning Shift') }}</option>
                    <option value="afternoon">{{ __('Afternoon Shift') }}</option>
                    <option value="both">{{ __('Both Shifts (Full Day)') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label>{{ __('Reason for Correction') }}</label>
                <textarea name="reason" class="form-control" rows="3" required placeholder="{{ __('e.g., Forgot to scan out, attended a meeting...') }}" style="padding: 0.8rem 1rem; resize:none;"></textarea>
            </div>
            
            <div style="display:flex; gap:1rem; margin-top:2rem;">
                <button type="button" class="btn-secondary" style="flex:1; margin:0;" onclick="closeCorrectionModal()">{{ __('Cancel') }}</button>
                <button type="submit" class="btn-check" style="flex:2; margin:0;">{{ __('Submit Request') }}</button>
            </div>
        </form>

        @if(isset($corrections) && count($corrections) > 0)
            <div style="margin-top:2rem; border-top:1px solid var(--border); padding-top:1.5rem;">
                <h4 style="margin-top:0; font-size:0.9rem; color:var(--text-sub);">{{ __('Recent Requests') }}</h4>
                <div style="display:flex; flex-direction:column; gap:0.5rem;">
                    @foreach($corrections as $req)
                        <div style="display:flex; justify-content:space-between; font-size:0.8rem; background:rgba(0,0,0,0.02); padding:0.5rem; border-radius:0.5rem; border:1px solid var(--border);">
                            <div>
                                <strong style="color:var(--text-main);">{{ \Carbon\Carbon::parse($req->date)->format('M d') }}</strong> 
                                <span style="color:var(--text-sub);">({{ $req->shift }})</span>
                            </div>
                            <div>
                                @if($req->status === 'pending') <span style="color:var(--warning); font-weight:bold;">{{ __('Pending') }}</span>
                                @elseif($req->status === 'approved') <span style="color:var(--success); font-weight:bold;">{{ __('Approved') }}</span>
                                @else <span style="color:var(--danger); font-weight:bold;">{{ __('Rejected') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

{{-- FAQ / Help Modal --}}
<div id="faqModal" class="modal-overlay" onclick="if(event.target == this) closeFaqModal()">
    <div class="modal-content" style="max-width: 400px; padding: 2rem;">
        <h3 style="margin-top:0; display:flex; align-items:center; gap:0.5rem; color:var(--text-main);">
            <i class="ph ph-question" style="color:var(--primary);"></i> 
            {{ __('How to Use the Portal') }}
        </h3>
        <ul style="font-size:0.85rem; color:var(--text-sub); margin-bottom:1.5rem; padding-left: 1.5rem; line-height: 1.6;">
            <li style="margin-bottom: 0.5rem;"><strong>{{ __('Checking Attendance') }}</strong>: {{ __('Enter your Teacher ID in the search box to view your monthly and daily records.') }}</li>
            <li style="margin-bottom: 0.5rem;"><strong>{{ __('Forgotten ID?') }}</strong>: {{ __('Contact the HR/Admin department to retrieve your ID.') }}</li>
            <li style="margin-bottom: 0.5rem;"><strong>{{ __('Missing Records?') }}</strong>: {{ __('If you forgot to scan your card, click the "Dispute / Correct" button inside your dashboard to request a correction.') }}</li>
        </ul>
        <button class="btn-modal-close" onclick="closeFaqModal()">
            {{ __('Got It') }}
        </button>
    </div>
</div>

{{-- Floating FAQ Button --}}
<button onclick="openFaqModal()" class="floating-faq-btn">
    <i class="ph ph-question"></i>
</button>
{{-- Change PIN Modal --}}
<div id="changePinModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px; padding: 2rem;">
        <h3 style="margin-top:0; display:flex; align-items:center; gap:0.5rem; color:var(--text-main);">
            <i class="ph ph-key" style="color:var(--primary);"></i> 
            {{ __('Change Portal PIN') }}
        </h3>
        
        <form action="{{ route('portal.change-password') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>{{ __('Current PIN') }}</label>
                <input type="password" name="current_pin" class="form-control" required pattern="\d{6}" maxlength="6" inputmode="numeric">
            </div>
            <div class="form-group">
                <label>{{ __('New PIN (6 digits)') }}</label>
                <input type="password" name="new_pin" class="form-control" required pattern="\d{6}" maxlength="6" inputmode="numeric">
            </div>
            <div class="form-group">
                <label>{{ __('Confirm New PIN') }}</label>
                <input type="password" name="new_pin_confirmation" class="form-control" required pattern="\d{6}" maxlength="6" inputmode="numeric">
            </div>
            <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                <button type="button" class="btn-secondary" style="flex:1; margin:0;" onclick="closeChangePinModal()">{{ __('Cancel') }}</button>
                <button type="submit" class="btn-check" style="flex:2; margin:0;">{{ __('Save PIN') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openChangePinModal() { document.getElementById('changePinModal').classList.add('active'); }
    function closeChangePinModal() { document.getElementById('changePinModal').classList.remove('active'); }

    // Cropper JS Logic
    let portalCropper = null;

    function initPortalCropper(fileInput) {
        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('portalCropImage').src = e.target.result;
                document.getElementById('portalCropModal').classList.add('active');
                
                if (portalCropper) {
                    portalCropper.destroy();
                }

                portalCropper = new Cropper(document.getElementById('portalCropImage'), {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 0.8,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                });
            }
            reader.readAsDataURL(fileInput.files[0]);
        }
    }

    function closePortalCropper() {
        document.getElementById('portalCropModal').classList.remove('active');
        if (portalCropper) {
            portalCropper.destroy();
            portalCropper = null;
        }
        document.getElementById('portalPhotoInput').value = '';
    }

    function submitPortalCroppedImage() {
        if (!portalCropper) return;
        
        const btn = document.getElementById('portalCropSaveBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> {{ __("Saving...") }}';
        btn.disabled = true;

        portalCropper.getCroppedCanvas({
            width: 512,
            height: 512,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        }).toBlob((blob) => {
            const formData = new FormData();
            formData.append('photo', blob, 'profile.jpg');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch("{{ route('portal.change-photo') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async res => {
                if (!res.ok) {
                    const text = await res.text();
                    console.error("Server error response:", text);
                    let errMsg = "An error occurred.";
                    try {
                        const json = JSON.parse(text);
                        errMsg = json.message || errMsg;
                    } catch(e) {}
                    throw new Error(errMsg);
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Update image on page
                    const imgEl = document.getElementById('portal-profile-img');
                    if (imgEl) {
                        imgEl.src = data.photo_url;
                    } else {
                        // If there was no photo previously, the img tag might not exist. Reload to show it.
                        window.location.reload();
                    }
                    closePortalCropper();
                    // If window.showToast exists use it, else alert
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message, 'success');
                    } else {
                        if (imgEl) alert(data.message); // don't alert if we're reloading anyway
                    }
                } else {
                    alert(data.message || 'Error uploading photo.');
                }
            })
            .catch(err => {
                console.error(err);
                alert(err.message || 'An error occurred while uploading the photo.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }, 'image/jpeg', 0.9);
    }

    async function downloadQrCode(employeeId, name) {
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=${encodeURIComponent(employeeId)}`;
        try {
            const response = await fetch(qrUrl);
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `NTTI_QR_${name.replace(/\s+/g, '_')}.png`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        } catch (e) {
            // Fallback if CORS blocks the fetch
            const printWindow = window.open('', '', 'height=700,width=600');
            printWindow.document.write(`
                <html><head><title>QR Code - ${name}</title>
                <style>
                    body { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; background: #f0f4f8; }
                    .card { background: #fff; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
                    img { width: 300px; height: 300px; }
                    h2 { margin: 1.5rem 0 0.5rem; color: #0f172a; }
                    p { color: #64748b; margin-bottom: 1.5rem; }
                    .btn { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; text-decoration: none; }
                </style>
                </head><body>
                <div class="card">
                    <img src="${qrUrl}" />
                    <h2>${name}</h2>
                    <p>Right-click the image and select "Save Image As..."</p>
                    <a href="${qrUrl}" download class="btn">Open Image</a>
                </div>
                </body></html>
            `);
        }
    }
</script>

{{-- Crop Photo Modal --}}
<div id="portalCropModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px; padding: 2rem;">
        <h3 style="margin-top:0; display:flex; align-items:center; gap:0.5rem; color:var(--text-main);">
            <i class="ph ph-crop" style="color:var(--primary);"></i> 
            {{ __('Crop Profile Photo') }}
        </h3>
        
        <div style="width: 100%; max-height: 400px; background: #000; display: flex; justify-content: center; align-items: center; border-radius: 0.5rem; overflow: hidden; margin-bottom: 1.5rem;">
            <img id="portalCropImage" style="max-width: 100%; max-height: 400px; display: block;">
        </div>

        <div style="display:flex; gap:1rem;">
            <button type="button" class="btn-secondary" style="flex:1; margin:0;" onclick="closePortalCropper()">{{ __('Cancel') }}</button>
            <button type="button" class="btn-check" id="portalCropSaveBtn" style="flex:2; margin:0;" onclick="submitPortalCroppedImage()">{{ __('Save Photo') }}</button>
        </div>
    </div>
</div>
<style>
.circle-cropper .cropper-view-box,
.circle-cropper .cropper-face {
  border-radius: 50%;
}
</style>

{{-- Portal Face Register Modal --}}
<div class="modal-overlay" id="portalFaceRegisterModal">
    <div class="modal-content" style="max-width: 600px; padding: 2rem;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <h2 style="margin: 0; font-size: 1.25rem;"><i class="ph ph-bounding-box" style="margin-right: 0.5rem; color: var(--primary);"></i>{{ __('Register Face ID') }}</h2>
            <button class="modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-sub);" onclick="closePortalFaceRegisterModal()">&times;</button>
        </div>
        <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem; margin-top: 1rem;">
            <div style="position: relative; width: 100%; max-width: 480px; aspect-ratio: 4/3; background: #000; border-radius: 1rem; overflow: hidden; display: flex; justify-content: center; align-items: center;">
                <video id="portalFaceVideo" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                <canvas id="portalFaceCanvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></canvas>
                <div id="portalFaceLoading" style="position: absolute; color: white; font-weight: bold; background: rgba(0,0,0,0.5); padding: 1rem; border-radius: 0.5rem; display: none;">
                    <i class="ph ph-circle-notch animate-spin"></i> Loading AI Models...
                </div>
            </div>
            <p id="portalFaceStatus" style="font-weight: bold; color: var(--text-sub); text-align: center;">{{ __('Please position your face in the camera.') }}</p>
            <div class="form-actions" style="width: 100%; display: flex; justify-content: space-between; margin-top: 1rem;">
                <button type="button" class="btn-secondary" onclick="closePortalFaceRegisterModal()">{{ __('Cancel') }}</button>
                <button type="button" class="btn-check" id="btnSavePortalFace" disabled onclick="savePortalFaceDescriptor()">
                    <i class="ph ph-check-circle"></i> {{ __('Save Face Data') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Leave Application Modal -->
<div id="leaveModal" class="modal-overlay">
    <div class="modal-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h3 style="margin:0; font-weight:800; color:var(--text-main);"><i class="ph ph-calendar-plus" style="color:var(--primary);"></i> {{ __('Apply for Leave') }}</h3>
            <button onclick="closeLeaveModal()" style="background:none; border:none; color:var(--text-sub); font-size:1.5rem; cursor:pointer;"><i class="ph ph-x"></i></button>
        </div>
        <form id="leaveForm" onsubmit="submitLeaveForm(event)">
            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
            <div class="form-group">
                <label>{{ __('Leave Type') }}</label>
                <select name="leave_type" class="form-control" required>
                    <option value="sick">{{ __('Sick Leave') }}</option>
                    <option value="mission">{{ __('Official Mission') }}</option>
                    <option value="annual">{{ __('Annual Leave') }}</option>
                    <option value="personal">{{ __('Personal Leave') }}</option>
                </select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                <div class="form-group">
                    <label>{{ __('Start Date') }}</label>
                    <input type="date" name="start_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label>{{ __('End Date') }}</label>
                    <input type="date" name="end_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="form-group">
                <label>{{ __('Reason / Notes') }}</label>
                <textarea name="reason" class="form-control" rows="3" placeholder="{{ __('Reason for leave...') }}" required></textarea>
            </div>
            <button type="submit" class="btn-check" style="width:100%;">
                <i class="ph ph-paper-plane-right"></i> {{ __('Submit Leave Request') }}
            </button>
        </form>
    </div>
</div>

<script>
function openLeaveModal() {
    document.getElementById('leaveModal').classList.add('active');
}
function closeLeaveModal() {
    document.getElementById('leaveModal').classList.remove('active');
}
async function submitLeaveForm(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('{{ route("portal.leave.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        const res = await response.json();
        if (res.success) {
            closeLeaveModal();
            alert(res.message);
            form.reset();
        } else {
            alert(res.message || 'Error submitting leave request.');
        }
    } catch(err) {
        alert(err.message);
    }
}
</script>

<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script>
    let portalFaceStream = null;
    let portalFaceInterval = null;
    let portalFaceModelsLoaded = false;
    let portalCapturedDescriptor = null;

    async function loadPortalFaceModels() {
        if (portalFaceModelsLoaded) return true;
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            portalFaceModelsLoaded = true;
            document.getElementById('portalFaceLoading').style.display = 'none';
            return true;
        } catch (e) {
            console.error(e);
            alert('Failed to load AI models. Please check internet connection.');
            return false;
        }
    }

    async function openPortalFaceRegisterModal() {
        document.getElementById('portalFaceStatus').innerText = 'Please position your face in the camera.';
        document.getElementById('portalFaceStatus').style.color = 'var(--text-sub)';
        document.getElementById('btnSavePortalFace').disabled = true;
        portalCapturedDescriptor = null;
        
        document.getElementById('portalFaceRegisterModal').classList.add('active');
        document.getElementById('portalFaceLoading').style.display = 'block';
        
        const loaded = await loadPortalFaceModels();
        if (!loaded) return;
        
        startPortalFaceVideo();
    }

    function closePortalFaceRegisterModal() {
        document.getElementById('portalFaceRegisterModal').classList.remove('active');
        if (portalFaceStream) {
            portalFaceStream.getTracks().forEach(track => track.stop());
            portalFaceStream = null;
        }
        if (portalFaceInterval) {
            clearInterval(portalFaceInterval);
            portalFaceInterval = null;
        }
        const canvas = document.getElementById('portalFaceCanvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    async function startPortalFaceVideo() {
        const video = document.getElementById('portalFaceVideo');
        try {
            portalFaceStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
            video.srcObject = portalFaceStream;
        } catch (err) {
            console.error(err);
            alert("Camera access denied or unavailable.");
            return;
        }

        video.onloadedmetadata = () => {
            const canvas = document.getElementById('portalFaceCanvas');
            const displaySize = { width: video.videoWidth || 480, height: video.videoHeight || 360 };
            faceapi.matchDimensions(canvas, displaySize);
            
            portalFaceInterval = setInterval(async () => {
                if (!portalFaceStream) return;
                const detections = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                if (detections) {
                    const resizedDetections = faceapi.resizeResults(detections, displaySize);
                    faceapi.draw.drawDetections(canvas, resizedDetections);
                    
                    if (detections.detection.score > 0.8) {
                        document.getElementById('portalFaceStatus').innerText = 'Face detected securely! You can save now.';
                        document.getElementById('portalFaceStatus').style.color = 'var(--success)';
                        document.getElementById('btnSavePortalFace').disabled = false;
                        portalCapturedDescriptor = Array.from(detections.descriptor);
                    }
                } else {
                    document.getElementById('portalFaceStatus').innerText = 'No face detected. Please look at the camera.';
                    document.getElementById('portalFaceStatus').style.color = 'var(--warning)';
                    document.getElementById('btnSavePortalFace').disabled = true;
                }
            }, 500);
        };
    }

    async function savePortalFaceDescriptor() {
        if (!portalCapturedDescriptor) return;
        
        const btn = document.getElementById('btnSavePortalFace');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> {{ __("Saving...") }}';
        btn.disabled = true;
        
        try {
            const response = await fetch('{{ route("portal.change-face") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ face_descriptor: JSON.stringify(portalCapturedDescriptor) })
            });
            
            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                console.error("Server Error:", errData);
                alert(errData.message || "Server returned error: " + response.status);
                return;
            }
            
            const res = await response.json();
            if (res.status === 'success') {
                closePortalFaceRegisterModal();
                alert('{{ __("Face registered successfully!") }}');
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert(res.message);
            }
        } catch (e) {
            console.error(e);
            alert('An error occurred while saving face data: ' + e.message);
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    // PWA Install Prompt Listener
    let deferredPwaPrompt = null;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPwaPrompt = e;
        const banner = document.getElementById('pwaInstallBanner');
        if (banner) banner.style.display = 'flex';
    });

    const pwaBtn = document.getElementById('pwaInstallBtn');
    if (pwaBtn) {
        pwaBtn.addEventListener('click', async () => {
            if (deferredPwaPrompt) {
                deferredPwaPrompt.prompt();
                const choice = await deferredPwaPrompt.userChoice;
                if (choice.outcome === 'accepted') {
                    document.getElementById('pwaInstallBanner').style.display = 'none';
                }
                deferredPwaPrompt = null;
            }
        });
    }

    // Print Monthly Attendance Slip Function
    function printMonthlySlip() {
        const uName = @json(\App\Models\Setting::getValue('university_name', 'National Technical Training Institute'));
        const uLogo = @json(\App\Models\Setting::getAssetUrl('university_logo', '/images/ntti_logo.png'));
        const teacherName = @json($teacher->name_kh ? ($teacher->name_kh . " (" . $teacher->name . ")") : $teacher->name);
        const employeeId = @json($teacher->employee_id);
        const dept = @json($teacher->department);
        const monthLabel = @json($calendarLabel);
        const presentCount = @json($stats['present']);
        const lateCount = @json($stats['late']);
        const absentCount = @json($stats['absent']);
        const workedHours = @json(floor(($totalWorkedMinutes ?? 0) / 60) . "h " . (($totalWorkedMinutes ?? 0) % 60) . "m");

        const historyData = @json($history);

        let tableRows = '';
        historyData.forEach((item, index) => {
            tableRows += `
                <tr>
                    <td style="text-align: center; border: 1px solid #ddd; padding: 6px;">${index + 1}</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">${item.date} (${item.day})</td>
                    <td style="text-align: center; border: 1px solid #ddd; padding: 6px;">${item.morning}</td>
                    <td style="text-align: center; border: 1px solid #ddd; padding: 6px;">${item.afternoon}</td>
                    <td style="text-align: center; border: 1px solid #ddd; padding: 6px; font-weight: bold; color: ${item.has_late ? '#d97706' : '#059669'};">
                        ${item.has_late ? 'LATE' : 'REGULAR'}
                    </td>
                </tr>
            `;
        });

        const printWin = window.open('', '', 'width=850,height=900');
        printWin.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Attendance Slip - ${employeeId}</title>
                <style>
                    body { font-family: 'Kantumruy Pro', 'Inter', sans-serif; padding: 30px; color: #1e293b; max-width: 800px; margin: 0 auto; }
                    .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
                    .logo { height: 70px; margin-bottom: 10px; }
                    h2 { margin: 0; color: #1e3a8a; font-size: 20px; }
                    h4 { margin: 5px 0 0; color: #64748b; font-size: 14px; font-weight: normal; }
                    .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
                    .meta-item { font-size: 13px; }
                    .meta-item strong { color: #0f172a; }
                    .kpi-row { display: flex; justify-content: space-around; background: #eff6ff; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bfdbfe; font-size: 13px; text-align: center; }
                    .kpi-val { font-weight: bold; font-size: 16px; color: #1d4ed8; }
                    table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 30px; }
                    th { background: #2563eb; color: white; border: 1px solid #1d4ed8; padding: 8px; }
                    .footer-sig { display: flex; justify-content: space-between; margin-top: 50px; text-align: center; font-size: 12px; }
                    .sig-box { width: 200px; border-top: 1px dashed #94a3b8; padding-top: 5px; }
                    @media print {
                        body { padding: 0; }
                        button { display: none; }
                    }
                </style>
            </head>
            <body>
                <div style="text-align: right; margin-bottom: 10px;">
                    <button onclick="window.print()" style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer;">🖨️ Print / Save PDF</button>
                </div>
                <div class="header">
                    ${uLogo ? `<img src="${uLogo}" class="logo" />` : ''}
                    <h2>${uName}</h2>
                    <h4>Monthly Teacher Attendance Statement — <strong>${monthLabel}</strong></h4>
                </div>

                <div class="meta-grid">
                    <div class="meta-item"><strong>Teacher Name:</strong> ${teacherName}</div>
                    <div class="meta-item"><strong>Teacher ID:</strong> ${employeeId}</div>
                    <div class="meta-item"><strong>Department:</strong> ${dept}</div>
                    <div class="meta-item"><strong>Issued Date:</strong> ${new Date().toLocaleDateString()}</div>
                </div>

                <div class="kpi-row">
                    <div><div>Present Days</div><div class="kpi-val">${presentCount}</div></div>
                    <div><div>Late Days</div><div class="kpi-val" style="color:#d97706;">${lateCount}</div></div>
                    <div><div>Absent Days</div><div class="kpi-val" style="color:#dc2626;">${absentCount}</div></div>
                    <div><div>Total Worked</div><div class="kpi-val">${workedHours}</div></div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Date</th>
                            <th>Morning Shift</th>
                            <th>Afternoon Shift</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableRows}
                    </tbody>
                </table>

                <div class="footer-sig">
                    <div>
                        <br/><br/>
                        <div class="sig-box">Teacher Signature</div>
                    </div>
                    <div>
                        <br/><br/>
                        <div class="sig-box">Academic Director / HR</div>
                    </div>
                </div>
            </body>
            </html>
        `);
        printWin.document.close();
    }
</script>
</html>
