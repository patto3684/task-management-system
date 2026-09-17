<?php

$host = 'localhost';
$dbName = 'task_management';
$dbUser = 'root';
$dbPass = '';

$dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.',
        'errors' => ['database' => 'Unable to connect to the database.'],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}
