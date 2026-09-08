<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#020617">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    
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

        /* ── Date Inputs Mobile Responsive Fix ── */
        input[type="date"].form-control, select.form-control, textarea.form-control {
            padding-left: 1rem !important;
        }
        .date-range-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        @media (max-width: 576px) {
            .date-range-grid {
                grid-template-columns: 1fr !important;
                gap: 0.5rem;
            }
        }
        .date-input {
            padding-left: 1rem !important;
            padding-right: 0.75rem !important;
            font-size: 0.9rem !important;
            width: 100% !important;
            box-sizing: border-box !important;
            height: 48px;
        }

        /* ── Action Tile Cards ── */
        .action-tile-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.1rem 0.65rem;
            border-radius: 1.25rem;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            outline: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border);
            backdrop-filter: blur(10px);
        }
        .action-tile-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }
        .action-tile-icon {
            width: 44px;
            height: 44px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 0.6rem;
            transition: transform 0.2s ease;
        }
        .action-tile-btn:hover .action-tile-icon {
            transform: scale(1.1);
        }
        .action-tile-label {
            font-weight: 800;
            font-size: 0.85rem;
            margin-bottom: 2px;
            text-align: center;
            line-height: 1.2;
        }
        .action-tile-sub {
            font-size: 0.7rem;
            color: var(--text-sub);
            font-weight: 500;
            text-align: center;
        }

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

        /* ── Action Grid Default ── */
        .portal-quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
            gap: 0.85rem;
        }

        /* ── Smartphone Native App Responsive Architecture (<= 576px) ── */
        @media (max-width: 576px) {
            body {
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
            .container {
                padding: max(0.75rem, env(safe-area-inset-top)) 0.75rem max(2rem, env(safe-area-inset-bottom)) 0.75rem !important;
                max-width: 100% !important;
            }
            header {
                margin-bottom: 1.25rem !important;
            }
            .campus-widget {
                padding: 0.35rem 0.75rem !important;
                font-size: 0.75rem !important;
            }
            .logo-wrapper {
                width: 62px !important;
                height: 62px !important;
                margin-bottom: 0.75rem !important;
            }
            #liveClock {
                font-size: 2.2rem !important;
                margin-bottom: 0.25rem !important;
                letter-spacing: 1px !important;
            }
            h1#greetingText {
                font-size: 1.2rem !important;
                margin-bottom: 0.25rem !important;
            }
            p.subtitle {
                font-size: 0.82rem !important;
            }
            .search-card {
                padding: 1.25rem 0.85rem !important;
                border-radius: 1.6rem !important;
                box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
            }
            .teacher-header {
                margin-bottom: 1.15rem !important;
            }
            .teacher-photo {
                width: 76px !important;
                height: 76px !important;
                border-width: 3px !important;
            }
            .teacher-meta h2 {
                font-size: 1.25rem !important;
                margin-bottom: 0.15rem !important;
            }
            .teacher-meta h3 {
                font-size: 0.98rem !important;
            }
            
            /* Grid Actions 2-Column Mobile App Touch Layout */
            .portal-quick-actions-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.55rem !important;
            }
            .portal-quick-actions-grid form {
                grid-column: span 2 !important;
            }
            .action-tile-btn {
                padding: 0.85rem 0.45rem !important;
                border-radius: 1.1rem !important;
                touch-action: manipulation;
                -webkit-tap-highlight-color: transparent;
            }
            .action-tile-btn:active {
                transform: scale(0.96) !important;
                filter: brightness(0.95);
            }
            .action-tile-icon {
                width: 40px !important;
                height: 40px !important;
                border-radius: 0.85rem !important;
                font-size: 1.25rem !important;
                margin-bottom: 0.4rem !important;
            }
            .action-tile-label {
                font-size: 0.8rem !important;
                line-height: 1.15 !important;
            }
            .action-tile-sub {
                font-size: 0.65rem !important;
            }

            /* Modal Bottom-Sheet Slide on Phones */
            .modal-overlay {
                align-items: flex-end !important;
                padding: 0 !important;
                backdrop-filter: blur(12px) !important;
                -webkit-backdrop-filter: blur(12px) !important;
            }
            .modal-content {
                max-width: 100% !important;
                width: 100% !important;
                border-radius: 2rem 2rem 0 0 !important;
                padding: 1.5rem 1.25rem max(1.75rem, env(safe-area-inset-bottom)) 1.25rem !important;
                max-height: 90vh !important;
                overflow-y: auto !important;
                box-shadow: 0 -10px 40px rgba(0,0,0,0.5) !important;
                animation: mobileSheetUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }
            @keyframes mobileSheetUp {
                from { transform: translateY(100%); opacity: 0.8; }
                to   { transform: translateY(0); opacity: 1; }
            }

            /* Action Buttons (CSV, Print, Dispute) */
            .action-buttons {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 0.45rem !important;
                margin-bottom: 1.75rem !important;
            }
            .btn-secondary {
                padding: 0.65rem 0.35rem !important;
                font-size: 0.75rem !important;
                border-radius: 0.9rem !important;
            }
            .btn-secondary i {
                font-size: 1.25rem !important;
            }

            /* Shift Info & History */
            .shift-info-card {
                padding: 1rem 0.75rem !important;
                border-radius: 1.15rem !important;
                margin-bottom: 1.5rem !important;
            }
            .shift-info-item h4 {
                font-size: 0.7rem !important;
            }
            .shift-info-item p {
                font-size: 0.95rem !important;
            }
            .history-card {
                padding: 1rem !important;
                border-radius: 1.15rem !important;
            }
            .hist-date {
                font-size: 0.9rem !important;
            }
            .session-box {
                padding: 0.65rem 0.5rem !important;
                border-radius: 0.75rem !important;
            }
            .session-box .time {
                font-size: 0.85rem !important;
            }

            /* Calendar Grid on Small Phones */
            .calendar-grid {
                gap: 3px !important;
            }
            .calendar-cell {
                font-size: 0.78rem !important;
                border-radius: 0.5rem !important;
            }
        }

        /* ── 3D Digital Teacher ID Card ── */
        .id-card-scene {
            /* perspective must be on parent, NOT on the transforming element */
            perspective: 1200px;
            width: 100%;
            max-width: 340px;
            /* Fixed height: prevents jump when card is clicked */
            height: 460px;
            margin: 0 auto;
            cursor: pointer;
            /* Don't let children change the scene's layout footprint */
            position: relative;
            flex-shrink: 0;
        }
        .id-card-3d {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0; left: 0;
            transition: transform 0.7s cubic-bezier(0.4, 0.2, 0.2, 1);
            transform-style: preserve-3d;
            border-radius: 1.5rem;
            box-shadow: 0 20px 50px rgba(0,0,0,0.35);
            will-change: transform;
        }
        .id-card-3d.flipped {
            transform: rotateY(180deg);
        }
        .id-card-face {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            border-radius: 1.5rem;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }
        .id-card-front {
            background: linear-gradient(135deg, #0b1329 0%, #1e293b 60%, #0f172a 100%);
            color: #fff;
            border: 2px solid rgba(255,255,255,0.15);
            padding: 1.25rem;
            position: relative;
        }
        .id-card-back {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #fff;
            transform: rotateY(180deg);
            border: 2px solid rgba(255,255,255,0.15);
            padding: 1.5rem;
            justify-content: space-between;
        }
        .id-card-sheen {
            position: absolute;
            inset: 0;
            background: linear-gradient(115deg, transparent 35%, rgba(255,255,255,0.12) 45%, rgba(var(--primary-rgb), 0.25) 50%, transparent 60%);
            pointer-events: none;
            animation: idCardSheen 5s infinite;
        }
        @keyframes idCardSheen {
            0%   { transform: translateX(-150%); }
            40%  { transform: translateX(150%); }
            100% { transform: translateX(150%); }
        }

        /* ── Personal Notification Drawer ── */
        .notif-drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 999999;
            display: none;
            justify-content: flex-end;
        }
        .notif-drawer-overlay.active {
            display: flex;
            animation: fadeInModal 0.25s ease;
        }
        .notif-drawer {
            background: var(--bg);
            width: 100%;
            max-width: 410px;
            height: 100%;
            box-shadow: -10px 0 40px rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            animation: slideNotifDrawer 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border-left: 1px solid var(--border);
        }
        @keyframes slideNotifDrawer {
            from { transform: translateX(100%); }
            to   { transform: translateX(0); }
        }

        /* ── Timetable Day Tabs ── */
        .tt-day-tabs {
            display: flex;
            gap: 0.4rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            margin-bottom: 1.25rem;
            scrollbar-width: none;
        }
        .tt-day-tabs::-webkit-scrollbar { display: none; }
        .tt-day-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text-sub);
            font-weight: 700;
            font-size: 0.8rem;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .tt-day-btn.active {
            background: var(--primary);
            color: #000;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.25);
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
                    <button id="portalNotifBtn" onclick="toggleNotificationDrawer()" title="{{ __('Notifications') }}"
                            style="position: relative; background: var(--card); border: 1px solid var(--border); backdrop-filter: blur(10px); color: var(--text-sub); width: 34px; height: 34px; border-radius: 0.75rem; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; transition: all 0.2s; flex-shrink: 0;"
                            onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)';"
                            onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-sub)';">
                        <i class="ph ph-bell"></i>
                        @if(isset($personalNotifications) && $personalNotifications->count() > 0)
                            <span id="notifBadge" style="position: absolute; top: -3px; right: -3px; background: var(--danger); color: #fff; font-size: 0.62rem; font-weight: 800; min-width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid var(--bg); line-height: 1;">
                                {{ $personalNotifications->count() }}
                            </span>
                        @endif
                    </button>
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

            {{-- 1. Teacher Header Profile Card --}}
            <div class="teacher-header" style="margin-bottom: 1.5rem;">
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
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.25rem;">
                        <span class="badge" style="background: rgba(var(--primary-rgb), 0.12); color: var(--primary); font-size: 0.78rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 0.5rem;">
                            <i class="ph ph-buildings" style="margin-right: 0.2rem;"></i> {{ $deptLabel }}
                        </span>
                        <span style="font-size: 0.78rem; color: var(--text-sub); font-weight: 600;">
                            ID: <strong>{{ $teacher->employee_id }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            {{-- 2. Today's Attendance Status Banner --}}
            @if($todayRecord)
                <div style="background: rgba(var(--primary-rgb), 0.05); border: 1px solid var(--primary); border-radius: 1.25rem; padding: 1.1rem 1.25rem; margin-bottom: 1.75rem; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0; font-size: 0.88rem; font-weight: 800; color: var(--primary);">{{ __("Today's Status") }}</h4>
                        <p style="margin: 0.25rem 0 0; font-size: 0.82rem; color: var(--text-main); font-weight: 600;">
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
                    <div style="font-size: 1.8rem; color: var(--primary);">
                        <i class="ph ph-calendar-star"></i>
                    </div>
                </div>
            @else
                <div style="background: rgba(239, 68, 68, 0.05); border: 1px dashed rgba(239, 68, 68, 0.3); border-radius: 1.25rem; padding: 1.1rem 1.25rem; margin-bottom: 1.75rem; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0; font-size: 0.88rem; font-weight: 800; color: var(--danger);">{{ __("Today's Status") }}</h4>
                        <p style="margin: 0.25rem 0 0; font-size: 0.82rem; color: var(--danger); font-weight: 600;">{{ __('Not checked in yet') }}</p>
                    </div>
                    <div style="font-size: 1.8rem; color: var(--danger);">
                        <i class="ph ph-warning-circle"></i>
                    </div>
                </div>
            @endif

            {{-- 3. Quick Actions Grid --}}
            <div style="margin-bottom: 2.25rem;">
                <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--text-sub); margin-bottom: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="ph ph-squares-four" style="color: var(--primary);"></i>
                    {{ __('Quick Actions & Services') }}
                </div>
                
                <div class="portal-quick-actions-grid">
                    {{-- 1. Mobile GPS Check-In --}}
                    <button onclick="triggerGpsCheckin()" class="action-tile-btn" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); color: var(--text-main);">
                        <div class="action-tile-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                            <i class="ph ph-map-pin"></i>
                        </div>
                        <div class="action-tile-label">{{ __('GPS Check-In') }}</div>
                        <div class="action-tile-sub">{{ __('Location Check') }}</div>
                    </button>

                    {{-- 2. Scan Screen Live QR --}}
                    <button onclick="openDynamicQrScannerModal()" class="action-tile-btn" style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.25); color: var(--text-main);">
                        <div class="action-tile-icon" style="background: rgba(14, 165, 233, 0.15); color: #0ea5e9;">
                            <i class="ph ph-qr-code"></i>
                        </div>
                        <div class="action-tile-label">{{ __('Scan Screen QR') }}</div>
                        <div class="action-tile-sub">{{ __('Live Anti-Proxy') }}</div>
                    </button>

                    {{-- 3. Digital Teacher ID Card (3D Flip Card) --}}
                    <button onclick="openDigitalIdModal()" class="action-tile-btn" style="background: rgba(234, 179, 8, 0.08); border: 1px solid rgba(234, 179, 8, 0.25); color: var(--text-main);">
                        <div class="action-tile-icon" style="background: rgba(234, 179, 8, 0.15); color: #eab308;">
                            <i class="ph ph-identification-card"></i>
                        </div>
                        <div class="action-tile-label">{{ __('Digital ID Card') }}</div>
                        <div class="action-tile-sub">{{ __('3D Smart Badge') }}</div>
                    </button>

                    {{-- 4. Full Weekly Timetable --}}
                    <button onclick="openWeeklyTimetableModal()" class="action-tile-btn" style="background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.25); color: var(--text-main);">
                        <div class="action-tile-icon" style="background: rgba(99, 102, 241, 0.15); color: #6366f1;">
                            <i class="ph ph-calendar"></i>
                        </div>
                        <div class="action-tile-label">{{ __('Weekly Schedule') }}</div>
                        <div class="action-tile-sub">{{ __('Mon-Sat Classes') }}</div>
                    </button>

                    {{-- 5. Class Swap & Substitute Request --}}
                    <button onclick="openSubstituteModal()" class="action-tile-btn" style="background: rgba(20, 184, 166, 0.08); border: 1px solid rgba(20, 184, 166, 0.25); color: var(--text-main);">
                        <div class="action-tile-icon" style="background: rgba(20, 184, 166, 0.15); color: #14b8a6;">
                            <i class="ph ph-arrows-left-right"></i>
                        </div>
                        <div class="action-tile-label">{{ __('Class Substitute') }}</div>
                        <div class="action-tile-sub">{{ __('Faculty Swap') }}</div>
                    </button>

                    {{-- 6. Apply Leave --}}
                    <button onclick="openLeaveModal()" class="action-tile-btn" style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.25); color: var(--text-main);">
                        <div class="action-tile-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                            <i class="ph ph-calendar-plus"></i>
                        </div>
                        <div class="action-tile-label">{{ __('Apply Leave') }}</div>
                        <div class="action-tile-sub">{{ __('Absence Request') }}</div>
                    </button>

                    {{-- 7. My QR Code --}}
                    <button onclick="downloadQrCode('{{ $teacher->employee_id }}', '{{ addslashes($teacher->name) }}')" class="action-tile-btn" style="background: rgba(168, 85, 247, 0.08); border: 1px solid rgba(168, 85, 247, 0.25); color: var(--text-main);">
                        <div class="action-tile-icon" style="background: rgba(168, 85, 247, 0.15); color: #a855f7;">
                            <i class="ph ph-qr-code"></i>
                        </div>
                        <div class="action-tile-label">{{ __('My QR Code') }}</div>
                        <div class="action-tile-sub">{{ __('Digital ID Pass') }}</div>
                    </button>

                    {{-- 8. Register/Update Face ID --}}
                    <button onclick="openPortalFaceRegisterModal()" class="action-tile-btn" style="background: rgba(236, 72, 153, 0.08); border: 1px solid rgba(236, 72, 153, 0.25); color: var(--text-main);">
                        <div class="action-tile-icon" style="background: rgba(236, 72, 153, 0.15); color: #ec4899;">
                            @if($teacher->face_descriptor)
                                <i class="ph-fill ph-bounding-box"></i>
                            @else
                                <i class="ph ph-bounding-box"></i>
                            @endif
                        </div>
                        <div class="action-tile-label">
                            {{ $teacher->face_descriptor ? __('Update Face ID') : __('Register Face ID') }}
                        </div>
                        <div class="action-tile-sub">{{ __('Biometric Profile') }}</div>
                    </button>

                    {{-- 9. Biometric 1-Tap Unlock --}}
                    <button onclick="openBiometricSetupModal()" class="action-tile-btn" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); color: var(--text-main);">
                        <div class="action-tile-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                            <i class="ph ph-fingerprint"></i>
                        </div>
                        <div class="action-tile-label">{{ __('Biometric Login') }}</div>
                        <div class="action-tile-sub">{{ __('Touch/Face Unlock') }}</div>
                    </button>

                    {{-- 10. Change PIN --}}
                    <button onclick="openChangePinModal()" class="action-tile-btn" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); color: var(--text-main);">
                        <div class="action-tile-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                            <i class="ph ph-key"></i>
                        </div>
                        <div class="action-tile-label">{{ __('Change PIN') }}</div>
                        <div class="action-tile-sub">{{ __('Portal Security') }}</div>
                    </button>

                    {{-- 11. Logout --}}
                    <form action="{{ route('portal.logout') }}" method="POST" style="margin: 0; grid-column: span 2;">
                        @csrf
                        <button type="submit" class="action-tile-btn" style="width: 100%; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: var(--text-main);">
                            <div class="action-tile-icon" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                                <i class="ph ph-sign-out"></i>
                            </div>
                            <div class="action-tile-label" style="color: #ef4444;">{{ __('Logout') }}</div>
                            <div class="action-tile-sub">{{ __('End Session') }}</div>
                        </button>
                    </form>
                </div>
            </div>

            {{-- 4. Official Announcements Section --}}
            @if(isset($portalAnnouncements) && $portalAnnouncements->count() > 0)
                <div style="margin-bottom: 2rem;">
                    <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--text-sub); margin-bottom: 0.85rem; display: flex; align-items: center; justify-content: space-between;">
                        <span style="display: flex; align-items: center; gap: 0.4rem;">
                            <i class="ph ph-megaphone" style="color: var(--primary);"></i>
                            {{ __('Official Announcements') }}
                        </span>
                        <span class="badge" style="background: rgba(var(--primary-rgb), 0.15); color: var(--primary); font-size: 0.7rem; padding: 0.2rem 0.55rem; border-radius: 0.5rem; font-weight: 800;">
                            {{ $portalAnnouncements->count() }}
                        </span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                        @foreach($portalAnnouncements as $ann)
                            @php
                                $pBadge = 'background: rgba(59, 130, 246, 0.12); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3);';
                                if ($ann->priority === 'urgent') {
                                    $pBadge = 'background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);';
                                } elseif ($ann->priority === 'warning') {
                                    $pBadge = 'background: rgba(245, 158, 11, 0.12); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);';
                                }
                            @endphp
                            <div style="background: var(--card); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.25rem; backdrop-filter: blur(10px); transition: transform 0.2s, box-shadow 0.2s;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="{{ $pBadge }} font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <i class="ph ph-warning-circle" style="margin-right: 0.2rem;"></i>{{ strtoupper($ann->priority) }}
                                    </span>
                                    <span style="font-size: 0.75rem; color: var(--text-sub); display: flex; align-items: center; gap: 0.3rem;">
                                        <i class="ph ph-clock"></i> {{ $ann->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.25rem 0; line-height: 1.3;">
                                    {{ app()->getLocale() === 'km' && $ann->title_kh ? $ann->title_kh : $ann->title }}
                                </h4>

                                <p style="font-size: 0.88rem; color: var(--text-sub); margin: 0; line-height: 1.5; font-family: 'Battambang', sans-serif;">
                                    {{ app()->getLocale() === 'km' && $ann->content_kh ? $ann->content_kh : $ann->content }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

                    @php
                        $total = 30;
                        $present = $stats['present'];
                        $rate = round(($present / $total) * 100);
                        $offset = 283 - (283 * $rate / 100);
                        $kpiHoursDecimal = number_format(($totalWorkedMinutes ?? 0) / 60, 1);
                    @endphp

                    {{-- ── Ultra-Premium Attendance Performance & Health Dashboard Card ── --}}
                    <div style="background: var(--card); border: 1px solid var(--border); border-radius: 1.75rem; padding: 1.5rem 1.35rem; margin-bottom: 2rem; box-shadow: 0 15px 35px rgba(0,0,0,0.06); backdrop-filter: blur(20px); position: relative; overflow: hidden;">
                        {{-- Ambient background gradient glow --}}
                        <div style="position: absolute; top: -40px; right: -40px; width: 140px; height: 140px; border-radius: 50%; background: radial-gradient(circle, rgba(var(--primary-rgb), 0.18), transparent 70%); pointer-events: none;"></div>

                        {{-- Card Header --}}
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.45rem;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 10px #10b981; display: inline-block;"></span>
                                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.01em;">
                                        {{ __('Attendance Health') }}
                                    </h4>
                                </div>
                                <div style="font-size: 0.72rem; color: var(--text-sub); margin-top: 2px; font-weight: 600;">
                                    {{ __('Last 30 Calendar Days Evaluation') }}
                                </div>
                            </div>
                            
                            {{-- Punctuality Standing Badge --}}
                            @if($rate >= 90)
                                <span style="background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.72rem; font-weight: 800; padding: 0.3rem 0.75rem; border-radius: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <i class="ph ph-seal-check" style="font-size: 0.9rem;"></i> {{ __('Excellent Standing') }}
                                </span>
                            @elseif($rate >= 75)
                                <span style="background: rgba(14, 165, 233, 0.12); color: #0ea5e9; border: 1px solid rgba(14, 165, 233, 0.3); font-size: 0.72rem; font-weight: 800; padding: 0.3rem 0.75rem; border-radius: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <i class="ph ph-thumbs-up" style="font-size: 0.9rem;"></i> {{ __('Good Standing') }}
                                </span>
                            @else
                                <span style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); font-size: 0.72rem; font-weight: 800; padding: 0.3rem 0.75rem; border-radius: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <i class="ph ph-warning-circle" style="font-size: 0.9rem;"></i> {{ __('Review Needed') }}
                                </span>
                            @endif
                        </div>

                        {{-- Hero Analytics Row: Ring + 3 Micro Status Cards --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem;">
                            {{-- Circular Gauge --}}
                            <div style="position: relative; width: 104px; height: 104px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="104" height="104" style="transform: rotate(-90deg);">
                                    <circle cx="52" cy="52" r="44" fill="transparent" stroke="rgba(255,255,255,0.06)" stroke-width="8" />
                                    <circle cx="52" cy="52" r="44" fill="transparent" stroke="url(#portalRateGrad)" stroke-width="8" 
                                            stroke-dasharray="276" stroke-dashoffset="{{ 276 - (276 * $rate / 100) }}" stroke-linecap="round" 
                                            style="transition: stroke-dashoffset 1.5s cubic-bezier(0.16, 1, 0.3, 1);" />
                                    <defs>
                                        <linearGradient id="portalRateGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="var(--primary)" />
                                            <stop offset="100%" stop-color="#0ea5e9" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <div style="position: absolute; text-align: center; display: flex; flex-direction: column; align-items: center;">
                                    <span style="font-size: 1.35rem; font-weight: 900; color: var(--text-main); line-height: 1; letter-spacing: -0.02em;">{{ $rate }}%</span>
                                    <span style="font-size: 0.62rem; font-weight: 800; color: var(--text-sub); text-transform: uppercase; margin-top: 2px;">{{ __('Rate') }}</span>
                                </div>
                            </div>

                            {{-- 3 Micro Status Chips --}}
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; flex: 1; min-width: 0;">
                                {{-- Present --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 0.85rem; padding: 0.4rem 0.85rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="ph ph-check-circle" style="color: #10b981; font-size: 1.1rem;"></i>
                                        <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-main);">{{ __('Present') }}</span>
                                    </div>
                                    <span style="font-weight: 800; font-size: 0.95rem; color: #10b981;">{{ $stats['present'] }}</span>
                                </div>

                                {{-- Late --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 0.85rem; padding: 0.4rem 0.85rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="ph ph-clock-user" style="color: #f59e0b; font-size: 1.1rem;"></i>
                                        <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-main);">{{ __('Late') }}</span>
                                    </div>
                                    <span style="font-weight: 800; font-size: 0.95rem; color: #f59e0b;">{{ $stats['late'] }}</span>
                                </div>

                                {{-- Absent --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 0.85rem; padding: 0.4rem 0.85rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="ph ph-x-circle" style="color: #ef4444; font-size: 1.1rem;"></i>
                                        <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-main);">{{ __('Absent') }}</span>
                                    </div>
                                    <span style="font-weight: 800; font-size: 0.95rem; color: #ef4444;">{{ $stats['absent'] }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Subtle Divider --}}
                        <div style="height: 1px; background: var(--border); margin-bottom: 1.15rem; opacity: 0.6;"></div>

                        {{-- Bottom 3 KPI Metric Cards --}}
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.65rem;">
                            {{-- Worked Hours --}}
                            <div style="background: rgba(var(--primary-rgb), 0.05); border: 1px solid rgba(var(--primary-rgb), 0.18); padding: 0.75rem 0.4rem; border-radius: 1.15rem; text-align: center; transition: all 0.2s;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(var(--primary-rgb), 0.12); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-bottom: 0.35rem;">
                                    <i class="ph ph-hourglass-medium"></i>
                                </div>
                                <div style="font-weight: 900; font-size: 1.05rem; color: var(--text-main); line-height: 1.1;">{{ $kpiHoursDecimal }}<span style="font-size: 0.75rem; font-weight: 700; color: var(--text-sub); margin-left: 1px;">h</span></div>
                                <div style="font-size: 0.62rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-top: 3px; letter-spacing: 0.3px;">{{ __('Worked') }}</div>
                            </div>

                            {{-- Avg Arrival --}}
                            <div style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.18); padding: 0.75rem 0.4rem; border-radius: 1.15rem; text-align: center; transition: all 0.2s;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(245, 158, 11, 0.12); color: #f59e0b; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-bottom: 0.35rem;">
                                    <i class="ph ph-sun-horizon"></i>
                                </div>
                                <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-main); line-height: 1.1;">{{ $avgArrivalTime ?? '—' }}</div>
                                <div style="font-size: 0.62rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-top: 3px; letter-spacing: 0.3px;">{{ __('Arrival') }}</div>
                            </div>

                            {{-- Punctual Streak --}}
                            <div style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.18); padding: 0.75rem 0.4rem; border-radius: 1.15rem; text-align: center; transition: all 0.2s;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(239, 68, 68, 0.12); color: #ef4444; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-bottom: 0.35rem;">
                                    <i class="ph ph-fire"></i>
                                </div>
                                <div style="font-weight: 900; font-size: 1.05rem; color: var(--text-main); line-height: 1.1;">{{ $onTimeStreak ?? 0 }}<span style="font-size: 0.75rem; font-weight: 700; color: var(--text-sub); margin-left: 1px;">d</span></div>
                                <div style="font-size: 0.62rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-top: 3px; letter-spacing: 0.3px;">{{ __('Streak') }}</div>
                            </div>
                        </div>

                        {{-- ── Teaching Hours & Overtime Tracker Bar ── --}}
                        <div style="margin-top: 1rem; padding: 0.85rem 1rem; background: rgba(0,0,0,0.02); border: 1px solid var(--border); border-radius: 1.15rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem; flex-wrap: wrap; gap: 0.4rem;">
                                <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 0.35rem;">
                                    <i class="ph ph-briefcase" style="color: var(--primary);"></i> {{ __('Teaching Workload Target') }}
                                </span>
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <span style="font-size: 0.78rem; font-weight: 900; color: var(--primary);">
                                        {{ $actualTeachingHours ?? 0 }}h / {{ $targetTeachingHours ?? 60 }}h
                                    </span>
                                    @if(isset($overtimeHours) && $overtimeHours > 0)
                                        <span style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); font-size: 0.65rem; font-weight: 800; padding: 0.15rem 0.45rem; border-radius: 0.5rem;">
                                            +{{ $overtimeHours }}h {{ __('Overtime') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div style="height: 7px; background: rgba(0,0,0,0.06); border-radius: 10px; overflow: hidden; position: relative;">
                                <div style="width: {{ $teachingHoursRate ?? 0 }}%; height: 100%; background: linear-gradient(90deg, var(--primary), #0ea5e9); border-radius: 10px; transition: width 1.2s cubic-bezier(0.16, 1, 0.3, 1);"></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 0.4rem; font-size: 0.68rem; color: var(--text-sub); font-weight: 600;">
                                <span>{{ __('Target Progress') }}: <strong>{{ $teachingHoursRate ?? 0 }}%</strong></span>
                                <span>{{ $academicSemester ?? 'Semester 1' }} • {{ $academicYear ?? '2025-2026' }}</span>
                            </div>
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
                        <a href="{{ route('portal.slip', ['month' => $calendarMonth, 'year' => $calendarYear]) }}" target="_blank" class="btn-secondary" style="padding: 0.75rem 0.4rem; font-size: 0.8rem; text-decoration: none;">
                            <i class="ph ph-certificate"></i>
                            {{ __('Official Slip') }}
                        </a>
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
                    @endif

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
            <div class="date-range-grid">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label>{{ __('Start Date') }}</label>
                    <input type="date" name="start_date" class="form-control date-input" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label>{{ __('End Date') }}</label>
                    <input type="date" name="end_date" class="form-control date-input" required value="{{ date('Y-m-d') }}">
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

{{-- Interactive GPS Check-In Radar Modal --}}
<div id="portalGpsModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 540px; padding: 2rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
            <h3 style="margin:0; font-weight:800; color:var(--text-main); display:flex; align-items:center; gap:0.6rem;">
                <i class="ph ph-map-pin" style="color:var(--primary); font-size:1.4rem;"></i>
                {{ __('Mobile GPS Geofence Check-In') }}
            </h3>
            <button onclick="closePortalGpsModal()" style="background:none; border:none; color:var(--text-sub); font-size:1.5rem; cursor:pointer;"><i class="ph ph-x"></i></button>
        </div>

        {{-- Live Radar Distance Header --}}
        <div style="background: rgba(0,0,0,0.03); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.5rem;">
                <span id="gpsRadarStatusBadge" class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.75rem; font-weight: 800; padding: 0.3rem 0.75rem; border-radius: 0.6rem;">
                    <i class="ph ph-circle-notch animate-spin"></i> {{ __('Locating Satellite...') }}
                </span>
                <span id="gpsAccuracyText" style="font-size: 0.78rem; color: var(--text-sub); font-weight: 600;">
                    <i class="ph ph-broadcast"></i> GPS: --
                </span>
            </div>

            <div style="display: flex; align-items: baseline; gap: 0.5rem;">
                <span id="gpsDistanceValue" style="font-size: 1.8rem; font-weight: 900; color: var(--text-main);">--</span>
                <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-sub);">{{ __('meters from Campus Center') }}</span>
            </div>
            <p id="gpsSubText" style="margin: 0.35rem 0 0; font-size: 0.82rem; color: var(--text-sub); font-weight: 600;">
                {{ __('Maximum allowed geofence radius:') }} <strong>{{ \App\Models\Setting::getValue('campus_gps_radius', '1000') }}m</strong>
            </p>
        </div>

        {{-- Interactive Leaflet Map --}}
        <div id="portalGpsMap" style="height: 220px; width: 100%; border-radius: 1.25rem; border: 1px solid var(--border); margin-bottom: 1.25rem; background: rgba(0,0,0,0.05); position: relative; z-index: 1;"></div>

        {{-- Off-Campus Warning & 1-Click Dispute Section --}}
        <div id="gpsDisputeSection" style="display: none; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 1.25rem; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                <i class="ph ph-warning-circle" style="color: #ef4444; font-size: 1.4rem; flex-shrink: 0; margin-top: 2px;"></i>
                <div>
                    <h4 style="margin: 0; color: #ef4444; font-size: 0.92rem; font-weight: 800;">{{ __('Outside Campus Geofence Boundary') }}</h4>
                    <p style="margin: 0.25rem 0 0.75rem; font-size: 0.82rem; color: var(--text-main); font-weight: 600;">
                        {{ __('You are outside the required campus radius. If you are conducting off-campus duty or at an authorized event, submit a 1-click location dispute to HR.') }}
                    </p>
                    <button type="button" onclick="triggerLocationDispute()" style="background: #ef4444; color: white; border: none; padding: 0.55rem 1rem; border-radius: 0.75rem; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
                        <i class="ph ph-paper-plane-right"></i> {{ __('Submit Location Dispute to HR') }}
                    </button>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem;">
            <button type="button" class="btn-back" style="flex: 1; padding: 0.9rem;" onclick="closePortalGpsModal()">{{ __('Cancel') }}</button>
            <button type="button" id="btnConfirmGpsCheckin" class="btn-check" style="flex: 2; padding: 0.9rem;" disabled onclick="confirmGpsCheckinSubmit()">
                <i class="ph ph-map-pin"></i> {{ __('Confirm Check-In') }}
            </button>
        </div>
    </div>
</div>

{{-- Virtual Digital Teacher ID Card Modal --}}
<div id="digitalIdModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px; padding: 1.5rem 1.25rem; text-align: center; overflow: visible;">
        {{-- Header --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div style="text-align: left;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 1rem; font-weight: 800; color: var(--text-main);">
                    <i class="ph ph-identification-card" style="color: #eab308; font-size: 1.25rem;"></i>
                    <span>{{ __('Digital Faculty ID Card') }}</span>
                </div>
                <div style="font-size: 0.72rem; color: var(--text-sub); margin-top: 2px;">កាតបញ្ជាក់អត្តសញ្ញាណគ្រូបង្រៀន</div>
            </div>
            <button onclick="closeDigitalIdModal()" style="background: none; border: none; color: var(--text-sub); font-size: 1.4rem; cursor: pointer;"><i class="ph ph-x"></i></button>
        </div>

        {{-- 3D Flippable Scene — fixed height container prevents layout jump --}}
        <div class="id-card-scene" onclick="flipDigitalCard()">
            <div class="id-card-3d" id="digitalIdCard">
                {{-- Front Face --}}
                <div class="id-card-face id-card-front" id="digitalCardFront">
                    <div class="id-card-sheen"></div>
                    
                    {{-- Card Header --}}
                    <div style="display: flex; align-items: center; gap: 0.6rem; border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 0.6rem; margin-bottom: 0.75rem;">
                        <img src="{{ $uLogo }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: contain; background: #fff; padding: 2px; flex-shrink:0;" alt="Logo">
                        <div style="text-align: left; flex: 1; min-width:0;">
                            <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">National Technical Training Institute</div>
                            <div style="font-size: 0.72rem; font-weight: 900; color: #38bdf8;">វិទ្យាស្ថានជាតិបណ្តុះបណ្តាលបច្ចេកទេស</div>
                        </div>
                        <span style="background: rgba(var(--primary-rgb), 0.2); color: var(--primary); font-size: 0.58rem; font-weight: 800; padding: 0.18rem 0.4rem; border-radius: 5px; border: 1px solid rgba(var(--primary-rgb), 0.4); flex-shrink:0;">FACULTY</span>
                    </div>

                    {{-- Photo & Names --}}
                    <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 0.75rem;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid var(--primary); overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.5); margin-bottom: 0.4rem; background: #1e293b; flex-shrink: 0;">
                            @if($teacher->photo)
                                <img src="{{ to_asset_url($teacher->photo) }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $teacher->name }}">
                            @else
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 900; color: var(--primary);">{{ substr($teacher->name, 0, 1) }}</div>
                            @endif
                        </div>
                        <div style="font-size: 1.05rem; font-weight: 900; color: #fff; line-height: 1.25;">{{ $teacher->name_kh ?: $teacher->name }}</div>
                        <div style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; margin-top: 1px;">{{ $teacher->name }}</div>
                        <div style="margin-top: 0.3rem; display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap; justify-content: center;">
                            <span style="background: rgba(255,255,255,0.1); font-size: 0.65rem; font-weight: 700; padding: 0.15rem 0.45rem; border-radius: 5px; color: #e2e8f0;">{{ $deptLabel }}</span>
                            <span style="background: rgba(56,189,248,0.15); font-size: 0.65rem; font-weight: 800; padding: 0.15rem 0.45rem; border-radius: 5px; color: #38bdf8;">ID: {{ $teacher->employee_id }}</span>
                        </div>
                    </div>

                    {{-- QR / Barcode strip --}}
                    <div style="margin-top: auto; background: #fff; border-radius: 10px; padding: 7px 10px; display: flex; align-items: center; justify-content: space-between;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data={{ urlencode($teacher->employee_id) }}" style="width: 50px; height: 50px; border-radius: 4px;" alt="QR">
                        <div style="text-align: right; color: #0f172a;">
                            <div style="font-family: monospace; font-size: 0.88rem; font-weight: 900; letter-spacing: 2px;">{{ $teacher->employee_id }}</div>
                            <div style="font-size: 0.58rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Smart Kiosk Pass · ស្មាតប័ណ្ណ</div>
                            <div style="font-size: 0.58rem; color: #10b981; font-weight: 800;">● Active · សកម្ម</div>
                        </div>
                    </div>
                </div>

                {{-- Back Face --}}
                <div class="id-card-face id-card-back">
                    <div style="text-align: left;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.6rem;">
                            <i class="ph ph-shield-check" style="color: var(--primary); font-size: 1.2rem;"></i>
                            <div>
                                <div style="font-size: 0.85rem; font-weight: 800; color: #fff;">Institutional Terms</div>
                                <div style="font-size: 0.65rem; color: #94a3b8;">លក្ខខណ្ឌស្ថាប័ន</div>
                            </div>
                        </div>
                        <p style="font-size: 0.68rem; color: #94a3b8; line-height: 1.55; margin: 0 0 0.75rem;">
                            ប័ណ្ណឌីជីថលនេះបញ្ជាក់ថា អ្នកកាន់ប័ណ្ណគឺជាសមាជិកគ្រូបង្រៀនដែលមានការអនុញ្ញាតផ្លូវការ
                            នៃ<strong style="color:#fff;">វិទ្យាស្ថានជាតិបណ្ដុះបណ្ដាលបច្ចេកទេស (NTTI)</strong>។
                        </p>
                        <div style="background: rgba(255,255,255,0.06); padding: 0.6rem 0.8rem; border-radius: 8px; font-size: 0.68rem; color: #cbd5e1; margin-bottom: 0.75rem; border: 1px solid rgba(255,255,255,0.1);">
                            <div>• <span style="color:#94a3b8;">ឆ្នាំសិក្សា / Academic Year:</span> <strong>{{ $academicYear ?? '2025-2026' }}</strong></div>
                            <div style="margin-top: 3px;">• <span style="color:#94a3b8;">ភាគ / Semester:</span> <strong>{{ $academicSemester ?? 'Semester 1' }}</strong></div>
                            <div style="margin-top: 3px;">• <span style="color:#94a3b8;">ផ្នែក / Dept:</span> <strong>{{ $deptLabel }}</strong></div>
                            <div style="margin-top: 3px;">• <span style="color:#94a3b8;">ទំនាក់ទំនង / Contact:</span> <strong>info@ntti.edu.kh</strong></div>
                        </div>
                    </div>

                    <div style="text-align: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 0.65rem;">
                        <div style="font-size: 0.6rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Security Holographic Verification · ការផ្ទៀងផ្ទាត់</div>
                        <div style="font-family: monospace; font-size: 0.78rem; color: var(--primary); font-weight: 800; margin-top: 3px; letter-spacing: 1px;">
                            NTTI-SEC-{{ strtoupper(substr(md5($teacher->employee_id), 0, 10)) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Controls --}}
        <div style="display: flex; gap: 0.6rem; margin-top: 1rem;">
            <button onclick="flipDigitalCard()" class="btn-secondary" style="flex: 1; padding: 0.7rem; font-size: 0.8rem; margin: 0; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                <i class="ph ph-arrows-left-right"></i>
                <span>{{ __('Flip') }} <span style="font-size:0.68rem; opacity:0.7;">/ បង្វិល</span></span>
            </button>
            <button onclick="downloadDigitalCardImage()" class="btn-check" style="flex: 1; padding: 0.7rem; font-size: 0.8rem; margin: 0; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                <i class="ph ph-download-simple"></i>
                <span>{{ __('Save') }} <span style="font-size:0.68rem; opacity:0.8;">/ រក្សាទុក</span></span>
            </button>
        </div>
        <p style="font-size: 0.68rem; color: var(--text-sub); margin: 0.5rem 0 0;">ចុចលើកាត ឬ "បង្វិល" ដើម្បីមើលផ្នែកខាងក្រោយ</p>
    </div>
</div>

{{-- Full Weekly Timetable Modal --}}
<div id="weeklyTimetableModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 580px; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">
                <i class="ph ph-calendar" style="color: #6366f1; font-size: 1.4rem;"></i>
                {{ __('Weekly Teaching Timetable') }}
            </div>
            <button onclick="closeWeeklyTimetableModal()" style="background: none; border: none; color: var(--text-sub); font-size: 1.4rem; cursor: pointer;"><i class="ph ph-x"></i></button>
        </div>

        {{-- Day Selector Tabs --}}
        <div class="tt-day-tabs">
            @php
                $ttDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $ttToday = now()->format('l');
            @endphp
            @foreach($ttDays as $d)
                <button type="button" class="tt-day-btn {{ $d === $ttToday ? 'active' : '' }}" onclick="switchTimetableDay('{{ $d }}', this)">
                    {{ __($d) }}
                </button>
            @endforeach
        </div>

        {{-- Schedule Slots by Day --}}
        <div id="ttSlotsContainer">
            @foreach($ttDays as $d)
                <div id="tt-day-{{ $d }}" class="tt-day-content" style="{{ $d === $ttToday ? 'display:block;' : 'display:none;' }}">
                    @php
                        $daySlots = isset($weeklySchedules) && isset($weeklySchedules[$d]) ? $weeklySchedules[$d] : collect();
                    @endphp
                    @if($daySlots->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                            @foreach($daySlots as $slot)
                                <div style="background: rgba(0,0,0,0.02); border: 1px solid var(--border); border-radius: 1rem; padding: 0.85rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-size: 0.95rem; font-weight: 800; color: var(--text-main);">{{ $slot->subject_name }}</div>
                                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 3px; font-size: 0.78rem; color: var(--text-sub);">
                                            <span><i class="ph ph-door" style="color: var(--primary);"></i> {{ __('Room') }}: <strong>{{ $slot->room_number ?? 'N/A' }}</strong></span>
                                            @if($slot->substitute_teacher_id && $slot->substituteTeacher)
                                                <span style="background: rgba(20,184,166,0.12); color: #14b8a6; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 700;">
                                                    Sub: {{ $slot->substituteTeacher->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div style="background: rgba(99, 102, 241, 0.1); color: #6366f1; padding: 0.35rem 0.7rem; border-radius: 0.65rem; font-weight: 800; font-size: 0.85rem; text-align: right;">
                                        {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-sub); border: 2px dashed var(--border); border-radius: 1.25rem;">
                            <i class="ph ph-coffee" style="font-size: 2rem; opacity: 0.4; display: block; margin-bottom: 0.5rem;"></i>
                            {{ __('No scheduled teaching classes for') }} {{ __($d) }}.
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Export / Sync .ics --}}
        <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--border);">
            <span style="font-size: 0.75rem; color: var(--text-sub);">{{ __('Sync all classes to your phone calendar') }}</span>
            <a href="{{ route('portal.timetable.ics') }}" class="btn-check" style="width: auto; padding: 0.5rem 1rem; font-size: 0.8rem; margin: 0; display: inline-flex; align-items: center; gap: 0.35rem; text-decoration: none;">
                <i class="ph ph-calendar-plus"></i> {{ __('Export .ICS') }}
            </a>
        </div>
    </div>
</div>

{{-- Class Swap & Substitute Modal --}}
<div id="substituteModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 520px; padding: 1.75rem 1.5rem;">
        {{-- Modal Header --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 1.05rem; font-weight: 800; color: var(--text-main);">
                    <i class="ph ph-arrows-left-right" style="color: #14b8a6; font-size: 1.3rem;"></i>
                    {{ __('Class Substitute Request') }}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-sub); margin-top: 3px;">ការស្នើសុំអ្នកបង្រៀនជំនួស</div>
            </div>
            <button onclick="closeSubstituteModal()" style="background: rgba(var(--text-sub-rgb, 100,116,139), 0.1); border: none; color: var(--text-sub); font-size: 1.1rem; cursor: pointer; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="ph ph-x"></i></button>
        </div>

        <form id="substituteForm" onsubmit="submitSubstituteForm(event)">
            {{-- Row 1: My Teaching Slot (full width) --}}
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="font-weight: 700; font-size: 0.82rem; display: block; margin-bottom: 0.4rem;">
                    <i class="ph ph-chalkboard-teacher" style="color: #14b8a6; margin-right: 4px;"></i>
                    {{ __('My Teaching Slot') }}
                    <span style="font-size: 0.72rem; font-weight: 400; color: var(--text-sub);">· វេនបង្រៀនរបស់ខ្ញុំ</span>
                </label>
                <select name="schedule_id" class="form-control" required style="width: 100%;">
                    <option value="">-- ជ្រើសរើសវេនបង្រៀន (Select Slot) --</option>
                    @if(isset($weeklySchedules))
                        @foreach($weeklySchedules as $day => $slots)
                            <optgroup label="{{ __($day) }}">
                                @foreach($slots as $s)
                                    <option value="{{ $s->id }}">{{ $s->subject_name }} · {{ substr($s->start_time, 0, 5) }}–{{ substr($s->end_time, 0, 5) }} · បន្ទប់ {{ $s->room_number }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- Row 2: Date + Colleague in 2 columns --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                {{-- Date --}}
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 700; font-size: 0.82rem; display: block; margin-bottom: 0.4rem;">
                        <i class="ph ph-calendar" style="color: #6366f1; margin-right: 4px;"></i>
                        {{ __('Date') }}
                        <span style="font-size: 0.72rem; font-weight: 400; color: var(--text-sub);">· កាលបរិច្ឆេទ</span>
                    </label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required min="{{ date('Y-m-d') }}" style="width: 100%;">
                </div>

                {{-- Colleague --}}
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 700; font-size: 0.82rem; display: block; margin-bottom: 0.4rem;">
                        <i class="ph ph-user-switch" style="color: #f59e0b; margin-right: 4px;"></i>
                        {{ __('Substitute') }}
                        <span style="font-size: 0.72rem; font-weight: 400; color: var(--text-sub);">· គ្រូជំនួស</span>
                    </label>
                    <select name="substitute_teacher_id" class="form-control" required style="width: 100%;">
                        <option value="">-- ជ្រើសរើសគ្រូ --</option>
                        @if(isset($departmentColleagues))
                            @foreach($departmentColleagues as $c)
                                <option value="{{ $c->id }}">{{ $c->name_kh ?: $c->name }} ({{ $c->employee_id }})</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            {{-- Reason (full width) --}}
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="font-weight: 700; font-size: 0.82rem; display: block; margin-bottom: 0.4rem;">
                    <i class="ph ph-chat-text" style="color: #ec4899; margin-right: 4px;"></i>
                    {{ __('Reason') }}
                    <span style="font-size: 0.72rem; font-weight: 400; color: var(--text-sub);">· មូលហេតុ / ចំណាំ</span>
                </label>
                <textarea name="reason" class="form-control" rows="3"
                    placeholder="ឧ. បេសកកម្មផ្លូវការ, ត្រួតពិនិត្យប្រឡង... (e.g. Official Mission, Exam monitoring swap)"
                    required style="width: 100%; resize: vertical;"></textarea>
            </div>

            {{-- Info notice --}}
            <div style="background: rgba(20,184,166,0.08); border: 1px solid rgba(20,184,166,0.2); border-radius: 10px; padding: 0.65rem 0.85rem; margin-bottom: 1.25rem; font-size: 0.75rem; color: var(--text-sub); display: flex; gap: 0.5rem; align-items: flex-start;">
                <i class="ph ph-telegram-logo" style="color: #14b8a6; font-size: 1rem; flex-shrink:0; margin-top: 1px;"></i>
                <span>ការជូនដំណឹងតាម <strong style="color: #14b8a6;">Telegram</strong> នឹងត្រូវបញ្ជូនដោយស្វ័យប្រវត្តិទៅគ្រូជំនួស នៅពេលដែរការស្នើសុំនេះត្រូវបានបញ្ជូន។</span>
            </div>

            {{-- Action Buttons --}}
            <div style="display: flex; gap: 0.75rem;">
                <button type="button" class="btn-secondary" style="flex: 1; margin: 0; padding: 0.85rem;" onclick="closeSubstituteModal()">
                    <i class="ph ph-x-circle"></i> {{ __('Cancel') }} / បោះបង់
                </button>
                <button type="submit" id="btnSubmitSubstitute" class="btn-check" style="flex: 2; margin: 0; padding: 0.85rem; background: #14b8a6; color: #fff;">
                    <i class="ph ph-paper-plane-right"></i> {{ __('Send Request') }} / បញ្ជូន
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Personal Notification Drawer --}}
<div id="notifDrawerOverlay" class="notif-drawer-overlay" onclick="handleDrawerBackdropClick(event)">
    <div class="notif-drawer">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1.1rem; color: var(--text-main);">
                <i class="ph ph-bell" style="color: var(--primary);"></i>
                {{ __('Notifications & Alerts') }}
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <button onclick="markNotificationsAsRead()" style="background: none; border: none; font-size: 0.75rem; font-weight: 700; color: var(--primary); cursor: pointer;">
                    {{ __('Mark all read') }}
                </button>
                <button onclick="closeNotificationDrawer()" style="background: none; border: none; font-size: 1.3rem; color: var(--text-sub); cursor: pointer;">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>

        <div style="flex: 1; overflow-y: auto; padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
            @if(isset($personalNotifications) && $personalNotifications->count() > 0)
                @foreach($personalNotifications as $n)
                    <div style="background: var(--card); border: 1px solid var(--border); border-radius: 1rem; padding: 0.85rem 1rem; display: flex; gap: 0.75rem; align-items: flex-start;">
                        <div style="width: 34px; height: 34px; border-radius: 50%; background: rgba(var(--primary-rgb), 0.12); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem; margin-top: 2px;">
                            <i class="ph {{ $n['icon'] }}"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 2px;">
                                <div style="font-weight: 800; font-size: 0.85rem; color: var(--text-main);">{{ $n['title'] }}</div>
                                <span style="font-size: 0.65rem; color: var(--text-sub);">{{ $n['time'] }}</span>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-sub); line-height: 1.4;">{{ $n['message'] }}</div>
                        </div>
                    </div>
                @endforeach
            @else
                <div style="text-align: center; padding: 4rem 1rem; color: var(--text-sub);">
                    <i class="ph ph-bell-slash" style="font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                    {{ __('No notifications right now.') }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Biometric 1-Tap Unlock Setup Modal --}}
<div id="biometricModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 440px; padding: 2rem; text-align: center;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">
                <i class="ph ph-fingerprint" style="color: #10b981; font-size: 1.4rem;"></i>
                {{ __('Biometric 1-Tap Unlock') }}
            </div>
            <button onclick="closeBiometricSetupModal()" style="background: none; border: none; color: var(--text-sub); font-size: 1.4rem; cursor: pointer;"><i class="ph ph-x"></i></button>
        </div>

        <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(16, 185, 129, 0.12); color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 2.5rem; margin-bottom: 1rem;">
            <i class="ph ph-fingerprint"></i>
        </div>

        <h3 style="margin: 0 0 0.5rem; font-weight: 900; color: var(--text-main);">{{ __('Fast & Secure Sign-in') }}</h3>
        <p style="font-size: 0.82rem; color: var(--text-sub); line-height: 1.5; margin: 0 0 1.5rem;">
            {{ __('Pair your phone’s Fingerprint, Touch ID or Face ID to sign in to the Teacher Portal with 1 tap, without entering your 6-digit PIN every time.') }}
        </p>

        <div id="biometricPairStatus" style="font-size: 0.82rem; font-weight: 700; margin-bottom: 1rem; display: none;"></div>

        <div style="display: flex; gap: 0.75rem;">
            <button type="button" class="btn-secondary" style="flex: 1; margin: 0;" onclick="closeBiometricSetupModal()">{{ __('Cancel') }}</button>
            <button type="button" id="btnEnableBiometrics" onclick="enableDeviceBiometrics()" class="btn-check" style="flex: 2; margin: 0; background: #10b981; color: #fff;">
                <i class="ph ph-lock-key-open"></i> {{ __('Enable on This Device') }}
            </button>
        </div>
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

// ── Digital ID Card ──
function openDigitalIdModal() {
    document.getElementById('digitalIdModal').classList.add('active');
}
function closeDigitalIdModal() {
    document.getElementById('digitalIdModal').classList.remove('active');
}
function flipDigitalCard() {
    document.getElementById('digitalIdCard').classList.toggle('flipped');
}
async function downloadDigitalCardImage() {
    const teacherId = '{{ $teacher->employee_id }}';
    const teacherName = '{{ addslashes($teacher->name) }}';
    const teacherNameKh = '{{ addslashes($teacher->name_kh ?: $teacher->name) }}';
    const dept = '{{ addslashes($deptLabel) }}';
    
    // Create an offscreen canvas
    const canvas = document.createElement('canvas');
    canvas.width = 600;
    canvas.height = 920;
    const ctx = canvas.getContext('2d');

    // Rounded rectangle background
    const grad = ctx.createLinearGradient(0, 0, 600, 920);
    grad.addColorStop(0, '#0b1329');
    grad.addColorStop(0.5, '#1e293b');
    grad.addColorStop(1, '#0f172a');
    ctx.fillStyle = grad;
    ctx.beginPath();
    ctx.roundRect(0, 0, 600, 920, 30);
    ctx.fill();

    // Border
    ctx.strokeStyle = 'rgba(255,255,255,0.2)';
    ctx.lineWidth = 4;
    ctx.beginPath();
    ctx.roundRect(4, 4, 592, 912, 28);
    ctx.stroke();

    // Header strip
    ctx.fillStyle = 'rgba(0, 212, 160, 0.15)';
    ctx.fillRect(4, 4, 592, 140);
    
    ctx.fillStyle = '#38bdf8';
    ctx.font = 'bold 22px "Battambang", sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('វិទ្យាស្ថានជាតិបណ្តុះបណ្តាលបច្ចេកទេស', 300, 50);

    ctx.fillStyle = '#94a3b8';
    ctx.font = 'bold 15px "Inter", sans-serif';
    ctx.fillText('NATIONAL TECHNICAL TRAINING INSTITUTE', 300, 80);

    ctx.fillStyle = '#00d4a0';
    ctx.font = 'bold 14px "Inter", sans-serif';
    ctx.fillText('OFFICIAL FACULTY SMART PASS', 300, 110);

    // Profile photo circle
    const imgElem = document.getElementById('portal-profile-img');
    ctx.save();
    ctx.beginPath();
    ctx.arc(300, 260, 75, 0, Math.PI * 2);
    ctx.closePath();
    ctx.clip();
    if (imgElem && imgElem.src) {
        try {
            ctx.drawImage(imgElem, 225, 185, 150, 150);
        } catch(e) {
            ctx.fillStyle = '#00d4a0';
            ctx.fill();
        }
    } else {
        ctx.fillStyle = '#00d4a0';
        ctx.fill();
    }
    ctx.restore();

    // Photo Ring
    ctx.strokeStyle = '#00d4a0';
    ctx.lineWidth = 6;
    ctx.beginPath();
    ctx.arc(300, 260, 76, 0, Math.PI * 2);
    ctx.stroke();

    // Names
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 30px "Battambang", sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(teacherNameKh, 300, 390);

    ctx.fillStyle = '#94a3b8';
    ctx.font = 'bold 20px "Inter", sans-serif';
    ctx.fillText(teacherName, 300, 425);

    // Department & ID badge
    ctx.fillStyle = 'rgba(56, 189, 248, 0.2)';
    ctx.beginPath();
    ctx.roundRect(100, 455, 400, 44, 12);
    ctx.fill();
    ctx.fillStyle = '#38bdf8';
    ctx.font = 'bold 18px "Battambang", sans-serif';
    ctx.fillText(`${dept}  •  ID: ${teacherId}`, 300, 483);

    // Academic Year
    ctx.fillStyle = '#64748b';
    ctx.font = '14px "Inter", sans-serif';
    ctx.fillText('Academic Year: {{ $academicYear ?? "2025-2026" }} ({{ $academicSemester ?? "Semester 1" }})', 300, 530);

    // QR Code
    const qrImg = new Image();
    qrImg.crossOrigin = 'Anonymous';
    qrImg.onload = function() {
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.roundRect(80, 570, 440, 260, 20);
        ctx.fill();

        ctx.drawImage(qrImg, 110, 600, 200, 200);

        ctx.fillStyle = '#0f172a';
        ctx.font = 'bold 28px monospace';
        ctx.textAlign = 'left';
        ctx.fillText(teacherId, 330, 670);

        ctx.fillStyle = '#64748b';
        ctx.font = 'bold 14px "Inter", sans-serif';
        ctx.fillText('SMART KIOSK PASS', 330, 705);

        ctx.fillStyle = '#16a34a';
        ctx.font = 'bold 14px "Inter", sans-serif';
        ctx.fillText('● ACTIVE FACULTY', 330, 740);

        ctx.fillStyle = '#64748b';
        ctx.font = '11px monospace';
        ctx.textAlign = 'center';
        ctx.fillText('DIGITAL PASS VERIFIED • NTTI SYSTEM', 300, 875);

        const link = document.createElement('a');
        link.download = `NTTI_Faculty_ID_${teacherId}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    };
    qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(teacherId)}`;
}

// ── Weekly Timetable ──
function openWeeklyTimetableModal() {
    document.getElementById('weeklyTimetableModal').classList.add('active');
}
function closeWeeklyTimetableModal() {
    document.getElementById('weeklyTimetableModal').classList.remove('active');
}
function switchTimetableDay(day, btn) {
    document.querySelectorAll('.tt-day-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.tt-day-content').forEach(c => c.style.display = 'none');
    const target = document.getElementById('tt-day-' + day);
    if (target) target.style.display = 'block';
}

// ── Class Substitute Request ──
function openSubstituteModal() {
    document.getElementById('substituteModal').classList.add('active');
}
function closeSubstituteModal() {
    document.getElementById('substituteModal').classList.remove('active');
}
async function submitSubstituteForm(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitSubstitute');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Submitting...';

    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('{{ route("portal.substitute.request") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        const res = await response.json();
        if (res.status === 'success') {
            alert(res.message);
            closeSubstituteModal();
            form.reset();
        } else {
            alert(res.message || 'Failed to submit substitute request.');
        }
    } catch(err) {
        alert('Network error: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

// ── Personal Notification Drawer ──
function toggleNotificationDrawer() {
    const drawer = document.getElementById('notifDrawerOverlay');
    drawer.classList.toggle('active');
}
function closeNotificationDrawer() {
    document.getElementById('notifDrawerOverlay').classList.remove('active');
}
function handleDrawerBackdropClick(e) {
    if (e.target.id === 'notifDrawerOverlay') {
        closeNotificationDrawer();
    }
}
function markNotificationsAsRead() {
    const badge = document.getElementById('notifBadge');
    if (badge) badge.style.display = 'none';
    localStorage.setItem('portal_notifs_read_at', Date.now());
    alert('{{ __("All notifications marked as read.") }}');
}

// -- Biometric 1-Tap Unlock --
function openBiometricSetupModal() {
    document.getElementById('biometricModal').classList.add('active');
}
function closeBiometricSetupModal() {
    document.getElementById('biometricModal').classList.remove('active');
}
async function enableDeviceBiometrics() {
    const statusDiv = document.getElementById('biometricPairStatus');
    statusDiv.style.display = 'block';
    statusDiv.style.color = 'var(--text-sub)';
    statusDiv.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> កំពុងភ្ជាប់ឧបករណ៍... Pairing device token...';

    // Generate a stable device ID (persisted in localStorage)
    let deviceId = localStorage.getItem('portal_device_id');
    if (!deviceId) {
        deviceId = 'dev_' + Math.random().toString(36).substring(2, 15) + '_' + Date.now().toString(36);
        localStorage.setItem('portal_device_id', deviceId);
    }

    try {
        const response = await fetch('{{ route("portal.biometric.register") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                device_id: deviceId,
                device_name: navigator.userAgent.includes('Mobile') ? 'Mobile Phone' : 'Desktop Browser'
            })
        });

        const res = await response.json();
        if (res.success) {
            // Save with CONSISTENT key names matching login.blade.php
            localStorage.setItem('portal_biometric_token',        res.token);
            localStorage.setItem('portal_biometric_emp_id',       res.employee_id);   // KEY: matches login.blade.php
            localStorage.setItem('portal_biometric_teacher_name', res.teacher_name);  // KEY: matches login.blade.php
            // portal_device_id already saved above
            statusDiv.style.color = '#10b981';
            statusDiv.innerHTML = '<i class="ph ph-check-circle"></i> ' + res.message;
            setTimeout(() => {
                closeBiometricSetupModal();
            }, 1600);
        } else {
            statusDiv.style.color = '#ef4444';
            statusDiv.innerHTML = '<i class="ph ph-warning"></i> ' + (res.message || 'Pairing failed. / ការភ្ជាប់បរាជ័យ');
        }
    } catch(err) {
        statusDiv.style.color = '#ef4444';
        statusDiv.innerHTML = '<i class="ph ph-warning"></i> Connection error: ' + err.message;
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
        let uLogoRaw = @json(\App\Models\Setting::getAssetUrl('university_logo', '/images/ntti_logo.png'));
        let uLogo = uLogoRaw;
        if (uLogo && !uLogo.startsWith('http://') && !uLogo.startsWith('https://') && !uLogo.startsWith('data:')) {
            uLogo = window.location.origin + (uLogo.startsWith('/') ? '' : '/') + uLogo;
        }

        const uNameKh = @json(\App\Models\Setting::getValue('university_name_kh', 'វិទ្យាស្ថានជាតិបណ្តុះបណ្តាលបច្ចេកទេស'));
        const uNameEn = @json(\App\Models\Setting::getValue('university_name', 'National Technical Training Institute'));
        
        const teacherNameKh = @json($teacher->name_kh ?: $teacher->name);
        const teacherNameEn = @json($teacher->name);
        const displayTeacherName = teacherNameKh === teacherNameEn ? teacherNameEn : `${teacherNameKh} (${teacherNameEn})`;
        
        const employeeId = @json($teacher->employee_id);
        const dept = @json($teacher->department);
        const monthLabel = @json($calendarLabel);
        const presentCount = @json($stats['present']);
        const lateCount = @json($stats['late']);
        const absentCount = @json($stats['absent']);
        const totalMins = @json($totalWorkedMinutes ?? 0);
        const workedHoursDecimal = (totalMins / 60).toFixed(2);
        const workedHours = `${workedHoursDecimal} ម៉ោង (${workedHoursDecimal}h)`;

        const historyData = @json($history);

        let tableRows = '';
        historyData.forEach((item, index) => {
            const statusBadge = item.has_late 
                ? `<span style="color: #d97706; font-weight: bold;">មកយឺត (LATE)</span>` 
                : `<span style="color: #059669; font-weight: bold;">ទៀងម៉ោង (REGULAR)</span>`;

            tableRows += `
                <tr>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 7px;">${index + 1}</td>
                    <td style="border: 1px solid #cbd5e1; padding: 7px; font-weight: 600;">${item.date} (${item.day})</td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 7px;">${item.morning || '—'}</td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 7px;">${item.afternoon || '—'}</td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 7px;">${statusBadge}</td>
                </tr>
            `;
        });

        const printWin = window.open('', '', 'width=900,height=950');
        printWin.document.write(`
            <!DOCTYPE html>
            <html lang="km">
            <head>
                <meta charset="UTF-8">
                <base href="${window.location.origin}/">
                <title>ប័ណ្ណវត្តមានប្រចាំខែ - ${employeeId}</title>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700;900&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
                <style>
                    body { font-family: 'Kantumruy Pro', 'Battambang', sans-serif; padding: 35px; color: #1e293b; max-width: 850px; margin: 0 auto; line-height: 1.5; }
                    .kingdom-header { text-align: center; margin-bottom: 20px; }
                    .kingdom-title { font-family: 'Moul', 'Battambang', serif; font-weight: 700; font-size: 16px; color: #1e3a8a; }
                    .kingdom-sub { font-family: 'Battambang', sans-serif; font-weight: 700; font-size: 13px; color: #1e3a8a; margin-top: 2px; }
                    .kingdom-dots { margin-top: 4px; font-size: 10px; color: #64748b; letter-spacing: 3px; }
                    
                    .brand-row { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 25px; }
                    .brand-info { text-align: left; }
                    .u-name-kh { font-size: 18px; font-weight: 700; color: #1e3a8a; margin: 0; font-family: 'Battambang', sans-serif; }
                    .u-name-en { font-size: 12px; font-weight: 600; color: #64748b; margin-top: 2px; }
                    .logo-img { width: 75px; height: 75px; border-radius: 50%; object-fit: cover; border: 2px solid #2563eb; background: transparent; }
                    
                    .doc-title { text-align: center; margin-bottom: 20px; }
                    .doc-title h3 { margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; font-family: 'Battambang', sans-serif; }
                    .doc-title p { margin: 3px 0 0; font-size: 13px; color: #475569; }
                    
                    .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; background: #f8fafc; padding: 16px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #e2e8f0; font-size: 13px; }
                    .meta-item strong { color: #0f172a; }
                    
                    .kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; background: #eff6ff; padding: 14px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #bfdbfe; font-size: 12px; text-align: center; }
                    .kpi-label { font-weight: 600; color: #475569; margin-bottom: 4px; }
                    .kpi-val { font-weight: 700; font-size: 15px; color: #1d4ed8; }
                    
                    table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 35px; }
                    th { background: #2563eb; color: white; border: 1px solid #1d4ed8; padding: 9px 8px; font-weight: 700; }
                    td { font-size: 12px; }
                    
                    .footer-sig { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; font-size: 13px; }
                    .sig-title { font-weight: 700; margin-bottom: 60px; color: #1e293b; }
                    .sig-box { width: 220px; border-top: 1px dashed #94a3b8; padding-top: 6px; font-weight: 600; color: #64748b; margin: 0 auto; }
                    
                    @media print {
                        body { padding: 10px; }
                        .no-print { display: none !important; }
                    }
                </style>
            </head>
            <body>
                <div class="no-print" style="text-align: right; margin-bottom: 15px;">
                    <button onclick="window.print()" style="background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; font-family: 'Kantumruy Pro', sans-serif; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                        🖨️ បោះពុម្ព / រក្សាទុកជា PDF (Print / Save PDF)
                    </button>
                </div>

                <!-- Kingdom Motto -->
                <div class="kingdom-header">
                    <div class="kingdom-title">ព្រះរាជាណាចក្រកម្ពុជា</div>
                    <div class="kingdom-sub">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
                    <div class="kingdom-dots">-----------------</div>
                </div>

                <!-- Header Brand & Logo -->
                <div class="brand-row">
                    <div class="brand-info">
                        <h2 class="u-name-kh">${uNameKh}</h2>
                        <div class="u-name-en">${uNameEn}</div>
                    </div>
                    ${uLogo ? `<img src="${uLogo}" class="logo-img" alt="Logo" />` : ''}
                </div>

                <!-- Document Title -->
                <div class="doc-title">
                    <h3>ប័ណ្ណសង្ខេបវត្តមានគ្រូបង្រៀនប្រចាំខែ</h3>
                    <p>Monthly Teacher Attendance Statement — <strong>${monthLabel}</strong></p>
                </div>

                <!-- Teacher Meta Info -->
                <div class="meta-grid">
                    <div class="meta-item"><strong>ឈ្មោះគ្រូបង្រៀន (Teacher Name):</strong> ${displayTeacherName}</div>
                    <div class="meta-item"><strong>អត្តលេខ (Employee ID):</strong> ${employeeId}</div>
                    <div class="meta-item"><strong>ដេប៉ាតឺម៉ង់ (Department):</strong> ${dept || '—'}</div>
                    <div class="meta-item"><strong>ថ្ងៃចេញប័ណ្ណ (Issued Date):</strong> ${new Date().toLocaleDateString('km-KH')}</div>
                </div>

                <!-- KPI Summary Box -->
                <div class="kpi-row">
                    <div>
                        <div class="kpi-label">វត្តមាន (Present)</div>
                        <div class="kpi-val">${presentCount} ថ្ងៃ</div>
                    </div>
                    <div>
                        <div class="kpi-label">មកយឺត (Late)</div>
                        <div class="kpi-val" style="color:#d97706;">${lateCount} ថ្ងៃ</div>
                    </div>
                    <div>
                        <div class="kpi-label">អវត្តមាន (Absent)</div>
                        <div class="kpi-val" style="color:#dc2626;">${absentCount} ថ្ងៃ</div>
                    </div>
                    <div>
                        <div class="kpi-label">ម៉ោងធ្វើការសរុប (Worked)</div>
                        <div class="kpi-val" style="font-size:13px;">${workedHours}</div>
                    </div>
                </div>

                <!-- Attendance History Table -->
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;">ល.រ</th>
                            <th>កាលបរិច្ឆេទ (Date)</th>
                            <th>វេនព្រឹក (Morning Shift)</th>
                            <th>វេនរសៀល (Afternoon Shift)</th>
                            <th>ស្ថានភាព (Status)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableRows}
                    </tbody>
                </table>

                <!-- Signatures -->
                <div class="footer-sig">
                    <div>
                        <div class="sig-title">ហត្ថលេខាគ្រូបង្រៀន (Teacher Signature)</div>
                        <div class="sig-box">កាលបរិច្ឆេទ (Date): ...... / ...... / ..........</div>
                    </div>
                    <div>
                        <div class="sig-title">បានឃើញ និងពិនិត្យ (Approved By HR/Director)</div>
                        <div class="sig-box">នាយកសិក្សា / ការិយាល័យបុគ្គលិក</div>
                    </div>
                </div>
            </body>
            </html>
        `);
        printWin.document.close();
    }

    let portalGpsMap = null;
    let portalGpsMarker = null;
    let portalCampusCircle = null;
    let portalCurrentPosition = null;

    const CAMPUS_LAT = parseFloat(@json(\App\Models\Setting::getValue('campus_latitude', '11.5621')));
    const CAMPUS_LNG = parseFloat(@json(\App\Models\Setting::getValue('campus_longitude', '104.8885')));
    const ALLOWED_RADIUS = parseFloat(@json(\App\Models\Setting::getValue('campus_gps_radius', '1000')));
    const IS_GEOFENCE_EXEMPT = @json(!empty($teacher->is_geofence_exempt));
    const ENFORCE_GEOFENCE = @json(\App\Models\Setting::getValue('enforce_gps_geofence', 'true') === 'true');

    function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function openPortalGpsModal() {
        document.getElementById('portalGpsModal').classList.add('active');
        document.getElementById('gpsDisputeSection').style.display = 'none';
        document.getElementById('btnConfirmGpsCheckin').disabled = true;
        document.getElementById('gpsDistanceValue').innerText = '--';
        document.getElementById('gpsAccuracyText').innerHTML = '<i class="ph ph-broadcast"></i> GPS: Locating...';
        document.getElementById('gpsRadarStatusBadge').innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Locating Satellite...';
        document.getElementById('gpsRadarStatusBadge').style.background = 'rgba(245, 158, 11, 0.15)';
        document.getElementById('gpsRadarStatusBadge').style.color = '#f59e0b';
        document.getElementById('gpsRadarStatusBadge').style.borderColor = 'rgba(245, 158, 11, 0.3)';

        initPortalGpsMap();
    }

    function closePortalGpsModal() {
        document.getElementById('portalGpsModal').classList.remove('active');
    }

    function initPortalGpsMap() {
        setTimeout(() => {
            if (!portalGpsMap) {
                portalGpsMap = L.map('portalGpsMap', { zoomControl: false }).setView([CAMPUS_LAT, CAMPUS_LNG], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(portalGpsMap);

                // Campus center marker
                L.marker([CAMPUS_LAT, CAMPUS_LNG], {
                    icon: L.divIcon({
                        className: 'campus-center-icon',
                        html: '<div style="background:#00d4a0; color:#fff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:3px solid #fff; box-shadow:0 4px 10px rgba(0,0,0,0.3); font-size:1.1rem;"><i class="ph ph-buildings"></i></div>',
                        iconSize: [34, 34], iconAnchor: [17, 17]
                    })
                }).addTo(portalGpsMap).bindPopup("<b>Campus Center</b>");

                // Campus boundary circle
                portalCampusCircle = L.circle([CAMPUS_LAT, CAMPUS_LNG], {
                    color: '#00d4a0',
                    fillColor: '#00d4a0',
                    fillOpacity: 0.12,
                    radius: ALLOWED_RADIUS
                }).addTo(portalGpsMap);
            } else {
                portalGpsMap.invalidateSize();
            }
        }, 200);
    }

    function triggerGpsCheckin() {
        if (!navigator.geolocation) {
            alert("Geolocation is not supported by your mobile browser.");
            return;
        }

        openPortalGpsModal();

        navigator.geolocation.getCurrentPosition(function(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const accuracy = Math.round(pos.coords.accuracy || 0);
            portalCurrentPosition = { lat, lng };

            const distance = Math.round(calculateHaversineDistance(lat, lng, CAMPUS_LAT, CAMPUS_LNG));

            document.getElementById('gpsDistanceValue').innerText = distance + ' m';
            document.getElementById('gpsAccuracyText').innerHTML = `<i class="ph ph-broadcast"></i> GPS Accuracy: ±${accuracy}m`;

            const isWithin = distance <= ALLOWED_RADIUS;
            const canCheckin = isWithin || !ENFORCE_GEOFENCE || IS_GEOFENCE_EXEMPT;

            const badge = document.getElementById('gpsRadarStatusBadge');
            if (canCheckin) {
                badge.innerHTML = IS_GEOFENCE_EXEMPT 
                    ? '<i class="ph ph-shield-check"></i> Exempt Person' 
                    : '<i class="ph ph-check-circle"></i> Inside Geofence';
                badge.style.background = 'rgba(16, 185, 129, 0.15)';
                badge.style.color = '#10b981';
                badge.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                document.getElementById('gpsDisputeSection').style.display = 'none';
                document.getElementById('btnConfirmGpsCheckin').disabled = false;
            } else {
                badge.innerHTML = '<i class="ph ph-warning-circle"></i> Outside Geofence Boundary';
                badge.style.background = 'rgba(239, 68, 68, 0.15)';
                badge.style.color = '#ef4444';
                badge.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                document.getElementById('gpsDisputeSection').style.display = 'block';
                document.getElementById('btnConfirmGpsCheckin').disabled = true;
            }

            // Map pin update
            if (portalGpsMap) {
                if (portalGpsMarker) portalGpsMap.removeLayer(portalGpsMarker);

                const pinColor = canCheckin ? '#10b981' : '#ef4444';
                portalGpsMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'user-gps-icon',
                        html: `<div style="background:${pinColor}; color:#fff; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:3px solid #fff; box-shadow:0 4px 10px rgba(0,0,0,0.3); font-size:1rem;"><i class="ph ph-user"></i></div>`,
                        iconSize: [30, 30], iconAnchor: [15, 15]
                    })
                }).addTo(portalGpsMap).bindPopup(`<b>Your Location</b><br>${distance}m to campus center`).openPopup();

                const bounds = L.latLngBounds([[CAMPUS_LAT, CAMPUS_LNG], [lat, lng]]);
                portalGpsMap.fitBounds(bounds, { padding: [40, 40] });
            }

        }, function(err) {
            alert("GPS Error: " + err.message + ". Please enable device location services.");
            closePortalGpsModal();
        }, { enableHighAccuracy: true, timeout: 15000 });
    }

    function confirmGpsCheckinSubmit() {
        if (!portalCurrentPosition) return;
        const btn = document.getElementById('btnConfirmGpsCheckin');
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Submitting...';

        fetch("{{ route('portal.gps-checkin') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                latitude: portalCurrentPosition.lat,
                longitude: portalCurrentPosition.lng
            })
        }).then(r => r.json()).then(d => {
            if (d.success) {
                alert(d.message);
                closePortalGpsModal();
                location.reload();
            } else {
                alert(d.message);
                if (d.can_dispute) {
                    document.getElementById('gpsDisputeSection').style.display = 'block';
                }
            }
        }).catch(err => {
            alert("GPS Check-in error: " + err);
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        });
    }

    function triggerLocationDispute() {
        closePortalGpsModal();
        openLeaveModal();
        // Pre-fill dispute details in leave modal
        const leaveForm = document.getElementById('leaveForm');
        if (leaveForm) {
            const selectType = leaveForm.querySelector('select[name="leave_type"]');
            if (selectType) selectType.value = 'mission';
            
            const textReason = leaveForm.querySelector('textarea[name="reason"]');
            if (textReason && portalCurrentPosition) {
                const dist = document.getElementById('gpsDistanceValue').innerText;
                textReason.value = `[GPS Location Dispute] Attempted Mobile GPS Check-In at (${portalCurrentPosition.lat.toFixed(5)}, ${portalCurrentPosition.lng.toFixed(5)}) - ${dist} away from campus. Reason: ...`;
            }
        }
    }

    // ── Live Screen Dynamic QR Code Camera Scanner ──
    let portalQrScanner = null;
    let isProcessingQr = false;

    window.openDynamicQrScannerModal = function() {
        const modal = document.getElementById('portalDynamicQrModal');
        const feedback = document.getElementById('portalQrScanFeedback');
        if (!modal) return;
        modal.style.display = 'flex';
        if (feedback) { feedback.style.display = 'none'; feedback.innerHTML = ''; }
        isProcessingQr = false;

        setTimeout(() => {
            try {
                portalQrScanner = new Html5Qrcode("portal-qr-reader");
                portalQrScanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    onDynamicQrSuccess,
                    (err) => {}
                ).catch(err => {
                    console.error("Camera start error:", err);
                    if (feedback) {
                        feedback.style.display = 'block';
                        feedback.style.background = 'rgba(239, 68, 68, 0.15)';
                        feedback.style.color = '#ef4444';
                        feedback.innerHTML = '{{ __("Camera access required or not available.") }}';
                    }
                });
            } catch(e) {
                console.error(e);
            }
        }, 150);
    };

    window.closeDynamicQrScannerModal = function() {
        const modal = document.getElementById('portalDynamicQrModal');
        if (portalQrScanner) {
            portalQrScanner.stop().then(() => {
                portalQrScanner.clear();
                portalQrScanner = null;
            }).catch(() => {
                portalQrScanner = null;
            });
        }
        if (modal) modal.style.display = 'none';
        isProcessingQr = false;
    };

    async function onDynamicQrSuccess(decodedText) {
        if (isProcessingQr) return;
        isProcessingQr = true;

        const feedback = document.getElementById('portalQrScanFeedback');
        if (feedback) {
            feedback.style.display = 'block';
            feedback.style.background = 'rgba(14, 165, 233, 0.15)';
            feedback.style.color = '#0ea5e9';
            feedback.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> {{ __("Verifying screen token...") }}';
        }

        try {
            const res = await fetch("{{ route('portal.dynamic-qr-checkin') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token: decodedText })
            });

            const data = await res.json();

            if (data.success) {
                if (feedback) {
                    feedback.style.background = 'rgba(16, 185, 129, 0.15)';
                    feedback.style.color = '#10b981';
                    feedback.innerHTML = `<i class="ph ph-check-circle"></i> ${data.message || '{{ __("Attendance recorded successfully!") }}'}`;
                }
                setTimeout(() => {
                    closeDynamicQrScannerModal();
                    alert(data.message || '{{ __("Attendance recorded successfully!") }}');
                    location.reload();
                }, 1200);
            } else {
                if (feedback) {
                    feedback.style.background = 'rgba(239, 68, 68, 0.15)';
                    feedback.style.color = '#ef4444';
                    feedback.innerHTML = `<i class="ph ph-warning-circle"></i> ${data.message || '{{ __("Failed to verify QR code.") }}'}`;
                }
                setTimeout(() => { isProcessingQr = false; }, 2500);
            }
        } catch(err) {
            console.error(err);
            if (feedback) {
                feedback.style.background = 'rgba(239, 68, 68, 0.15)';
                feedback.style.color = '#ef4444';
                feedback.innerHTML = '{{ __("Network error. Please try again.") }}';
            }
            setTimeout(() => { isProcessingQr = false; }, 2500);
        }
    }
