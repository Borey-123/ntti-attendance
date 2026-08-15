@extends('layouts.app')

@section('title', __('System Settings'))

@push('styles')
<style>
/* ── Settings Hub Layout ── */
.settings-container {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
    min-height: calc(100vh - 180px);
}

/* ── Sidebar Navigation ── */
.settings-sidebar {
    width: 280px;
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    padding: 1rem;
    position: sticky;
    top: 20px;
    flex-shrink: 0;
}

.settings-nav-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: 1rem;
    font-weight: 600;
    transition: all 0.3s;
    cursor: pointer;
    border: 1px solid transparent;
    margin-bottom: 0.5rem;
}

.settings-nav-item:hover {
    background: rgba(var(--primary-rgb), 0.05);
    color: var(--primary);
}

.settings-nav-item.active {
    background: var(--primary);
    color: #000;
    box-shadow: 0 8px 20px rgba(var(--primary-rgb), 0.2);
}

.settings-nav-item i {
    font-size: 1.25rem;
}

/* ── Main Content Area ── */
.settings-content {
    flex: 1;
    min-width: 0;
}

.settings-section {
    display: none;
    animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.settings-section.active {
    display: block;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ── Form Cards ── */
.glass-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    padding: 2.5rem;
    box-shadow: var(--shadow-lg);
    margin-bottom: 2rem;
}

.panel-header {
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.panel-header h2 {
    font-size: 1.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.panel-header p {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin: 0;
}

/* ── Custom Form Controls ── */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.75rem;
}

.form-control {
    background: rgba(0,0,0,0.2) !important;
    border: 1px solid var(--border) !important;
    border-radius: 0.85rem !important;
    padding: 0.8rem 1.2rem !important;
    color: var(--text-primary) !important;
    font-size: 0.95rem !important;
    transition: all 0.3s !important;
}

.form-control:focus {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1) !important;
    background: rgba(0,0,0,0.3) !important;
}

/* ── Fix native time/date picker icon visibility in dark mode ── */
input[type="time"].form-control::-webkit-calendar-picker-indicator,
input[type="date"].form-control::-webkit-calendar-picker-indicator {
    filter: invert(0.7) sepia(1) saturate(3) hue-rotate(120deg);
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s;
}

input[type="time"].form-control::-webkit-calendar-picker-indicator:hover,
input[type="date"].form-control::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

[data-theme="light"] input[type="time"].form-control::-webkit-calendar-picker-indicator,
[data-theme="light"] input[type="date"].form-control::-webkit-calendar-picker-indicator {
    filter: none;
    opacity: 0.6;
}

[data-theme="light"] input[type="time"].form-control::-webkit-calendar-picker-indicator:hover,
[data-theme="light"] input[type="date"].form-control::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

/* ── Toggle Switch ── */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.toggle-switch input { opacity: 0; width: 0; height: 0; }

.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(255,255,255,0.1);
    transition: .4s;
    border-radius: 34px;
    border: 1px solid var(--border);
}

[data-theme="light"] .slider {
    background-color: rgba(0,0,0,0.15);
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px; width: 18px;
    left: 4px; bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

input:checked + .slider { background-color: var(--primary); }
input:checked + .slider:before { transform: translateX(24px); background-color: #000; }

/* ── Day Badges ── */
.day-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.day-checkbox { display: none; }
.day-label {
    padding: 0.6rem 1.2rem;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s;
    color: var(--text-secondary);
}

.day-checkbox:checked + .day-label {
    background: var(--primary);
    color: #000;
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);
}

/* ── Admin Table ── */
.admin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 0.5rem;
}

.admin-row {
    background: rgba(255,255,255,0.02);
    border-radius: 1rem;
    transition: all 0.3s;
}

.admin-row:hover {
    background: rgba(255,255,255,0.05);
}

.admin-row td {
    padding: 1.25rem;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
}

.admin-row td:first-child { border-left: 1px solid var(--border); border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; }
.admin-row td:last-child { border-right: 1px solid var(--border); border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; }

.avatar {
    width: 44px; height: 44px; border-radius: 1rem;
    background: linear-gradient(135deg, var(--primary), #fff);
    color: #000; display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 1.1rem;
}

.btn-premium {
    background: var(--primary);
    color: #000;
    border: none;
    padding: 0.8rem 2rem;
    border-radius: 0.85rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.3);
}

.btn-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(var(--primary-rgb), 0.4);
}

