@extends('layouts.app')

@section('title', __('Scan Station'))

@push('styles')
<style>
.scan-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1000px) {
    .scan-layout { grid-template-columns: 1fr; }
}

/* ── Premium Stat Cards ── */
.premium-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.p-stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    padding: 1.25rem;
    border-radius: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}
.p-stat-card:hover { transform: translateY(-3px); border-color: var(--primary); }
.p-stat-icon {
    width: 48px; height: 48px; border-radius: 1rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
}
.p-stat-info .label { font-size: 0.7rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; }
.p-stat-info .value { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); line-height: 1.2; }

/* ── Terminal Scanner ── */
.terminal-card {
    background: var(--bg-card);
    /* Removed blur */
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    padding: 2.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.scanner-hologram {
    position: relative;
    width: 100%;
    height: 180px;
    margin: 0 0 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--primary-rgb), 0.03);
    border: 1.5px dashed rgba(var(--primary-rgb), 0.25);
    border-radius: 1.25rem;
    overflow: hidden;
    box-shadow: inset 0 0 30px rgba(var(--primary-rgb), 0.05);
    transition: all 0.3s ease;
}
.scanning.scanner-hologram {
    border-color: var(--primary);
    box-shadow: inset 0 0 40px rgba(var(--primary-rgb), 0.15), 0 0 25px rgba(var(--primary-rgb), 0.25);
}

.scanner-deck-laser {
    position: absolute;
    top: 0;
    left: 0;
    width: 35%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(var(--primary-rgb), 0.25), transparent);
    animation: laserSweep 3s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    pointer-events: none;
}
@keyframes laserSweep {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(350%); }
}

.scanner-main-icon {
    font-size: 5rem;
    color: var(--primary);
    filter: drop-shadow(0 0 15px rgba(var(--primary-rgb), 0.6));
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.scanning .scanner-main-icon { transform: scale(1.1); color: #fff; }

/* ── Full-Width Lottie RFID Card Scan Bay ── */
.lottie-card-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5;
}
.rfid-lottie-svg {
    width: 100%;
    height: 100%;
    max-height: 160px;
    filter: drop-shadow(0 8px 20px rgba(var(--primary-rgb), 0.35));
}
.animated-rfid-card {
    animation: cardHoverWide 3.5s ease-in-out infinite;
    transform-origin: 200px 80px;
}
.wave {
    animation: wavePulseWide 2s ease-in-out infinite;
}
.wave-1 { animation-delay: 0s; }
.wave-2 { animation-delay: 0.4s; }
.wave-3 { animation-delay: 0.8s; }

@keyframes cardHoverWide {
    0%, 100% {
        transform: translateY(0px) scale(1);
    }
    50% {
        transform: translateY(-6px) scale(1.02);
    }
}

@keyframes wavePulseWide {
    0%, 100% {
        opacity: 0.25;
        stroke: rgba(var(--primary-rgb), 0.4);
    }
    50% {
        opacity: 1;
        stroke: var(--primary);
    }
}

.scanning .animated-rfid-card {
    animation: cardScanActive 0.7s ease-in-out infinite;
}

@keyframes cardScanActive {
    0%, 100% { transform: scale(1) translateY(0); filter: drop-shadow(0 0 20px var(--primary)); }
    50% { transform: scale(1.08) translateY(-8px); filter: drop-shadow(0 0 30px #00b894); }
}

.terminal-info { margin-bottom: 1.5rem; }
.terminal-clock { font-size: 2.5rem; font-weight: 800; color: var(--text-primary); font-family: 'JetBrains Mono', monospace; }
.terminal-shift { font-size: 0.85rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 2px; }

/* ── Identity Card Preview ── */
.identity-preview {
    background: rgba(var(--primary-rgb), 0.05);
    border: 1px solid rgba(var(--primary-rgb), 0.1);
    border-radius: 1.25rem;
    padding: 1.25rem;
    margin-top: 1.5rem;
    display: none;
    text-align: left;
    animation: slideUp 0.4s ease;
}
.id-content { display: flex; align-items: center; gap: 1.25rem; }
.id-photo { 
    width: 60px; 
    height: 60px; 
    border-radius: 1rem; 
    object-fit: cover; 
    border: 2px solid var(--primary);
    image-rendering: -webkit-optimize-contrast;
}
.id-meta h4 { margin: 0; font-size: 1.1rem; font-weight: 800; }
.id-meta p { margin: 0; font-size: 0.8rem; color: var(--text-secondary); }

/* ── Timeline Log ── */
.timeline { position: relative; padding-left: 1.5rem; margin-top: 1.5rem; }
.timeline::before {
    content: '';
    position: absolute;
    left: 4px; top: 0; bottom: 0;
    width: 2px;
    background: var(--border);
}
.timeline-item {
    position: relative;
    padding-bottom: 1.25rem;
    animation: slideIn 0.4s ease backwards;
}
.timeline-dot {
    position: absolute;
    left: -1.5rem;
    top: 4px;
    width: 10px; height: 10px;
    border-radius: 50%;
    background: var(--border);
    border: 2px solid var(--bg-card);
    z-index: 2;
}
.timeline-dot.success { background: var(--success); box-shadow: 0 0 10px var(--success); }
.timeline-dot.info    { background: var(--info); box-shadow: 0 0 10px var(--info); }
.timeline-dot.error   { background: var(--danger); }
.timeline-dot.warning { background: var(--warning); }

.timeline-content {
    background: rgba(255,255,255,0.02);
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    border: 1px solid var(--border);
}
.timeline-time { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 2px; }
.timeline-msg { font-size: 0.85rem; font-weight: 600; }

/* ── Teacher List ── */
.teacher-side-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 1.5rem;
    padding: 1.5rem;
    height: calc(100vh - 200px);
    display: flex;
    flex-direction: column;
}
.teacher-scroll { flex: 1; overflow-y: auto; margin-top: 1rem; padding-right: 0.5rem; }
.teacher-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 1rem;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 0.5rem;
    background: rgba(255,255,255,0.02);
}
.teacher-card:hover { border-color: var(--primary); background: rgba(var(--primary-rgb), 0.05); }
.teacher-card.selected { border-color: var(--primary); background: rgba(var(--primary-rgb), 0.1); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

.t-avatar { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: var(--bg-dark); flex-shrink: 0; }
.t-avatar img { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    transform: translateZ(0);
    backface-visibility: hidden;
}
.t-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--primary); }

