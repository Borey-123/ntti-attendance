<?php
$file = 'c:\\xampp\\htdocs\\sana_project\\Final_Project\\NTTI_Teacher_Attendent\\ntti-attendance\\resources\\views\\portal\\index.blade.php';
$content = file_get_contents($file);

// 1. Remove @if(!$teacher) ... @else
$startStr = '@if(!$teacher)';
$endStr = '@else';
$startPos = strpos($content, $startStr);
$endPos = strpos($content, $endStr, $startPos);
if ($startPos !== false && $endPos !== false) {
    $content = substr($content, 0, $startPos) . substr($content, $endPos + strlen($endStr));
}

// 2. Remove the trailing @endif of that block.
// The block ends around <a href="{{ route('portal.index') }}" class="btn-back" ... </a> \n </div> \n @endif
$btnBackStart = '<a href="{{ route(\'portal.index\') }}" class="btn-back"';
$btnBackPos = strpos($content, $btnBackStart);

if ($btnBackPos !== false) {
    // Replace "Check Another ID" button with Logout and Change Password buttons
    $buttons = '
                    <div style="display: flex; gap: 1rem; margin-top: 2.5rem;">
                        <button onclick="openChangePasswordModal()" class="btn-back" style="flex: 1; border-color: var(--primary); color: var(--primary);">
                            <i class="ph ph-lock-key"></i>
                            {{ __(\'Change PIN\') }}
                        </button>
                        <form action="{{ route(\'portal.logout\') }}" method="POST" style="flex: 1; margin: 0;">
                            @csrf
                            <button type="submit" class="btn-back" style="width: 100%;">
                                <i class="ph ph-sign-out"></i>
                                {{ __(\'Logout\') }}
                            </button>
                        </form>
                    </div>';

    // We also need to remove the @endif that closes the if(!$teacher).
    // Let's regex it
    $content = preg_replace('/<a href="\{\{ route\(\'portal\.index\'\) \}\}" class="btn-back".*?<\/a>\s*<\/div>\s*@endif/s', $buttons . "\n                </div>", $content);
}

// 3. Add Change Password Modal at the bottom, before closing body
$modal = '
    <div class="modal-overlay" id="changePasswordModal">
        <div class="modal-content">
            <h2 style="margin-top:0; font-size:1.5rem; font-weight:800; color:var(--text-main);">{{ __(\'Change Portal PIN\') }}</h2>
            <form action="{{ route(\'portal.change-password\') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>{{ __(\'Current PIN\') }}</label>
                    <div class="input-wrapper">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" name="current_pin" class="form-control" required pattern="\d{6}" maxlength="6" inputmode="numeric" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;">
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __(\'New PIN (6 digits)\') }}</label>
                    <div class="input-wrapper">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" name="new_pin" class="form-control" required pattern="\d{6}" maxlength="6" inputmode="numeric" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;">
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __(\'Confirm New PIN\') }}</label>
                    <div class="input-wrapper">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" name="new_pin_confirmation" class="form-control" required pattern="\d{6}" maxlength="6" inputmode="numeric" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;">
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn-back" onclick="closeChangePasswordModal()" style="flex:1;">{{ __(\'Cancel\') }}</button>
                    <button type="submit" class="btn-check" style="flex:1; padding:1.1rem;">{{ __(\'Save PIN\') }}</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function openChangePasswordModal() { document.getElementById("changePasswordModal").classList.add("active"); }
        function closeChangePasswordModal() { document.getElementById("changePasswordModal").classList.remove("active"); }
    </script>
';
$content = str_replace('</body>', $modal . "\n</body>", $content);

// 4. Add success message handler at the top
$successMsg = '
            @if(session(\'success\'))
                <div class="error-msg" style="background: rgba(16, 185, 129, 0.1); color: var(--success); border-color: rgba(16, 185, 129, 0.2);">
                    <i class="ph ph-check-circle" style="font-size:1.2rem; vertical-align:middle; margin-right:0.3rem;"></i>
                    {{ session(\'success\') }}
                </div>
            @endif
';
$content = str_replace('@if($error)', $successMsg . "\n            @if(\$error)", $content);


file_put_contents($file, $content);
echo "Blade file updated.";
