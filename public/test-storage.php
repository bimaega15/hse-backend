<?php
/**
 * Storage Access Diagnostic Test
 * Access via: http://hse-backend.test/test-storage.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Storage Access Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Storage Access Diagnostic Test</h1>

    <?php
    $checks = [];

    // Check 1: Symlink exists
    $symlinkPath = __DIR__ . '/storage';
    $checks[] = [
        'test' => 'Symlink Exists',
        'result' => is_link($symlinkPath),
        'details' => is_link($symlinkPath) ? 'Yes: ' . readlink($symlinkPath) : 'No'
    ];

    // Check 2: Symlink target exists
    $targetPath = realpath($symlinkPath);
    $checks[] = [
        'test' => 'Symlink Target Exists',
        'result' => $targetPath !== false && is_dir($targetPath),
        'details' => $targetPath ? $targetPath : 'Target not found'
    ];

    // Check 3: Profile images directory exists
    $profileImagesPath = $symlinkPath . '/profile_images';
    $checks[] = [
        'test' => 'Profile Images Directory',
        'result' => is_dir($profileImagesPath),
        'details' => is_dir($profileImagesPath) ? 'Exists' : 'Not found'
    ];

    // Check 4: Can read directory
    $files = [];
    if (is_dir($profileImagesPath)) {
        $files = array_diff(scandir($profileImagesPath), ['.', '..']);
    }
    $checks[] = [
        'test' => 'Can Read Directory',
        'result' => count($files) > 0,
        'details' => count($files) . ' files found'
    ];

    // Check 5: Files are readable
    $readableFiles = 0;
    foreach ($files as $file) {
        $filePath = $profileImagesPath . '/' . $file;
        if (is_file($filePath) && is_readable($filePath)) {
            $readableFiles++;
        }
    }
    $checks[] = [
        'test' => 'Files Are Readable',
        'result' => $readableFiles > 0,
        'details' => "$readableFiles of " . count($files) . " files are readable"
    ];

    // Display results
    echo '<table>';
    echo '<tr><th>Test</th><th>Status</th><th>Details</th></tr>';
    foreach ($checks as $check) {
        $status = $check['result'] ? '<span class="success">✓ PASS</span>' : '<span class="error">✗ FAIL</span>';
        echo "<tr><td>{$check['test']}</td><td>$status</td><td>{$check['details']}</td></tr>";
    }
    echo '</table>';

    // List files with URLs
    if (count($files) > 0) {
        echo '<h2>Uploaded Files:</h2>';
        echo '<table>';
        echo '<tr><th>Filename</th><th>Size</th><th>URL</th><th>Test Access</th></tr>';
        foreach ($files as $file) {
            if (is_file($profileImagesPath . '/' . $file)) {
                $filePath = $profileImagesPath . '/' . $file;
                $fileSize = filesize($filePath);
                $url = url('storage/profile_images/' . $file);
                $relativeUrl = '/storage/profile_images/' . $file;

                echo '<tr>';
                echo '<td>' . htmlspecialchars($file) . '</td>';
                echo '<td>' . number_format($fileSize) . ' bytes</td>';
                echo '<td><a href="' . htmlspecialchars($relativeUrl) . '" target="_blank">' . htmlspecialchars($url) . '</a></td>';
                echo '<td><img src="' . htmlspecialchars($relativeUrl) . '" style="max-width:100px; max-height:100px;" onerror="this.parentElement.innerHTML=\'<span class=error>Failed to load</span>\'"></td>';
                echo '</tr>';
            }
        }
        echo '</table>';
    }

    // Apache info
    echo '<h2>Server Info:</h2>';
    echo '<table>';
    echo '<tr><td>PHP Version</td><td>' . PHP_VERSION . '</td></tr>';
    echo '<tr><td>Server Software</td><td>' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</td></tr>';
    echo '<tr><td>Document Root</td><td>' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . '</td></tr>';
    echo '</table>';
    ?>

</body>
</html>