.t-info h5 { margin: 0; font-size: 0.9rem; font-weight: 700; }
.t-info p { margin: 0; font-size: 0.7rem; color: var(--text-secondary); }

@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
@keyframes pulse { 0%, 100% { transform: scale(1); opacity: 0.3; } 50% { transform: scale(1.1); opacity: 0.6; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
@keyframes slideIn { from { opacity: 0; transform: translateX(-15px); } to { opacity: 1; transform: translateX(0); } }

.btn-terminal {
    width: 100%;
    padding: 1.1rem;
    border-radius: 1.25rem;
    background: var(--primary);
    color: #000;
    font-weight: 800;
    font-size: 1rem;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-top: 1rem;
    transition: all 0.3s;
}
.btn-terminal:disabled { opacity: 0.4; filter: grayscale(1); cursor: not-allowed; }
.btn-terminal:not(:disabled):hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(var(--primary-rgb), 0.3); }
</style>
@endpush

@section('content')
<div class="d-flex justify-between align-center" style="margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 class="page-title">{{ __('Manual Terminal') }}</h1>
        <p style="color:var(--text-secondary); font-size:0.8rem; margin-top:0.25rem;">
            <i class="ph ph-terminal-window"></i> {{ __('Admin intervention and manual attendance override') }}
        </p>
    </div>
    <div id="deviceBadge" style="display:flex;align-items:center;gap:0.6rem;padding:0.5rem 1rem;border-radius:1rem;background:rgba(255,255,255,0.03);border:1px solid var(--border);font-size:0.8rem;font-weight:700;">
        <span id="deviceDot" style="width:8px;height:8px;border-radius:50%;background:var(--text-muted);display:inline-block;"></span>
        <span id="deviceLabel">{{ __('Hardware Offline') }}</span>
    </div>
</div>

{{-- ── Premium Stats ── --}}
<div class="premium-stats">
    <div class="p-stat-card">
        <div class="p-stat-icon" style="background:rgba(var(--primary-rgb),0.1); color:var(--primary);"><i class="ph ph-users-three"></i></div>
        <div class="p-stat-info">
            <div class="label">{{ __('Today Total') }}</div>
            <div class="value animate-val" id="stat-total">{{ $totalScans ?? 0 }}</div>
        </div>
    </div>
    <div style="grid-column: span 2; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; background: rgba(255,255,255,0.02); padding: 0.75rem; border-radius: 1.5rem; border: 1px solid var(--border);">
        <div class="p-stat-card" style="background: transparent; border: none; padding: 0.5rem 1rem;">
            <div class="p-stat-icon" style="background:rgba(16,185,129,0.1); color:#10b981;"><i class="ph ph-sun-dim"></i></div>
            <div class="p-stat-info">
                <div class="label">{{ __('Morning') }}</div>
                <div class="value animate-val" id="stat-morning">{{ $morningScans ?? 0 }}</div>
            </div>
        </div>
        <div class="p-stat-card" style="background: transparent; border: none; padding: 0.5rem 1rem;">
            <div class="p-stat-icon" style="background:rgba(245,158,11,0.1); color:#f59e0b;"><i class="ph ph-cloud-sun"></i></div>
            <div class="p-stat-info">
                <div class="label">{{ __('Afternoon') }}</div>
                <div class="value animate-val" id="stat-afternoon">{{ $afternoonScans ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<div class="scan-layout">

    {{-- LEFT: Terminal Scanner --}}
    <div>
        <div class="terminal-card">
            <div class="scanner-hologram" id="scannerRing">
                <div class="scanner-deck-laser"></div>
                <div id="scannerIcon" class="lottie-card-wrapper">
                    <svg class="rfid-lottie-svg" viewBox="0 0 400 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Corner Tech Accents -->
                        <path d="M20 30 H40 M20 30 V50" stroke="var(--primary)" stroke-width="2" opacity="0.6"/>
                        <path d="M380 30 H360 M380 30 V50" stroke="var(--primary)" stroke-width="2" opacity="0.6"/>
                        <path d="M20 130 H40 M20 130 V110" stroke="var(--primary)" stroke-width="2" opacity="0.6"/>
                        <path d="M380 130 H360 M380 130 V110" stroke="var(--primary)" stroke-width="2" opacity="0.6"/>

                        <!-- Left & Right Wireless Signal Waves -->
                        <!-- Left Waves -->
                        <path class="wave wave-3" d="M80 50 C60 70, 60 90, 80 110" stroke="var(--primary)" stroke-width="3.5" stroke-linecap="round"/>
                        <path class="wave wave-2" d="M95 60 C80 73, 80 87, 95 100" stroke="var(--primary)" stroke-width="3.5" stroke-linecap="round"/>
                        <path class="wave wave-1" d="M110 70 C100 76, 100 84, 110 90" stroke="var(--primary)" stroke-width="3.5" stroke-linecap="round"/>

                        <!-- Right Waves -->
                        <path class="wave wave-3" d="M320 50 C340 70, 340 90, 320 110" stroke="var(--primary)" stroke-width="3.5" stroke-linecap="round"/>
                        <path class="wave wave-2" d="M305 60 C320 73, 320 87, 305 100" stroke="var(--primary)" stroke-width="3.5" stroke-linecap="round"/>
                        <path class="wave wave-1" d="M290 70 C300 76, 300 84, 290 90" stroke="var(--primary)" stroke-width="3.5" stroke-linecap="round"/>

                        <!-- Center Horizontal RFID Card -->
                        <g class="animated-rfid-card">
                            <!-- Card Base Drop Shadow -->
                            <rect x="123" y="33" width="154" height="94" rx="12" fill="rgba(0,0,0,0.5)"/>
                            <!-- Card Main Body -->
                            <rect x="120" y="30" width="154" height="94" rx="12" fill="url(#cardGradWide)" stroke="var(--primary)" stroke-width="2"/>
                            <!-- Metallic Glass Shimmer -->
                            <rect x="120" y="30" width="154" height="94" rx="12" fill="url(#shineGradWide)" opacity="0.4"/>
                            
                            <!-- Gold Chip -->
                            <rect x="140" y="52" width="26" height="20" rx="4" fill="url(#chipGradWide)" stroke="#b58900" stroke-width="1"/>
                            <path d="M147 52 V72 M159 52 V72 M140 62 H166" stroke="#856404" stroke-width="1"/>

                            <!-- Wireless Wave Symbol on Card -->
                            <path d="M245 57 C249 54, 249 48, 245 45" stroke="rgba(255,255,255,0.85)" stroke-width="2" stroke-linecap="round"/>
                            <path d="M249 61 C255 56, 255 46, 249 41" stroke="rgba(255,255,255,0.85)" stroke-width="2" stroke-linecap="round"/>
                            <path d="M253 65 C261 58, 261 44, 253 37" stroke="rgba(255,255,255,0.85)" stroke-width="2" stroke-linecap="round"/>

                            <!-- Card Branding Bar -->
                            <rect x="140" y="94" width="70" height="5" rx="2.5" fill="rgba(255,255,255,0.3)"/>
                            <rect x="220" y="94" width="30" height="5" rx="2.5" fill="var(--primary)" opacity="0.8"/>
                        </g>

                        <!-- Gradients -->
                        <defs>
                            <linearGradient id="cardGradWide" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#1e293b"/>
                                <stop offset="50%" stop-color="#0f172a"/>
                                <stop offset="100%" stop-color="#020617"/>
                            </linearGradient>
                            <linearGradient id="shineGradWide" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="var(--primary)" stop-opacity="0.7"/>
                                <stop offset="40%" stop-color="#3b82f6" stop-opacity="0.2"/>
                                <stop offset="100%" stop-color="transparent"/>
                            </linearGradient>
                            <linearGradient id="chipGradWide" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#ffe066"/>
                                <stop offset="100%" stop-color="#f59e0b"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>

            <div class="terminal-info" style="background:rgba(255,255,255,0.03); border:1px solid var(--border); padding:1.5rem; border-radius:1.5rem; margin-top:1rem;">
                <div class="terminal-shift" id="shiftLabel" style="margin-bottom:0.5rem;">{{ __('Detecting Shift...') }}</div>
                <div class="terminal-clock" id="scanClock">00:00:00</div>
            </div>

            {{-- Selected Teacher Identity Card --}}
            <div class="identity-preview" id="idPreview">
                <div class="id-content">
                    <div id="idPhotoContainer">
                        <img id="idPhoto" src="" class="id-photo">
                        <div id="idPhotoPlaceholder" class="id-photo" style="display:none; align-items:center; justify-content:center; background:var(--primary); color:#000; font-weight:800; font-size:1.5rem;">?</div>
                    </div>
                    <div class="id-meta">
                        <h4 id="idNameKh" style="color:var(--primary); margin:0; line-height:1.1; font-weight: 800;" translate="no" class="notranslate"></h4>
                        <h5 id="idName" style="margin:0; font-size:1.1rem; font-weight: 700; opacity:0.8;">Teacher Name</h5>
                        <p id="idDept" style="margin-top: 0.25rem;">Department Name</p>
                        <div id="idBadge" class="badge badge-primary mt-1" style="font-size:0.6rem;">{{ __('READY TO SCAN') }}</div>
                    </div>
                </div>
            </div>

            <div class="result-flash" id="resultFlash" style="margin-top: 1.5rem; border-radius: 1rem;">
                <div class="result-name" id="resultName" style="font-size:1rem;"></div>
                <div class="result-meta" id="resultMeta" style="font-size:0.75rem;"></div>
            </div>
        </div>

        {{-- Timeline Log --}}
        <div class="card mt-4" style="border-radius: 1.5rem;">
            <div class="card-header">
                <h3><i class="ph ph-activity" style="margin-right:0.4rem;"></i>{{ __("Live Activity Log") }}</h3>
                <span class="badge badge-success">LIVE</span>
            </div>
            <div style="padding: 1.5rem;">
                <div class="timeline" id="scanLog">
                    <p style="color:var(--text-muted);text-align:center;padding:1rem;font-size:0.85rem;">{{ __('No activity detected.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Teacher Selection --}}
    <div class="teacher-side-card">
        <h3 style="font-size: 1.1rem; margin-bottom: 1rem;"><i class="ph ph-users-four"></i> {{ __('Teacher Directory') }}</h3>
        
        <div style="display:flex; flex-direction:column; gap:0.5rem;">
            <div class="input-wrapper" style="position:relative;">
                <i class="ph ph-magnifying-glass" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--primary);"></i>
                <input type="text" id="teacherSearch" class="form-control" style="padding-left:2.5rem; border-radius:1rem;" placeholder="{{ __('Search...') }}">
            </div>
            <div class="input-wrapper" style="position:relative;">
                <i class="ph ph-buildings" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--primary);"></i>
                <select id="deptFilter" class="form-control" style="padding-left:2.5rem; border-radius:1rem;">
                    <option value="">{{ __('All Departments') }}</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->name }}">{{ app()->getLocale() == 'km' ? ($dept->name_kh ?: $dept->name) : $dept->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="teacher-scroll" id="teacherList">
            @foreach($teachers as $teacher)
            <div class="teacher-card stagger-item"
                 style="animation-delay: {{ $loop->index * 0.03 }}s"
                 data-id="{{ $teacher->id }}"
                 data-name="{{ $teacher->name }}"
                 data-name-kh="{{ $teacher->name_kh }}"
                 data-dept="{{ $teacher->department }}"
                 data-empid="{{ $teacher->employee_id }}"
                 data-photo="{{ $teacher->photo ? to_asset_url($teacher->photo) : '' }}"
                 onclick="selectTeacher(this)">
                <div class="t-avatar">
                    @if($teacher->photo)
                        <img src="{{ to_asset_url($teacher->photo) }}" alt="">
                    @else
                        <div class="t-placeholder">{{ strtoupper(substr($teacher->name, 0, 1)) }}</div>
                    @endif
                </div>
                <div class="t-info">
                    <h5 style="color:var(--primary); font-size:0.95rem; margin-bottom: 2px;" translate="no" class="notranslate">{{ $teacher->name_kh ?: '' }}</h5>
                    <h6 style="margin:0; font-size:0.8rem; font-weight:700; opacity:0.8;">{{ $teacher->name }}</h6>
                    <p style="margin-top: 2px; font-size:0.7rem;">
                        @php
                            $deptObj = $departments->firstWhere('name', $teacher->department);
                            $deptLabel = $deptObj ? (app()->getLocale() == 'km' ? ($deptObj->name_kh ?: $deptObj->name) : $deptObj->name) : $teacher->department;
                        @endphp
                        {{ $deptLabel }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        <div style="margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 1rem;">
            <button class="btn-terminal" id="scanBtn" disabled onclick="doAdminScan()">
                <i class="ph ph-fingerprint"></i> {{ __('Authenticate & Scan') }}
            </button>
            <p id="selectedInfo" style="text-align:center; font-size:0.7rem; color:var(--text-muted); margin-top:0.5rem;">
                {{ __('Select a profile to begin') }}
            </p>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.3} }
</style>
<script>
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

let selectedTeacherId = null;

let scanLog = [];
let lastUpdatedAt = '{{ now()->format('Y-m-d H:i:s.u') }}';

// Audio objects
const audioSuccess = new Audio('https://assets.mixkit.co/active_storage/sfx/2354/2354-preview.mp3');
const audioError = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');

function playSound(type) {
    if (type === 'success') audioSuccess.play().catch(e => console.log('Audio disabled by browser'));
    else audioError.play().catch(e => console.log('Audio disabled by browser'));
}

// ── Attendance Rules from Settings ───────────────
function timeToDecimal(t) {
    const [hh, mm] = t.split(':').map(Number);
    return hh + mm / 60;
}
const SYSTEM_OPEN      = timeToDecimal('{{ $systemOpen }}');
const SYSTEM_CLOSE     = timeToDecimal('{{ $systemClose }}');
const MORNING_START    = timeToDecimal('{{ $morningStart }}');
const MORNING_END      = timeToDecimal('{{ $morningEnd }}');
const AFTERNOON_START  = timeToDecimal('{{ $afternoonStart }}');
const AFTERNOON_END    = timeToDecimal('{{ $afternoonEnd }}');

// ── Clock & Shift detection ──────────────────────
function updateScanClock() {
    const now = new Date();
    document.getElementById('scanClock').textContent =
        now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });

    const h = now.getHours() + now.getMinutes() / 60;
    let shift = '{{ __("No Active Shift") }}';
    window.systemClosed = false;

    if (h >= SYSTEM_OPEN && h < SYSTEM_CLOSE) {
        // System is open — determine which shift
        if (h >= MORNING_START && h < MORNING_END) {
            shift = '☀️  {{ __("Morning Shift") }} ({{ $morningStart }} - {{ $morningEnd }})';
        } else if (h >= AFTERNOON_START && h < AFTERNOON_END) {
            shift = '🌤  {{ __("Afternoon Shift") }} ({{ $afternoonStart }} - {{ $afternoonEnd }})';
        } else {
            // Between shifts but still within system hours
            shift = '⏳  {{ __("Between Shifts") }}';
        }
    } else {
        shift = '🌙  {{ __("System Closed") }}';
        window.systemClosed = true;
    }

    document.getElementById('shiftLabel').textContent = shift;

    // Enforce button state based on system schedule
    if (window.systemClosed) {
        document.getElementById('scanBtn').disabled = true;
        document.getElementById('selectedInfo').innerHTML = '{{ __("System Closed") }}';
    } else if (selectedTeacherId) {
        document.getElementById('scanBtn').disabled = false;
        document.getElementById('selectedInfo').innerHTML = `{{ __('READY TO AUTHENTICATE') }}`;
    }
}
setInterval(updateScanClock, 1000);
updateScanClock();

