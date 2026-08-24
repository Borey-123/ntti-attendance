<?php
$file = 'c:\\xampp\\htdocs\\sana_project\\Final_Project\\NTTI_Teacher_Attendent\\ntti-attendance\\resources\\views\\portal\\index.blade.php';
$lines = file($file);

// Find the @if(!$teacher) line
$startIndex = -1;
$elseIndex = -1;
$endifIndex = -1;

$ifCount = 0;

for ($i = 0; $i < count($lines); $i++) {
    $line = trim($lines[$i]);
    if (strpos($line, '@if(!$teacher)') !== false) {
        $startIndex = $i;
        $ifCount = 1;
    } elseif ($startIndex !== -1) {
        if (strpos($line, '@if') === 0) {
            $ifCount++;
        } elseif (strpos($line, '@endif') === 0) {
            $ifCount--;
            if ($ifCount === 0) {
                $endifIndex = $i;
                break;
            }
        } elseif (strpos($line, '@else') === 0 && $ifCount === 1) {
            $elseIndex = $i;
        }
    }
}

if ($startIndex !== -1 && $elseIndex !== -1 && $endifIndex !== -1) {
    // 1. Remove the @endif
    unset($lines[$endifIndex]);
    
    // 2. Remove the @else and everything before it up to @if(!$teacher)
    for ($i = $startIndex; $i <= $elseIndex; $i++) {
        unset($lines[$i]);
    }
    
    // 3. We need to add the Logout & Change PIN UI, and also we need to remove `<div id="resultArea" style="display:block;">` because it was inside the `@else` block. 
    // Let's find `<div id="resultArea" style="display:block;">` which is just after the @else.
    $resultAreaIndex = $elseIndex + 1;
    if (strpos($lines[$resultAreaIndex], '<div id="resultArea"') !== false) {
        unset($lines[$resultAreaIndex]);
    }

    // Now let's inject the new header with Logout/Change Pin just where @if(!$teacher) was.
    $headerHTML = <<<HTML
            {{-- Auth Controls --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <button onclick="openChangePinModal()" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); padding: 0.6rem 1rem; border-radius: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph ph-key"></i> {{ __('Change PIN') }}
                </button>
                <form action="{{ route('portal.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--danger); padding: 0.6rem 1rem; border-radius: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ph ph-sign-out"></i> {{ __('Logout') }}
                    </button>
                </form>
            </div>
            
            <div id="resultArea" style="display:block;">
HTML;

    $lines = array_values($lines); // reindex
    
    // Insert header
    array_splice($lines, $startIndex, 0, $headerHTML . "\n");
    
    // Also inject the change pin modal at the bottom before </body>
    $modalHTML = <<<HTML
{{-- Change PIN Modal --}}
<div id="changePinModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px; padding: 2rem;">
        <h3 style="margin-top:0; display:flex; align-items:center; gap:0.5rem; color:var(--text-main);">
            <i class="ph ph-key" style="color:var(--primary);"></i> 
            {{ __('Change Portal PIN') }}
        </h3>
        
        <form action="{{ route('portal.change_pin') }}" method="POST">
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
</script>
HTML;

    // Find </body>
    $bodyEnd = -1;
    for ($i = count($lines) - 1; $i >= 0; $i--) {
        if (strpos($lines[$i], '</body>') !== false) {
            $bodyEnd = $i;
            break;
        }
    }
    
    if ($bodyEnd !== -1) {
        array_splice($lines, $bodyEnd, 0, $modalHTML . "\n");
    }

    file_put_contents($file, implode("", $lines));
    echo "Successfully updated blade file without syntax errors!\n";

} else {
    echo "Could not find the bounds properly.\n";
    echo "Start: $startIndex, Else: $elseIndex, Endif: $endifIndex\n";
}
