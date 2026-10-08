<?php

$srcDir = 'C:/Users/Ahad/.gemini/antigravity-ide/brain/a0ff4a4e-9278-4451-aad8-aaca7ca816db/.user_uploaded';
$destDir = __DIR__ . '/../public/images/avatars';

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

$map = [
    'media_1791441085652.jpg' => 'female_niqab_black.jpg',
    'media_1791436433596.jpg' => 'female_hijab_pink.jpg',
    'media_1791436502215.jpg' => 'female_hijab_red.jpg',
    'media_1791441245673.jpg' => 'female_niqab_blue.jpg',
    'media_1791435773200.jpg' => 'male_topi_white.jpg',
    'media_1791435791117.jpg' => 'male_topi_pattern.jpg',
    'media_1791435802986.jpg' => 'male_topi_black.jpg',
    'media_1791435640946.jpg' => 'male_topi_cream.jpg',
    'media_1791435679710.jpg' => 'male_topi_brown.jpg',
];

foreach ($map as $src => $dest) {
    $srcPath = "{$srcDir}/{$src}";
    $destPath = "{$destDir}/{$dest}";
    if (file_exists($srcPath)) {
        copy($srcPath, $destPath);
        echo "Copied {$src} -> {$dest} (" . filesize($destPath) . " bytes)\n";
    } else {
        echo "Source not found: {$srcPath}\n";
    }
}
