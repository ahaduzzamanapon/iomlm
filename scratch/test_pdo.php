<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=learning_plus', 'root', '');
    echo "Connected successfully to localhost learning_plus!\n";
    $stmt = $pdo->query("SELECT id, name, student_code FROM students WHERE student_code IS NOT NULL LIMIT 10");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . " | " . $row['name'] . " | Code: " . $row['student_code'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
