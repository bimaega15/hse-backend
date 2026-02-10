<?php
/**
 * PHP Upload Configuration Checker
 * Run this via: php check-upload-config.php
 */

echo "=== PHP Upload Configuration ===\n\n";

$settings = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'file_uploads' => ini_get('file_uploads') ? 'Enabled' : 'Disabled',
    'upload_tmp_dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
    'max_input_time' => ini_get('max_input_time'),
    'memory_limit' => ini_get('memory_limit'),
];

foreach ($settings as $key => $value) {
    printf("%-25s : %s\n", $key, $value);
}

// Check if temp directory is writable
$tmpDir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
echo "\n=== Temp Directory Check ===\n\n";
echo "Temp Directory: {$tmpDir}\n";
echo "Exists: " . (is_dir($tmpDir) ? 'Yes' : 'No') . "\n";
echo "Writable: " . (is_writable($tmpDir) ? 'Yes' : 'No') . "\n";

// Check storage directory
echo "\n=== Storage Directory Check ===\n\n";
$storagePath = __DIR__ . '/storage/app/public';
echo "Storage Path: {$storagePath}\n";
echo "Exists: " . (is_dir($storagePath) ? 'Yes' : 'No') . "\n";
echo "Writable: " . (is_writable($storagePath) ? 'Yes' : 'No') . "\n";

echo "\n=== Recommendations ===\n\n";

// Convert to bytes for comparison
function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    $value = (int) $value;
    switch($last) {
        case 'g': $value *= 1024;
        case 'm': $value *= 1024;
        case 'k': $value *= 1024;
    }
    return $value;
}

$uploadMax = convertToBytes(ini_get('upload_max_filesize'));
$postMax = convertToBytes(ini_get('post_max_size'));

if ($uploadMax < 5242880) { // 5MB
    echo "⚠️  upload_max_filesize is less than 5MB. Consider increasing it.\n";
}

if ($postMax < 5242880) {
    echo "⚠️  post_max_size is less than 5MB. Consider increasing it.\n";
}

if ($postMax < $uploadMax) {
    echo "⚠️  post_max_size should be larger than upload_max_filesize\n";
}

if (!is_writable($tmpDir)) {
    echo "❌ Temp directory is not writable!\n";
}

if (!is_writable($storagePath)) {
    echo "❌ Storage directory is not writable!\n";
}

echo "\n✅ Configuration check completed.\n";
