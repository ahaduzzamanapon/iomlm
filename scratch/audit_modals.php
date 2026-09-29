<?php

$viewsPath = realpath(__DIR__ . '/../resources/views');
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$filesWithModals = [];
$totalModals = 0;

foreach ($rii as $file) {
    if ($file->isDir() || !str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }
    
    $content = file_get_contents($file->getPathname());
    $rel = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', realpath($file->getPathname()));
    
    // Find overlay containers (divs that represent the backdrop or modal container)
    // Matches: class="modal-overlay", class="modal-backdrop", class="admin-modal-overlay", or id="...Modal"
    if (preg_match_all('/<div[^>]*(?:class=["\'][^"\']*(?:modal-overlay|modal-backdrop|admin-modal-overlay)[^"\']*["\']|id=["\'][^"\']*(?:Modal|modal)[^"\']*["\'])[^>]*>/i', $content, $matches)) {
        $fileModals = [];
        foreach ($matches[0] as $tag) {
            // Exclude modal inner elements like modal-body, modal-footer, modal-header, modal-title, modal-close
            if (preg_match('/class=["\'][^"\']*(?:modal-body|modal-footer|modal-header|modal-title|modal-close|modal-content)[^"\']*/i', $tag) && !preg_match('/(?:modal-overlay|modal-backdrop|admin-modal-overlay)/i', $tag)) {
                continue;
            }
            if (preg_match('/id=["\'][^"\']*(?:title|btn|body|text|desc|input|select|header|footer|close|inv|gateway|due)[^"\']*/i', $tag)) {
                continue;
            }
            $fileModals[] = $tag;
        }
        if (!empty($fileModals)) {
            $filesWithModals[$rel] = $fileModals;
            $totalModals += count($fileModals);
        }
    }
}

$out = "Found {$totalModals} modal containers in " . count($filesWithModals) . " files.\n\n";

foreach ($filesWithModals as $file => $tags) {
    $out .= "=== {$file} (" . count($tags) . " modals) ===\n";
    foreach ($tags as $t) {
        $clean = preg_replace('/\s+/', ' ', trim($t));
        $out .= "  " . $clean . "\n";
    }
}

file_put_contents(__DIR__ . '/modal_audit_report.txt', $out);
echo "Written to scratch/modal_audit_report.txt\n";
