<?php
$adminConfig = [
    'title' => 'Kategori dokumen',
    'page' => 'kategori_dokumen.php',
    'table' => 'kategori_dokumen',
    'pk' => 'id_kategori',
    'fields' => [
        'nama_kategori' => ['label' => 'Nama kategori', 'type' => 'text', 'required' => true],
        'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea'],
    ],
    'list' => [
        ['Nama kategori', 't.nama_kategori'],
        ['Deskripsi', 't.deskripsi'],
    ],
];
include "admin_table.php";