// ── Teacher selection ────────────────────────────
function selectTeacher(el) {
    document.querySelectorAll('.teacher-card').forEach(i => i.classList.remove('selected'));
    el.classList.add('selected');
    selectedTeacherId = el.dataset.id;
    
    const name = el.dataset.name;
    const nameKh = el.dataset.nameKh;
    const dept = el.dataset.dept;
    const photo = el.dataset.photo;
    
    // Update Identity Preview
    document.getElementById('idNameKh').textContent = nameKh || '';
    document.getElementById('idName').textContent = name;
    document.getElementById('idDept').textContent = window.transDept(dept);

    const idPhoto = document.getElementById('idPhoto');
    const idPlaceholder = document.getElementById('idPhotoPlaceholder');
    
    if (photo) {
        idPhoto.src = photo;
        idPhoto.style.display = 'block';
        idPlaceholder.style.display = 'none';
    } else {
        idPhoto.style.display = 'none';
        idPlaceholder.style.display = 'flex';
        idPlaceholder.textContent = name.charAt(0).toUpperCase();
    }
    
    document.getElementById('idPreview').style.display = 'block';
    
    if (!window.systemClosed) {
        document.getElementById('scanBtn').disabled = false;
        document.getElementById('selectedInfo').innerHTML = `{{ __('READY TO AUTHENTICATE') }}`;
    } else {
        document.getElementById('scanBtn').disabled = true;
        document.getElementById('selectedInfo').innerHTML = `{{ __('System Closed') }}`;
    }
}

