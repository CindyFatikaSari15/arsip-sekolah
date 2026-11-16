<?php
$adminConfig = [
    'title' => 'Kelas',
    'page' => 'kelas.php',
    'table' => 'kelas',
    'pk' => 'id_kelas',
    'fields' => [
        'nama_kelas' => ['label' => 'Nama kelas', 'type' => 'text', 'required' => true],
        'tingkat' => ['label' => 'Tingkat', 'type' => 'text', 'required' => true],
        'tahun_ajaran' => ['label' => 'Tahun ajaran', 'type' => 'text', 'required' => true],
        'status_kelas' => ['label' => 'Status kelas', 'type' => 'text', 'required' => true],
        'id_wali_kelas' => ['label' => 'Wali kelas', 'type' => 'foreign', 'ref' => ['table' => 'pengguna', 'pk' => 'id_pengguna', 'label' => 'nama_lengkap']],
    ],
    'list' => [
        ['Nama kelas', 't.nama_kelas'],
        ['Tingkat', 't.tingkat'],
        ['Tahun ajaran', 't.tahun_ajaran'],
        ['Status kelas', 't.status_kelas'],
        ['Wali kelas', 'p.nama_lengkap'],
    ],
    'joins' => 'LEFT JOIN pengguna p ON p.id_pengguna = t.id_wali_kelas',
];
include "admin_table.php";
