<?php
$adminConfig = [
    'title' => 'Pengguna',
    'page' => 'pengguna.php',
    'table' => 'pengguna',
    'pk' => 'id_pengguna',
    'fields' => [
        'nama_lengkap' => ['label' => 'Nama lengkap', 'type' => 'text', 'required' => true],
        'username' => ['label' => 'Username', 'type' => 'text', 'required' => true],
        'email' => ['label' => 'Email', 'type' => 'email'],
        'password_hash' => ['label' => 'Password', 'type' => 'password', 'password' => true, 'required' => true],
        'role' => ['label' => 'Role', 'type' => 'select', 'required' => true, 'options' => ['admin' => 'Admin', 'petugas' => 'Petugas']],
        'nip' => ['label' => 'NIP', 'type' => 'text'],
        'jabatan' => ['label' => 'Jabatan', 'type' => 'text'],
    ],
    'list' => [
        ['Nama lengkap', 't.nama_lengkap'],
        ['Username', 't.username'],
        ['Email', 't.email'],
        ['Role', 't.role'],
        ['NIP', 't.nip'],
        ['Jabatan', 't.jabatan'],
    ],
];
include "admin_table.php";
