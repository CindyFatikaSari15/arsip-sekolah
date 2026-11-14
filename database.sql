CREATE DATABASE IF NOT EXISTS arsip_sekolah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE arsip_sekolah;

CREATE TABLE IF NOT EXISTS pengguna (
  id_pengguna   INT AUTO_INCREMENT PRIMARY KEY,
  nama_lengkap  VARCHAR(100) NOT NULL,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  email         VARCHAR(100),
  password_hash VARCHAR(255) NOT NULL,
  role          VARCHAR(30)  NOT NULL DEFAULT 'petugas',
  nip           VARCHAR(30),
  jabatan       VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS log_aktivitas (
  id_log           INT AUTO_INCREMENT PRIMARY KEY,
  id_pengguna      INT NOT NULL,
  waktu_aktivitas  DATETIME NOT NULL,
  jenis_aktivitas  VARCHAR(50) NOT NULL,
  nama_tabel       VARCHAR(50),
  id_data          INT,
  keterangan       VARCHAR(255),
  alamat_ip        VARCHAR(45),
  FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE
);
