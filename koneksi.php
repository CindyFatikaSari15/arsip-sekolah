<?php
// Koneksi ke database (host, user, password, nama database)
$koneksi = mysqli_connect("localhost", "root", "", "db_arsip_siswa") or die('database tidak terhubung');
mysqli_set_charset($koneksi, 'utf8mb4');

// Catat aktivitas ke tabel log_aktivitas
function catat_log($koneksi, $id_pengguna, $jenis, $keterangan) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO log_aktivitas (id_pengguna, waktu_aktivitas, jenis_aktivitas, nama_tabel, id_data, keterangan, alamat_ip)
         VALUES (?, NOW(), ?, 'pengguna', ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isiss', $id_pengguna, $jenis, $id_pengguna, $keterangan, $ip);
    mysqli_stmt_execute($stmt);
}
