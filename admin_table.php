<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    exit('Akses hanya untuk administrator.');
}

function admin_h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function admin_bind_values($stmt, $types, &$values) {
    $params = [$types];
    foreach ($values as &$value) {
        $params[] = &$value;
    }
    return call_user_func_array([$stmt, 'bind_param'], $params);
}

$config = $adminConfig;
$error = '';
$formValues = [];
$editRecord = null;
$readOnly = !empty($config['read_only']);

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';
    $recordId = isset($_POST['id']) && is_string($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($readOnly || $csrfToken === '' || !hash_equals($_SESSION['admin_csrf'], $csrfToken)) {
        $error = 'Permintaan tidak valid atau halaman ini hanya dapat dibaca.';
    } else {
        try {
            if ($action === 'delete') {
                if ($recordId < 1) {
                    throw new RuntimeException('Data yang dipilih tidak valid.');
                }
                if ($config['table'] === 'pengguna' && $recordId === (int) $_SESSION['user']['id']) {
                    throw new RuntimeException('Akun yang sedang digunakan tidak dapat dihapus.');
                }
                $stmt = mysqli_prepare($koneksi, "DELETE FROM `{$config['table']}` WHERE `{$config['pk']}` = ?");
                if (!$stmt) {
                    throw new RuntimeException('Gagal menyiapkan penghapusan data.');
                }
                mysqli_stmt_bind_param($stmt, 'i', $recordId);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new RuntimeException('Data gagal dihapus. Data mungkin masih digunakan tabel lain.');
                }
                catat_log($koneksi, $_SESSION['user']['id'], 'DELETE', 'Menghapus data ' . $config['title'], $config['table'], $recordId);
                $_SESSION['admin_flash'] = 'Data berhasil dihapus.';
                header('Location: ' . $config['page']);
                exit;
            }

            if ($action !== 'create' && $action !== 'update') {
                throw new RuntimeException('Aksi tidak dikenali.');
            }
            if ($action === 'update' && $recordId < 1) {
                throw new RuntimeException('Data yang dipilih tidak valid.');
            }

            foreach ($config['fields'] as $name => $field) {
                if (isset($_POST[$name]) && !is_string($_POST[$name])) {
                    throw new RuntimeException('Nilai ' . $field['label'] . ' tidak valid.');
                }
                $value = isset($_POST[$name]) ? trim($_POST[$name]) : '';
                $formValues[$name] = $value;

                if (!empty($field['password'])) {
                    if ($value === '' && $action === 'update') {
                        unset($formValues[$name]);
                        continue;
                    }
                    if ($value === '' || strlen($value) < 6) {
                        throw new RuntimeException('Password wajib diisi dan minimal 6 karakter.');
                    }
                    $value = password_hash($value, PASSWORD_DEFAULT);
                } elseif ($value === '') {
                    if (!empty($field['required'])) {
                        throw new RuntimeException($field['label'] . ' wajib diisi.');
                    }
                    $value = null;
                } elseif ($field['type'] === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new RuntimeException('Format email tidak valid.');
                } elseif ($field['type'] === 'select' && !array_key_exists($value, $field['options'])) {
                    throw new RuntimeException('Pilihan ' . $field['label'] . ' tidak valid.');
                } elseif ($field['type'] === 'number' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    throw new RuntimeException($field['label'] . ' harus berupa bilangan bulat.');
                } elseif ($field['type'] === 'foreign' && !ctype_digit($value)) {
                    throw new RuntimeException('Pilihan ' . $field['label'] . ' tidak valid.');
                } elseif ($field['type'] === 'date') {
                    $date = DateTime::createFromFormat('!Y-m-d', $value);
                    if (!$date || $date->format('Y-m-d') !== $value) {
                        throw new RuntimeException('Format ' . $field['label'] . ' tidak valid.');
                    }
                } elseif ($field['type'] === 'datetime-local') {
                    $date = DateTime::createFromFormat('!Y-m-d\TH:i', $value);
                    if (!$date || $date->format('Y-m-d\TH:i') !== $value) {
                        throw new RuntimeException('Format ' . $field['label'] . ' tidak valid.');
                    }
                    $value = $date->format('Y-m-d H:i:s');
                }
                $formValues[$name] = $value;
            }

            $columns = array_keys($formValues);
            if (!$columns) {
                throw new RuntimeException('Tidak ada data untuk disimpan.');
            }
            $values = array_values($formValues);
            $types = '';
            foreach ($columns as $column) {
                $type = $config['fields'][$column]['type'];
                $types .= $type === 'number' || $type === 'foreign' ? 'i' : 's';
            }

            if ($action === 'create') {
                $sql = "INSERT INTO `{$config['table']}` (`" . implode('`, `', $columns) . '`) VALUES (' .
                    implode(', ', array_fill(0, count($columns), '?')) . ')';
            } else {
                $assignments = [];
                foreach ($columns as $column) {
                    $assignments[] = '`' . $column . '` = ?';
                }
                $sql = "UPDATE `{$config['table']}` SET " . implode(', ', $assignments) . " WHERE `{$config['pk']}` = ?";
                $types .= 'i';
                $values[] = $recordId;
            }

            $stmt = mysqli_prepare($koneksi, $sql);
            if (!$stmt || !admin_bind_values($stmt, $types, $values)) {
                throw new RuntimeException('Gagal menyiapkan penyimpanan data.');
            }
            if (!mysqli_stmt_execute($stmt)) {
                if (mysqli_errno($koneksi) === 1062) {
                    throw new RuntimeException('Data duplikat. Periksa kembali kolom yang harus unik.');
                }
                throw new RuntimeException('Data gagal disimpan. Periksa isian dan relasi datanya.');
            }

            $savedId = $action === 'create' ? mysqli_insert_id($koneksi) : $recordId;
            catat_log($koneksi, $_SESSION['user']['id'], strtoupper($action),
                ($action === 'create' ? 'Menambah data ' : 'Memperbarui data ') . $config['title'],
                $config['table'], $savedId);
            $_SESSION['admin_flash'] = 'Data berhasil disimpan.';
            header('Location: ' . $config['page']);
            exit;
        } catch (RuntimeException $exception) {
            $error = $exception->getMessage();
        }
    }
}

