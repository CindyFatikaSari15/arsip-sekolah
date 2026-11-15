<?php
// Buka file ini SEKALI di browser untuk membuat akun admin pertama, lalu HAPUS file ini.
include "koneksi.php";

$cek = mysqli_query($koneksi, "SELECT 1 FROM pengguna WHERE username = 'admin'");
if (mysqli_num_rows($cek) > 0) {
    exit('Akun admin sudah ada. Hapus file buat-admin.php ini.');
}

$hash = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = mysqli_prepare($koneksi,
    "INSERT INTO pengguna (nama_lengkap, username, email, password_hash, role, jabatan)
     VALUES ('Administrator', 'admin', 'admin@sekolah.local', ?, 'admin', 'Admin sistem')");
mysqli_stmt_bind_param($stmt, 's', $hash);
mysqli_stmt_execute($stmt);

echo 'Akun dibuat. Username: admin, password: admin123. Segera hapus file buat-admin.php dan ganti password.';
