
<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('location:login.php');
    exit;
}

include "koneksi.php";

$pesan = "";

// PESAN STATUS
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'berhasil') {
        $pesan = "Data dan dokumen berhasil disimpan!";
    } elseif ($_GET['status'] == 'hapus') {
        $pesan = "Data dan file berhasil dihapus!";
    }
}

// PROSES HAPUS DATA DAN FILE
if (isset($_POST['hapus'])) {

    $id = (int) $_POST['id_dokumen'];

    // Mengambil lokasi file
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT file_dokumen FROM dokumen_arsip
         WHERE id_dokumen = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($hasil);

    if ($data) {

        // Menghapus data arsip dari database
        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM dokumen_arsip WHERE id_dokumen = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {

            // Menghapus file dari folder uploads
            $file = $data['file_dokumen'];

            if (strpos($file, "uploads/") === 0) {
                $nama_file = basename($file);
                $lokasi_file = __DIR__ . "/uploads/" . $nama_file;

                if (is_file($lokasi_file)) {
                    unlink($lokasi_file);
                }
            }

            header("Location: index.php?status=hapus");
            exit;

        } else {
            $pesan = "Data gagal dihapus.";
        }

    } else {
        $pesan = "Data tidak ditemukan.";
    }
}

// PROSES SIMPAN DATA
if (isset($_POST['simpan'])) {

    $nis = trim($_POST['nis']);
    $nama = trim($_POST['nama_siswa']);
    $kelas = trim($_POST['kelas']);
    $kategori = trim($_POST['kategori']);
    $nama_dokumen = trim($_POST['nama_dokumen']);

    if ($nis == "" || $nama == "" || $kelas == "" ||
        $kategori == "" || $nama_dokumen == "") {

        $pesan = "Semua data wajib diisi.";

    } elseif (!isset($_FILES['dokumen']) ||
              $_FILES['dokumen']['error'] != 0) {

        $pesan = "Silakan pilih file dokumen.";

    } else {

        $ekstensi = strtolower(
            pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION)
        );

        $daftar_ekstensi = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($ekstensi, $daftar_ekstensi)) {
            $pesan = "File harus PDF, JPG, atau PNG.";
        } else {

            // Membuat folder uploads jika belum ada
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }

            $nama_file = uniqid() . "." . $ekstensi;
            $lokasi_file = "uploads/" . $nama_file;

            if (move_uploaded_file(
                $_FILES['dokumen']['tmp_name'],
                $lokasi_file
            )) {

                // Mencari atau menambahkan kelas
                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT id_kelas FROM kelas
                     WHERE nama_kelas = ? LIMIT 1"
                );
                mysqli_stmt_bind_param($stmt, "s", $kelas);
                mysqli_stmt_execute($stmt);
                $hasil = mysqli_stmt_get_result($stmt);

                if ($data = mysqli_fetch_assoc($hasil)) {
                    $id_kelas = $data['id_kelas'];
                } else {
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO kelas (nama_kelas) VALUES (?)"
                    );
                    mysqli_stmt_bind_param($stmt, "s", $kelas);
                    mysqli_stmt_execute($stmt);
                    $id_kelas = mysqli_insert_id($koneksi);
                }

                // Mencari atau menambahkan kategori dokumen
                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT id_kategori FROM kategori_dokumen
                     WHERE nama_kategori = ? LIMIT 1"
                );
                mysqli_stmt_bind_param($stmt, "s", $kategori);
                mysqli_stmt_execute($stmt);
                $hasil = mysqli_stmt_get_result($stmt);

                if ($data = mysqli_fetch_assoc($hasil)) {
                    $id_kategori = $data['id_kategori'];
                } else {
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO kategori_dokumen (nama_kategori)
                         VALUES (?)"
                    );
                    mysqli_stmt_bind_param($stmt, "s", $kategori);
                    mysqli_stmt_execute($stmt);
                    $id_kategori = mysqli_insert_id($koneksi);
                }

                // Mencari atau menambahkan siswa
                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT id_siswa FROM siswa
                     WHERE nis = ? LIMIT 1"
                );
                mysqli_stmt_bind_param($stmt, "s", $nis);
                mysqli_stmt_execute($stmt);
                $hasil = mysqli_stmt_get_result($stmt);

                if ($data = mysqli_fetch_assoc($hasil)) {
                    $id_siswa = $data['id_siswa'];

                    $stmt = mysqli_prepare(
                        $koneksi,
                        "UPDATE siswa
                         SET nama_siswa = ?, id_kelas = ?
                         WHERE id_siswa = ?"
                    );
                    mysqli_stmt_bind_param(
                        $stmt, "sii", $nama, $id_kelas, $id_siswa
                    );
                    mysqli_stmt_execute($stmt);

                } else {
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO siswa (nis, nama_siswa, id_kelas)
                         VALUES (?, ?, ?)"
                    );
                    mysqli_stmt_bind_param(
                        $stmt, "ssi", $nis, $nama, $id_kelas
                    );
                    mysqli_stmt_execute($stmt);
                    $id_siswa = mysqli_insert_id($koneksi);
                }

                // Menyimpan dokumen arsip
                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO dokumen_arsip
                    (id_kategori, id_siswa, nama_dokumen,
                     tanggal_dokumen, status_verifikasi,
                     file_dokumen, tanggal_unggah)
                    VALUES (?, ?, ?, CURDATE(), 'Menunggu', ?, NOW())"
                );

                mysqli_stmt_bind_param(
                    $stmt, "iiss",
                    $id_kategori,
                    $id_siswa,
                    $nama_dokumen,
                    $lokasi_file
                );

                if (mysqli_stmt_execute($stmt)) {
                    header("Location: index.php?status=berhasil");
                    exit;
                } else {
                    $pesan = "Gagal menyimpan dokumen.";

                    if (file_exists($lokasi_file)) {
                        unlink($lokasi_file);
                    }
                }

            } else {
                $pesan = "File gagal diunggah.";
            }
        }
    }
}

