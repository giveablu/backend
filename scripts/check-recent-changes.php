<?php

echo "🔍 Checking for recently modified files...\n\n";

// Get all PHP files in the app directory
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('app/'),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$files = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $files[] = [
            'path' => $file->getPathname(),
            'modified' => $file->getMTime(),
            'size' => $file->getSize()
        ];
    }
}

// Sort by modification time (newest first)
usort($files, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

echo "📅 Recently modified PHP files (last 24 hours):\n";
echo str_repeat("-", 60) . "\n";

$oneDayAgo = time() - (24 * 60 * 60);
$recentFiles = 0;

foreach ($files as $file) {
    if ($file['modified'] > $oneDayAgo) {
        $recentFiles++;
        $timeAgo = time() - $file['modified'];
        $timeString = $timeAgo < 3600 ? 
            floor($timeAgo / 60) . ' minutes ago' : 
            floor($timeAgo / 3600) . ' hours ago';
            
        echo sprintf("%-40s %s\n", $file['path'], $timeString);
    }
}

if ($recentFiles === 0) {
    echo "No files modified in the last 24 hours.\n";
}

echo "\n📊 All controller files by modification date:\n";
echo str_repeat("-", 60) . "\n";

foreach ($files as $file) {
    if (strpos($file['path'], 'Controller') !== false) {
        echo sprintf("%-40s %s\n", 
            $file['path'], 
            date('Y-m-d H:i:s', $file['modified'])
        );
    }
}
