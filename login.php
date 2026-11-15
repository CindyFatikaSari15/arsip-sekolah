<?php
session_start();
include "koneksi.php";

// kalau sudah login, langsung ke halaman utama
if (isset($_SESSION['user'])) {
    header('location:index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login | Arsip Dokumen Sekolah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // cari pengguna berdasarkan username (prepared statement, aman dari SQL injection)
    $stmt = mysqli_prepare($koneksi,
        "SELECT id_pengguna, nama_lengkap, username, password_hash, role FROM pengguna WHERE username = ?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    // cocokkan password dengan hash di database
    if ($data && password_verify($password, $data['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => $data['id_pengguna'],
            'nama'     => $data['nama_lengkap'],
            'username' => $data['username'],
            'role'     => $data['role'],
        ];
        catat_log($koneksi, $data['id_pengguna'], 'LOGIN', 'Berhasil masuk');

        $sapaan = json_encode('Selamat datang, ' . $data['nama_lengkap'],
                  JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        echo '<script>alert(' . $sapaan . '); location.href="index.php";</script>';
        exit;
    } else {
        if ($data) {
            catat_log($koneksi, $data['id_pengguna'], 'LOGIN_GAGAL', 'Password salah');
        }
        echo '<script>alert("Username/password tidak sesuai.");</script>';
    }
}
?>

    <form method="post" class="kotak">
        <table align="center">
            <tr>
                <td colspan="2" align="center"><h3>Login Arsip Dokumen Sekolah</h3></td>
            </tr>
            <tr>
                <td>Username</td>
                <td><input type="text" name="username" required></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <button type="submit">Login</button>
                    <a href="daftar.php">Daftar</a>
                </td>
            </tr>
        </table>
    </form>

</body>
</html>