// ── Teacher search filter ────────────────────────
function filterTeachers() {
    const q = document.getElementById('teacherSearch').value.toLowerCase().trim();
    const dept = document.getElementById('deptFilter').value.toLowerCase().trim();

    document.querySelectorAll('.teacher-card').forEach(item => {
        const itemDept = (item.dataset.dept || '').toLowerCase().trim();
        const itemName = (item.dataset.name || '').toLowerCase().trim();
        const itemNameKh = (item.dataset.nameKh || '').toLowerCase().trim();
        const itemEmpId = (item.dataset.empid || '').toLowerCase().trim();

        const nameMatch = itemName.includes(q) || itemNameKh.includes(q) || itemEmpId.includes(q);
        const deptMatch = dept === '' || itemDept === dept;
        
        const visible = nameMatch && deptMatch;
        item.style.display = visible ? 'flex' : 'none';

        // Clear selection if it becomes hidden
        if (!visible && item.classList.contains('selected')) {
            selectedTeacherId = null;
            item.classList.remove('selected');
            document.getElementById('scanBtn').disabled = true;
            document.getElementById('idPreview').style.display = 'none';
            document.getElementById('selectedInfo').innerHTML = '{{ __("No teacher selected") }}';
        }
    });
}

document.getElementById('teacherSearch').addEventListener('input', filterTeachers);
document.getElementById('deptFilter').addEventListener('change', filterTeachers);

