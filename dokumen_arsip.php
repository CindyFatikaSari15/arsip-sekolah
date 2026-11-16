<?php
$adminConfig = [
    'title' => 'Dokumen arsip',
    'page' => 'dokumen_arsip.php',
    'table' => 'dokumen_arsip',
    'pk' => 'id_dokumen',
    'fields' => [
        'id_kategori' => ['label' => 'Kategori', 'type' => 'foreign', 'required' => true, 'ref' => ['table' => 'kategori_dokumen', 'pk' => 'id_kategori', 'label' => 'nama_kategori']],
        'id_siswa' => ['label' => 'Siswa', 'type' => 'foreign', 'required' => true, 'ref' => ['table' => 'siswa', 'pk' => 'id_siswa', 'label' => 'nama_siswa']],
        'id_pengunggah' => ['label' => 'Pengunggah', 'type' => 'foreign', 'required' => true, 'ref' => ['table' => 'pengguna', 'pk' => 'id_pengguna', 'label' => 'nama_lengkap']],
        'id_verifikator' => ['label' => 'Verifikator', 'type' => 'foreign', 'ref' => ['table' => 'pengguna', 'pk' => 'id_pengguna', 'label' => 'nama_lengkap']],
        'nama_dokumen' => ['label' => 'Nama dokumen', 'type' => 'text', 'required' => true],
        'nomor_dokumen' => ['label' => 'Nomor dokumen', 'type' => 'text'],
        'tanggal_dokumen' => ['label' => 'Tanggal dokumen', 'type' => 'date'],
        'tahun_dokumen' => ['label' => 'Tahun dokumen', 'type' => 'number'],
        'status_verifikasi' => ['label' => 'Status verifikasi', 'type' => 'text', 'required' => true],
        'file_dokumen' => ['label' => 'Lokasi/nama file dokumen', 'type' => 'text'],
        'tanggal_unggah' => ['label' => 'Tanggal unggah', 'type' => 'datetime-local'],
    ],
    'list' => [
        ['Nama dokumen', 't.nama_dokumen'],
        ['Nomor dokumen', 't.nomor_dokumen'],
        ['Kategori', 'c.nama_kategori'],
        ['Siswa', 's.nama_siswa'],
        ['Pengunggah', 'u.nama_lengkap'],
        ['Status verifikasi', 't.status_verifikasi'],
        ['Tanggal unggah', 't.tanggal_unggah'],
    ],
    'joins' => 'LEFT JOIN kategori_dokumen c ON c.id_kategori = t.id_kategori
                LEFT JOIN siswa s ON s.id_siswa = t.id_siswa
                LEFT JOIN pengguna u ON u.id_pengguna = t.id_pengunggah',
];
include "admin_table.php";