// MENGAMBIL DAFTAR ARSIP
$query = mysqli_query(
    $koneksi,
    "SELECT d.id_dokumen, s.nis, s.nama_siswa,
            k.nama_kelas, c.nama_kategori,
            d.nama_dokumen, d.file_dokumen
     FROM dokumen_arsip d
     LEFT JOIN siswa s ON d.id_siswa = s.id_siswa
     LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
     LEFT JOIN kategori_dokumen c
        ON d.id_kategori = c.id_kategori
     ORDER BY d.id_dokumen DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Arsip Dokumen Sekolah</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="kotak lebar">

    <h1>Arsip Dokumen Sekolah</h1>

    <a href="index.php">Home</a> |
    <a href="logout.php">Logout</a>

    <hr>

    <h3>Selamat datang,
        <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
    </h3>

    <p>Silakan lihat daftar data siswa dan dokumen arsip.</p>

    <?php if ($pesan != "") { ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($pesan); ?>
        </div>
    <?php } ?>

    <!-- TOMBOL INPUT DATA -->
    <div class="mb-3">
        <button type="button"
                class="btn btn-primary"
                data-bs-toggle="collapse"
                data-bs-target="#formInput">
            + Input Data
        </button>
    </div>

    <!-- FORMULIR INPUT DATA -->
    <div class="collapse <?php
        echo ($pesan != "" &&
              $pesan != "Data dan dokumen berhasil disimpan!")
              ? "show" : "";
    ?>" id="formInput">

        <div class="card card-body mb-4">

            <h3>Input Data dan Arsip Siswa</h3>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">1. NIS/NISN</label>
                    <input type="text" name="nis"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">2. Nama Siswa</label>
                    <input type="text" name="nama_siswa"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">3. Kelas</label>
                    <input type="text" name="kelas"
                           class="form-control"
                           placeholder="Contoh: XII RPL 1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">4. Jenis Dokumen</label>
                    <input type="text" name="kategori"
                           class="form-control"
                           placeholder="Contoh: Ijazah, KK, Akta Kelahiran"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">5. Nama Dokumen</label>
                    <input type="text" name="nama_dokumen"
                           class="form-control"
                           placeholder="Contoh: Ijazah SMP" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">6. Upload File Dokumen</label>
                    <input type="file" name="dokumen"
                           class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png" required>
                </div>

                <button type="submit" name="simpan"
                        class="btn btn-primary">
                    Simpan Data
                </button>

            </form>

        </div>
    </div>

    <hr>

    <!-- DAFTAR ARSIP -->
    <h3>Daftar Arsip Siswa</h3>

    <div class="table-responsive">

        <table class="table table-bordered table-striped">

            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Jenis Dokumen</th>
                    <th>Nama Dokumen</th>
                    <th>File</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                <?php
                $no = 1;

                while ($row = mysqli_fetch_assoc($query)) {
                ?>

                <tr>
                    <td><?php echo $no++; ?></td>

                    <td>
                        <?php echo htmlspecialchars($row['nis'] ?? ''); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['nama_siswa'] ?? ''); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['nama_kelas'] ?? ''); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['nama_kategori'] ?? ''); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['nama_dokumen'] ?? ''); ?>
                    </td>

                    <td>
                        <a href="<?php echo htmlspecialchars($row['file_dokumen']); ?>"
                           target="_blank">Lihat File</a>
                    </td>

                    <td>
                        <form method="POST"
                              onsubmit="return confirm('Yakin ingin menghapus data dan file ini?');">

                            <input type="hidden"
                                   name="id_dokumen"
                                   value="<?php echo (int) $row['id_dokumen']; ?>">

                            <button type="submit"
                                    name="hapus"
                                    class="btn btn-danger btn-sm">
                                Hapus
                            </button>

                        </form>
                    </td>

                </tr>

                <?php } ?>

                <?php if (mysqli_num_rows($query) == 0) { ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            Belum ada data arsip.
                        </td>
                    </tr>
                <?php } ?>

            </tbody>

        </table>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
