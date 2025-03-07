<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Konfigurasi database
$host = 'localhost';
$dbname = $_GET["db"] ?? '';
$user = 'app';
$pass = 'G0dISthe)N#';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Koneksi gagal: " . $e->getMessage()]);
    exit;
}

// Ambil query dari parameter GET
$query = $_GET['query'] ?? '';

if (!$query) {
    echo json_encode(["error" => "Query tidak boleh kosong"]);
    exit;
}

try {
    $stmt = $pdo->query($query);

    // Jika query adalah SELECT, ambil hasilnya
    if (stripos(trim($query), "SELECT") === 0) {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cek apakah ada kolom bernama "json" dan dekode isinya
        foreach ($result as &$row) {
            if (isset($row['json'])) {
                $decoded_json = json_decode($row['json'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $row['json'] = $decoded_json; // Ganti dengan array hasil decode
                }
            }
        }

        echo json_encode(["status" => "success", "data" => $result]);
    } else {
        // Jika query bukan SELECT, jalankan dan berikan pesan sukses
        echo json_encode(["status" => "success", "message" => "Query berhasil dieksekusi"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>

