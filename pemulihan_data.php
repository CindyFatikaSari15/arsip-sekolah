<?php
$adminConfig = [
    'title' => 'Pemulihan data',
    'page' => 'pemulihan_data.php',
    'table' => 'pemulihan_data',
    'pk' => 'id_pemulihan',
    'fields' => [
        'id_backup' => ['label' => 'Backup sumber', 'type' => 'foreign', 'required' => true, 'ref' => ['table' => 'backup', 'pk' => 'id_backup', 'label' => 'id_backup']],
        'id_pengguna' => ['label' => 'Pengguna', 'type' => 'foreign', 'required' => true, 'ref' => ['table' => 'pengguna', 'pk' => 'id_pengguna', 'label' => 'nama_lengkap']],
        'waktu_mulai' => ['label' => 'Waktu mulai', 'type' => 'datetime-local', 'required' => true],
        'waktu_selesai' => ['label' => 'Waktu selesai', 'type' => 'datetime-local'],
        'status_pemulihan' => ['label' => 'Status pemulihan', 'type' => 'text', 'required' => true],
    ],
    'list' => [
        ['ID backup', 't.id_backup'],
        ['Pengguna', 'p.nama_lengkap'],
        ['Waktu mulai', 't.waktu_mulai'],
        ['Waktu selesai', 't.waktu_selesai'],
        ['Status pemulihan', 't.status_pemulihan'],
    ],
    'joins' => 'LEFT JOIN pengguna p ON p.id_pengguna = t.id_pengguna',
    'note' => 'Halaman ini mengelola catatan pemulihan. Menambah catatan tidak menjalankan proses restore secara otomatis.',
];
include "admin_table.php";