</script>

{{-- Interactive Dynamic Screen QR Scanner Modal --}}
<div id="portalDynamicQrModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center; z-index: 9999;" onclick="if(event.target===this) closeDynamicQrScannerModal()">
    <div class="modal-content" style="max-width: 440px; padding: 2rem; border-radius: 1.75rem; text-align: center; border: 1px solid var(--border); background: var(--card); backdrop-filter: blur(20px);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="margin: 0; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 0.6rem; font-size: 1.2rem;">
                <i class="ph ph-qr-code" style="color: #0ea5e9; font-size: 1.4rem;"></i>
                {{ __('Scan Live Screen QR') }}
            </h3>
            <button onclick="closeDynamicQrScannerModal()" style="background: none; border: none; color: var(--text-sub); font-size: 1.5rem; cursor: pointer;">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <p style="color: var(--text-sub); font-size: 0.85rem; margin-bottom: 1.25rem; line-height: 1.4;">
            {{ __('Point camera at the rotating QR code displayed on the Kiosk / Live TV screen to confirm attendance.') }}
        </p>

        {{-- Scanner Viewport Box --}}
        <div style="position: relative; width: 100%; border-radius: 1.25rem; overflow: hidden; border: 2px solid #0ea5e9; background: #000; aspect-ratio: 1; max-width: 280px; margin: 0 auto 1.25rem; box-shadow: 0 10px 30px rgba(14, 165, 233, 0.2);">
            <div id="portal-qr-reader" style="width: 100%; height: 100%;"></div>
        </div>

        <div id="portalQrScanFeedback" style="display: none; padding: 0.75rem 1rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem; margin-bottom: 1rem;"></div>

        <button type="button" onclick="closeDynamicQrScannerModal()" class="btn btn-secondary" style="width: 100%; border-radius: 1rem; padding: 0.85rem; font-weight: 800;">
            {{ __('Cancel') }}
        </button>
    </div>
</div>
</html>
