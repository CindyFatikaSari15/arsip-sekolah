<?php
session_start();
include "koneksi.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar | Arsip Dokumen Sekolah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
if (isset($_POST['username'])) {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = 'petugas'; // role selalu petugas, tidak boleh dipilih dari form

    if ($nama == '' || $username == '' || strlen($password) < 6) {
        echo '<script>alert("Nama dan username wajib diisi, password minimal 6 karakter.");</script>';
    } else {
        // cek apakah username sudah dipakai
        $cek = mysqli_prepare($koneksi, "SELECT id_pengguna FROM pengguna WHERE username = ?");
        mysqli_stmt_bind_param($cek, 's', $username);
        mysqli_stmt_execute($cek);
        $ada = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($ada) > 0) {
            echo '<script>alert("Username sudah dipakai, pilih yang lain.");</script>';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi,
                "INSERT INTO pengguna (nama_lengkap, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sssss', $nama, $username, $email, $hash, $role);

            if (mysqli_stmt_execute($stmt)) {
                echo '<script>alert("Selamat, pendaftaran anda berhasil. Silakan login."); location.href="login.php";</script>';
                exit;
            } else {
                echo '<script>alert("Pendaftaran gagal.");</script>';
            }
        }
    }
}
?>

    <form method="post" class="kotak">
        <table align="center">
            <tr>
                <td colspan="2" align="center"><h3>Pendaftaran Pengguna</h3></td>
            </tr>
            <tr>
                <td>Nama</td>
                <td><input type="text" name="nama" required></td>
            </tr>
            <tr>
                <td>Username</td>
                <td><input type="text" name="username" required></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input type="email" name="email"></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <button type="submit">Daftar User</button>
                    <a href="login.php">Login</a>
                </td>
            </tr>
        </table>
    </form>

</body>
</html>