.btn-secondary {
    background: rgba(255,255,255,0.05);
    color: var(--text-primary);
    border: 1px solid var(--border);
    padding: 0.8rem 1.5rem;
    border-radius: 0.85rem;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-secondary:hover {
    color: var(--primary);
    border-color: rgba(var(--primary-rgb), 0.5);
    background: rgba(var(--primary-rgb), 0.1);
    box-shadow: 0 6px 15px rgba(var(--primary-rgb), 0.15);
}

[data-theme="light"] .btn-secondary:hover {
    background: rgba(255, 255, 255, 0.9);
    border-color: rgba(var(--primary-rgb), 0.5);
    box-shadow: 0 8px 20px rgba(var(--primary-rgb), 0.2);
    color: var(--primary);
}

/* Banner Management Styles */
.banner-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}
.banner-item {
    position: relative;
    border-radius: 1rem;
    overflow: hidden;
    aspect-ratio: 16/9;
    border: 1px solid var(--border);
}
.banner-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.banner-remove {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}
.banner-remove:hover { transform: scale(1.1); background: #ef4444; }
.banner-remove input { display: none; }
.banner-remove.active { background: #000; }

@media (max-width: 768px) {
    .settings-container {
        flex-direction: column;
        gap: 1rem;
    }
    .settings-sidebar {
        width: 100%;
        position: relative;
        top: 0;
        margin-bottom: 1rem;
    }
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .glass-panel {
        padding: 1.5rem;
    }
}
</style>
@endpush

@section('content')

@php
    $uName = \App\Models\Setting::getValue('university_name', 'NTTI System');
    $uLogo = \App\Models\Setting::getValue('university_logo', '');
@endphp

<div style="margin-bottom: 2.5rem;">
    <h1 class="page-title">{{ __('Settings Hub') }}</h1>
    <p style="color: var(--text-secondary);">{{ __('Configure core system parameters, security protocols, and visual branding.') }}</p>
</div>

<div class="settings-container">
    {{-- ── Sidebar Navigation ── --}}
    <div class="settings-sidebar">
        <div class="settings-nav-item active" data-target="section-identity">
            <i class="ph ph-buildings"></i> {{ __('System Identity') }}
        </div>
        <div class="settings-nav-item" data-target="section-rules">
            <i class="ph ph-clock"></i> {{ __('Attendance Rules') }}
        </div>
        <div class="settings-nav-item" data-target="section-security">
            <i class="ph ph-shield-check"></i> {{ __('Security & Hardware') }}
        </div>
        <div class="settings-nav-item" data-target="section-appearance">
            <i class="ph ph-palette"></i> {{ __('System Appearance') }}
        </div>

        <div class="settings-nav-item" data-target="section-admins">
            <i class="ph ph-users-four"></i> {{ __('Team Management') }}
        </div>

        <div class="settings-nav-item" data-target="section-corrections">
            <i class="ph ph-shield-warning"></i> {{ __('Correction Requests') }}
        </div>
        
        <div class="settings-nav-item" data-target="section-holidays">
            <i class="ph ph-calendar-star"></i> {{ __('Calendar & Holidays') }}
        </div>
    </div>

    {{-- ── Main Content ── --}}
    <div class="settings-content">
        
        {{-- ══ SECTION 1: Identity ══ --}}
        <div id="section-identity" class="settings-section active">
            <div class="glass-panel">
                <div class="panel-header">
                    <h2><i class="ph ph-buildings" style="margin-right:0.5rem; color:var(--primary);"></i>{{ __('System Identity') }}</h2>
                    <p>{{ __('Configure how your institution appears across the platform.') }}</p>
                </div>
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>{{ __('Institution Name') }}</label>
                        <input type="text" name="university_name" class="form-control" value="{{ $universityName }}" required>
                    </div>
                    <div class="form-grid" style="margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label>{{ __('Website URL') }}</label>
                            <input type="url" name="university_website" class="form-control" value="{{ $universityWebsite }}" placeholder="https://ntti.edu.kh">
                        </div>
                        <div class="form-group">
                            <label>{{ __('Facebook Page URL') }}</label>
                            <input type="url" name="university_facebook" class="form-control" value="{{ $universityFacebook }}" placeholder="https://facebook.com/ntti">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Brand Logo') }}</label>
                        <div style="display: flex; align-items: center; gap: 2rem; background: rgba(0,0,0,0.1); padding: 1.5rem; border-radius: 1rem; border: 1px dashed var(--border);">
                            @php $displayLogo = $universityLogo ?: '/images/ntti_logo.png'; @endphp
                            <img src="{{ url($displayLogo) }}" style="height: 60px; object-fit: contain;">
                            <div style="flex: 1;">
                                <input type="file" name="university_logo" class="form-control" accept="image/*">
                                <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">{{ __('Recommended: PNG with transparency, 512x512px.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 2rem;">
                        <label>{{ __('Login Screen Wallpaper') }}</label>
                        <div style="position: relative; border-radius: 1rem; overflow: hidden; border: 1px solid var(--border);">
                            <div style="height: 180px; background: url('{{ $loginBg ? url($loginBg) : '' }}') center/cover #000;"></div>
                            <div style="padding: 1.5rem;">
                                <input type="file" name="login_bg" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 3rem; text-align: right;">
                        <button type="submit" class="btn-premium">
                            <i class="ph ph-check-circle"></i> {{ __('Save Identity') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══ SECTION 2: Rules ══ --}}
        <div id="section-rules" class="settings-section">
            <div class="glass-panel">
                <div class="panel-header">
                    <h2><i class="ph ph-clock" style="margin-right:0.5rem; color:var(--primary);"></i>{{ __('Attendance Rules') }}</h2>
                    <p>{{ __('Define shifts, working days, and late thresholds.') }}</p>
                </div>
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="university_name" value="{{ $universityName }}">
                    
                    <h3 style="font-size: 0.9rem; font-weight: 800; color: var(--primary); margin-bottom: 1.5rem;"><i class="ph ph-sun-horizon" style="margin-right:0.4rem;"></i>{{ __('Morning Shift') }}</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>{{ __('Shift Start') }}</label>
                            <input type="time" name="morning_shift_start" class="form-control" value="{{ $morningStart }}" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Shift End') }}</label>
                            <input type="time" name="morning_shift_end" class="form-control" value="{{ $morningEnd }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Morning Late Cutoff') }}</label>
                        <input type="time" name="morning_late_cutoff" class="form-control" value="{{ $morningLate }}" required>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--border); margin: 2.5rem 0;">

                    <h3 style="font-size: 0.9rem; font-weight: 800; color: var(--primary); margin-bottom: 1.5rem;"><i class="ph ph-sun-dim" style="margin-right:0.4rem;"></i>{{ __('Afternoon Shift') }}</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>{{ __('Shift Start') }}</label>
                            <input type="time" name="afternoon_shift_start" class="form-control" value="{{ $afternoonStart }}" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Shift End') }}</label>
                            <input type="time" name="afternoon_shift_end" class="form-control" value="{{ $afternoonEnd }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Afternoon Late Cutoff') }}</label>
                        <input type="time" name="afternoon_late_cutoff" class="form-control" value="{{ $afternoonLate }}" required>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--border); margin: 2.5rem 0;">

                    <h3 style="font-size: 0.9rem; font-weight: 800; color: var(--primary); margin-bottom: 1.5rem;"><i class="ph ph-robot" style="margin-right:0.4rem;"></i>{{ __('Auto Check-Out Settings') }}</h3>
                    
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; background: rgba(0, 212, 160, 0.05); padding: 1.5rem; border-radius: 1rem; border: 1px solid rgba(0, 212, 160, 0.1); margin-bottom: 1.5rem;">
                        <div>
                            <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">{{ __('Enable Automatic Check-Out') }}</h4>
                            <p style="margin: 0.25rem 0 0; font-size: 0.8rem; color: var(--text-secondary);">{{ __('Automatically check out teachers who forgot to scan out after their shift ends.') }}</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="hidden" name="enable_auto_checkout" value="off">
                            <input type="checkbox" name="enable_auto_checkout" value="on" {{ $enableAutoCheckout === 'on' ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Auto Check-Out Delay (Minutes)') }}</label>
                        <input type="number" name="auto_checkout_delay" class="form-control" value="{{ $autoCheckoutDelay }}" min="0" max="1440" required>
                        <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">{{ __('Number of minutes past the shift end time to wait before performing automatic check-out.') }}</p>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--border); margin: 2.5rem 0;">

                    <div class="form-group">
                        <label>{{ __('Active Working Days') }}</label>
                        <div class="day-selector">
                            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                <input type="checkbox" name="working_days[]" value="{{ $day }}" 
                                       id="day-{{ $day }}" class="day-checkbox" 
                                       {{ in_array($day, $workingDays) ? 'checked' : '' }}>
                                <label for="day-{{ $day }}" class="day-label">{{ __($day) }}</label>
                            @endforeach
                        </div>
                    </div>

                    <div style="margin-top: 3rem; text-align: right;">
                        <button type="submit" class="btn-premium">
                            <i class="ph ph-check-circle"></i> {{ __('Save Attendance Rules') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══ SECTION 3: Security ══ --}}
        <div id="section-security" class="settings-section">
            <div class="glass-panel">
                <div class="panel-header">
                    <h2><i class="ph ph-shield-check" style="margin-right:0.5rem; color:var(--primary);"></i>{{ __('Security & Hardware') }}</h2>
                    <p>{{ __('Scanner restrictions and operating schedules.') }}</p>
                </div>
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="university_name" value="{{ $universityName }}">
                    
                    <div class="form-group">
                        <label>{{ __('Authorized Terminal IP') }}</label>
                        <input type="text" name="authorized_ip" class="form-control" value="{{ $authorizedIp }}" placeholder="e.g. 192.168.1.50">
                        <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">{{ __('Leave blank to allow any IP address.') }}</p>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; background: rgba(239, 68, 68, 0.05); padding: 1.5rem; border-radius: 1rem; border: 1px solid rgba(239, 68, 68, 0.1);">
                        <div>
                            <h4 style="margin: 0; font-size: 1rem;">{{ __('Maintenance Mode') }}</h4>
                            <p style="margin: 0.25rem 0 0; font-size: 0.8rem; color: var(--text-secondary);">{{ __('Temporarily disable all RFID scanner inputs.') }}</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="hidden" name="maintenance_mode" value="off">
                            <input type="checkbox" name="maintenance_mode" value="on" {{ $maintenanceMode === 'on' ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--border); margin: 2.5rem 0;">

                    <h3 style="font-size: 0.9rem; font-weight: 800; color: #0088cc; margin-bottom: 1.5rem;"><i class="ph ph-telegram-logo"></i> {{ __('Telegram Bot Integration') }}</h3>
                    <div class="form-group">
                        <label>{{ __('Telegram Bot Token') }}</label>
                        <input type="text" name="telegram_bot_token" class="form-control" value="{{ $telegramBotToken ?? '' }}" placeholder="e.g. 123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11">
                        <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">{{ __('Required to send check-in and check-out notifications to teachers. Create a bot using @BotFather on Telegram to get a token.') }}</p>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--border); margin: 2.5rem 0;">

                    <h3 style="font-size: 0.9rem; font-weight: 800; color: var(--primary); margin-bottom: 1.5rem;"><i class="ph ph-timer" style="margin-right:0.4rem;"></i>{{ __('System Operating Window') }}</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>{{ __('System Open') }}</label>
                            <input type="time" name="system_open_time" class="form-control" value="{{ $systemOpen }}" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('System Close') }}</label>
                            <input type="time" name="system_close_time" class="form-control" value="{{ $systemClose }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Scan Alert Duration (Seconds)') }}</label>
                        <input type="number" name="scan_alert_duration" class="form-control" value="{{ $scanAlertDuration }}" min="1" max="60" required>
                        <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">{{ __('How long the check-in/out popup stays visible on the Live Monitor and Dashboard.') }}</p>
                    </div>

                    <div style="margin-top: 2rem; background: rgba(var(--primary-rgb), 0.03); padding: 1.5rem; border-radius: 1rem; border: 1px dashed var(--primary); display: flex; align-items: center; gap: 1.5rem;">
                        <i class="ph ph-database" style="font-size: 2.5rem; color: var(--primary);"></i>
                        <div style="flex: 1;">
                            <h4 style="margin: 0; font-size: 1rem;">{{ __('Attendance Database Report (XLS)') }}</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: var(--text-secondary);">{{ __('Download a formatted spreadsheet of all attendance logs.') }}</p>
                        </div>
                        <a href="{{ route('settings.backup') }}" class="btn-secondary">
                            <i class="ph ph-download-simple"></i> {{ __('Export XLS') }}
                        </a>
                    </div>

                    <div style="margin-top: 1.5rem; background: rgba(59, 130, 246, 0.03); padding: 1.5rem; border-radius: 1rem; border: 1px dashed rgba(59, 130, 246, 0.5); display: flex; align-items: center; gap: 1.5rem;">
                        <i class="ph ph-hard-drives" style="font-size: 2.5rem; color: #3b82f6;"></i>
                        <div style="flex: 1;">
                            <h4 style="margin: 0; font-size: 1rem;">{{ __('Full Database Backup & Import (.sqlite)') }}</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: var(--text-secondary);">{{ __('Export your complete system database file or restore an existing .sqlite backup.') }}</p>
                        </div>
                        <div style="display: flex; gap: 0.75rem;">
                            <a href="{{ route('settings.database.export') }}" class="btn-secondary" style="color: #3b82f6; border-color: rgba(59, 130, 246, 0.5); background: rgba(59, 130, 246, 0.08);">
                                <i class="ph ph-download"></i> {{ __('Export DB File') }}
                            </a>
                            <button type="button" onclick="document.getElementById('importDbFileInput').click();" class="btn-premium" style="padding: 0.5rem 1rem;">
                                <i class="ph ph-upload-simple"></i> {{ __('Import DB File') }}
                            </button>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; background: rgba(239, 68, 68, 0.03); padding: 1.5rem; border-radius: 1rem; border: 1px dashed rgba(239, 68, 68, 0.5); display: flex; align-items: center; gap: 1.5rem;">
                        <i class="ph ph-broom" style="font-size: 2.5rem; color: var(--danger);"></i>
                        <div style="flex: 1;">
                            <h4 style="margin: 0; font-size: 1rem;">{{ __('System Data Cleanup') }}</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: var(--text-secondary);">{{ __('Archive attendances older than 1 year and purge logs older than 3 months.') }}</p>
                        </div>
                        <button type="button" onclick="event.preventDefault(); openModal('systemCleanupModal')" class="btn-secondary" style="color: var(--danger); border-color: var(--danger); background: rgba(239, 68, 68, 0.1);">
                            <i class="ph ph-trash"></i> {{ __('Run Cleanup Now') }}
                        </button>
                    </div>

                    <div style="margin-top: 3rem; text-align: right;">
                        <button type="submit" class="btn-premium">
                            <i class="ph ph-check-circle"></i> {{ __('Save Security Settings') }}
                        </button>
                    </div>
                </form>
                
                {{-- Hidden Form for Database Import --}}
                <form id="dbImportForm" action="{{ route('settings.database.import') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                    @csrf
                    <input type="file" id="importDbFileInput" name="db_file" accept=".sqlite,.db,.sqlite3" onchange="if(confirm('Importing a database backup will replace all current data. Are you sure you want to proceed?')) document.getElementById('dbImportForm').submit();">
                </form>

                {{-- Hidden Form for System Cleanup --}}
                <form id="systemCleanupForm" action="{{ route('settings.cleanup') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>

        {{-- ══ SECTION 4: Appearance ══ --}}
        <div id="section-appearance" class="settings-section">
            <div class="glass-panel">
                <div class="panel-header">
                    <h2><i class="ph ph-palette" style="margin-right:0.5rem; color:var(--primary);"></i>{{ __('System Appearance') }}</h2>
                    <p>{{ __('Personalize the visual identity and interface scaling.') }}</p>
                </div>
                <form action="{{ route('settings.appearance.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="font_size" id="hidden_font_size" value="{{ $fontSize }}">
                    <input type="hidden" name="global_icon_size" id="hidden_icon_size" value="{{ $iconSize }}">
                    <input type="hidden" name="live_radar_size" id="hidden_radar_size" value="{{ $liveRadarSize ?? 360 }}">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>{{ __('Primary Brand Color') }}</label>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <input type="color" name="primary_color" id="colorPicker" value="{{ $primaryColor }}" 
                                       style="width: 60px; height: 60px; border: none; padding: 0; background: none; cursor: pointer;">
                                <span style="font-family: monospace; font-weight: 700;">{{ $primaryColor }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Default Interface Theme') }}</label>
                            <select name="default_theme" id="defaultThemeSelect" class="form-control">
                                <option value="dark" {{ $defaultTheme === 'dark' ? 'selected' : '' }}>🌙 {{ __('Dark Mode') }}</option>
                                <option value="light" {{ $defaultTheme === 'light' ? 'selected' : '' }}>☀️ {{ __('Light Mode') }}</option>
                            </select>
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--border); margin: 2.5rem 0;">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>{{ __('Typography / Font Family') }}</label>
                            <select name="font_family" class="form-control">
                                <option value="Inter" {{ (isset($fontFamily) && $fontFamily === 'Inter') ? 'selected' : '' }}>Inter (Global Standard)</option>
                                <option value="Kantumruy Pro" {{ (isset($fontFamily) && $fontFamily === 'Kantumruy Pro') ? 'selected' : '' }}>Kantumruy Pro (Khmer)</option>
                                <option value="Suwannaphum" {{ (isset($fontFamily) && $fontFamily === 'Suwannaphum') ? 'selected' : '' }}>Suwannaphum (Khmer)</option>
                                <option value="Roboto" {{ (isset($fontFamily) && $fontFamily === 'Roboto') ? 'selected' : '' }}>Roboto</option>
                                <option value="Poppins" {{ (isset($fontFamily) && $fontFamily === 'Poppins') ? 'selected' : '' }}>Poppins</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Border Radius Style') }}</label>
                            <select name="border_radius" class="form-control">
                                <option value="0px" {{ (isset($borderRadius) && $borderRadius === '0px') ? 'selected' : '' }}>Sharp (0px)</option>
                                <option value="0.5rem" {{ (isset($borderRadius) && $borderRadius === '0.5rem') ? 'selected' : '' }}>Soft (8px)</option>
                                <option value="1rem" {{ (isset($borderRadius) && $borderRadius === '1rem') ? 'selected' : '' }}>Rounded (16px)</option>
                                <option value="1.5rem" {{ (isset($borderRadius) && $borderRadius === '1.5rem') ? 'selected' : '' }}>Pill (24px)</option>
                            </select>
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--border); margin: 2.5rem 0;">

                    {{-- Glassmorphism Toggle --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(var(--primary-rgb), 0.04); padding: 1.5rem; border-radius: 1rem; border: 1px solid rgba(var(--primary-rgb), 0.1); margin-bottom: 2rem;">
                        <div style="display: flex; align-items: center; gap: 1.25rem;">
                            <div style="width: 46px; height: 46px; border-radius: 0.85rem; background: rgba(var(--primary-rgb), 0.12); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--primary); flex-shrink: 0;">
                                <i class="ph ph-drop-half"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--text-primary);">{{ __('Glassmorphism & Blur Effects') }}</h4>
                                <p style="margin: 0.2rem 0 0; font-size: 0.8rem; color: var(--text-secondary);">{{ __('Enable frosted glass and backdrop blur on sidebar, cards, and topbar. Disable on older hardware for better performance.') }}</p>
                            </div>
                        </div>
                        <label class="toggle-switch" style="flex-shrink: 0; margin-left: 1.5rem;">
                            <input type="checkbox" name="enable_glassmorphism" value="on" {{ ($enableGlassmorphism ?? 'on') === 'on' ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Interface Scaling (Font Size)') }}</label>
                        <div style="display: flex; align-items: center; gap: 1.5rem; background: rgba(0,0,0,0.1); padding: 2rem; border-radius: 1rem;">
                            <span style="font-size: 11px; font-weight: 800;">A</span>
                            <input type="range" id="fontSlider" min="11" max="20" value="{{ $fontSize }}" style="flex: 1; accent-color: var(--primary);">
                            <span style="font-size: 20px; font-weight: 800;">A</span>
                            <div id="fontSizeLabel" style="width: 50px; text-align: center; font-weight: 800; color: var(--primary);">{{ $fontSize }}px</div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 2rem;">
                        <label>{{ __('Global Icon Weight') }}</label>
                        <div style="display: flex; align-items: center; gap: 1.5rem; background: rgba(0,0,0,0.1); padding: 2rem; border-radius: 1rem;">
                            <i class="ph ph-hand-pointing" style="font-size: 0.8rem;"></i>
                            <input type="range" id="iconSlider" min="0.8" max="2.0" step="0.1" value="{{ $iconSize }}" style="flex: 1; accent-color: var(--primary);">
                            <i class="ph ph-hand-pointing" style="font-size: 2rem;"></i>
                            <div id="iconSizeLabel" style="width: 60px; text-align: center; font-weight: 800; color: var(--primary);">{{ $iconSize }}rem</div>
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--border); margin: 2.5rem 0;">

                    {{-- Live Monitor Animation Style --}}
                    <div class="form-group">
                        <label><i class="ph ph-broadcast" style="margin-right:0.4rem; color:var(--primary);"></i>{{ __('Live Monitor Animation Style') }}</label>
                        <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1.25rem;">{{ __('Choose the standby animation style shown on the Live Attendance Monitor.') }}</p>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;" id="liveStylePicker">
                            <div class="live-style-option" data-style="radar" onclick="selectLiveStyle('radar')" style="cursor:pointer; padding: 1.2rem; border-radius: 1rem; border: 2px solid var(--border); background: rgba(255,255,255,0.02); transition: all 0.3s; display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; border: 2px dashed var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgba(var(--primary-rgb),0.05);">
                                    <i class="ph ph-broadcast" style="color: var(--primary); font-size: 1.2rem;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-primary);">📡 Holographic Radar</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">Spinning conic sweep with 3D RFID card</div>
                                </div>
                            </div>
                            <div class="live-style-option" data-style="quantum" onclick="selectLiveStyle('quantum')" style="cursor:pointer; padding: 1.2rem; border-radius: 1rem; border: 2px solid var(--border); background: rgba(255,255,255,0.02); transition: all 0.3s; display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; border: 2px solid #a855f7; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgba(168,85,247,0.08);">
                                    <i class="ph ph-atom" style="color: #a855f7; font-size: 1.2rem;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-primary);">🌌 Quantum Portal</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">Pulsing plasma orb with electron orbits</div>
                                </div>
                            </div>
                            <div class="live-style-option" data-style="laser" onclick="selectLiveStyle('laser')" style="cursor:pointer; padding: 1.2rem; border-radius: 1rem; border: 2px solid var(--border); background: rgba(255,255,255,0.02); transition: all 0.3s; display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 44px; height: 44px; border-radius: 8px; border: 2px solid #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgba(16,185,129,0.08);">
                                    <i class="ph ph-crosshair" style="color: #10b981; font-size: 1.2rem;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-primary);">🎯 Laser Reticle</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">Biometric HUD grid with emerald scan beam</div>
                                </div>
                            </div>
                            <div class="live-style-option" data-style="ripple" onclick="selectLiveStyle('ripple')" style="cursor:pointer; padding: 1.2rem; border-radius: 1rem; border: 2px solid var(--border); background: rgba(255,255,255,0.02); transition: all 0.3s; display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; border: 2px solid var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgba(var(--primary-rgb),0.06); opacity: 0.7;">
                                    <i class="ph ph-circles-three" style="color: var(--primary); font-size: 1.2rem;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-primary);">🍏 Glass Ripple</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">Minimal glass rings with ambient glow</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 2.5rem;">
                        <label><i class="ph ph-bounding-box" style="margin-right:0.4rem; color:var(--primary);"></i>{{ __('Live Monitor Animation Size') }}</label>
                        <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1.25rem;">{{ __('Adjust the size of the radar/scanner animation on the Live Monitor.') }}</p>
                        <div style="display: flex; align-items: center; gap: 1.5rem; background: rgba(0,0,0,0.1); padding: 2rem; border-radius: 1rem;">
                            <i class="ph ph-corners-in" style="font-size: 1.2rem;"></i>
                            <input type="range" id="radarSizeSlider" min="200" max="600" step="10" value="{{ $liveRadarSize ?? 360 }}" style="flex: 1; accent-color: var(--primary);">
                            <i class="ph ph-corners-out" style="font-size: 2rem;"></i>
                            <div id="radarSizeLabel" style="width: 70px; text-align: center; font-weight: 800; color: var(--primary);">{{ $liveRadarSize ?? 360 }}px</div>
                        </div>
                    </div>

                    <div style="margin-top: 3rem; text-align: right;">
                        <button type="submit" class="btn-premium" onclick="localStorage.removeItem('theme')">
                            <i class="ph ph-check-circle"></i> {{ __('Save Branding') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══ SECTION 5: Admins ══ --}}
        <div id="section-admins" class="settings-section">
            <div class="glass-panel">
                <div class="panel-header">
                    <h2><i class="ph ph-users-four" style="margin-right:0.5rem; color:var(--primary);"></i>{{ __('Team Management') }}</h2>
                    <p>{{ __('Manage administrator access and credentials.') }}</p>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr style="text-align: left; color: var(--text-secondary); font-size: 0.75rem; text-transform: uppercase;">
                                <th style="padding: 1rem;">{{ __('Administrator') }}</th>
                                <th style="padding: 1rem;">{{ __('Security Level') }}</th>
                                <th style="padding: 1rem; text-align: right;">{{ __('Controls') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($admins as $admin)
                                <tr class="admin-row">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <div class="avatar">{{ strtoupper(substr($admin->name, 0, 1)) }}</div>
                                            <div>
                                                <div style="font-weight: 700; font-size: 1rem;">{{ $admin->name }}</div>
                                                <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $admin->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 800;">
                                            {{ $admin->id === auth()->id() ? __('ROOT ADMIN') : __('SYSTEM ADMIN') }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="btn-secondary" onclick="toggleReset('reset-{{ $admin->id }}')">
                                            <i class="ph ph-key"></i> {{ __('Reset') }}
                                        </button>
                                        
                                        <div id="reset-{{ $admin->id }}" style="display:none; margin-top: 1.5rem; text-align: left; background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--primary);">
                                            <form action="{{ route('settings.admin.reset-password', $admin->id) }}" method="POST">
                                                @csrf
                                                <div class="form-grid" style="margin-bottom: 1rem;">
                                                    <div class="form-group" style="margin:0;">
                                                        <label>{{ __('New Password') }}</label>
                                                        <input type="password" name="password" class="form-control" required minlength="6">
                                                    </div>
                                                    <div class="form-group" style="margin:0;">
                                                        <label>{{ __('Confirm') }}</label>
                                                        <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                                                    </div>
                                                </div>
                                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                                    <button type="button" class="btn-secondary" onclick="toggleReset('reset-{{ $admin->id }}')">{{ __('Cancel') }}</button>
                                                    <button type="submit" class="btn-premium" style="padding: 0.6rem 1.2rem;">{{ __('Update') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 3rem; background: rgba(var(--primary-rgb), 0.05); padding: 2.5rem; border-radius: 1.5rem; border: 1px solid rgba(var(--primary-rgb), 0.1);">
                    <h3 style="margin: 0 0 2rem; font-size: 1.1rem; font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="ph ph-user-plus" style="color: var(--primary);"></i> {{ __('Enroll New Administrator') }}
                    </h3>
                    <form action="{{ route('settings.admin.store') }}" method="POST">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1.5rem; align-items: flex-end;">
                            <div class="form-group" style="margin:0;">
                                <label>{{ __('Full Name') }}</label>
                                <input type="text" name="name" class="form-control" placeholder="IT08B2" required>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label>{{ __('Email') }}</label>
                                <input type="email" name="email" class="form-control" placeholder="admin@ntti.edu" required>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label>{{ __('Password') }}</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button type="submit" class="btn-premium" style="height: 48px;">
                                <i class="ph ph-plus"></i> {{ __('Create') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ══ SECTION 6: Corrections ══ --}}
        <div id="section-corrections" class="settings-section">
            <div class="glass-panel">
                <div class="panel-header">
                    <h2><i class="ph ph-shield-warning" style="margin-right:0.5rem; color:var(--primary);"></i>{{ __('Correction Requests') }}</h2>
                    <p>{{ __('Review and approve attendance disputes submitted by teachers via the portal.') }}</p>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr style="text-align: left; color: var(--text-secondary); font-size: 0.75rem; text-transform: uppercase;">
                                <th style="padding: 1rem;">{{ __('Teacher') }}</th>
                                <th style="padding: 1rem;">{{ __('Request Details') }}</th>
                                <th style="padding: 1rem;">{{ __('Status') }}</th>
                                <th style="padding: 1rem; text-align: right;">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($corrections as $req)
                                <tr class="admin-row">
                                    <td>
                                        <div style="font-weight: 700; font-size: 1rem;">{{ $req->teacher->name }}</div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ $req->teacher->employee_id }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700;">{{ \Carbon\Carbon::parse($req->date)->format('M d, Y') }} ({{ ucfirst($req->shift) }})</div>
                                        <div style="font-size: 0.85rem; color: var(--text-secondary); max-width: 300px; white-space: normal;">{{ $req->reason }}</div>
                                    </td>
                                    <td>
                                        @if($req->status === 'pending')
                                            <span style="background: rgba(245, 158, 11, 0.1); color: var(--warning); padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 800;">Pending</span>
                                        @elseif($req->status === 'approved')
                                            <span style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 800;">Approved</span>
                                        @else
                                            <span style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 800;">Rejected</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        @if($req->status === 'pending')
                                            <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                                                <form action="{{ route('settings.attendance_corrections.handle', $req->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn-secondary" style="background:rgba(16,185,129,0.1); color:var(--success); border-color:var(--success);"><i class="ph ph-check"></i></button>
                                                </form>
                                                <form action="{{ route('settings.attendance_corrections.handle', $req->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn-secondary" style="background:rgba(239,68,68,0.1); color:var(--danger); border-color:var(--danger);"><i class="ph ph-x"></i></button>
                                                </form>
                                            </div>
                                        @else
                                            <span style="font-size: 0.85rem; color: var(--text-secondary);">Reviewed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center; color:var(--text-secondary); padding: 2rem;">{{ __('No correction requests found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        {{-- ══ SECTION 7: Holidays ══ --}}
        <div id="section-holidays" class="settings-section">
            <div class="glass-panel">
                <div class="panel-header">
                    <h2><i class="ph ph-calendar-star" style="margin-right:0.5rem; color:var(--primary);"></i>{{ __('Calendar & Holidays') }}</h2>
                    <p>{{ __('Manage public holidays and school closures. Teachers are not marked absent on these dates.') }}</p>
                </div>
                
                <form id="addHolidayForm" style="margin-bottom: 2rem; background: rgba(0,0,0,0.05); padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border);">
                    <h3 style="margin-top: 0; font-size: 1.1rem;">{{ __('Add New Holiday') }}</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>{{ __('Date') }}</label>
                            <input type="date" id="holiday_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Name (English)') }}</label>
                            <input type="text" id="holiday_name" class="form-control" placeholder="e.g. Khmer New Year" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Name (Khmer)') }}</label>
                            <input type="text" id="holiday_name_kh" class="form-control" placeholder="e.g. ចូលឆ្នាំខ្មែរ">
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                        <button type="submit" class="btn-premium" style="height: 40px; padding: 0 1.5rem;">
                            <i class="ph ph-plus"></i> {{ __('Add Holiday') }}
                        </button>
                        <div style="display: flex; align-items: center; gap: 0.5rem; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 0.85rem; padding: 0 0.75rem; height: 40px;">
                            <i class="ph ph-magic-wand" style="color: #3b82f6;"></i>
                            <select id="autoFillYear" style="background: transparent; border: none; color: #3b82f6; font-weight: 700; font-size: 0.9rem; outline: none; cursor: pointer;">
                                @for($y = now()->year; $y <= now()->year + 5; $y++)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                            <button type="button" onclick="autoFillCambodiaHolidays()" style="background: transparent; border: none; color: #3b82f6; font-weight: 700; font-size: 0.9rem; cursor: pointer; padding: 0;">
                                {{ __('Auto-Fill Cambodia Holidays') }}
                            </button>
                        </div>
                    </div>
                </form>

                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr style="text-align: left; color: var(--text-secondary); font-size: 0.75rem; text-transform: uppercase;">
                                <th style="padding: 1rem;">{{ __('Date') }}</th>
                                <th style="padding: 1rem;">{{ __('Holiday Name') }}</th>
                                <th style="padding: 1rem; text-align: right;">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $hol)
                                <tr class="admin-row">
                                    <td>
                                        <div style="font-weight: 700; font-size: 1rem; color: var(--primary);">{{ \Carbon\Carbon::parse($hol->date)->format('M d, Y') }}</div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">{{ \Carbon\Carbon::parse($hol->date)->format('l') }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700;">{{ app()->getLocale() == 'km' ? ($hol->name_kh ?: $hol->name) : $hol->name }}</div>
                                        @if(app()->getLocale() == 'km' && $hol->name)
                                            <div style="font-size: 0.85rem; color: var(--text-secondary);">{{ $hol->name }}</div>
                                        @elseif($hol->name_kh)
                                            <div style="font-size: 0.85rem; color: var(--text-secondary);">{{ $hol->name_kh }}</div>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="btn-secondary" onclick="deleteHoliday({{ $hol->id }})" style="background:rgba(239,68,68,0.1); color:var(--danger); border-color:var(--danger); display: inline-flex;"><i class="ph ph-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center; color:var(--text-secondary); padding: 2rem;">{{ __('No holidays configured yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

</div> {{-- END CONTENT --}}
</div> {{-- END CONTAINER --}}

{{-- System Cleanup Modal --}}
<div class="modal-overlay" id="systemCleanupModal" onclick="if(event.target===this) closeModal('systemCleanupModal')">
    <div class="modal-content" style="max-width: 500px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 1.5rem; padding: 2rem; text-align: center;">
        <i class="ph ph-warning-circle" style="font-size: 4rem; color: var(--danger); margin-bottom: 1rem;"></i>
        <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">{{ __('Confirm System Cleanup') }}</h3>
        <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.5;">
            {{ __('Are you sure you want to run the system cleanup? This will permanently delete logs older than 3 months and archive attendances older than 1 year. This action cannot be undone.') }}
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <button type="button" class="btn-secondary" onclick="closeModal('systemCleanupModal')" style="flex: 1; border-radius: 1rem; padding: 0.75rem;">{{ __('Cancel') }}</button>
            <button type="button" onclick="document.getElementById('systemCleanupForm').submit();" style="flex: 1; background: var(--danger); color: white; border: none; border-radius: 1rem; padding: 0.75rem; font-weight: 700; cursor: pointer; transition: opacity 0.2s;">
                <i class="ph ph-trash"></i> {{ __('Yes, Run Cleanup') }}
            </button>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.spinner {
    animation: spin 1s linear infinite;
    display: inline-block;
}
</style>

@endsection



@push('scripts')
<script>
// ── Settings Hub Navigation ────────────────────
document.querySelectorAll('.settings-nav-item').forEach(item => {
    item.addEventListener('click', () => {
        // Toggle Nav
        document.querySelectorAll('.settings-nav-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');

        // Toggle Section
        const target = item.getAttribute('data-target');
        document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
        document.getElementById(target).classList.add('active');
        
        // Save state
        localStorage.setItem('settings_tab', target);
    });
});

// Restore state
const savedTab = localStorage.getItem('settings_tab');
if (savedTab && document.getElementById(savedTab)) {
    document.querySelector(`[data-target="${savedTab}"]`).click();
}

// ── Reset Form Toggle ───────────────────────────
function toggleReset(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

// ── Interface Scaling ───────────────────────────
const fontSlider = document.getElementById('fontSlider');
const fontLabel  = document.getElementById('fontSizeLabel');
const iconSlider = document.getElementById('iconSlider');
const iconLabel  = document.getElementById('iconSizeLabel');

fontSlider.addEventListener('input', (e) => {
    const val = e.target.value;
    document.documentElement.style.fontSize = val + 'px';
    fontLabel.textContent = val + 'px';
    localStorage.setItem('font_size', val);
    document.getElementById('hidden_font_size').value = val;
});

iconSlider.addEventListener('input', (e) => {
    const val = e.target.value;
    document.documentElement.style.setProperty('--icon-size', val + 'rem');
    iconLabel.textContent = val + 'rem';
    localStorage.setItem('global_icon_size', val);
    document.getElementById('hidden_icon_size').value = val;
});

// Sync UI on load
const savedFont = localStorage.getItem('font_size') || {{ $fontSize }};
fontSlider.value = savedFont;
fontLabel.textContent = savedFont + 'px';
document.getElementById('hidden_font_size').value = savedFont;

const savedIcon = localStorage.getItem('global_icon_size') || {{ $iconSize }};
iconSlider.value = savedIcon;
iconLabel.textContent = savedIcon + 'rem';
document.getElementById('hidden_icon_size').value = savedIcon;

const radarSlider = document.getElementById('radarSizeSlider');
const radarLabel = document.getElementById('radarSizeLabel');
if (radarSlider && radarLabel) {
    radarSlider.addEventListener('input', (e) => {
        const val = e.target.value;
        radarLabel.textContent = val + 'px';
        document.getElementById('hidden_radar_size').value = val;
    });
}

// ── Real-time Theme Preview ────────────────────
document.getElementById('defaultThemeSelect').addEventListener('change', function() {
    if (this.value === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }
});

// Modal helpers
function openModal(id) { 
    document.getElementById(id).classList.add('active'); 
}
function closeModal(id) { 
    document.getElementById(id).classList.remove('active'); 
}

// ── Holidays Logic ──────────────────────────────
document.getElementById('addHolidayForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;

    try {
        await window.fetchApi(`{{ route('api.holidays.store') }}`, {
            method: 'POST',
            body: JSON.stringify({
                date: document.getElementById('holiday_date').value,
                name: document.getElementById('holiday_name').value,
                name_kh: document.getElementById('holiday_name_kh').value
            })
        });
        window.location.reload();
    } catch(err) {
        await alert(err.message);
        btn.disabled = false;
    }
});

async function deleteHoliday(id) {
    if(!await confirm('Are you sure you want to delete this holiday?')) return;
    try {
        await window.fetchApi(`{{ url('/api-web/holidays') }}/${id}`, {
            method: 'DELETE'
        });
        window.location.reload();
    } catch(err) {
        await alert(err.message);
    }
}

async function autoFillCambodiaHolidays() {
    const year = document.getElementById('autoFillYear').value;
    if(!await confirm(`This will import the official Cambodia public holidays for ${year}. Proceed?`)) return;
    try {
        const resp = await window.fetchApi(`{{ route('api.holidays.autofill') }}`, {
            method: 'POST',
            body: JSON.stringify({ year: year })
        });
        if (resp && resp.status === 'partial') {
            await alert(resp.message);
        }
        window.location.reload();
    } catch(err) {
        await alert(err.message);
    }
}
// ── Live Monitor Style Picker ────────────────────────────────────────────
function selectLiveStyle(style) {
    // Save to localStorage so live.blade.php picks it up instantly
    localStorage.setItem('live_radar_style', style);

    // Update card selection visuals
    document.querySelectorAll('.live-style-option').forEach(card => {
        const isActive = card.dataset.style === style;
        card.style.borderColor = isActive ? 'var(--primary)' : 'var(--border)';
        card.style.background = isActive ? 'rgba(var(--primary-rgb), 0.08)' : 'rgba(255,255,255,0.02)';
        card.style.boxShadow = isActive ? '0 0 20px rgba(var(--primary-rgb), 0.2)' : 'none';
    });
}

// Initialize picker from saved preference
(function initLiveStylePicker() {
    const saved = localStorage.getItem('live_radar_style') || 'radar';
    selectLiveStyle(saved);
})();
</script>
@endpush
