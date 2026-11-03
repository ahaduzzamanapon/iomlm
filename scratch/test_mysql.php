<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=learning_plus', 'root', '');
    echo "CONNECTED_127_0_0_1\n";
} catch (Exception $e) {
    echo "127.0.0.1 failed: " . $e->getMessage() . "\n";
}

try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=learning_plus', 'root', '');
    echo "CONNECTED_LOCALHOST\n";
} catch (Exception $e) {
    echo "localhost failed: " . $e->getMessage() . "\n";
}