// ── Admin Scan ───────────────────────────────────
async function doAdminScan() {
    if (!selectedTeacherId) return;

    const btn = document.getElementById('scanBtn');
    const ring = document.getElementById('scannerRing');
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-circle-notch" style="animation:spin 1s linear infinite"></i> {{ __("Scanning...") }}';
    ring.classList.add('scanning');

    try {
        const res = await window.fetchApi('/api-web/attendance/admin-scan', {
            method: 'POST',
            body: JSON.stringify({ teacher_id: parseInt(selectedTeacherId) })
        });

        showResult(res);
        addLogEntry(res);
        updateStats(res);
        playSound(res.status === 'success' ? 'success' : 'error');
    } catch (e) {
        showResult({ status: 'error', message: e.message, action: 'error' });
        addLogEntry({ status: 'error', message: e.message, teacher_name: '—', action: 'error' });
        playSound('error');
    } finally {
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-fingerprint"></i> {{ __("Authenticate & Scan") }}';
            ring.classList.remove('scanning');
        }, 800);
    }
}

// ── Animate Values ────────────────────────────────
function animateVal(id, newVal) {
    const el = document.getElementById(id);
    if (!el) return;
    const oldVal = parseInt(el.textContent) || 0;
    if (oldVal === newVal) return;

    let start = oldVal;
    const duration = 1000;
    const stepTime = Math.abs(Math.floor(duration / (newVal - oldVal)));
    
    const timer = setInterval(() => {
        start += (newVal > oldVal ? 1 : -1);
        el.textContent = start;
        if (start == newVal) clearInterval(timer);
    }, stepTime || 50);
}

