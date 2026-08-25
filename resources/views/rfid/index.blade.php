@extends('layouts.app')

@section('title', __('RFID Cards'))

@section('content')
@push('styles')
<style>
    /* ── Summary Stats ── */
    .rfid-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }
    .r-summary-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 1.25rem;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s;
    }
    .r-summary-card:hover { transform: translateY(-3px); border-color: var(--primary); }
    .r-summary-icon {
        width: 44px; height: 44px; border-radius: 1rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
    }
    .r-summary-info .label { font-size: 0.65rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; }
    .r-summary-info .value { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); line-height: 1; }

    /* ── Animated Virtual ID Card ── */
    .virtual-card-container {
        perspective: 1000px;
        margin-bottom: 1.5rem;
        display: none;
    }
    .virtual-id-card {
        width: 100%;
        height: 190px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #020617 100%);
        border-radius: 1.25rem;
        border: 1.5px solid rgba(var(--primary-rgb), 0.3);
        position: relative;
        overflow: hidden;
        padding: 1.5rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.4), 0 0 25px rgba(var(--primary-rgb), 0.15);
        animation: cardAppear 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s, box-shadow 0.3s;
    }
    .virtual-id-card:hover {
        transform: translateY(-4px) rotateX(4deg) rotateY(-4deg);
        border-color: var(--primary);
        box-shadow: 0 20px 40px rgba(0,0,0,0.5), 0 0 30px rgba(var(--primary-rgb), 0.3);
    }
    .card-holographic-shine {
        position: absolute;
        top: -100%; left: -100%; width: 300%; height: 300%;
        background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.08) 45%, rgba(var(--primary-rgb), 0.25) 50%, rgba(255,255,255,0.08) 55%, transparent 60%);
        animation: cardGlint 6s infinite ease-in-out;
        pointer-events: none;
    }
    .card-animated-waves {
        display: flex; align-items: center; justify-content: center;
    }
    .card-chip {
        width: 42px; height: 30px;
        background: linear-gradient(135deg, #ffe066 0%, #f59e0b 100%);
        border-radius: 6px;
        border: 1px solid #b58900;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    .card-chip::before {
        content: '';
        position: absolute;
        inset: 4px;
        border: 1px stroke #856404;
        background: repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(133,100,4,0.3) 2px, rgba(133,100,4,0.3) 4px);
    }
    .card-uid { font-family: 'JetBrains Mono', monospace; font-size: 1.15rem; color: var(--primary); letter-spacing: 2px; margin-top: 1.25rem; font-weight: 800; text-shadow: 0 0 10px rgba(var(--primary-rgb),0.5); }
    .card-holder { margin-top: 0.25rem; font-weight: 800; color: #fff; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px; }
    .card-brand { position: absolute; bottom: 1.25rem; right: 1.5rem; font-size: 0.7rem; font-weight: 800; color: rgba(255,255,255,0.35); letter-spacing: 1.5px; }

    @keyframes cardGlint {
        0%, 100% { transform: translateY(0) translateX(0); }
        50% { transform: translateY(35%) translateX(35%); }
    }

    /* ── Holographic Proximity Scanner ── */
    .hologram-scanner {
        border: 2px dashed rgba(var(--primary-rgb), 0.3);
        border-radius: 1.5rem;
        padding: 2rem 1.5rem;
        text-align: center;
        margin-top: 1.25rem;
        margin-bottom: 1.5rem;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        display: none;
        background: rgba(var(--primary-rgb), 0.02);
        box-shadow: inset 0 0 20px rgba(var(--primary-rgb), 0.05);
    }
    .scan-active {
        border-color: var(--primary);
        background: rgba(var(--primary-rgb), 0.06);
        box-shadow: inset 0 0 30px rgba(var(--primary-rgb), 0.12), 0 0 25px rgba(var(--primary-rgb), 0.2);
    }
    .scan-found {
        border-color: #10b981 !important;
        background: rgba(16, 185, 129, 0.08) !important;
        box-shadow: inset 0 0 30px rgba(16, 185, 129, 0.15), 0 0 25px rgba(16, 185, 129, 0.3) !important;
    }

    .radar-container {
        position: relative;
        width: 140px;
        height: 140px;
        margin: 0 auto 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .radar-circle {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 1px dashed rgba(var(--primary-rgb), 0.35);
        animation: spinRadar 8s linear infinite;
    }
    .radar-sweep {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: conic-gradient(from 0deg, rgba(var(--primary-rgb), 0.4), transparent 60deg);
        animation: spinRadar 2.5s linear infinite;
    }
    .radar-card-icon {
        position: relative;
        z-index: 5;
        width: 70px;
        height: 45px;
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border: 1.5px solid var(--primary);
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.4);
        animation: cardRadarTap 2.5s ease-in-out infinite;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .scan-found .radar-card-icon {
        border-color: #10b981;
        box-shadow: 0 0 20px #10b981;
    }
    .scan-found .radar-card-icon i {
        color: #10b981 !important;
    }

    @keyframes spinRadar {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @keyframes cardRadarTap {
        0%, 100% { transform: scale(1) translateY(0); }
        50% { transform: scale(1.1) translateY(-6px); }
    }
    
    .scan-countdown {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--primary);
        text-shadow: 0 0 15px rgba(var(--primary-rgb), 0.6);
        margin: 0.25rem 0 0.5rem;
    }
    .scan-found .scan-countdown {
        color: #10b981;
        text-shadow: 0 0 15px rgba(16, 185, 129, 0.6);
    }
    .scan-bar-bg { width: 100%; height: 8px; background: rgba(255,255,255,0.08); border-radius: 4px; margin-top: 1rem; overflow: hidden; }
    .scan-bar-fill { width: 100%; height: 100%; background: linear-gradient(90deg, var(--primary), #3b82f6); border-radius: 4px; transition: width 1s linear; }
    .scan-found .scan-bar-fill { background: linear-gradient(90deg, #10b981, #34d399); }

    @keyframes cardAppear { from { opacity: 0; transform: translateY(20px) rotateX(-10deg); } to { opacity: 1; transform: translateY(0) rotateX(0); } }
    @keyframes scanPulse { 0%, 100% { transform: scale(1); opacity: 0.6; } 50% { transform: scale(1.1); opacity: 1; } }
    @keyframes ph-spin-anim { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .ph-spin { animation: ph-spin-anim 0.8s linear infinite; display: inline-block; }

    .modal-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.8); backdrop-filter: blur(8px);
        display: none; align-items: center; justify-content: center; z-index: 1000;
    }
    .modal-overlay.active { display: flex; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    .teacher-avatar {
        width: 45px; height: 45px;
        border-radius: 12px;
        border: 2px solid var(--border);
        flex-shrink: 0;
        background: var(--bg-dark);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .teacher-avatar img {
        width: 100%; height: 100%;
        object-fit: cover;
        border-radius: 12px;
    }
    .avatar-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; color: var(--primary);
        background: rgba(var(--primary-rgb), 0.1);
        border-radius: 12px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-between align-center" style="margin-bottom: 2rem;">
    <div>
        <h1 class="page-title" style="margin-bottom: 0.25rem;">{{ __('RFID Control Center') }}</h1>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Manage secure hardware credentials and card assignments.') }}</p>
    </div>
</div>

{{-- ── Summary Metrics ── --}}
<div class="rfid-summary">
    <div class="r-summary-card">
        <div class="r-summary-icon" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);"><i class="ph ph-cards"></i></div>
        <div class="r-summary-info">
            <div class="label">{{ __('Total Issued') }}</div>
            <div class="value">{{ $cards->count() }}</div>
        </div>
    </div>
    <div class="r-summary-card">
        <div class="r-summary-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;"><i class="ph ph-check-circle"></i></div>
        <div class="r-summary-info">
            <div class="label">{{ __('Active Cards') }}</div>
            <div class="value">{{ $cards->where('status', 'active')->count() }}</div>
        </div>
    </div>
    <div class="r-summary-card">
        <div class="r-summary-icon" style="background: rgba(88, 166, 255, 0.1); color: #58a6ff;"><i class="ph ph-chart-pie-slice"></i></div>
        <div class="r-summary-info">
            <div class="label">{{ __('Card Coverage') }}</div>
            @php $totalTeachers = \App\Models\Teacher::where('status','active')->count(); $coverage = $totalTeachers > 0 ? round(($cards->count() / $totalTeachers) * 100) : 0; @endphp
            <div class="value">{{ $coverage }}%</div>
        </div>
    </div>
    <div class="r-summary-card">
        <div class="r-summary-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="ph ph-x-circle"></i></div>
        <div class="r-summary-info">
            <div class="label">{{ __('Disabled') }}</div>
            <div class="value">{{ $cards->where('status', 'inactive')->count() }}</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 2.5rem; align-items: flex-start;">

    {{-- ── Left: Control Panel ── --}}
    <div style="position: relative;">
        {{-- Decorative background lines for "overlapping" look --}}
        <div style="position: absolute; top: -10px; left: -10px; right: 10px; bottom: 10px; border: 1px solid var(--primary); border-radius: 2rem; opacity: 0.1; pointer-events: none;"></div>
        
        <div class="card" style="padding: 2.5rem; border-radius: 2rem; position: relative; background: var(--bg-card); box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 5px; background: linear-gradient(90deg, var(--primary), transparent);"></div>
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 1.5rem; display:flex; align-items:center; gap:0.75rem;">
                <i class="ph ph-shield-plus"></i> {{ __('Assign Hardware ID') }}
            </h3>
            
            <form id="assignRfidForm" onsubmit="submitAssign(event)">
                <div class="form-group">
                    <label>{{ __('Select Personnel') }}</label>
                    <select name="teacher_id" id="teacher_select" class="form-control" required style="border-radius: 1rem; background-color: var(--bg-dark);" onchange="updateVirtualCard()">
                        <option value="" disabled selected>{{ __('Choose teacher...') }}</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" data-name="{{ $t->name }}">[{{ $t->employee_id }}] {{ $t->name_kh ?: $t->name }} ({{ $t->name }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Virtual ID Card Preview --}}
                <div class="virtual-card-container" id="cardPreviewContainer">
                    <div class="virtual-id-card">
                        <div class="card-holographic-shine"></div>
                        <div class="d-flex justify-between align-center">
                            <div class="card-chip"></div>
                            <div class="card-animated-waves">
                                <svg viewBox="0 0 50 50" fill="none" style="width:36px; height:36px;">
                                    <path class="wave wave-3" d="M35 15 C42 22, 42 28, 35 35" stroke="var(--primary)" stroke-width="3" stroke-linecap="round"/>
                                    <path class="wave wave-2" d="M28 19 C33 24, 33 26, 28 31" stroke="var(--primary)" stroke-width="3" stroke-linecap="round"/>
                                    <path class="wave wave-1" d="M21 23 C23 25, 23 25, 21 27" stroke="var(--primary)" stroke-width="3" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>
                        <div class="card-uid" id="previewUid">XXXX-XXXX-XXXX</div>
                        <div class="card-holder" id="previewName">John Doe</div>
                        <div class="card-brand">NTTI SECURITY SYSTEM</div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label>{{ __('Hardware Interface') }}</label>
                    
                    <button type="button" id="startScanBtn" class="btn" onclick="startHardwareScan()" style="width: 100%; padding: 1rem; border-radius: 1.25rem; font-weight: 800; font-size: 1rem; display:flex; align-items:center; justify-content:center; gap:0.75rem; background: rgba(var(--primary-rgb), 0.08); color: var(--primary); border: 2px dashed rgba(var(--primary-rgb), 0.4); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);" onmouseover="this.style.background='rgba(var(--primary-rgb), 0.15)'; this.style.borderColor='var(--primary)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(var(--primary-rgb), 0.08)'; this.style.borderColor='rgba(var(--primary-rgb), 0.4)'; this.style.transform='translateY(0)';">
                        <i class="ph ph-contactless-payment" style="font-size: 1.4rem;"></i>
                        <span>{{ __('Initiate Scan') }}</span>
                    </button>

                    {{-- Holographic Scan Animation --}}
                    <div id="scanWidget" class="hologram-scanner">
                        <div class="radar-container">
                            <div class="radar-circle"></div>
                            <div class="radar-sweep"></div>
                            <div class="radar-card-icon">
                                <i class="ph ph-contactless-payment" style="font-size: 1.6rem; color: var(--primary);"></i>
                            </div>
                        </div>

                        <div id="scanPrompt" style="font-size: 0.95rem; font-weight: 800; color: var(--text-primary); letter-spacing: 0.5px;">
                            {{ __('PROXIMITY SCANNING...') }}
                        </div>

                        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem;">
                            {{ __('Hold RFID card near hardware reader') }}
                        </div>

                        <div id="scanCountdown" class="scan-countdown">30s</div>

                        <div class="scan-bar-bg">
                            <div id="scanProgress" class="scan-bar-fill"></div>
                        </div>
                    </div>

                    <input type="text" name="uid" id="uid-input"
                           class="form-control" required
                           placeholder="{{ __('Awaiting Scanned UID...') }}"
                           style="margin-top: 1rem; font-family: 'JetBrains Mono', monospace; text-align: center; border-radius: 1rem; border-style: dashed;">

                    <div id="uid-status" style="margin-top: 1rem;"></div>
                </div>

                <button type="submit" class="btn mt-4" id="assignBtn" style="width: 100%; padding: 1.1rem; border-radius: 1.25rem; font-weight: 800; font-size: 1.05rem; display: flex; align-items: center; justify-content: center; gap: 0.75rem; background: linear-gradient(135deg, var(--primary), #00b894); color: #000; border: none; box-shadow: 0 8px 25px rgba(var(--primary-rgb), 0.4); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 30px rgba(var(--primary-rgb), 0.5)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(var(--primary-rgb), 0.4)';">
                    <i class="ph ph-lock-key" style="font-size: 1.3rem;"></i> <span>{{ __('Register & Assign') }}</span>
                </button>
            </form>
        </div>
    </div>

    {{-- ── Right: Registry ── --}}
    <div style="position: relative; flex: 1; min-width: 450px;">
        {{-- Decorative background lines for "overlapping" look --}}
        <div style="position: absolute; top: -10px; left: 10px; right: -10px; bottom: 10px; border: 1px solid #3b82f6; border-radius: 2rem; opacity: 0.1; pointer-events: none;"></div>

        <div class="card" style="border-radius: 2rem; position: relative; background: var(--bg-card); box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden; height: 100%;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 5px; background: linear-gradient(90deg, #3b82f6, transparent);"></div>
            <div class="card-header" style="padding: 1.5rem 2.5rem; background: transparent; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <h3 style="display:flex;align-items:center;gap:0.75rem; margin-bottom:0; font-weight: 800; color: #3b82f6;">
                    <i class="ph ph-fingerprint"></i>
                    {{ __('Registry List') }}
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-left: 0.25rem;">({{ $cards->count() }})</span>
                </h3>
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    <div style="position: relative;">
                        <i class="ph ph-magnifying-glass" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                        <input type="text" id="cardSearch" placeholder="{{ __('Search...') }}" oninput="searchCards()" class="form-control" style="width: 180px; padding-left: 2.2rem; border-radius: 1rem; background: var(--bg-dark); font-size: 0.85rem; height: 38px;">
                    </div>
                    <select id="deptFilter" class="form-control" style="width: 180px; border-radius: 1rem; background: var(--bg-dark); font-size: 0.85rem; height: 38px;" onchange="filterDept()">
                        <option value="">{{ __('All Departments') }}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ __($dept) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        
            <div style="padding: 0 1rem 1.5rem; max-height: 600px; overflow-y: auto; position: relative;">
                <table class="table">
                    <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 10; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <tr>
                            <th style="width: 50px; text-align: center;">{{ __('No.') }}</th>
                            <th>{{ __('Holder') }}</th>
                            <th>{{ __('Credential UID') }}</th>
                            <th>{{ __('Assigned') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th style="text-align: center;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="cardsTableBody">
                        @forelse($cards as $index => $card)
                        <tr class="card-row" style="transition: all 0.2s;" data-name="{{ strtolower(($card->teacher?->name ?? '') . ' ' . ($card->teacher?->name_kh ?? '') . ' ' . ($card->teacher?->employee_id ?? '') . ' ' . ($card->uid ?? '')) }}">
                            <td style="text-align: center;">
                                <div style="width: 28px; height: 28px; border-radius: 0.5rem; background: rgba(59, 130, 246, 0.08); color: #3b82f6; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem;">
                                    {{ $index + 1 }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.85rem;">
                                    <div class="teacher-avatar">
                                        @if($card->teacher && $card->teacher->photo)
                                            <img src="{{ to_asset_url($card->teacher->photo) }}" alt="">
                                        @else
                                            <div class="avatar-placeholder">{{ $card->teacher ? strtoupper(substr($card->teacher->name, 0, 1)) : '?' }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-weight:800; font-size: 1rem; color: var(--primary);">{{ $card->teacher?->name_kh ?? '' }}</div>
                                        <div style="font-weight:600; font-size: 0.85rem; color: var(--text-primary);">{{ $card->teacher?->name ?? 'Unassigned' }}</div>
                                        <div style="font-size:0.72rem; color:var(--text-muted); font-weight: 600;">{{ $card->teacher?->employee_id ?? '' }} · {{ $card->teacher ? __($card->teacher->department) : 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code style="font-family: 'JetBrains Mono', monospace; color: var(--primary); letter-spacing: 1px; font-size: 0.88rem; background: rgba(var(--primary-rgb), 0.06); padding: 0.25rem 0.6rem; border-radius: 0.4rem;">{{ $card->uid }}</code>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem; color: var(--text-secondary);">
                                    {{ $card->assigned_at ? $card->assigned_at->format('d M Y') : ($card->created_at ? $card->created_at->format('d M Y') : '—') }}
                                </div>
                            </td>
                            <td>
                                @if($card->status == 'active')
                                    <span class="badge badge-success" style="padding: 0.35rem 0.85rem; border-radius: 2rem; font-size: 0.75rem;"><i class="ph ph-check-circle"></i> {{ __('Active') }}</span>
                                @else
                                    <span class="badge badge-danger" style="padding: 0.35rem 0.85rem; border-radius: 2rem; font-size: 0.75rem;"><i class="ph ph-x-circle"></i> {{ __('Disabled') }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.35rem; justify-content: center;">
                                    @if($card->status == 'active')
                                        <button class="btn btn-sm" style="border-radius: 0.6rem; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" onclick="toggleStatus({{ $card->id }}, 'inactive')" title="{{ __('Disable') }}">
                                            <i class="ph ph-power" style="color: var(--warning);"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-sm" style="border-radius: 0.6rem; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-color: var(--success);" onclick="toggleStatus({{ $card->id }}, 'active')" title="{{ __('Activate') }}">
                                            <i class="ph ph-power" style="color: var(--success);"></i>
                                        </button>
                                    @endif
                                    <button class="btn btn-sm edit-btn" style="border-radius: 0.6rem; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                            data-id="{{ $card->id }}" data-uid="{{ $card->uid }}" data-name="{{ $card->teacher?->name ?? '' }}" title="{{ __('Edit UID') }}">
                                        <i class="ph ph-pencil-simple"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" style="border-radius: 0.6rem; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" onclick="deleteCard({{ $card->id }})" title="{{ __('Delete') }}">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                <i class="ph ph-contactless-payment" style="font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 0.75rem;"></i>
                                {{ __('No RFID cards registered yet.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </div>
</div>
</div>

{{-- ── Edit Modal ── --}}
<div id="editUidModal" class="modal-overlay">
    <div class="card" style="width: 460px; border-radius: 2.5rem; padding: 2.25rem; border: 1px solid var(--primary);">
        <h3 style="margin-bottom: 0.75rem; color: var(--primary); font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
            <i class="ph ph-identification-card"></i> {{ __('Update Credential') }}
        </h3>
        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">
            {{ __('Re-assigning secure card for') }}: <strong id="editTeacherNameKh" style="color: var(--primary);"></strong> (<strong id="editTeacherName" style="color: var(--text-primary);"></strong>)
        </p>

        <!-- Animated Virtual ID Card in Modal -->
        <div class="virtual-card-container" style="display:block; margin-bottom:1.5rem;">
            <div class="virtual-id-card" style="height:165px; padding:1.25rem;">
                <div class="card-holographic-shine"></div>
                <div class="d-flex justify-between align-center">
                    <div class="card-chip"></div>
                    <div class="card-animated-waves">
                        <svg viewBox="0 0 50 50" fill="none" style="width:34px; height:34px;">
                            <path class="wave wave-3" d="M35 15 C42 22, 42 28, 35 35" stroke="var(--primary)" stroke-width="3" stroke-linecap="round"/>
                            <path class="wave wave-2" d="M28 19 C33 24, 33 26, 28 31" stroke="var(--primary)" stroke-width="3" stroke-linecap="round"/>
                            <path class="wave wave-1" d="M21 23 C23 25, 23 25, 21 27" stroke="var(--primary)" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <div class="card-uid" id="modalPreviewUid" style="margin-top:0.75rem; font-size:1.05rem;">XXXX-XXXX-XXXX</div>
                <div class="card-holder" id="modalPreviewName" style="font-size:0.85rem;">PERSONNEL CARD</div>
                <div class="card-brand" style="bottom:1rem; right:1.25rem;">NTTI SECURITY SYSTEM</div>
            </div>
        </div>

        <div class="form-group">
            <label>{{ __('Scan New UID') }}</label>
            <div class="d-flex gap-3">
                <input type="text" id="new-uid-input" class="form-control" style="font-family: 'JetBrains Mono', monospace; border-radius: 1rem; text-align: center;">
                <button type="button" id="editScanBtn" class="btn btn-primary" onclick="startEditHardwareScan()" style="border-radius: 1rem; width: 50px;">
                    <i class="ph ph-contactless-payment"></i>
                </button>
            </div>
            <div id="edit-uid-status" class="mt-2"></div>
        </div>

        <div class="d-flex gap-3 mt-4">
            <button class="btn btn-secondary" onclick="closeEditModal()" style="flex: 1; border-radius: 1.25rem; padding: 0.9rem;">{{ __('Cancel') }}</button>
            <button class="btn btn-primary" onclick="submitEditUid()" style="flex: 1; border-radius: 1.25rem; padding: 0.9rem; font-weight: 800; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.25); transition: all 0.2s ease;" id="saveUidBtn">{{ __('Commit Update') }}</button>
        </div>
    </div>
</div>



@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Virtual Card Updates ──
    window.updateVirtualCard = function() {
        const select = document.getElementById('teacher_select');
        const container = document.getElementById('cardPreviewContainer');
        const nameEl = document.getElementById('previewName');
        const uidEl = document.getElementById('previewUid');
        
        if (select.value) {
            const name = select.options[select.selectedIndex].dataset.name;
            nameEl.textContent = name;
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }

    const uidInput      = document.getElementById('uid-input');
    const statusText    = document.getElementById('uid-status');
    const scanWidget    = document.getElementById('scanWidget');
    const scanPrompt    = document.getElementById('scanPrompt');
    const scanCountdown = document.getElementById('scanCountdown');
    const scanProgress  = document.getElementById('scanProgress');
    const startScanBtn  = document.getElementById('startScanBtn');
    const previewUid    = document.getElementById('previewUid');

    let scanTimer    = null;
    let pollTimer    = null;
    let secondsLeft  = 0;
    const SCAN_DURATION = 30; // seconds

    // ── Hardware Scan: Start countdown + poll for pending UID ──
    window.startHardwareScan = function() {
        if (!uidInput) return;
        uidInput.value = '';
        statusText.innerHTML = '';
        secondsLeft = SCAN_DURATION;

        scanWidget.style.display = 'block';
        scanWidget.classList.add('scan-active');
        scanWidget.classList.remove('scan-found', 'scan-duplicate');
        startScanBtn.disabled = true;
        startScanBtn.innerHTML = '<i class="ph ph-spinner ph-spin" style="font-size:1.4rem;"></i> <span>{{ app()->getLocale() === "km" ? "កំពុងស្គេន..." : "Scanning..." }}</span>';

        scanCountdown.textContent = secondsLeft + 's';
        scanProgress.style.width = '100%';
        scanPrompt.textContent = '{{ app()->getLocale() === "km" ? "សូមស្គេនកាតនៅលើឧបករណ៍អាន..." : "Tap your card on the reader now..." }}';

        scanTimer = setInterval(() => {
            secondsLeft--;
            scanCountdown.textContent = secondsLeft + 's';
            scanProgress.style.width = ((secondsLeft / SCAN_DURATION) * 100) + '%';
            if (secondsLeft <= 0) stopHardwareScan(false);
        }, 1000);

        pollTimer = setInterval(pollForPendingUid, 1000);
    }

    window.stopHardwareScan = function(found) {
        clearInterval(scanTimer);
        clearInterval(pollTimer);
        scanTimer = null;
        pollTimer = null;

        startScanBtn.disabled = false;
        startScanBtn.innerHTML = '<i class="ph ph-contactless-payment"></i> <span>{{ app()->getLocale() === "km" ? "ចាប់ផ្តើមស្គេនកាត" : "Start Card Scan" }}</span>';

        if (!found) {
            scanWidget.classList.remove('scan-active');
            scanPrompt.textContent = '{{ app()->getLocale() === "km" ? "អស់ពេល! សូមចុច Start Card Scan ដើម្បីព្យាយាមម្តងទៀត" : "Timed out! Click Start Card Scan to try again." }}';
            setTimeout(() => { scanWidget.style.display = 'none'; }, 3000);
        }
    }

    async function pollForPendingUid() {
        try {
            console.log("Polling for hardware scan...");
            const res = await window.fetchApi('{{ route("api.rfid.pending-scan") }}');
            if (res.found) {
                console.log("Card Found:", res.uid);
                uidInput.value = res.uid;
                if (previewUid) previewUid.textContent = res.uid;
                scanWidget.classList.remove('scan-active');
                scanWidget.classList.add('scan-found');
                scanPrompt.textContent = '{{ __("Card detected!") }}';
                if (window.showToast) window.showToast('{{ __("Card detected!") }}', 'success');
                stopHardwareScan(true);
                checkUid();
            }
        } catch (e) { console.error("Polling error:", e); }
    }

    if (uidInput) {
        let typingTimer;
        uidInput.addEventListener('input', () => {
            clearTimeout(typingTimer);
            statusText.innerHTML = '';
            uidInput.value = uidInput.value.toUpperCase();
            if (uidInput.value.length > 3) typingTimer = setTimeout(checkUid, 400);
        });
    }

    async function checkUid() {
        const uid = uidInput.value.trim();
        if (!uid) return;
        try {
            const res = await window.fetchApi(`{{ route('api.rfid.check') }}?uid=${encodeURIComponent(uid)}`);
            if (res.exists) {
                scanWidget.classList.remove('scan-active', 'scan-found');
                scanWidget.classList.add('scan-duplicate');
                const name = res.card.teacher?.name || 'Unknown';
                statusText.innerHTML = `<div style="background:rgba(239,68,68,0.1); border:1px solid var(--danger); padding:0.75rem; border-radius:4px; color:var(--danger); font-weight:600; display:flex; align-items:center; gap:0.5rem;">
                    <i class="ph ph-warning-octagon" style="font-size:1.25rem;"></i>
                    <div>
                        <div style="font-size:0.7rem; text-transform:uppercase; opacity:0.8;">{{ __('Duplicate UID Detected') }}</div>
                        <div style="font-size:0.9rem;">${name}</div>
                    </div>
                </div>`;
            } else {
                scanWidget.classList.remove('scan-active', 'scan-duplicate');
                scanWidget.classList.add('scan-found');
                statusText.innerHTML = `<div style="background:rgba(16,185,129,0.1); border:1px solid var(--success); padding:0.75rem; border-radius:4px; color:var(--success); font-weight:600; display:flex; align-items:center; gap:0.5rem;">
                    <i class="ph ph-check-circle" style="font-size:1.25rem;"></i>
                    <div>
                        <div style="font-size:0.7rem; text-transform:uppercase; opacity:0.8;">{{ __('UID Available') }}</div>
                        <div style="font-size:0.9rem;">{{ __('Ready to assign') }}</div>
                    </div>
                </div>`;
            }
        } catch (e) { console.error(e); }
    }

    window.submitAssign = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('assignBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner"></i> Assigning...';
        try {
            const data = Object.fromEntries(new FormData(e.target).entries());
            await window.fetchApi('{{ route("api.rfid.store") }}', { method: 'POST', body: JSON.stringify(data) });
            window.location.reload();
        } catch (err) {
            alert(err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-plus-circle"></i> {{ __("Assign Card") }}';
        }
    }

    window.toggleStatus = async function(id, newStatus) {
        try {
            await window.fetchApi(`{{ url('/api-web/rfid-cards') }}/${id}`, { method: 'PUT', body: JSON.stringify({ status: newStatus }) });
            window.location.reload();
        } catch (e) { alert(e.message); }
    }

    window.deleteCard = async function(id) {
        if (!await confirm('{{ __("Remove this card?") }}')) return;
        try {
            await window.fetchApi(`{{ url('/api-web/rfid-cards') }}/${id}`, { method: 'DELETE' });
            window.location.reload();
        } catch (e) { alert(e.message); }
    }

    window.filterDept = function() {
        const dept = document.getElementById('deptFilter').value;
        const url = new URL(window.location.href);
        if (dept) url.searchParams.set('department', dept);
        else url.searchParams.delete('department');
        window.location.href = url.toString();
    }

    window.searchCards = function() {
        const query = document.getElementById('cardSearch').value.toLowerCase().trim();
        document.querySelectorAll('.card-row').forEach(row => {
            const data = row.getAttribute('data-name') || '';
            row.style.display = !query || data.includes(query) ? '' : 'none';
        });
    }

    // ── Edit UID Modal Logic ──
    let currentEditId = null;
    let editPollTimer = null;

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.edit-btn');
        if (btn) {
            const id = btn.dataset.id;
            const uid = btn.dataset.uid;
            const name = btn.dataset.name;
            const nameKh = btn.closest('tr').querySelector('td div:first-child').textContent;
            window.openEditModal(id, uid, name, nameKh);
        }
    });

    window.openEditModal = function(id, oldUid, name, nameKh) {
        currentEditId = id;
        document.getElementById('editTeacherName').textContent = name;
        document.getElementById('editTeacherNameKh').textContent = nameKh;
        const modalUid = document.getElementById('modalPreviewUid');
        const modalName = document.getElementById('modalPreviewName');
        if (modalUid) modalUid.textContent = oldUid || 'XXXX-XXXX-XXXX';
        if (modalName) modalName.textContent = name || 'PERSONNEL CARD';

        const newUidInput = document.getElementById('new-uid-input');
        if (newUidInput) {
            newUidInput.value = oldUid || '';
            newUidInput.oninput = function() {
                if (modalUid) modalUid.textContent = this.value.toUpperCase() || 'XXXX-XXXX-XXXX';
            };
        }
        document.getElementById('edit-uid-status').innerHTML = '';
        const modal = document.getElementById('editUidModal');
        if (modal) {
            modal.classList.add('active');
        } else {
            console.error('Modal element not found');
        }
    }

    window.closeEditModal = function() {
        const modal = document.getElementById('editUidModal');
        if (modal) {
            modal.classList.remove('active');
        }
        clearInterval(editPollTimer);
        const btn = document.getElementById('editScanBtn');
        if (btn) {
            btn.innerHTML = '<i class="ph ph-contactless-payment"></i>';
            btn.disabled = false;
        }
    }

    window.startEditHardwareScan = function() {
        const btn = document.getElementById('editScanBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner ph-spin"></i>';
        
        editPollTimer = setInterval(async () => {
            try {
                const res = await window.fetchApi('{{ route("api.rfid.pending-scan") }}');
                if (res.found) {
                    document.getElementById('new-uid-input').value = res.uid;
                    const modalUid = document.getElementById('modalPreviewUid');
                    if (modalUid) modalUid.textContent = res.uid;
                    btn.innerHTML = '<i class="ph ph-check" style="color:var(--success);"></i>';
                    clearInterval(editPollTimer);
                    btn.disabled = false;
                    if (window.showToast) window.showToast('{{ __("Card detected!") }}', 'success');
                }
            } catch (e) { console.error(e); }
        }, 1000);

        setTimeout(() => {
            if (editPollTimer) {
                clearInterval(editPollTimer);
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-contactless-payment"></i>';
            }
        }, 30000);
    }

    window.submitEditUid = async function() {
        const newUid = document.getElementById('new-uid-input').value.trim();
        if (!newUid) return alert('{{ __("Please scan or enter a new UID") }}');
        const btn = document.getElementById('saveUidBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Saving...';
        try {
            await window.fetchApi(`{{ url('/api-web/rfid-cards') }}/${currentEditId}`, {
                method: 'PUT',
                body: JSON.stringify({ uid: newUid, status: 'active' })
            });
            window.location.reload();
        } catch (e) {
            alert(e.message);
            btn.disabled = false;
            btn.innerHTML = '{{ __("Save Changes") }}';
        }
    }

    // Auto-start scan if action=assign is in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'assign') {
        setTimeout(window.startHardwareScan, 500);
    }
});
</script>
@endpush

