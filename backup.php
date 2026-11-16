<?php
$adminConfig = [
    'title' => 'Backup',
    'page' => 'backup.php',
    'table' => 'backup',
    'pk' => 'id_backup',
    'fields' => [
        'id_pengguna' => ['label' => 'Pengguna', 'type' => 'foreign', 'required' => true, 'ref' => ['table' => 'pengguna', 'pk' => 'id_pengguna', 'label' => 'nama_lengkap']],
        'tipe_backup' => ['label' => 'Tipe backup', 'type' => 'text', 'required' => true],
        'waktu_mulai' => ['label' => 'Waktu mulai', 'type' => 'datetime-local', 'required' => true],
        'waktu_selesai' => ['label' => 'Waktu selesai', 'type' => 'datetime-local'],
        'ukuran_file' => ['label' => 'Ukuran file', 'type' => 'number'],
        'id_file_gdrive' => ['label' => 'ID file Google Drive', 'type' => 'text'],
        'checksum' => ['label' => 'Checksum', 'type' => 'text'],
        'status_backup' => ['label' => 'Status backup', 'type' => 'text', 'required' => true],
        'tanggal_kadaluarsa' => ['label' => 'Tanggal kadaluarsa', 'type' => 'date'],
    ],
    'list' => [
        ['Pengguna', 'p.nama_lengkap'],
        ['Tipe backup', 't.tipe_backup'],
        ['Waktu mulai', 't.waktu_mulai'],
        ['Waktu selesai', 't.waktu_selesai'],
        ['Ukuran file', 't.ukuran_file'],
        ['Status', 't.status_backup'],
        ['Kadaluarsa', 't.tanggal_kadaluarsa'],
    ],
    'joins' => 'LEFT JOIN pengguna p ON p.id_pengguna = t.id_pengguna',
    'note' => 'Halaman ini mengelola catatan metadata backup. Menambah catatan tidak membuat file backup secara otomatis.',
];
include "admin_table.php";
