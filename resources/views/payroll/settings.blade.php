@extends('layouts.app')

@section('title', __('Payroll Settings'))

@section('content')
<div class="animate-fade-up" style="max-width: 800px; margin: 0 auto;">

    {{-- ── Header ── --}}
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:2rem;">
        <a href="{{ route('payroll.index') }}" class="btn btn-secondary" style="border-radius:0.75rem; padding:0.6rem 0.85rem;" title="{{ __('Back') }}">
            <i class="ph ph-arrow-left" style="font-size:1.15rem;"></i>
        </a>
        <div>
            <h1 style="font-size:1.6rem; font-weight:800; margin:0; display:flex; align-items:center; gap:0.75rem;">
                <span style="width:42px; height:42px; background:linear-gradient(135deg,rgba(245,158,11,0.2),rgba(217,119,6,0.2)); border:1px solid rgba(245,158,11,0.3); border-radius:1rem; display:flex; align-items:center; justify-content:center; font-size:1.35rem; color:#f59e0b;">⚙️</span>
                <span>{{ __('Payroll Settings') }}</span>
            </h1>
            <p style="color:var(--text-secondary); margin:0.25rem 0 0; font-size:0.9rem;">
                {{ __('Configure salary calculation rules, late/absent deductions, working days, and payslip notes.') }}
            </p>
        </div>
    </div>

    <form id="settingsForm">
        @csrf
        <div style="display:flex; flex-direction:column; gap:1.75rem;">

            {{-- 1. Deduction Rules --}}
            <div class="card" style="border-radius:1.5rem; padding:1.75rem; border:1px solid var(--border);">
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--border);">
                    <div style="width:40px; height:40px; border-radius:0.75rem; background:rgba(239,68,68,0.12); color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:1.35rem;">
                        <i class="ph ph-minus-circle"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.1rem; font-weight:800; color:var(--text-primary);">{{ __('Deduction Rules') }}</h3>
                        <div style="font-size:0.8rem; color:var(--text-secondary);">{{ __('Rules for late arrival and unauthorized absence') }}</div>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:1.25rem;">
                    <div class="form-group">
                        <label style="font-weight:700; display:flex; justify-content:space-between; align-items:center;">
                            <span>{{ __('Late Deduction Rate ($ / min)') }}</span>
                            <span style="font-size:0.78rem; color:#f59e0b; font-weight:normal;">ឧ. 0.50 = កាត់ $0.50 ក្នុង 1 នាទីយឺត</span>
                        </label>
                        <div style="position:relative;">
                            <input type="number" name="late_deduction_per_minute" class="form-control" step="0.01" min="0"
                                   value="{{ $settings->firstWhere('key','late_deduction_per_minute')?->value ?? '0.50' }}"
                                   style="padding-left:2.2rem; font-weight:700; border-radius:0.75rem;">
                            <span style="position:absolute; left:0.85rem; top:50%; transform:translateY(-50%); font-weight:800; color:var(--primary);">$</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="font-weight:700; display:flex; justify-content:space-between; align-items:center;">
                            <span>{{ __('Absent Deduction Rate (Daily Rate Multiplier)') }}</span>
                            <span style="font-size:0.78rem; color:#ef4444; font-weight:normal;">1.0 = កាត់ 100% នៃប្រាក់ខែប្រចាំថ្ងៃ</span>
                        </label>
                        <input type="number" name="absent_deduction_rate" class="form-control" step="0.1" min="0" max="3"
                               value="{{ $settings->firstWhere('key','absent_deduction_rate')?->value ?? '1.0' }}"
                               style="font-weight:700; border-radius:0.75rem;">
                    </div>
                </div>
            </div>

            {{-- 2. Working Days & Leaves --}}
            <div class="card" style="border-radius:1.5rem; padding:1.75rem; border:1px solid var(--border);">
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--border);">
                    <div style="width:40px; height:40px; border-radius:0.75rem; background:rgba(16,185,129,0.12); color:#10b981; display:flex; align-items:center; justify-content:center; font-size:1.35rem;">
                        <i class="ph ph-calendar-check"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.1rem; font-weight:800; color:var(--text-primary);">{{ __('Working Days & Leave Policy') }}</h3>
                        <div style="font-size:0.8rem; color:var(--text-secondary);">{{ __('Standard workdays and paid leave calculation rules') }}</div>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:1.25rem;">
                    <div class="form-group">
                        <label style="font-weight:700; display:flex; justify-content:space-between; align-items:center;">
                            <span>{{ __('Standard Working Days per Month') }}</span>
                            <span style="font-size:0.78rem; color:var(--text-muted);">ប្រើសម្រាប់ចែករកប្រាក់ខែប្រចាំថ្ងៃ (Daily Rate)</span>
                        </label>
                        <input type="number" name="working_days_per_month" class="form-control" min="1" max="31"
                               value="{{ $settings->firstWhere('key','working_days_per_month')?->value ?? '22' }}"
                               style="font-weight:700; border-radius:0.75rem;">
                    </div>

                    <div style="background:rgba(255,255,255,0.02); border:1px solid var(--border); border-radius:1rem; padding:1rem 1.25rem;">
                        <label style="display:flex; align-items:center; gap:0.85rem; cursor:pointer; margin:0;">
                            <input type="checkbox" name="approved_leave_is_paid" value="1"
                                   {{ ($settings->firstWhere('key','approved_leave_is_paid')?->value ?? '1') == '1' ? 'checked' : '' }}
                                   style="width:20px; height:20px; accent-color:var(--primary); cursor:pointer;">
                            <div>
                                <div style="font-weight:800; font-size:0.95rem; color:var(--text-primary);">
                                    {{ __('Approved Leave is Paid') }}
                                </div>
                                <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:2px;">
                                    {{ __('If checked, approved leave days count as present for salary calculation') }}
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- 3. Currency & Overtime --}}
            <div class="card" style="border-radius:1.5rem; padding:1.75rem; border:1px solid var(--border);">
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--border);">
                    <div style="width:40px; height:40px; border-radius:0.75rem; background:rgba(99,102,241,0.12); color:#818cf8; display:flex; align-items:center; justify-content:center; font-size:1.35rem;">
                        <i class="ph ph-currency-dollar"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.1rem; font-weight:800; color:var(--text-primary);">{{ __('Currency & Overtime') }}</h3>
                        <div style="font-size:0.8rem; color:var(--text-secondary);">{{ __('Display currency and hourly overtime rate') }}</div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                    <div class="form-group">
                        <label style="font-weight:700;">{{ __('Currency Code') }}</label>
                        <input type="text" name="currency" class="form-control"
                               value="{{ $settings->firstWhere('key','currency')?->value ?? 'USD' }}"
                               placeholder="USD" style="border-radius:0.75rem; font-weight:700;">
                    </div>

                    <div class="form-group">
                        <label style="font-weight:700;">{{ __('Currency Symbol') }}</label>
                        <input type="text" name="currency_symbol" class="form-control"
                               value="{{ $settings->firstWhere('key','currency_symbol')?->value ?? '$' }}"
                               placeholder="$" style="border-radius:0.75rem; font-weight:700;">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label style="font-weight:700;">{{ __('Overtime Rate ($ / hour)') }}</label>
                        <div style="position:relative;">
                            <input type="number" name="overtime_rate_per_hour" class="form-control" step="0.50" min="0"
                                   value="{{ $settings->firstWhere('key','overtime_rate_per_hour')?->value ?? '2.50' }}"
                                   style="padding-left:2.2rem; font-weight:700; border-radius:0.75rem;">
                            <span style="position:absolute; left:0.85rem; top:50%; transform:translateY(-50%); font-weight:800; color:var(--primary);">$</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Payslip Note Footer --}}
            <div class="card" style="border-radius:1.5rem; padding:1.75rem; border:1px solid var(--border);">
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--border);">
                    <div style="width:40px; height:40px; border-radius:0.75rem; background:rgba(245,158,11,0.12); color:#f59e0b; display:flex; align-items:center; justify-content:center; font-size:1.35rem;">
                        <i class="ph ph-note-pencil"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.1rem; font-weight:800; color:var(--text-primary);">{{ __('Payslip Footer Note') }}</h3>
                        <div style="font-size:0.8rem; color:var(--text-secondary);">{{ __('Official statement printed at the bottom of each teacher payslip PDF') }}</div>
                    </div>
                </div>

                <div class="form-group" style="margin:0;">
                    <textarea name="payroll_note_footer" class="form-control" rows="3" style="border-radius:0.75rem;"
                              placeholder="{{ __('Note printed at the bottom of each payslip...') }}">{{ $settings->firstWhere('key','payroll_note_footer')?->value ?? '' }}</textarea>
                </div>
            </div>

            {{-- Buttons --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <a href="{{ route('payroll.index') }}" class="btn btn-secondary" style="border-radius:1rem; font-weight:700; padding:0.9rem; justify-content:center;">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-primary" id="saveBtn" style="border-radius:1rem; font-weight:800; padding:0.9rem; justify-content:center; box-shadow:0 8px 20px rgba(var(--primary-rgb),0.35);">
                    <i class="ph ph-floppy-disk" style="font-size:1.2rem;"></i>
                    <span>{{ __('Save Payroll Settings') }}</span>
                </button>
            </div>

        </div>
    </form>

</div>

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const ok = await window.confirmModal({
        title: '{{ __("Please Confirm") }}',
        message: 'តើអ្នកយល់ព្រមរក្សាទុកការកំណត់ប្រព័ន្ធប្រាក់បៀវត្សរ៍នេះដែរឬទេ?',
        confirmText: '{{ __("Agree") }}',
        cancelText: '{{ __("Disagree") }}',
        type: 'primary'
    });
    if (!ok) return;

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Saving...';

    const formData = new FormData(this);
    const payload = {};
    formData.forEach((v, k) => payload[k] = v);
    if (!formData.has('approved_leave_is_paid')) payload.approved_leave_is_paid = 0;

    try {
        const res = await fetch('{{ route("payroll.settings.save") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.status === 'success') {
            if (window.showToast) window.showToast('{{ __("Settings saved successfully!") }}', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            alert(data.message || 'Error saving settings');
        }
    } catch(err) {
        alert('Error: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ph ph-floppy-disk"></i> {{ __("Save Payroll Settings") }}';
    }
});
</script>
@endpush
@endsection
