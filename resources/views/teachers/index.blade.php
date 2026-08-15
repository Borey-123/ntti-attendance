@extends('layouts.app')

@section('title', __('Teacher Directory'))

@section('content')
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
<style>
    .teacher-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }
    .summary-item {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 1.5rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: transform 0.3s ease;
    }
    .summary-item:hover { transform: translateY(-3px); border-color: var(--primary); }
    .summary-item.active { border-color: var(--primary); background: rgba(var(--primary-rgb), 0.04); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .s-icon {
        width: 54px; height: 54px; border-radius: 1.25rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.75rem;
    }
    .s-data .label { font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; }
    .s-data .value { font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; margin-top: 2px; }

    .teacher-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    .t-profile-card {
        background: radial-gradient(circle at 100% 0%, rgba(var(--primary-rgb), 0.08) 0%, transparent 60%),
                    radial-gradient(circle at 0% 100%, rgba(var(--primary-rgb), 0.04) 0%, transparent 60%),
                    var(--bg-card);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 2rem;
        padding: 0 1.5rem 2rem;
        position: relative;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15), inset 0 0 20px rgba(255,255,255,0.02);
        overflow: hidden;
    }
    .t-card-banner {
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 110px;
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.2) 0%, rgba(var(--primary-rgb), 0.05) 100%);
        border-bottom: 1px solid rgba(var(--primary-rgb), 0.1);
        z-index: 0;
    }
    .t-card-banner::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle at 100% 0%, rgba(255,255,255,0.15) 0%, transparent 50%),
                          radial-gradient(circle at 0% 100%, rgba(var(--primary-rgb), 0.15) 0%, transparent 50%);
        opacity: 0.8;
    }
    .t-profile-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, var(--primary), transparent);
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: 2;
    }
    .t-profile-card:hover { 
        transform: translateY(-8px); 
        border-color: rgba(var(--primary-rgb), 0.4);
        box-shadow: 0 25px 50px rgba(0,0,0,0.2), 0 0 30px rgba(var(--primary-rgb), 0.15), inset 0 0 0 1px rgba(var(--primary-rgb), 0.2); 
    }
    .t-profile-card:hover::before { opacity: 1; }
    .t-card-photo {
        width: 110px; height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--bg-card);
        box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.4), 0 10px 25px rgba(0,0,0,0.3);
        margin-top: 55px;
        margin-bottom: 1.25rem;
        background: linear-gradient(135deg, rgba(var(--primary-rgb),0.2), rgba(var(--primary-rgb),0.05));
        display: flex; align-items: center; justify-content: center; font-size: 2.75rem; font-weight: 800;
        color: var(--primary);
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }
    .t-card-info, .t-card-dept, .t-card-contact, .t-card-actions {
        position: relative;
        z-index: 1;
    }
    .t-card-info h3 { margin: 0; font-size: 1.3rem; font-weight: 800; letter-spacing: -0.02em; color: var(--text-primary) !important; }
    .t-card-info p { margin: 0.35rem 0 1rem; font-size: 0.88rem; color: var(--text-primary); opacity: 0.9; font-weight: 600; }
    
    .t-card-dept {
        background: rgba(var(--primary-rgb), 0.08);
        color: var(--primary);
        font-size: 0.7rem;
        font-weight: 800;
        padding: 0.4rem 1.1rem;
        border-radius: 2rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1.25rem;
        display: inline-block;
        border: 1px solid rgba(var(--primary-rgb), 0.2);
    }
    
    .t-card-contact {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.08), rgba(var(--primary-rgb), 0.01));
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 1.25rem;
        margin-bottom: 1.25rem;
        font-size: 0.85rem;
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
    }
    .contact-line { display: flex; align-items: center; gap: 0.75rem; color: var(--text-primary); opacity: 0.9; font-weight: 600; }
    .contact-line i { color: var(--primary); font-size: 1.1rem; }

    /* ── Attendance Mini Visualization ── */
    .t-card-attendance {
        width: 100%;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 1.25rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        display: flex; align-items: center; gap: 1rem;
    }
    .attendance-ring {
        position: relative; flex-shrink: 0;
        width: 52px; height: 52px;
    }
    .attendance-ring svg { transform: rotate(-90deg); }
    .attendance-ring .ring-track { fill: none; stroke: rgba(255,255,255,0.06); stroke-width: 5; }
    .attendance-ring .ring-fill  { fill: none; stroke-width: 5; stroke-linecap: round; transition: stroke-dashoffset 1s ease; }
    .attendance-ring .ring-score { 
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 800; line-height: 1;
    }
    .attendance-stats { flex: 1; display: flex; flex-direction: column; gap: 0.35rem; text-align: left; }
    .stat-row { display: flex; justify-content: space-between; align-items: center; }
    .stat-label { font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-val { font-size: 0.85rem; font-weight: 800; }
    .mini-dots { display: flex; gap: 3px; margin-top: 0.35rem; }
    .mini-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.1); }
    .mini-dot.present { background: var(--card-accent, var(--primary)); }
    .mini-dot.late { background: #f59e0b; }
    .mini-dot.absent { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); }

    .t-card-actions {
        display: flex;
        gap: 0.75rem;
        width: 100%;
    }
    .btn-card {
        flex: 1;
        padding: 0.85rem;
        border-radius: 1.25rem;
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.15); }
    .btn.btn-edit-premium {
        background: rgba(var(--primary-rgb), 0.1);
        border: 2px solid var(--primary);
        color: var(--primary);
        box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);
        font-weight: 800;
    }
    .btn.btn-edit-premium:hover {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 8px 20px rgba(var(--primary-rgb), 0.4);
    }
    
    .status-badge-float {
        position: absolute;
        top: 0.85rem;
        right: 0.85rem;
        z-index: 2;
    }
    /* Paste-button wrapper for telegram chat id */
    .tg-input-wrap {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .tg-input-wrap .form-control { flex: 1; }
    .btn-paste {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.55rem 0.85rem;
        border-radius: 0.875rem;
        border: 1.5px solid var(--border);
        background: var(--bg-dark);
        color: var(--text-secondary);
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        height: 100%;
    }
    .btn-paste:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(var(--primary-rgb), 0.08);
        transform: translateY(-1px);
    }
    .btn-paste.pasted {
        border-color: #22c55e;
        color: #22c55e;
        background: rgba(34, 197, 94, 0.08);
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 0 !important;
        }
        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            margin: 0 !important;
            padding: 0 !important;
            height: auto !important;
            overflow: visible !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body.printing-cards * {
            visibility: hidden !important;
        }
        body.printing-cards #print-cards-container, 
        body.printing-cards #print-cards-container * {
            visibility: visible !important;
        }
        body.printing-cards #print-cards-container {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            display: block !important;
        }
        .print-card-page {
            width: 210mm !important;
            height: 297mm !important;
            padding: 10mm 0 !important;
            box-sizing: border-box !important;
            background: #ffffff !important;
            margin: 0 auto !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            page-break-inside: avoid !important;
            page-break-after: auto !important;
        }
        .print-card-page:not(:last-child) {
            page-break-after: always !important;
        }
        .print-card-page:last-child {
            page-break-after: avoid !important;
        }
        .cards-grid-8 {
            display: grid !important;
            grid-template-columns: repeat(2, 88mm) !important;
            grid-template-rows: repeat(4, 58mm) !important;
            gap: 4mm 8mm !important;
            justify-content: center !important;
            align-content: center !important;
            width: 100% !important;
            height: 100% !important;
        }
        .id-card {
            width: 88mm !important;
            height: 58mm !important;
            border: 1.5px solid #0f2942 !important;
            border-radius: 3.5mm !important;
            box-sizing: border-box !important;
            background: #ffffff !important;
            position: relative !important;
            overflow: hidden !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            font-family: 'Kantumruy Pro', 'Inter', system-ui, sans-serif !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .id-card-header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100% !important;
            background: #ffffff !important;
            padding: 1.8mm 3.5mm 1.5mm 3.5mm !important;
            box-sizing: border-box !important;
            border-bottom: 1.8px solid #0f2942 !important;
            gap: 2mm !important;
        }
        .id-card-header-left {
            display: flex !important;
            align-items: center !important;
            gap: 2.2mm !important;
            min-width: 0 !important;
            flex: 1 !important;
        }
        .id-card-logo {
            height: 8.5mm !important;
            width: auto !important;
            object-fit: contain !important;
            flex-shrink: 0 !important;
        }
        .id-card-header-titles {
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            min-width: 0 !important;
        }
        .id-card-univ-name {
            font-size: 6.8pt !important;
            font-weight: 900 !important;
            color: #0f2942 !important;
            line-height: 1.25 !important;
            font-family: 'Kantumruy Pro', sans-serif !important;
            margin-bottom: 0.6mm !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        .id-card-sub-name {
            font-size: 5pt !important;
            font-weight: 700 !important;
            color: #334155 !important;
            line-height: 1.1 !important;
            letter-spacing: 0.3px !important;
            text-transform: uppercase !important;
            font-family: 'Inter', system-ui, sans-serif !important;
        }
        .id-card-header-tag {
            font-size: 4.8pt !important;
            font-weight: 800 !important;
            color: #1d4ed8 !important;
            background: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            padding: 0.5mm 1.8mm !important;
            border-radius: 1mm !important;
            letter-spacing: 0.4px !important;
            text-transform: uppercase !important;
            font-family: 'Kantumruy Pro', 'Inter', sans-serif !important;
            flex-shrink: 0 !important;
        }
        .id-card-body {
            padding: 3mm 3.8mm 3.2mm 3.8mm !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: stretch !important;
            gap: 3.2mm !important;
            flex: 1 !important;
            box-sizing: border-box !important;
        }
        .id-card-photo-col {
            flex-shrink: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .id-card-photo-img {
            width: 22.5mm !important;
            height: 29.5mm !important;
            object-fit: cover !important;
            border: 1.5px solid #0f2942 !important;
            border-radius: 2.5mm !important;
            background: #f1f5f9 !important;
            box-shadow: 0 2px 6px rgba(15, 41, 66, 0.15) !important;
        }
        .id-card-info-col {
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            flex: 1 !important;
            min-width: 0 !important;
            height: 100% !important;
        }
        .id-card-top-row {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            width: 100% !important;
            margin-bottom: 1.5mm !important;
        }
        .id-card-name-kh {
            font-size: 8.8pt !important;
            font-weight: 900 !important;
            color: #0f2942 !important;
            line-height: 1.15 !important;
            font-family: 'Kantumruy Pro', sans-serif !important;
        }
        .id-card-name-en {
            font-size: 6.8pt !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            color: #334155 !important;
            line-height: 1.15 !important;
            letter-spacing: 0.3px !important;
            font-family: 'Kantumruy Pro', 'Inter', sans-serif !important;
        }
        .id-card-id-badge {
            background: linear-gradient(135deg, #0f2942, #1e3a8a) !important;
            color: #ffffff !important;
            font-size: 6pt !important;
            font-weight: 800 !important;
            padding: 0.6mm 2.2mm !important;
            border-radius: 1mm !important;
            letter-spacing: 0.4px !important;
            box-shadow: 0 1px 3px rgba(15, 41, 66, 0.3) !important;
            font-family: 'Kantumruy Pro', 'Inter', sans-serif !important;
        }
        .id-card-details-grid {
            display: flex !important;
            flex-direction: column !important;
            gap: 1.6mm !important;
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 2.2mm !important;
            padding: 2mm 2.8mm !important;
            width: 100% !important;
            box-sizing: border-box !important;
            flex: 1 !important;
            justify-content: space-around !important;
        }
        .id-card-grid-item-full {
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
            gap: 1.8mm !important;
            min-width: 0 !important;
        }
        .id-card-grid-icon {
            width: 4.2mm !important;
            height: 4.2mm !important;
            border-radius: 50% !important;
            background: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
        }
        .id-card-grid-content {
            display: flex !important;
            flex-direction: column !important;
            min-width: 0 !important;
            overflow: hidden !important;
        }
        .id-card-grid-label {
            font-size: 4.2pt !important;
            font-weight: 800 !important;
            color: #64748b !important;
            text-transform: uppercase !important;
            line-height: 1 !important;
            margin-bottom: 0.2px !important;
            letter-spacing: 0.4px !important;
            font-family: 'Kantumruy Pro', 'Inter', sans-serif !important;
        }
        .id-card-grid-value {
            font-size: 5.4pt !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            line-height: 1.15 !important;
            word-break: break-word !important;
            white-space: normal !important;
            font-family: 'Kantumruy Pro', 'Inter', sans-serif !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $uName = \App\Models\Setting::getValue('university_name', 'NTTI System');
    $uLogo = \App\Models\Setting::getValue('university_logo', '');
@endphp
<div class="d-flex justify-between align-center" style="margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="page-title" style="margin-bottom: 0.25rem;">{{ __('Teacher Directory') }}</h1>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Manage personnel, credentials, and profile identities.') }}</p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <select id="printDepartmentSelect" class="form-control" style="width: 200px; border-radius: 1.25rem; font-weight: 700; background: var(--bg-card); border: 1.5px solid var(--border); padding: 0.65rem 1rem;">
            <option value="">{{ __('All Departments') }}</option>
            @foreach($departments as $d)
                <option value="{{ $d->name }}" {{ request('department') == $d->name ? 'selected' : '' }}>
                    {{ app()->getLocale() == 'km' ? ($d->name_kh ?: $d->name) : $d->name }}
                </option>
            @endforeach
        </select>
        <button class="btn" onclick="printFilteredCards()" style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 1.25rem; padding: 0.8rem 1.5rem; font-weight: 800; border: 1.5px solid #3b82f6; background: rgba(59, 130, 246, 0.1); color: #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15); transition: all 0.2s ease; cursor: pointer;">
            <i class="ph ph-printer" style="font-size: 1.2rem;"></i> <span>{{ __('Print Cards') }}</span>
        </button>
        <button class="btn" onclick="openModal('importTeacherModal')" style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 1.25rem; padding: 0.8rem 1.5rem; font-weight: 800; border: 1.5px solid #10b981; background: rgba(16, 185, 129, 0.1); color: #10b981; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15); transition: all 0.2s ease; cursor: pointer;">
            <i class="ph ph-upload-simple" style="font-size: 1.2rem;"></i> <span>{{ __('Import Data') }}</span>
        </button>
        <button class="btn btn-primary" onclick="openModal('addTeacherModal')" style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 1.25rem; padding: 0.8rem 1.5rem; font-weight: 800; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.25); transition: all 0.2s ease; width: fit-content;">
            <i class="ph ph-plus-circle" style="font-size: 1.2rem;"></i> <span>{{ __('Register New Teacher') }}</span>
        </button>
    </div>
</div>

{{-- ── Summary Dashboard ── --}}
@php
    $statsQuery = \App\Models\Teacher::query();
    if (request('department')) {
        $statsQuery->where('department', request('department'));
    }
    $statTotal    = (clone $statsQuery)->count();
    $statAssigned = (clone $statsQuery)->whereHas('rfidCard')->count();
    $statPending  = (clone $statsQuery)->whereDoesntHave('rfidCard')->count();
@endphp
<div class="teacher-summary-grid">
    <a href="{{ route('teachers.index', array_merge(request()->query(), ['filter' => null])) }}" class="summary-item {{ !request('filter') ? 'active' : '' }}" style="text-decoration: none; color: inherit; cursor: pointer;">
        <div class="s-icon" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);"><i class="ph ph-users-four"></i></div>
        <div class="s-data">
            <div class="label">{{ __('Total Staff') }}</div>
            <div class="value">{{ $statTotal }}</div>
        </div>
    </a>
    <a href="{{ route('teachers.index', array_merge(request()->query(), ['filter' => 'assigned'])) }}" class="summary-item {{ request('filter') === 'assigned' ? 'active' : '' }}" style="text-decoration: none; color: inherit; cursor: pointer;">
        <div class="s-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="ph ph-identification-card"></i></div>
        <div class="s-data">
            <div class="label">{{ __('RFID Assigned') }}</div>
            <div class="value">{{ $statAssigned }}</div>
        </div>
    </a>
    <a href="{{ route('teachers.index', array_merge(request()->query(), ['filter' => 'pending'])) }}" class="summary-item {{ request('filter') === 'pending' ? 'active' : '' }}" style="text-decoration: none; color: inherit; cursor: pointer;">
        <div class="s-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="ph ph-shield-warning"></i></div>
        <div class="s-data">
            <div class="label">{{ __('Pending Cards') }}</div>
            <div class="value">{{ $statPending }}</div>
        </div>
    </a>
</div>

{{-- ── Search & Filter Bar ── --}}
<div class="card" style="margin-bottom: 2rem; border-radius: 1.5rem; padding: 1rem;">
    <form method="GET" action="{{ route('teachers.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
        <div style="position: relative; flex: 1; min-width: 250px;">
            <i class="ph ph-magnifying-glass" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size: 1.2rem;"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="form-control" style="padding-left: 2.75rem; border-radius: 1rem;"
                   placeholder="{{ __('Search name, ID or position...') }}">
            <input type="hidden" name="filter" value="{{ request('filter') }}">
        </div>

        <div style="position: relative; min-width: 250px;">
            <i class="ph ph-funnel" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--text-muted);"></i>
            <select name="department" class="form-control" style="padding-left: 2.5rem; border-radius: 1rem; appearance: auto;" onchange="this.form.submit()">
                <option value="">{{ __('All Departments') }}</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->name }}" {{ request('department') == $dept->name ? 'selected' : '' }}>
                        {{ app()->getLocale() == 'km' ? ($dept->name_kh ?: $dept->name) : $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="position: relative; min-width: 180px;">
            <i class="ph ph-activity" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--text-muted);"></i>
            <select name="status" class="form-control" style="padding-left: 2.5rem; border-radius: 1rem; appearance: auto;" onchange="this.form.submit()">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </select>
        </div>

        @if(request('search') || request('department') || request('status') || request('filter'))
        <a href="{{ route('teachers.index') }}" class="btn btn-secondary" style="border-radius: 1rem; padding: 0.75rem 1.25rem;">
            <i class="ph ph-x"></i> {{ __('Reset') }}
        </a>
        @endif
    </form>
</div>

<div class="teacher-grid">
    @foreach($teachers as $teacher)
    <div class="t-profile-card stagger-item" style="animation-delay: {{ $loop->index * 0.04 }}s">
        <div class="t-card-banner"></div>
        <div class="status-badge-float">
            @if($teacher->telegram_chat_id)
                <span class="badge" style="background: rgba(0,136,204,0.15); color: #0088cc; border: 1px solid rgba(0,136,204,0.3);" title="{{ __('Telegram Connected') }}"><i class="ph ph-telegram-logo"></i></span>
            @endif
            @if($teacher->rfidCard)
                <span class="badge badge-success" title="RFID Assigned"><i class="ph ph-identification-card"></i></span>
            @else
                <span class="badge badge-secondary" title="No RFID"><i class="ph ph-warning"></i></span>
            @endif
            @if($teacher->status === 'inactive')
                <span class="badge badge-danger" title="Inactive" style="margin-left:0.2rem;"><i class="ph ph-user-minus"></i></span>
            @endif
        </div>

        @if($teacher->photo)
            <img src="{{ to_asset_url($teacher->photo) }}" class="t-card-photo" alt="{{ $teacher->name }}">
        @else
            <div class="t-card-photo">
                {{ substr($teacher->name, 0, 1) }}
            </div>
        @endif

        <div class="t-card-info">
            <h3 translate="no" class="notranslate" style="color: var(--primary); margin-bottom: 4px;">{{ $teacher->name_kh }}</h3>
            <h4 translate="no" class="notranslate" style="margin: 0; font-size: 1.1rem; font-weight: 700; opacity: 1; color: var(--text-primary);">{{ $teacher->name }}</h4>
            <div style="display: flex; gap: 0.5rem; justify-content: center; margin-top: 0.75rem; flex-wrap: wrap;">
                <span style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 0.2rem 0.6rem; border-radius: 0.5rem; font-family: monospace; font-size: 0.75rem; color: var(--text-secondary); display: inline-flex; align-items: center; gap: 4px;">
                    <i class="ph ph-identification-badge"></i> {{ $teacher->employee_id }}
                </span>
                @if($teacher->rfidCard)
                    <span style="background: rgba(var(--primary-rgb),0.1); border: 1px solid rgba(var(--primary-rgb),0.3); padding: 0.2rem 0.6rem; border-radius: 0.5rem; font-family: monospace; font-size: 0.75rem; color: var(--primary); display: inline-flex; align-items: center; gap: 4px;">
                        <i class="ph ph-wifi-high"></i> {{ $teacher->rfidCard->uid }}
                    </span>
                @endif
            </div>
            <p style="margin-top: 0.5rem; margin-bottom: 0;">{{ $teacher->position ?? __('Instructor') }}</p>
        </div>

        @php
            $deptObj = $departments->firstWhere('name', $teacher->department);
            $deptLabel = $deptObj ? (app()->getLocale() == 'km' ? ($deptObj->name_kh ?: $deptObj->name) : $deptObj->name) : $teacher->department;
        @endphp
        <div class="t-card-dept">{{ $deptLabel }}</div>


        <div class="t-card-contact">
            <div class="contact-line">
                <i class="ph ph-envelope"></i>
                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $teacher->email ?? __('No Email') }}</span>
            </div>
            <div class="contact-line">
                <i class="ph ph-phone"></i>
                <span>{{ $teacher->phone ?? __('No Phone') }}</span>
            </div>
        </div>

        <div class="t-card-actions">
            <button class="btn btn-sm btn-card btn-edit-premium" onclick="editTeacher({{ $teacher->id }})">
                <i class="ph ph-pencil-simple"></i> {{ __('Edit') }}
            </button>
            <button class="btn btn-sm btn-card" style="flex: 0 0 45px; padding: 0; background: rgba(59, 130, 246, 0.1); border: 2.5px solid #3b82f6; color: #3b82f6; font-weight: 800;" onclick="printTeacherCard({{ $teacher->id }})" title="{{ __('Print Card') }}">
                <i class="ph ph-printer"></i>
            </button>
            <button class="btn btn-sm btn-danger btn-card" style="flex: 0 0 45px; padding: 0;" onclick="removeTeacher({{ $teacher->id }})">
                <i class="ph ph-trash"></i>
            </button>
        </div>
    </div>
    @endforeach
