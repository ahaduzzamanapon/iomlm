<?php

$viewsPath = realpath(__DIR__ . '/../resources/views');
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$directClassListModals = [];

foreach ($rii as $file) {
    if ($file->isDir() || !str_ends_with($file->getFilename(), '.blade.php')) continue;
    $content = file_get_contents($file->getPathname());
    $rel = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', realpath($file->getPathname()));
    
    // Check for direct classList.add('active') or classList.add('open')
    if (preg_match_all('/document\.getElementById\([\'"]([^\'"]+)[\'"]\)\.classList\.add\([\'"](active|open|show)[\'"]\)/i', $content, $m)) {
        for ($i = 0; $i < count($m[0]); $i++) {
            $directClassListModals[] = [
                'file' => $rel,
                'id' => $m[1][$i],
                'class' => $m[2][$i],
                'match' => $m[0][$i],
            ];
        }
    }
}

echo "Found " . count($directClassListModals) . " direct classList.add calls:\n";
foreach ($directClassListModals as $d) {
    echo "  [{$d['file']}] ID: {$d['id']} -> {$d['match']}\n";
}
