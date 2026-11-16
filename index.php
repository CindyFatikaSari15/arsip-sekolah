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

        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
            <p><a href="admin.php">Buka menu panel admin</a></p>
        <?php endif; ?>

        <ul>
            <li>Data siswa, kelas, pengguna, dan dokumen arsip dikelola melalui panel admin.</li>
            <li>Backup, pemulihan, dan log aktivitas belum tersedia pada panel ini.</li>
        </ul>
    </div>
</body>
</html>
