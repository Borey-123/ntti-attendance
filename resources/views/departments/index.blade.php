@extends('layouts.app')

@section('title', __('Department Management'))

@section('content')
@push('styles')
<style>
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

</style>
@endpush
<div class="d-flex justify-between align-center" style="margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="page-title" style="margin-bottom: 0.25rem;">{{ __('Department Control Center') }}</h1>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Manage organizational hierarchy and leadership.') }}</p>
    </div>
    <button class="btn btn-primary" onclick="openDeptModal()" style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 1.25rem; padding: 0.8rem 1.5rem; font-weight: 800; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.25); transition: all 0.2s ease; width: fit-content;">
        <i class="ph ph-plus-circle" style="font-size: 1.2rem;"></i> <span>{{ __('Add Department') }}</span>
    </button>
</div>

<div class="animate-fade-up">
    {{-- ── Summary Metrics ── --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
        <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="ph ph-buildings"></i>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Total Departments') }}</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary);">{{ $departments->count() }}</div>
        </div>
    </div>
    <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
        <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="ph ph-users-four"></i>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Total Staff') }}</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary);">{{ $departments->sum('teachers_count') }}</div>
        </div>
    </div>
    <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
        <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="ph ph-user-gear"></i>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Active HODs') }}</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary);">{{ $departments->whereNotNull('head_id')->count() }}</div>
        </div>
    </div>
    <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; border-radius: 1.5rem;">
        <div style="width: 54px; height: 54px; border-radius: 1.25rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="ph ph-user-minus"></i>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">{{ __('Unassigned HODs') }}</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary);">{{ $departments->whereNull('head_id')->count() }}</div>
        </div>
    </div>
</div>

<div class="card" style="border-radius: 2rem; overflow: hidden;">
    <div class="card-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <h3 style="margin-bottom: 0; font-weight: 800; display: flex; align-items: center; gap: 0.75rem; width: 250px;">
            <i class="ph ph-list-numbers"></i>
            {{ __('Department Registry') }}
            <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary);">({{ $departments->count() }})</span>
        </h3>
        
        <div style="flex: 1;"></div>

        <div style="position: relative; width: 250px; display: flex; justify-content: flex-end;">
            <div style="position: relative; width: 100%;">
                <i class="ph ph-magnifying-glass" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                <input type="text" id="deptSearch" placeholder="{{ __('Search department...') }}" oninput="searchDepts()" class="form-control" style="width: 100%; padding-left: 2.2rem; border-radius: 1rem; background: var(--bg-elevated); font-size: 0.85rem; height: 38px; border: 1px solid var(--border);">
            </div>
        </div>
    </div>
    <div style="overflow-x: auto;">
    <table class="table" style="min-width: 700px;">
        <thead>
            <tr>
                <th style="width: 60px; text-align: center;">{{ __('No.') }}</th>
                <th>{{ __('Department Name') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Department Head') }}</th>
                <th style="text-align: center; width: 100px;">{{ __('Staff') }}</th>
                <th style="width: 130px; text-align: center;">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $index => $dept)
            <tr class="dept-row" data-name="{{ strtolower($dept->name . ' ' . ($dept->name_kh ?? '') . ' ' . ($dept->description ?? '')) }}">
                <td style="text-align: center;">
                    <div style="width: 32px; height: 32px; border-radius: 0.6rem; background: rgba(var(--primary-rgb), 0.08); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                        {{ $index + 1 }}
                    </div>
                </td>
                <td>
                    <div style="cursor: pointer;" onclick="viewStaff({{ $dept->id }})" title="{{ __('Click to view staff list') }}">
                        <div style="font-weight: 800; color: var(--primary); font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
                            {{ app()->getLocale() == 'km' ? ($dept->name_kh ?: $dept->name) : $dept->name }}
                            <i class="ph ph-arrow-square-out" style="font-size: 0.7rem; opacity: 0.35;"></i>
                        </div>
                        @if(app()->getLocale() == 'km' && $dept->name)
                            <div style="font-size: 0.78rem; color: var(--text-secondary); font-weight: 500; margin-top: 2px;">{{ $dept->name }}</div>
                        @elseif($dept->name_kh)
                            <div style="font-size: 0.78rem; color: var(--text-secondary); font-weight: 500; margin-top: 2px;">{{ $dept->name_kh }}</div>
                        @endif
                    </div>
                </td>
                <td>
                    <span style="color: var(--text-secondary); font-size: 0.88rem;">{{ Str::limit($dept->description, 40) ?? '—' }}</span>
                </td>
                <td>
                    @if($dept->head)
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <div style="width: 34px; height: 34px; border-radius: 50%; background: var(--bg-elevated); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.75rem; color: var(--primary); overflow: hidden; border: 2px solid rgba(var(--primary-rgb), 0.2); flex-shrink: 0;">
                                @if($dept->head->photo)
                                    <img src="{{ to_asset_url($dept->head->photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    {{ substr($dept->head->name, 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 0.9rem; line-height: 1.2;">{{ app()->getLocale() == 'km' ? ($dept->head->name_kh ?: $dept->head->name) : $dept->head->name }}</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $dept->head->position ?? 'HOD' }}</div>
                            </div>
                        </div>
                    @else
                        <span style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">{{ __('Unassigned') }}</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(16, 185, 129, 0.08); color: #10b981; padding: 0.3rem 0.75rem; border-radius: 2rem; font-weight: 800; font-size: 0.85rem;">
                        <i class="ph ph-users" style="font-size: 0.8rem;"></i>
                        {{ $dept->teachers_count }}
                    </div>
                </td>
                <td>
                    <div style="display: flex; gap: 0.4rem; justify-content: center;">
                        <button class="btn btn-sm btn-info" onclick="viewStaff({{ $dept->id }})" title="{{ __('View Staff') }}" style="border-radius: 0.6rem; width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="ph ph-users" style="font-size: 1.15rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-edit-premium" onclick="editDept({{ $dept->id }}, '{{ addslashes($dept->name) }}', '{{ addslashes($dept->name_kh ?? '') }}', '{{ addslashes($dept->description ?? '') }}', {{ $dept->head_id ?? 'null' }})" title="{{ __('Edit') }}" style="border-radius: 0.6rem; width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="ph ph-pencil-simple" style="font-size: 1.15rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteDept({{ $dept->id }})" title="{{ __('Delete') }}" style="border-radius: 0.6rem; width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="ph ph-trash" style="font-size: 1.15rem;"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
            @if($departments->isEmpty())
            <tr>
                <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <i class="ph ph-buildings" style="font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 0.75rem;"></i>
                    {{ __('No departments found. Click "Add Department" to get started.') }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>
    </div>
</div>

<!-- Department Modal -->
<div class="modal-overlay" id="deptModal" onclick="if(event.target == this) closeModal('deptModal')">
    <div class="modal-content" style="border-radius: 2rem; padding: 2.5rem;">
        <div class="modal-header" style="margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <h3 id="deptModalTitle" style="font-weight: 800; color: var(--primary);">{{ __('Add Department') }}</h3>
            <button class="modal-close" onclick="closeModal('deptModal')">&times;</button>
        </div>
        <form id="deptForm" onsubmit="submitDept(event)">
            <input type="hidden" id="dept_id" name="id">
            <div class="form-group">
                <label>{{ __('Department Name (English)') }}</label>
                <input type="text" id="dept_name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>{{ __('Department Name (Khmer)') }}</label>
                <input type="text" id="dept_name_kh" name="name_kh" class="form-control">
            </div>
            <div class="form-group">
                <label>{{ __('Description') }}</label>
                <textarea id="dept_description" name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>{{ __('Department Head') }}</label>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div id="head_photo_preview" style="width: 50px; height: 50px; border-radius: 50%; background: var(--bg-dark); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid var(--border); flex-shrink: 0;">
                        <i class="ph ph-user" style="font-size: 1.5rem; opacity: 0.3;"></i>
                    </div>
                    <select id="dept_head_id" name="head_id" class="form-control" style="background-color: var(--bg-dark);" onchange="updateHeadPreview()">
                        <option value="">{{ __('None') }}</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" data-photo="{{ $t->photo }}" data-department="{{ $t->department }}">{{ app()->getLocale() == 'km' ? ($t->name_kh ?: $t->name) : $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex justify-between align-center mt-4">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deptModal')">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary" style="width: auto;">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="staffModal" onclick="if(event.target == this) closeModal('staffModal')">
    <div class="modal-content" style="width: 500px; max-height: 85vh; border-radius: 2rem; padding: 0; overflow: hidden; display: flex; flex-direction: column;">
        <div class="modal-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); background: var(--bg-card);">
            <h3 id="staffModalTitle" style="margin: 0; font-weight: 800; color: var(--primary);">{{ __('Department Staff') }}</h3>
            <button class="modal-close" onclick="closeModal('staffModal')">&times;</button>
        </div>
        <div id="staffListContent" style="padding: 2rem; overflow-y: auto; flex: 1; background: var(--bg-dark);">
            {{-- Populated via JS --}}
        </div>
        <div class="modal-footer" style="padding: 1.25rem 2rem; border-top: 1px solid var(--border); background: var(--bg-card); text-align: right;">
            <button class="btn btn-secondary" style="border-radius: 1rem; padding: 0.6rem 1.5rem;" onclick="closeModal('staffModal')">{{ __('Close') }}</button>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
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
    function updateHeadPreview() {
        const select = document.getElementById('dept_head_id');
        const preview = document.getElementById('head_photo_preview');
        const selectedOption = select.options[select.selectedIndex];
        const photo = selectedOption.getAttribute('data-photo');
        
        if (photo && photo !== '') {
            preview.innerHTML = `<img src="${photo}" style="width: 100%; height: 100%; object-fit: cover;">`;
            preview.style.borderColor = 'var(--primary)';
        } else {
            preview.innerHTML = `<i class="ph ph-user" style="font-size: 1.5rem; opacity: 0.3;"></i>`;
            preview.style.borderColor = 'var(--border)';
        }
    }

    function filterTeachersByDept(deptName) {
        const select = document.getElementById('dept_head_id');
        const options = select.querySelectorAll('option[data-department]');
        
        options.forEach(opt => {
            const tDept = opt.getAttribute('data-department');
            if (deptName && tDept === deptName) {
                opt.hidden = false;
                opt.disabled = false;
            } else {
                opt.hidden = true;
                opt.disabled = true;
            }
        });
        
        const currentOpt = select.options[select.selectedIndex];
        if (currentOpt && currentOpt.hidden) {
            select.value = "";
        }
    }

    function openDeptModal() {
        document.getElementById('deptForm').reset();
        document.getElementById('dept_id').value = '';
        document.getElementById('deptModalTitle').textContent = '{{ __("Add Department") }}';
        filterTeachersByDept(''); // Hide all for new dept
        updateHeadPreview();
        document.getElementById('deptModal').classList.add('active');
    }

    function editDept(id, name, name_kh, desc, head_id) {
        document.getElementById('deptForm').reset();
        document.getElementById('dept_id').value = id;
        document.getElementById('dept_name').value = name;
        document.getElementById('dept_name_kh').value = name_kh || '';
        document.getElementById('dept_description').value = desc;
        
        filterTeachersByDept(name);
        
        document.getElementById('dept_head_id').value = head_id || '';
        updateHeadPreview();
        document.getElementById('deptModalTitle').textContent = '{{ __("Edit Department") }}';
        document.getElementById('deptModal').classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    async function submitDept(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        const id = document.getElementById('dept_id').value;
        const isEdit = id !== '';
        const url = isEdit ? `{{ url('/api-web/departments') }}/${id}` : `{{ route('api.departments.store') }}`;
        const method = isEdit ? 'PUT' : 'POST';

        const data = {
            name: document.getElementById('dept_name').value,
            name_kh: document.getElementById('dept_name_kh').value,
            description: document.getElementById('dept_description').value,
            head_id: document.getElementById('dept_head_id').value || null
        };

        try {
            await window.fetchApi(url, {
                method: method,
                body: JSON.stringify(data),
                headers: { 'Content-Type': 'application/json' }
            });
            window.location.reload();
        } catch (err) {
            await alert(err.message);
            submitBtn.disabled = false;
        }
    }

    async function deleteDept(id) {
        if(!await confirm('Are you sure you want to delete this department?')) return;
        
        try {
            await window.fetchApi(`{{ url('/api-web/departments') }}/${id}`, {
                method: 'DELETE'
            });
            window.location.reload();
        } catch(e) {
            await alert(e.message);
        }
    }

    function searchDepts() {
        const query = document.getElementById('deptSearch').value.toLowerCase().trim();
        document.querySelectorAll('.dept-row').forEach(row => {
            const data = row.getAttribute('data-name') || '';
            row.style.display = !query || data.includes(query) ? '' : 'none';
        });
    }

    const deptData = @json($departments);

    function viewStaff(id) {
        const dept = deptData.find(d => d.id === id);
        if (!dept) return;

        const deptDisplayName = window.transDept(dept.name);
        const staffCount = dept.teachers ? dept.teachers.length : 0;
        document.getElementById('staffModalTitle').innerHTML = `${deptDisplayName} <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-left: 0.5rem;">(${staffCount} {{ __('staff') }})</span>`;
        const content = document.getElementById('staffListContent');
        
        if (!dept.teachers || dept.teachers.length === 0) {
            content.innerHTML = `<div style="text-align:center; padding:2rem; color:var(--text-muted);"><i class="ph ph-users" style="font-size:2rem; opacity:0.3; display:block; margin-bottom:0.5rem;"></i>{{ __('No staff assigned to this department.') }}</div>`;
        } else {
            let html = `<div style="display: flex; flex-direction: column; gap: 0.75rem;">`;
            dept.teachers.forEach((t, i) => {
                html += `
                <div style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem; background: var(--bg-dark); border-radius: 0.75rem; border: 1px solid var(--border); cursor: pointer; transition: all 0.2s;" 
                     onclick="window.openTeacherInsights(${t.id})"
                     onmouseover="this.style.borderColor='var(--primary)'" 
                     onmouseout="this.style.borderColor='var(--border)'">
                    <div style="width: 26px; height: 26px; border-radius: 0.4rem; background: rgba(var(--primary-rgb), 0.08); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.75rem; flex-shrink: 0;">${i + 1}</div>
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: #000; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; overflow:hidden; flex-shrink: 0;">
                        ${t.photo ? `<img src="${t.photo}" style="width:100%; height:100%; object-fit:cover;">` : t.name.charAt(0)}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 700; color: var(--text-primary);">${'{{ app()->getLocale() }}' === 'km' ? (t.name_kh || t.name) : t.name}</div>
                        <div style="font-size: 0.75rem; color: var(--text-secondary);">${t.employee_id} • ${t.position || 'Teacher'}</div>
                    </div>
                    <i class="ph ph-arrow-right" style="color: var(--text-muted); font-size: 0.85rem; flex-shrink: 0;"></i>
                </div>`;
            });
            html += `</div>`;
            content.innerHTML = html;
        }

        document.getElementById('staffModal').classList.add('active');
    }
</script>
@endpush

