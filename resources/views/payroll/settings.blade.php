@extends('layouts.app')

@section('title', __('Payroll Settings'))

@section('content')
<div style="max-width:700px; margin:0 auto;">

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:2rem;">
    <a href="{{ route('payroll.index') }}" class="btn btn-secondary" style="padding:0.5rem 0.75rem;">
        <i class="ph ph-arrow-left"></i>
    </a>
    <div>
        <h1 style="font-size:1.5rem; font-weight:900; margin:0; display:flex; align-items:center; gap:0.75rem;">
            <span style="width:38px; height:38px; background:linear-gradient(135deg,#f59e0b,#d97706); border-radius:0.75rem; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">⚙️</span>
            {{ __('Payroll Settings') }}
        </h1>
        <p style="color:var(--text-secondary); margin:0.25rem 0 0; font-size:0.85rem;">{{ __('Configure salary calculation rules') }}</p>
    </div>
</div>

<form id="settingsForm">
    @csrf
    <div style="display:flex; flex-direction:column; gap:1.5rem;">

        {{-- Currency --}}
        <div class="card" style="border-radius:1.25rem; padding:1.5rem;">
            <h3 style="margin:0 0 1.25rem; font-size:0.95rem; font-weight:800; display:flex; align-items:center; gap:0.5rem;">
                <i class="ph ph-currency-dollar" style="color:#10b981;"></i> {{ __('Currency') }}
            </h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Currency Code') }}</label>
                    <input type="text" name="currency" class="form-control" value="{{ $settings->firstWhere('key','currency')?->value ?? 'USD' }}" placeholder="USD">
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">{{ __('Currency Symbol') }}</label>
                    <input type="text" name="currency_symbol" class="form-control" value="{{ $settings->firstWhere('key','currency_symbol')?->value ?? '$' }}" placeholder="$">
                </div>
            </div>
        </div>

        {{-- Deduction Rules --}}
        <div class="card" style="border-radius:1.25rem; padding:1.5rem;">
            <h3 style="margin:0 0 1.25rem; font-size:0.95rem; font-weight:800; display:flex; align-items:center; gap:0.5rem;">
                <i class="ph ph-minus-circle" style="color:#ef4444;"></i> {{ __('Deduction Rules') }}
            </h3>
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">
                        {{ __('Late Deduction per Minute') }}
                        <span style="color:var(--text-muted); font-weight:400;">(e.g. 0.50 = $0.50 per minute late)</span>
                    </label>
                    <input type="number" name="late_deduction_per_minute" class="form-control" step="0.01" min="0"
                           value="{{ $settings->firstWhere('key','late_deduction_per_minute')?->value ?? '0.50' }}">
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">
                        {{ __('Absent Deduction Rate') }}
                        <span style="color:var(--text-muted); font-weight:400;">(multiplier on daily rate, e.g. 1.0 = full day deduction)</span>
                    </label>
                    <input type="number" name="absent_deduction_rate" class="form-control" step="0.1" min="0" max="3"
                           value="{{ $settings->firstWhere('key','absent_deduction_rate')?->value ?? '1.0' }}">
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">
                        {{ __('Working Days per Month') }}
                        <span style="color:var(--text-muted); font-weight:400;">(default, used when not counting from calendar)</span>
                    </label>
                    <input type="number" name="working_days_per_month" class="form-control" min="1" max="31"
                           value="{{ $settings->firstWhere('key','working_days_per_month')?->value ?? '22' }}">
                </div>
                <div>
                    <label style="display:flex; align-items:center; gap:0.75rem; cursor:pointer;">
                        <input type="checkbox" name="approved_leave_is_paid" value="1"
                               {{ ($settings->firstWhere('key','approved_leave_is_paid')?->value ?? '1') == '1' ? 'checked' : '' }}
                               style="width:18px; height:18px;">
                        <div>
                            <div style="font-weight:700; font-size:0.9rem;">{{ __('Approved Leave is Paid') }}</div>
                            <div style="font-size:0.78rem; color:var(--text-muted);">{{ __('If checked, approved leave days count as present for salary calculation') }}</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Bonus & Overtime --}}
        <div class="card" style="border-radius:1.25rem; padding:1.5rem;">
            <h3 style="margin:0 0 1.25rem; font-size:0.95rem; font-weight:800; display:flex; align-items:center; gap:0.5rem;">
                <i class="ph ph-plus-circle" style="color:#10b981;"></i> {{ __('Bonus & Overtime') }}
            </h3>
            <div>
                <label style="font-size:0.82rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.4rem;">
                    {{ __('Overtime Rate per Hour') }}
                    <span style="color:var(--text-muted); font-weight:400;">(USD)</span>
                </label>
                <input type="number" name="overtime_rate_per_hour" class="form-control" step="0.50" min="0"
                       value="{{ $settings->firstWhere('key','overtime_rate_per_hour')?->value ?? '2.50' }}">
            </div>
        </div>

        {{-- Payslip Note --}}
        <div class="card" style="border-radius:1.25rem; padding:1.5rem;">
            <h3 style="margin:0 0 1.25rem; font-size:0.95rem; font-weight:800; display:flex; align-items:center; gap:0.5rem;">
                <i class="ph ph-note-pencil" style="color:#6366f1;"></i> {{ __('Payslip Footer Note') }}
            </h3>
            <textarea name="payroll_note_footer" class="form-control" rows="3"
                      placeholder="{{ __('Note printed at the bottom of each payslip...') }}">{{ $settings->firstWhere('key','payroll_note_footer')?->value ?? '' }}</textarea>
        </div>

        <div style="display:flex; gap:0.75rem;">
            <button type="submit" class="btn btn-primary" id="saveBtn" style="flex:1;">
                <i class="ph ph-floppy-disk"></i> {{ __('Save Settings') }}
            </button>
            <a href="{{ route('payroll.index') }}" class="btn btn-secondary" style="flex:1; text-align:center; display:flex; align-items:center; justify-content:center;">
                {{ __('Cancel') }}
            </a>
        </div>
        <div id="saveResult" style="display:none;"></div>
    </div>
</form>

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    const result = document.getElementById('saveResult');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin" style="display:inline-block">⟳</span> Saving...';

    const formData = new FormData(this);
    const payload = {};
    formData.forEach((v, k) => payload[k] = v);
    // Handle unchecked checkbox
    if (!formData.has('approved_leave_is_paid')) payload.approved_leave_is_paid = 0;

    try {
        const res = await fetch('{{ route("payroll.settings.save") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        result.style.display = 'block';
        result.innerHTML = data.success
            ? '<div style="background:rgba(0,212,160,0.1); border:1px solid rgba(0,212,160,0.3); border-radius:0.75rem; padding:0.875rem; color:#00d4a0; font-weight:700;">✅ Settings saved successfully!</div>'
            : '<div style="color:var(--danger);">Error saving settings.</div>';
        setTimeout(() => result.style.display = 'none', 3000);
    } catch(err) {
        result.style.display = 'block';
        result.innerHTML = `<div style="color:var(--danger);">Error: ${err.message}</div>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ph ph-floppy-disk"></i> Save Settings';
    }
});
</script>
@endpush
</div>
@endsection
