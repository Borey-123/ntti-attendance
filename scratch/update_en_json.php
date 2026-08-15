<?php
$missingFile = 'scratch/missing_translations.txt';
if (!file_exists($missingFile)) exit("Missing file not found");

$missing = explode("\n", file_get_contents($missingFile));
$enFile = 'lang/en.json';
$en = json_decode(file_get_contents($enFile), true) ?: [];

foreach ($missing as $m) {
    $m = trim($m);
    if ($m !== '') {
        $en[$m] = $m;
    }
}

file_put_contents($enFile, json_encode($en, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "En.json updated with " . count($missing) . " keys.";
?>
