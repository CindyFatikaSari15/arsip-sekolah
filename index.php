<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('location:login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Beranda | Arsip Dokumen Sekolah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="kotak lebar">
        <h1>Arsip Dokumen Sekolah</h1>
        <a href="index.php">Home</a> |
        <a href="logout.php">Logout</a>
        <hr>
        <h3>Selamat datang, <?php echo htmlspecialchars($_SESSION['user']['username']); ?></h3>
        <p>Masuk sebagai <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
           (<?php echo htmlspecialchars($_SESSION['user']['role']); ?>)</p>

        <ul>
            <li>Data siswa (belum dibuat)</li>
            <li>Dokumen arsip (belum dibuat)</li>
            <li>Backup dan pemulihan (belum dibuat)</li>
            <li>Log aktivitas (belum dibuat)</li>
        </ul>
    </div>
</body>
</html>