// ── Result flash ─────────────────────────────────
function showResult(res) {
    const flash = document.getElementById('resultFlash');
    const nameEl = document.getElementById('resultName');
    const metaEl = document.getElementById('resultMeta');

    flash.className = 'result-flash ' + (res.status === 'success' ? (res.action === 'check-in' ? 'success' : 'info') : res.status === 'info' ? 'warning' : 'error');
    flash.style.display = 'block';

    const icon = res.action === 'check-in' ? '✅' : res.action === 'check-out' ? '🔵' : res.status === 'info' ? '⚠️' : '❌';
    const nameKh = res.teacher_name_kh || '';
    nameEl.innerHTML = `
        <div style="font-size: 1.4rem; font-weight: 800; color: var(--primary);">${icon} ${nameKh}</div>
        <div style="font-size: 1rem; font-weight: 600; opacity: 0.8;">${res.teacher_name || ''}</div>
    `;

    let meta = res.message || '';
    if (res.time)          meta += `  ·  ${res.time}`;
    if (res.working_hours) meta += `  ·  ${res.working_hours}`;
    metaEl.textContent = meta;

    // Trigger active scan animation on hologram ring & RFID card
    const ring = document.getElementById('scannerRing');
    if (ring) {
        ring.classList.add('scanning');
        clearTimeout(window._scanAnimTimer);
        window._scanAnimTimer = setTimeout(() => { ring.classList.remove('scanning'); }, 2500);
    }

    clearTimeout(window._flashTimer);
    window._flashTimer = setTimeout(() => { flash.style.display = 'none'; }, 60000); // 60 seconds
}

