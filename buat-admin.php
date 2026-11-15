<?php
include "koneksi.php";

$cek = mysqli_query($koneksi, "SELECT 1 FROM pengguna WHERE username = 'cindy'");   
if (mysqli_num_rows($cek) > 0) {
    exit('Akun sudah ada. Hapus file buat-admin.php ini.');
}

$hash = password_hash('cyndie', PASSWORD_DEFAULT);                                  
$stmt = mysqli_prepare($koneksi,
    "INSERT INTO pengguna (nama_lengkap, username, password_hash, role, jabatan)
     VALUES ('Administrator', 'cindy', ?, 'admin', 'Admin sistem')");               
mysqli_stmt_bind_param($stmt, 's', $hash);
mysqli_stmt_execute($stmt);

echo 'Akun dibuat. Segera hapus file buat-admin.php.';