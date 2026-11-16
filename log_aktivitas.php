<?php
$adminConfig = [
    'title' => 'Log aktivitas',
    'page' => 'log_aktivitas.php',
    'table' => 'log_aktivitas',
    'pk' => 'id_log',
    'read_only' => true,
    'fields' => [],
    'list' => [
        ['Pengguna', 'p.nama_lengkap'],
        ['Waktu aktivitas', 't.waktu_aktivitas'],
        ['Jenis aktivitas', 't.jenis_aktivitas'],
        ['Nama tabel', 't.nama_tabel'],
        ['ID data', 't.id_data'],
        ['Keterangan', 't.keterangan'],
        ['Alamat IP', 't.alamat_ip'],
    ],
    'joins' => 'LEFT JOIN pengguna p ON p.id_pengguna = t.id_pengguna',
    'note' => 'Log hanya ditampilkan dan tidak dapat diubah atau dihapus dari panel.',
];
include "admin_table.php";
