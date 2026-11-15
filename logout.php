<?php
session_start();
include "koneksi.php";

// catat dulu ke log sebelum session dihapus
if (isset($_SESSION['user'])) {
    catat_log($koneksi, $_SESSION['user']['id'], 'LOGOUT', 'Keluar dari sistem');
}

session_destroy();
?>
<script type="text/javascript">
    alert('Selamat, anda berhasil logout.');
    location.href = "login.php";
</script>