// ── Timeline log ─────────────────────────────────────
function addLogEntry(res) {
    const logEl = document.getElementById('scanLog');
    const time = new Date().toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true });

    const dotClass = res.action === 'check-in' ? 'success' : res.action === 'check-out' ? 'info' : res.status === 'info' ? 'warning' : 'error';

    const item = document.createElement('div');
    item.className = 'timeline-item';
    item.innerHTML = `
        <div class="timeline-dot ${dotClass}"></div>
        <div class="timeline-content">
            <span class="timeline-time">${time}</span>
            <div class="timeline-msg">
                <strong style="color: var(--primary);">${res.teacher_name_kh || ''}</strong> 
                <span style="font-size: 0.85rem; opacity: 0.8;">(${res.teacher_name || '—'})</span> 
                · ${res.message || ''}
            </div>
        </div>
    `;

    // Remove placeholder
    const placeholder = logEl.querySelector('p');
    if (placeholder) placeholder.remove();

    logEl.insertBefore(item, logEl.firstChild);

    // Keep only last 10 entries
    const items = logEl.querySelectorAll('.timeline-item');
    if (items.length > 10) items[items.length - 1].remove();
}

function updateStats(res) {
    if (res.status !== 'success') return;
    
    const total = parseInt(document.getElementById('stat-total').textContent) + 1;
    animateVal('stat-total', total);
    
    const now = new Date();
    const h = now.getHours() + now.getMinutes() / 60;
    if (h >= 5.0 && h < 12.0) {
        animateVal('stat-morning', parseInt(document.getElementById('stat-morning').textContent) + 1);
    } else if (h >= 12.0 && h < 17.5) {
        animateVal('stat-afternoon', parseInt(document.getElementById('stat-afternoon').textContent) + 1);
    }
}

