<?php
session_start();

// Koneksi ke MySQL
$host = 'localhost';
$user = 'app';
$pass = 'G0dISthe)N#';
$dbname = $_GET['db'] ?? 'xxx'; // Ambil database dari URL, default ke database awal

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Ambil semua database yang bisa diakses
$databases = [];
try {
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = $e->getMessage();
}

// Ambil daftar tabel dari database yang dipilih
$tables = [];
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = $e->getMessage();
}

// Jalankan Query
$query = $_POST['query'] ?? '';
$result = [];
$error = '';
if ($query) {
    try {
        $stmt = $pdo->query($query);
        if ($stmt->columnCount() > 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = [['Message' => 'Query berhasil dijalankan.']];
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

// Ambil data tabel jika diklik
$table_data = [];
$selected_table = $_GET['table'] ?? '';
if ($selected_table) {
    try {
        $stmt = $pdo->query("SELECT * FROM `$selected_table`");
        $table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Adminer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <style>
        body { background-color: #121212; color: white; }
        .container { max-width: 900px; margin-top: 20px; }
        .table { background-color: #1e1e1e; color: white; }
        .table th, .table td { border-color: #444; }
        .error { color: red; }
        textarea { width: 100%; height: 150px; background: #222; color: white; border: 1px solid #444; }
        a { color: #0dcaf0; text-decoration: none; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-3">Simple Adminer</h2>

    <!-- List Database -->
    <h3>🗄️ Database yang Bisa Diakses</h3>
    <ul>
        <?php foreach ($databases as $db): ?>
            <li><a href="?db=<?= urlencode($db) ?>">📂 <?= htmlspecialchars($db) ?></a></li>
        <?php endforeach; ?>
    </ul>

    <h4 class="mt-3">📌 Database Saat Ini: <strong><?= htmlspecialchars($dbname) ?></strong></h4>

    <!-- Form Query -->
    <form method="POST" class="mb-3">
        <textarea name="query" placeholder="Masukkan SQL Query di sini..."><?= htmlspecialchars($query) ?></textarea>
        <button type="submit" class="btn btn-primary mt-2">Jalankan</button>
    </form>

    <!-- Error -->
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- List Table -->
    <h3>📋 Daftar Tabel</h3>
    <ul>
        <?php foreach ($tables as $table): ?>
            <li><a href="?db=<?= urlencode($dbname) ?>&table=<?= urlencode($table) ?>">📄 <?= htmlspecialchars($table) ?></a></li>
        <?php endforeach; ?>
    </ul>

    <!-- Hasil Query -->
    <?php if ($query): ?>
        <h3 class="mt-3">📜 Hasil Query</h3>
        <?php if ($result): ?>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <?php foreach (array_keys($result[0]) as $column): ?>
                            <th><?= htmlspecialchars($column) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td><?= htmlspecialchars($value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-success">✅ Query berhasil, tetapi tidak ada data yang dikembalikan.</p>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Tampilkan Data Tabel -->
    <?php if ($selected_table): ?>
        <h3 class="mt-3">📄 Data: <?= htmlspecialchars($selected_table) ?></h3>
        <?php if ($table_data): ?>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <?php foreach (array_keys($table_data[0]) as $column): ?>
                            <th><?= htmlspecialchars($column) ?></th>
                        <?php endforeach; ?>
                        <th>🔧 Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($table_data as $row): ?>
                        <tr>
                            <?php foreach ($row as $key => $value): ?>
                                <td contenteditable="true" data-table="<?= $selected_table ?>" data-column="<?= $key ?>" data-id="<?= $row['id'] ?? '' ?>">
                                    <?= htmlspecialchars($value) ?>
                                </td>
                            <?php endforeach; ?>
                            <td>
                                <a href="?db=<?= urlencode($dbname) ?>&delete=<?= urlencode($selected_table) ?>&id=<?= $row['id'] ?? '' ?>" class="btn btn-danger btn-sm">🗑️ Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-warning">⚠ Tidak ada data di tabel ini.</p>
        <?php endif; ?>
    <?php endif; ?>

</div>

</body>
</html>
