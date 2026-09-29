<?php

$viewsPath = realpath(__DIR__ . '/../resources/views');
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$directStyleDisplayModals = [];

foreach ($rii as $file) {
    if ($file->isDir() || !str_ends_with($file->getFilename(), '.blade.php')) continue;
    $content = file_get_contents($file->getPathname());
    $rel = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', realpath($file->getPathname()));
    
    // Check for document.getElementById('...').style.display = '...'
    if (preg_match_all('/document\.getElementById\([\'"]([^\'"]*modal[^\'"]*)[\'"]\)\.style\.display\s*=\s*[\'"]([^\'"]+)[\'"]/i', $content, $m)) {
        for ($i = 0; $i < count($m[0]); $i++) {
            $directStyleDisplayModals[] = [
                'file' => $rel,
                'id' => $m[1][$i],
                'val' => $m[2][$i],
                'match' => $m[0][$i],
            ];
        }
    }
}

echo "Found " . count($directStyleDisplayModals) . " direct style.display calls on modal elements:\n";
foreach ($directStyleDisplayModals as $d) {
    echo "  [{$d['file']}] ID: {$d['id']} -> {$d['match']}\n";
}
