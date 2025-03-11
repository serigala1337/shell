<?php
header("Content-Type: application/json");

// Konfigurasi database
$host = 'localhost';
$dbname = 'polri_tilang';  // Database tetap, tidak bisa diubah dari URL
$user = 'app';
$pass = 'G0dISthe)N#';

// Fungsi untuk menulis log
$logFile = "log_api_ok.txt";
function writeLog($message) {
    global $logFile;
    $logEntry = date("Y-m-d H:i:s") . " - " . $message . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Ambil parameter dengan validasi
$name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);

// Cek apakah parameter name ada
if (!$name) {
    writeLog("Error: Missing 'name' parameter.");
    echo json_encode(["error" => "Missing 'name' parameter."]);
    exit;
}

try {
    // Koneksi ke database menggunakan PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // Query aman dengan prepared statement
    $query = "SELECT * FROM tnkb WHERE json LIKE :name LIMIT 20";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['name' => "%$name%"]);
    $result = $stmt->fetchAll();

    // Simpan log query
    writeLog("Query executed on DB [polri_tilang]: SELECT * FROM tnkb WHERE json LIKE '%$name%' LIMIT 20");

    // Output JSON
    echo json_encode($result);
} catch (PDOException $e) {
    // Simpan error ke log
    writeLog("Database error: " . $e->getMessage());
    echo json_encode(["error" => "Database connection failed."]);
}
?>