$flash = isset($_SESSION['admin_flash']) ? $_SESSION['admin_flash'] : '';
unset($_SESSION['admin_flash']);

$editId = isset($_GET['edit']) && is_string($_GET['edit']) ? (int) $_GET['edit'] : 0;
if (!$readOnly && $editId > 0) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM `{$config['table']}` WHERE `{$config['pk']}` = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $editId);
        mysqli_stmt_execute($stmt);
        $editRecord = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }
}

if ($editRecord) {
    foreach ($config['fields'] as $name => $field) {
        if (empty($field['password']) && !array_key_exists($name, $formValues)) {
            $formValues[$name] = $editRecord[$name];
        }
    }
}

$select = ['t.' . $config['pk'] . ' AS record_id'];
$columns = [];
foreach ($config['list'] as $index => $column) {
    $alias = 'column_' . $index;
    $select[] = $column[1] . ' AS ' . $alias;
    $columns[] = ['label' => $column[0], 'key' => $alias];
}
$joins = isset($config['joins']) ? $config['joins'] : '';
$sql = 'SELECT ' . implode(', ', $select) . ' FROM `' . $config['table'] . '` t ' . $joins .
    ' ORDER BY t.' . $config['pk'] . ' DESC LIMIT 200';
$result = mysqli_query($koneksi, $sql);
$records = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
if (!$result) {
    error_log('Gagal memuat tabel ' . $config['table'] . ': ' . mysqli_error($koneksi));
    $error = 'Data tidak dapat dimuat. Pastikan tabel dan kolom sesuai ERD.';
}

$foreignOptions = [];
foreach ($config['fields'] as $name => $field) {
    if ($field['type'] === 'foreign') {
        $ref = $field['ref'];
        $optionResult = mysqli_query($koneksi,
            'SELECT `' . $ref['pk'] . '`, `' . $ref['label'] . '` FROM `' . $ref['table'] . '` ORDER BY `' . $ref['label'] . '`');
        $foreignOptions[$name] = $optionResult ? mysqli_fetch_all($optionResult, MYSQLI_ASSOC) : [];
        if (!$optionResult) {
            error_log('Gagal memuat opsi relasi ' . $name . ': ' . mysqli_error($koneksi));
            $error = 'Pilihan data relasi tidak dapat dimuat.';
        }
    }
}

