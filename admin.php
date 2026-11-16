<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    exit('Akses hanya untuk administrator.');
}

$pages = [
    ['Pengguna', 'pengguna.php', 'Kelola akun, role, dan data petugas.'],
    ['Kelas', 'kelas.php', 'Kelola kelas, tahun ajaran, dan wali kelas.'],
    ['Siswa', 'siswa.php', 'Kelola identitas, kelas, dan status arsip siswa.'],
    ['Dokumen arsip', 'dokumen_arsip.php', 'Kelola metadata dokumen dan verifikasi.'],
    ['Kategori dokumen', 'kategori_dokumen.php', 'Kelola kelompok dokumen arsip.'],
    ['Log aktivitas', 'log_aktivitas.php', 'Lihat riwayat aktivitas sistem.'],
    ['Backup', 'backup.php', 'Kelola catatan metadata backup.'],
    ['Pemulihan data', 'pemulihan_data.php', 'Kelola catatan pemulihan data.'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin | Arsip Dokumen Sekolah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="kotak lebar admin">
    <header class="admin-header">
        <div>
            <h1>Panel Admin</h1>
            <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['user']['nama'], ENT_QUOTES, 'UTF-8'); ?>.</p>
        </div>
        <nav><a href="index.php">Beranda</a> | <a href="logout.php">Logout</a></nav>
    </header>
    <section class="admin-menu">
        <?php foreach ($pages as $page): ?>
            <article class="admin-menu-item">
                <h2><a href="<?php echo htmlspecialchars($page[1], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($page[0], ENT_QUOTES, 'UTF-8'); ?></a></h2>
                <p><?php echo htmlspecialchars($page[2], ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
        <?php endforeach; ?>
    </section>
</main>
</body>
</html>
