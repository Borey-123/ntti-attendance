<?php
$file = 'c:\\xampp\\htdocs\\sana_project\\Final_Project\\NTTI_Teacher_Attendent\\ntti-attendance\\resources\\views\\teachers\\index.blade.php';
$content = file_get_contents($file);

// 1. Add Reset PIN button to .t-card-actions
$searchActions = '<div class="t-card-actions">';
$resetBtn = '
            <button class="btn btn-sm btn-card" style="flex: 0 0 45px; padding: 0; background: rgba(16, 185, 129, 0.1); border: 2.5px solid #10b981; color: #10b981; font-weight: 800;" onclick="openResetPinModal({{ $teacher->id }}, \'{{ addslashes($teacher->name) }}\')" title="{{ __(\'Reset Portal PIN\') }}">
                <i class="ph ph-key"></i>
            </button>';

// Only add if not already added
if (strpos($content, 'openResetPinModal') === false) {
    // Replace the opening of t-card-actions to include the new button right after the Edit button
    $editBtnEnd = '<i class="ph ph-pencil-simple"></i> {{ __(\'Edit\') }}
            </button>';
    $content = str_replace($editBtnEnd, $editBtnEnd . $resetBtn, $content);

    // 2. Add Reset PIN Modal HTML
    $modalHTML = '
<!-- Reset PIN Modal -->
<div class="modal-overlay" id="resetPinModal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>{{ __(\'Reset Portal PIN\') }}</h3>
            <button class="modal-close" onclick="closeModal(\'resetPinModal\')">&times;</button>
        </div>
        <form id="resetPinForm" onsubmit="submitResetPin(event)">
            <input type="hidden" id="reset_pin_teacher_id">
            <p style="margin-top: 0; margin-bottom: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                {{ __(\'Set a new 6-digit Portal PIN for:\') }} <strong id="reset_pin_teacher_name" style="color: var(--text-primary);"></strong>
            </p>
            <div class="form-group">
                <label>{{ __(\'New PIN (6 digits)\') }}</label>
                <input type="password" id="reset_pin_input" class="form-control" required pattern="\d{6}" maxlength="6" inputmode="numeric" placeholder="123456">
            </div>
            <div class="d-flex justify-between align-center mt-4">
                <button type="button" class="btn btn-secondary" onclick="closeModal(\'resetPinModal\')">{{ __(\'Cancel\') }}</button>
                <button type="submit" class="btn btn-primary" style="width: auto; background: var(--success); border-color: var(--success);">{{ __(\'Save PIN\') }}</button>
            </div>
        </form>
    </div>
</div>
';

    // Insert modal before the final @endsection (which is at the end of the file, or near other modals)
    $content = str_replace('<!-- Edit Modal -->', $modalHTML . "\n<!-- Edit Modal -->", $content);

    // 3. Add JS functions for Reset PIN
    $jsFunctions = '
    function openResetPinModal(id, name) {
        document.getElementById("reset_pin_teacher_id").value = id;
        document.getElementById("reset_pin_teacher_name").textContent = name;
        document.getElementById("reset_pin_input").value = "";
        openModal("resetPinModal");
    }

    async function submitResetPin(e) {
        e.preventDefault();
        const id = document.getElementById("reset_pin_teacher_id").value;
        const pin = document.getElementById("reset_pin_input").value;
        const btn = e.target.querySelector("button[type=submit]");
        const originalText = btn.innerHTML;
        
        btn.innerHTML = `<i class="ph ph-spinner ph-spin"></i>`;
        btn.disabled = true;

        try {
            const res = await fetch(`/api-web/teachers/${id}/reset-pin`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(\'meta[name="csrf-token"]\').content
                },
                body: JSON.stringify({ pin: pin })
            });
            const data = await res.json();
            
            if (data.status === "success") {
                showToast("success", data.message);
                closeModal("resetPinModal");
            } else {
                showToast("error", data.message || "Failed to reset PIN");
            }
        } catch (err) {
            showToast("error", "Network Error");
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
';

    // Insert JS before the closing script tag of the main script block
    $content = str_replace('function confirmRfid(', $jsFunctions . "\n    function confirmRfid(", $content);

    file_put_contents($file, $content);
    echo "Teachers blade file updated.";
} else {
    echo "Already updated.";
}