$isEditing = $editRecord !== null || (isset($_POST['action']) && $_POST['action'] === 'update');
$adminPages = [
    ['Pengguna', 'pengguna.php'],
    ['Kelas', 'kelas.php'],
    ['Siswa', 'siswa.php'],
    ['Dokumen arsip', 'dokumen_arsip.php'],
    ['Kategori dokumen', 'kategori_dokumen.php'],
    ['Log aktivitas', 'log_aktivitas.php'],
    ['Backup', 'backup.php'],
    ['Pemulihan data', 'pemulihan_data.php'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo admin_h($config['title']); ?> | Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="kotak lebar admin">
    <header class="admin-header">
        <div>
            <h1><?php echo admin_h($config['title']); ?></h1>
            <p>Pengelolaan data Arsip Dokumen Sekolah.</p>
        </div>
        <nav><a href="admin.php">Menu admin</a> | <a href="index.php">Beranda</a> | <a href="logout.php">Logout</a></nav>
    </header>

    <nav class="admin-tabs" aria-label="Menu data">
        <?php foreach ($adminPages as $page): ?>
            <a class="<?php echo $config['page'] === $page[1] ? 'aktif' : ''; ?>" href="<?php echo admin_h($page[1]); ?>"><?php echo admin_h($page[0]); ?></a>
        <?php endforeach; ?>
    </nav>

    <?php if (!empty($config['note'])): ?><p class="catatan"><?php echo admin_h($config['note']); ?></p><?php endif; ?>
    <?php if ($flash !== ''): ?><p class="notice sukses"><?php echo admin_h($flash); ?></p><?php endif; ?>
    <?php if ($error !== ''): ?><p class="notice gagal"><?php echo admin_h($error); ?></p><?php endif; ?>

    <?php if (!$readOnly): ?>
        <section class="admin-form">
            <h2><?php echo $isEditing ? 'Ubah' : 'Tambah'; ?> <?php echo admin_h($config['title']); ?></h2>
            <form method="post" action="<?php echo admin_h($config['page']); ?><?php echo $isEditing && $editRecord ? '?edit=' . (int) $editRecord[$config['pk']] : ''; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo admin_h($_SESSION['admin_csrf']); ?>">
                <input type="hidden" name="action" value="<?php echo $isEditing ? 'update' : 'create'; ?>">
                <?php if ($isEditing): ?>
                    <input type="hidden" name="id" value="<?php echo $editRecord ? (int) $editRecord[$config['pk']] : (int) (isset($_POST['id']) ? $_POST['id'] : 0); ?>">
                <?php endif; ?>

                <div class="admin-fields">
                    <?php foreach ($config['fields'] as $name => $field): ?>
                        <?php
                        $value = isset($formValues[$name]) ? $formValues[$name] : '';
                        $required = !empty($field['required']) && !(isset($field['password']) && $isEditing);
                        $inputValue = $field['type'] === 'datetime-local' && $value !== ''
                            ? str_replace(' ', 'T', substr($value, 0, 16))
                            : $value;
                        ?>
                        <label class="admin-field">
                            <span><?php echo admin_h($field['label']); ?><?php echo $required ? ' *' : ''; ?></span>
                            <?php if ($field['type'] === 'textarea'): ?>
                                <textarea name="<?php echo admin_h($name); ?>" rows="3" <?php echo $required ? 'required' : ''; ?>><?php echo admin_h($value); ?></textarea>
                            <?php elseif ($field['type'] === 'select'): ?>
                                <select name="<?php echo admin_h($name); ?>" <?php echo $required ? 'required' : ''; ?>>
                                    <option value="">Pilih <?php echo admin_h($field['label']); ?></option>
                                    <?php foreach ($field['options'] as $optionValue => $optionLabel): ?>
                                        <option value="<?php echo admin_h($optionValue); ?>" <?php echo (string) $value === (string) $optionValue ? 'selected' : ''; ?>><?php echo admin_h($optionLabel); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ($field['type'] === 'foreign'): ?>
                                <select name="<?php echo admin_h($name); ?>" <?php echo $required ? 'required' : ''; ?>>
                                    <option value="">-- Tidak dipilih --</option>
                                    <?php foreach ($foreignOptions[$name] as $option): ?>
                                        <option value="<?php echo (int) $option[$field['ref']['pk']]; ?>" <?php echo (string) $value === (string) $option[$field['ref']['pk']] ? 'selected' : ''; ?>><?php echo admin_h($option[$field['ref']['label']]); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="<?php echo admin_h($field['type']); ?>" name="<?php echo admin_h($name); ?>" value="<?php echo !empty($field['password']) ? '' : admin_h($inputValue); ?>" <?php echo $required ? 'required' : ''; ?> <?php echo !empty($field['password']) ? 'minlength="6" autocomplete="new-password"' : ''; ?>>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit"><?php echo $isEditing ? 'Simpan perubahan' : 'Tambah data'; ?></button>
                <?php if ($isEditing): ?><a class="batal" href="<?php echo admin_h($config['page']); ?>">Batal</a><?php endif; ?>
            </form>
        </section>
    <?php endif; ?>

    <section class="admin-table">
        <h2>Daftar <?php echo admin_h($config['title']); ?></h2>
        <div class="table-scroll">
            <table>
                <thead><tr>
                    <?php foreach ($columns as $column): ?><th><?php echo admin_h($column['label']); ?></th><?php endforeach; ?>
                    <?php if (!$readOnly): ?><th>Aksi</th><?php endif; ?>
                </tr></thead>
                <tbody>
                    <?php if (!$records): ?>
                        <tr><td colspan="<?php echo count($columns) + ($readOnly ? 0 : 1); ?>">Belum ada data.</td></tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <?php foreach ($columns as $column): ?><td><?php echo admin_h($record[$column['key']]); ?></td><?php endforeach; ?>
                                <?php if (!$readOnly): ?>
                                    <td class="aksi">
                                        <a href="<?php echo admin_h($config['page']); ?>?edit=<?php echo (int) $record['record_id']; ?>">Ubah</a>
                                        <?php if ($config['table'] !== 'pengguna' || (int) $record['record_id'] !== (int) $_SESSION['user']['id']): ?>
                                            <form method="post" action="<?php echo admin_h($config['page']); ?>" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo admin_h($_SESSION['admin_csrf']); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo (int) $record['record_id']; ?>">
                                                <button class="button-hapus" type="submit">Hapus</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p class="catatan">Menampilkan maksimal 200 data terbaru.</p>
    </section>
</main>
</body>
</html>