</div>

@if($teachers->isEmpty())
<div class="card" style="padding: 5rem 2rem; text-align: center; border-radius: 2rem; border: 2px dashed var(--border); background: transparent;">
    <i class="ph ph-users-three" style="font-size: 4rem; opacity: 0.1; display: block; margin-bottom: 1rem;"></i>
    <h3 style="color: var(--text-secondary);">{{ __('No teachers found in this selection.') }}</h3>
    <p style="color: var(--text-muted);">{{ __('Try adjusting your filters or register a new teacher.') }}</p>
</div>
@endif

<!-- Add Modal -->
<div class="modal-overlay" id="addTeacherModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>{{ __('Add Teacher') }}</h3>
            <button class="modal-close" onclick="closeModal('addTeacherModal')">&times;</button>
        </div>
        <form id="addTeacherForm" onsubmit="submitAdd(event)">
            <div class="form-group">
                <label>{{ __('Full Name (Khmer)') }}</label>
                <input type="text" name="name_kh" class="form-control">
            </div>
            <div class="form-group">
                <label>{{ __('Full Name (English)') }}</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>{{ __('Department') }}</label>
                <select name="department" class="form-control" required style="background-color: var(--bg-dark);">
                    <option value="" disabled selected>{{ __('Select department...') }}</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->name }}">{{ app()->getLocale() == 'km' ? ($dept->name_kh ?: $dept->name) : $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-4">
                <div class="form-group" style="flex:1;">
                    <label>{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>{{ __('Phone') }}</label>
                    <input type="text" name="phone" class="form-control">
                </div>
            </div>
            <div class="d-flex gap-4">
                <div class="form-group" style="flex:1;">
                    <label>{{ __('Position / Title') }}</label>
                    <input type="text" name="position" class="form-control">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>{{ __('Telegram Chat ID') }} <span style="font-size: 0.8rem; color: var(--text-secondary);">({{ __('Optional') }})</span></label>
                    <div class="tg-input-wrap">
                        <input type="text" id="add_telegram_chat_id" name="telegram_chat_id" class="form-control" placeholder="e.g. 123456789">
                        <button type="button" class="btn-paste" onclick="pasteChatId('add_telegram_chat_id', this)" title="{{ __('Paste from clipboard') }}">
                            <i class="ph ph-clipboard-text"></i> {{ __('Paste') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>{{ __('Profile Photo') }}</label>
                <input type="file" name="photo" id="add_photo" class="form-control" accept="image/*" onchange="initCropper(this)">
            </div>
            <div class="d-flex justify-between align-center mt-4">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addTeacherModal')">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary" style="width: auto;">{{ __('Save Teacher') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editTeacherModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>{{ __('Edit Teacher') }}</h3>
            <button class="modal-close" onclick="closeModal('editTeacherModal')">&times;</button>
        </div>
        <form id="editTeacherForm" onsubmit="submitEdit(event)">
            <input type="hidden" id="edit_id" name="id">
            <div class="form-group">
                <label>{{ __('Teacher ID') }}</label>
                <input type="text" id="edit_employee_id" name="employee_id" class="form-control" readonly style="opacity: 0.7; background: var(--bg-dark);">
            </div>
            <div class="form-group">
                <label>{{ __('Full Name (Khmer)') }}</label>
                <input type="text" id="edit_name_kh" name="name_kh" class="form-control">
            </div>
            <div class="form-group">
                <label>{{ __('Full Name (English)') }}</label>
                <input type="text" id="edit_name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>{{ __('Department') }}</label>
                <select id="edit_department" name="department" class="form-control" required style="background-color: var(--bg-dark);">
                    <option value="" disabled selected>{{ __('Select department...') }}</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->name }}">{{ app()->getLocale() == 'km' ? ($dept->name_kh ?: $dept->name) : $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-4">
                <div class="form-group" style="flex:1;">
                    <label>{{ __('Email') }}</label>
                    <input type="email" id="edit_email" name="email" class="form-control">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>{{ __('Phone') }}</label>
                    <input type="text" id="edit_phone" name="phone" class="form-control">
                </div>
            </div>
            <div class="d-flex gap-4">
                <div class="form-group" style="flex:1;">
                    <label>{{ __('Position / Title') }}</label>
                    <input type="text" id="edit_position" name="position" class="form-control">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>{{ __('Telegram Chat ID') }} <span style="font-size: 0.8rem; color: var(--text-secondary);">({{ __('Optional') }})</span></label>
                    <div class="tg-input-wrap">
                        <input type="text" id="edit_telegram_chat_id" name="telegram_chat_id" class="form-control">
                        <button type="button" class="btn-paste" onclick="pasteChatId('edit_telegram_chat_id', this)" title="{{ __('Paste from clipboard') }}">
                            <i class="ph ph-clipboard-text"></i> {{ __('Paste') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>{{ __('Profile Photo') }} <span style="font-size: 0.8rem; color: var(--text-secondary);">({{ __('Leave blank to keep existing') }})</span></label>
                <div id="edit_photo_preview_container" style="margin-bottom: 0.75rem; display: none; align-items: center; gap: 1rem;">
                    <div style="position: relative; cursor: pointer;" onclick="reCropExisting()" title="{{ __('Click to re-adjust') }}">
                        <img id="edit_photo_preview" src="" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
                        <div style="position: absolute; bottom: 0; right: 0; background: var(--primary); color: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; border: 2px solid var(--bg-card);">
                            <i class="ph ph-pencil-simple"></i>
                        </div>
                    </div>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--danger); font-size: 0.85rem; font-weight: 600;">
                        <input type="checkbox" name="remove_photo" value="1" style="width: 16px; height: 16px; accent-color: var(--danger);">
                        {{ __('Remove current photo') }}
                    </label>
                </div>
                <input type="file" id="edit_photo" name="photo" class="form-control" accept="image/*" onchange="initCropper(this)">
            </div>
            <div class="form-group">
                <label>{{ __('Status') }}</label>
                <select id="edit_status" name="status" class="form-control" style="background-color: var(--bg-dark);">
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="d-flex justify-between align-center mt-4">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editTeacherModal')">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary" style="width: auto;">{{ __('Update Teacher') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Teacher Data Modal -->
<div class="modal" id="importTeacherModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="ph ph-upload-simple" style="color: var(--primary); margin-right: 0.5rem;"></i>{{ __('Import Teacher Records') }}</h3>
            <button class="modal-close" onclick="closeModal('importTeacherModal')">&times;</button>
        </div>
        <form action="{{ route('teachers.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body" style="padding: 1.5rem 0;">
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">
                    {{ __('Upload a .sql database dump or .csv list containing teacher data. The system will automatically create or update matching records.') }}
                </p>
                <div class="form-group">
                    <label>{{ __('Select File (.sql, .csv)') }}</label>
                    <input type="file" name="file" class="form-control" accept=".sql,.csv,.txt" required style="padding: 0.75rem;">
                </div>
            </div>
            <div class="d-flex justify-between align-center mt-4">
                <button type="button" class="btn btn-secondary" onclick="closeModal('importTeacherModal')">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary" style="width: auto;">{{ __('Import Now') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Crop Modal -->
<div class="modal-overlay" id="cropModal" style="z-index: 1000001;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>{{ __('Adjust Photo') }}</h3>
            <button class="modal-close" type="button" onclick="closeCropModal()">&times;</button>
        </div>
        <div style="max-height: 400px; height: 400px; overflow: hidden; display: flex; justify-content: center; background: var(--bg-dark); border-radius: 1rem;">
            <img id="cropImage" src="" style="max-width: 100%; display: block;">
        </div>
        <div class="d-flex justify-between align-center mt-4">
            <button type="button" class="btn btn-secondary" onclick="closeCropModal()">{{ __('Cancel') }}</button>
            <button type="button" class="btn btn-primary" style="width: auto;" onclick="applyCrop()">{{ __('Crop & Apply') }}</button>
        </div>
    </div>
</div>
<div id="print-cards-container" style="display: none;"></div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    let currentCropper = null;
    let croppedBlob = null;
    let currentFileInput = null;

    function initCropper(fileInput) {
        if (!fileInput.files || !fileInput.files[0]) return;
        
        const file = fileInput.files[0];
        if (!file.type.startsWith('image/')) return;

        currentFileInput = fileInput;
        const reader = new FileReader();
        reader.onload = function(e) {
            openCropper(e.target.result);
        };
        reader.readAsDataURL(file);
    }

    function reCropExisting() {
        const previewImg = document.getElementById('edit_photo_preview');
        if (!previewImg.src) return;
        
        currentFileInput = document.getElementById('edit_photo');
        openCropper(previewImg.src);
    }

    function openCropper(imageSrc) {
        const cropImg = document.getElementById('cropImage');
        cropImg.src = imageSrc;
        
        document.getElementById('cropModal').classList.add('active');
        
        if (currentCropper) {
            currentCropper.destroy();
        }
        
        setTimeout(() => {
            currentCropper = new Cropper(cropImg, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 1,
                background: false,
                checkOrientation: true,
                crossOrigin: 'anonymous'
            });
        }, 50);
    }

    function closeCropModal() {
        document.getElementById('cropModal').classList.remove('active');
        if (currentCropper) {
            currentCropper.destroy();
            currentCropper = null;
        }
        // If they cancelled without cropping, clear the file input
        if (currentFileInput && !croppedBlob) {
            currentFileInput.value = '';
        }
    }

    function applyCrop() {
        if (!currentCropper) return;
        
        currentCropper.getCroppedCanvas({
            width: 500,
            height: 500,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        }).toBlob(function(blob) {
            croppedBlob = blob;
            closeCropModal();
            
            // Show preview if in edit mode
            if (currentFileInput.id === 'edit_photo') {
                const url = URL.createObjectURL(blob);
                document.getElementById('edit_photo_preview').src = url;
                document.getElementById('edit_photo_preview_container').style.display = 'flex';
                const removeCheck = document.getElementById('editTeacherForm').querySelector('input[name="remove_photo"]');
                if (removeCheck) removeCheck.checked = false;
            }
        }, 'image/jpeg', 0.9);
    }

    function openModal(id) { 
        if(id === 'addTeacherModal') {
            croppedBlob = null; // Reset
            document.getElementById('addTeacherForm').reset();
        }
        document.getElementById(id).classList.add('active'); 
    }
    
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    async function submitAdd(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.textContent = 'Saving...'; submitBtn.disabled = true;

        const formData = new FormData(form);
        if (croppedBlob) {
            formData.set('photo', croppedBlob, 'photo.jpg');
        }

        try {
            await window.fetchApi(`{{ route('api.teachers.store') }}`, {
                method: 'POST',
                body: formData,
                headers: {} 
            });
            window.location.reload();
        } catch (err) {
            await alert(err.message);
            submitBtn.textContent = 'Save Teacher'; submitBtn.disabled = false;
        }
    }

    async function editTeacher(id) {
        try {
            croppedBlob = null; // Reset blob
            const res = await fetch(`{{ url('/api-web/teachers') }}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            const teacher = data.find(t => t.id === id);
            
            if(!teacher) {
                window.showToast("Teacher not found", "error");
                return;
            }

            document.getElementById('edit_id').value = teacher.id;
            document.getElementById('edit_employee_id').value = teacher.employee_id;
            document.getElementById('edit_name').value = teacher.name;
            document.getElementById('edit_name_kh').value = teacher.name_kh || '';
            document.getElementById('edit_department').value = teacher.department;
            document.getElementById('edit_email').value = teacher.email || '';
            document.getElementById('edit_phone').value = teacher.phone || '';
            document.getElementById('edit_position').value = teacher.position || '';
            document.getElementById('edit_telegram_chat_id').value = teacher.telegram_chat_id || '';
            document.getElementById('edit_status').value = teacher.status;

            // Photo preview
            const editForm = document.getElementById('editTeacherForm');
            const previewCont = document.getElementById('edit_photo_preview_container');
            const previewImg = document.getElementById('edit_photo_preview');
            const removeCheck = editForm.querySelector('input[name="remove_photo"]');
            
            if (removeCheck) removeCheck.checked = false; 

            if (teacher.photo) {
                previewImg.src = teacher.photo;
                previewCont.style.display = 'flex';
            } else {
                previewCont.style.display = 'none';
            }

            openModal('editTeacherModal');
        } catch(e) {
            await alert('Failed to load teacher data');
            console.error(e);
        }
    }

    async function submitEdit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.textContent = 'Updating...'; submitBtn.disabled = true;

        const formData = new FormData(form);
        const id = document.getElementById('edit_id').value;
        formData.append('_method', 'PUT'); 

        if (croppedBlob) {
            formData.set('photo', croppedBlob, 'photo.jpg');
        }

        try {
            await window.fetchApi(`{{ url('/api-web/teachers') }}/${id}`, {
                method: 'POST', 
                body: formData,
                headers: {}
            });
            window.location.reload();
        } catch (err) {
            await alert(err.message);
            submitBtn.textContent = 'Update Teacher'; submitBtn.disabled = false;
        }
    }

    async function pasteChatId(inputId, btn) {
        try {
            const text = await navigator.clipboard.readText();
            document.getElementById(inputId).value = text.trim();
            btn.innerHTML = '<i class="ph ph-check"></i> {{ __('Pasted!') }}';
            btn.classList.add('pasted');
            setTimeout(() => {
                btn.innerHTML = '<i class="ph ph-clipboard-text"></i> {{ __('Paste') }}';
                btn.classList.remove('pasted');
            }, 2000);
        } catch (err) {
            window.showToast('{{ __('Clipboard access denied. Please paste manually.') }}', 'error');
        }
    }

    async function removeTeacher(id) {
        if(!await confirm('Are you sure you want to permanently delete this teacher? This will also remove their RFID card assignments and attendance records.')) return;
        
        try {
            await window.fetchApi(`{{ url('/api-web/teachers') }}/${id}`, {
                method: 'DELETE'
            });
            window.location.reload();
        } catch(e) {
            await alert(e.message);
        }
    }

    // Global teacher list and university details for card printing
    const teachersData = @json($teachers->load('rfidCard'));
    const universityName = @json($uName);
    const universityNameKh = @json($uNameKh ?? 'វិទ្យាស្ថានជាតិបណ្ដុះបណ្ដាលបច្ចេកទេស');
    const universityLogo = @json($uLogo);
    
    // Department translation mapping
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

    function getPositionTitles(pos) {
        if (!pos) return { kh: 'គ្រូបង្រៀន', en: 'LECTURER' };
        const lower = pos.toLowerCase().trim();
        if (lower.includes('head') && lower.includes('department')) {
            return { kh: 'ប្រធានផ្នែក', en: 'HEAD OF DEPARTMENT' };
        }
        if (lower.includes('deputy') || lower.includes('vice')) {
            return { kh: 'អនុប្រធានផ្នែក', en: pos.toUpperCase() };
        }
        if (lower.includes('lecturer')) {
            return { kh: 'គ្រូបង្រៀន', en: 'LECTURER' };
        }
        if (lower.includes('instructor')) {
            return { kh: 'គ្រូបង្ហាត់', en: 'INSTRUCTOR' };
        }
        if (lower.includes('professor')) {
            return { kh: 'សាស្ត្រាចារ្យ', en: 'PROFESSOR' };
        }
        return { kh: pos, en: pos.toUpperCase() };
    }

    function generateCardHtml(teacher) {
        const deptLabel = window.transDept ? window.transDept(teacher.department) : teacher.department;
        const posInfo = getPositionTitles(teacher.position);
        const photoUrl = teacher.photo ? (teacher.photo.startsWith('http') || teacher.photo.startsWith('/') ? teacher.photo : '/' + teacher.photo) : null;

        const buildingSvg = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v8h20v-8a2 2 0 0 0-2-2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>`;
        const envelopeSvg = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>`;
        const phoneSvg = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>`;
        const briefcaseSvg = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>`;
        const footerPeopleSvg = `<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`;

        return `
            <div class="id-card">
                <div class="id-card-header">
                    <div class="id-card-header-left">
                        ${universityLogo ? `<img src="${universityLogo}" class="id-card-logo" alt="Logo">` : ''}
                        <div class="id-card-header-titles">
                            <div class="id-card-univ-name">${universityNameKh}</div>
                            <div class="id-card-sub-name">${universityName ? universityName.toUpperCase() : 'NATIONAL TECHNICAL TRAINING INSTITUTE'}</div>
                        </div>
                    </div>
                </div>
                
                <div class="id-card-body">
                    <div class="id-card-photo-col">
                        ${photoUrl ? `<img src="${photoUrl}" class="id-card-photo-img" alt="${teacher.name}">` : `
                            <div class="id-card-photo-img" style="display:flex;align-items:center;justify-content:center;font-size:14pt;font-weight:800;color:#0f2942;">
                                ${teacher.name.charAt(0)}
                            </div>
                        `}
                    </div>

                    <div class="id-card-info-col">
                        <div class="id-card-top-row">
                            <div>
                                <div class="id-card-name-kh">${teacher.name_kh || ''}</div>
                                <div class="id-card-name-en">${teacher.name}</div>
                            </div>
                            <div class="id-card-id-badge">ID: ${teacher.employee_id}</div>
                        </div>

                        <div class="id-card-details-grid">
                            <div class="id-card-grid-item-full">
                                <div class="id-card-grid-icon">${briefcaseSvg}</div>
                                <div class="id-card-grid-content">
                                    <span class="id-card-grid-label">POSITION</span>
                                    <span class="id-card-grid-value" title="${posInfo.en}">${posInfo.kh}${posInfo.kh !== posInfo.en ? ` (${posInfo.en})` : ''}</span>
                                </div>
                            </div>

                            <div class="id-card-grid-item-full">
                                <div class="id-card-grid-icon">${buildingSvg}</div>
                                <div class="id-card-grid-content">
                                    <span class="id-card-grid-label">DEPARTMENT</span>
                                    <span class="id-card-grid-value" title="${deptLabel}">${deptLabel}</span>
                                </div>
                            </div>

                            <div class="id-card-grid-item-full">
                                <div class="id-card-grid-icon">${phoneSvg}</div>
                                <div class="id-card-grid-content">
                                    <span class="id-card-grid-label">PHONE</span>
                                    <span class="id-card-grid-value">${teacher.phone || '—'}</span>
                                </div>
                            </div>

                            <div class="id-card-grid-item-full">
                                <div class="id-card-grid-icon">${envelopeSvg}</div>
                                <div class="id-card-grid-content">
                                    <span class="id-card-grid-label">EMAIL</span>
                                    <span class="id-card-grid-value" title="${teacher.email || '—'}">${teacher.email || '—'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function printTeacherCard(id) {
        const teacher = teachersData.find(t => t.id === id);
        if (!teacher) {
            window.showToast('{{ __('Teacher not found') }}', 'error');
            return;
        }
        
        const container = document.getElementById('print-cards-container');
        container.innerHTML = `
            <div class="print-card-page">
                <div class="cards-grid-8">
                    ${generateCardHtml(teacher)}
                </div>
            </div>
        `;
        container.style.display = 'block';
        
        document.body.classList.add('printing-cards');
        setTimeout(() => {
            window.print();
        }, 150);
    }

    function printFilteredCards() {
        const selectedDept = document.getElementById('printDepartmentSelect') ? document.getElementById('printDepartmentSelect').value : '';
        
        let listToPrint = teachersData;
        if (selectedDept) {
            listToPrint = teachersData.filter(t => t.department && t.department.toLowerCase() === selectedDept.toLowerCase());
        }
        
        if (listToPrint.length === 0) {
            window.showToast('{{ __('No teachers to print for selected department') }}', 'error');
            return;
        }
        
        const container = document.getElementById('print-cards-container');
        let pagesHtml = '';
        
        // Chunk teachers array into pages of 8 cards
        const chunkSize = 8;
        for (let i = 0; i < listToPrint.length; i += chunkSize) {
            const chunk = listToPrint.slice(i, i + chunkSize);
            let cardsInPage = '';
            chunk.forEach(teacher => {
                cardsInPage += generateCardHtml(teacher);
            });
            
            pagesHtml += `
                <div class="print-card-page">
                    <div class="cards-grid-8">
                        ${cardsInPage}
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = pagesHtml;
        container.style.display = 'block';
        
        document.body.classList.add('printing-cards');
        setTimeout(() => {
            window.print();
        }, 150);
    }

    window.addEventListener('afterprint', () => {
        document.body.classList.remove('printing-cards');
        const container = document.getElementById('print-cards-container');
        if (container) {
            container.innerHTML = '';
            container.style.display = 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'register') {
            openModal('addTeacherModal');
        }
    });
</script>
@endpush
