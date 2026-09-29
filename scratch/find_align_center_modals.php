<?php

$viewsPath = realpath(__DIR__ . '/../resources/views');
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$matches = [];

foreach ($rii as $file) {
    if ($file->isDir() || !str_ends_with($file->getFilename(), '.blade.php')) continue;
    $content = file_get_contents($file->getPathname());
    $rel = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', realpath($file->getPathname()));
    
    // Check if file has modal elements with align-items: center
    // Check styles or inline styles
    if (preg_match_all('/(?:class|id)=["\'][^"\']*(?:modal|overlay|backdrop|popup|dialog)[^"\']*["\'][^>]*style=["\'][^"\']*align-items\s*:\s*center[^"\']*["\']/i', $content, $m)) {
        foreach ($m[0] as $tag) {
            $matches[] = "[$rel] TAG: " . substr(trim($tag), 0, 100);
        }
    }
    
    // Check <style> blocks in the file
    if (preg_match('/<style>[\s\S]*?<\/style>/i', $content, $styleM)) {
        if (preg_match_all('/(?:\.modal[^{]*|\.modal-overlay[^{]*|\.modal-backdrop[^{]*|\.admin-modal-overlay[^{]*)\{[^}]*align-items\s*:\s*center[^}]*\}/is', $styleM[0], $cssM)) {
            foreach ($cssM[0] as $rule) {
                $matches[] = "[$rel] CSS: " . preg_replace('/\s+/', ' ', trim($rule));
            }
        }
    }
}

echo "Found " . count($matches) . " occurrences of align-items: center in modal styles:\n\n";
foreach ($matches as $m) {
    echo $m . "\n";
}