// ── Device status ────────────────────────────────
async function checkDeviceStatus() {
    try {
        const data  = await window.fetchApi('{{ route("api.device.status") }}');
        const dot   = document.getElementById('deviceDot');
        const label = document.getElementById('deviceLabel');
        const teacherCard = document.querySelector('.teacher-side-card');
        if (data.online) {
            dot.style.background = 'var(--success)';
            label.textContent    = `{{ __('TERMINAL ONLINE') }} · ${data.last_seen_ago}`;
            label.style.color    = 'var(--success)';
            teacherCard.style.opacity = '0.6';
            teacherCard.style.pointerEvents = 'none';
        } else {
            dot.style.background = 'var(--danger)';
            label.textContent    = data.timestamp ? `{{ __('OFFLINE') }} · Last: ${data.last_seen_ago}` : `{{ __('OFFLINE') }}`;
            label.style.color    = 'var(--danger)';
            teacherCard.style.opacity = '1';
            teacherCard.style.pointerEvents = 'auto';
        }
    } catch { document.getElementById('deviceLabel').textContent = 'STATUS UNKNOWN'; }
}
checkDeviceStatus();
setInterval(checkDeviceStatus, 10000);

// ── Real-time Polling ────────────────────────────
async function pollLatestScans() {
    try {
        const url = `{{ route('api.live.latest') }}?last_updated_at=${lastUpdatedAt}`;
        const response = await fetch(url);
        if (!response.ok) return;
        const data = await response.json();
        
        lastUpdatedAt = data.server_time;

        if (data.scans && data.scans.length > 0) {
            // New scans found!
            data.scans.reverse().forEach(scan => {
                // Map to format expected by local functions
                const res = {
                    status: 'success',
                    action: scan.type,
                    teacher_name: scan.teacher_name,
                    message: `${scan.shift_label} {{ __('recorded') }}`,
                    time: scan.time,
                    working_hours: null // working hours not in latest yet
                };

                // Update UI
                showResult(res);
                addLogEntry(res);
                updateStats(res);
            });
        }
    } catch (e) {
        console.error("Polling error:", e);
    }
}
setInterval(pollLatestScans, 2000);

// keyboard shortcut: Enter = scan
document.addEventListener('keydown', e => {
    const teacherCard = document.querySelector('.teacher-side-card');
    const isOffline = teacherCard && teacherCard.style.pointerEvents !== 'none';
    if (e.key === 'Enter' && selectedTeacherId && isOffline && !document.getElementById('teacherSearch').matches(':focus')) {
        doAdminScan();
    }
});
</script>
@endpush
