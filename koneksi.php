<?php
// Koneksi ke database (host, user, password, nama database)
$koneksi = mysqli_connect("localhost", "root", "", "arsip_sekolah") or die('database tidak terhubung');
mysqli_set_charset($koneksi, 'utf8mb4');

// Catat aktivitas ke tabel log_aktivitas
function catat_log($koneksi, $id_pengguna, $jenis, $keterangan, $nama_tabel = 'pengguna', $id_data = null) {
    $ip = $_SERVER['REMOTE_ADDR'];
    if ($id_data === null) {
        $id_data = $id_pengguna;
    }
    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO log_aktivitas (id_pengguna, waktu_aktivitas, jenis_aktivitas, nama_tabel, id_data, keterangan, alamat_ip)
         VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ississ', $id_pengguna, $jenis, $nama_tabel, $id_data, $keterangan, $ip);
    mysqli_stmt_execute($stmt);
}
