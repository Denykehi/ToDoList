<?php
// Koneksi ke Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_todo";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Inisialisasi variabel edit
$edit_mode = false;
$edit_id = "";
$edit_task_value = "";

// Proses Tambah Task
if (isset($_POST['add_task'])) {
    $task = trim($_POST['task']);
    if (!empty($task)) {
        $stmt = $conn->prepare("INSERT INTO tasks (task) VALUES (?)");
        $stmt->bind_param("s", $task);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit();
    }
}

// Proses Update / Edit Task
if (isset($_POST['update_task'])) {
    $id = $_POST['id'];
    $task = trim($_POST['task']);
    if (!empty($task)) {
        $stmt = $conn->prepare("UPDATE tasks SET task = ? WHERE id = ?");
        $stmt->bind_param("si", $task, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit();
    }
}

// Tangkap data saat tombol edit diklik (Masuk Mode Edit)
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result_edit = $stmt->get_result();
    if ($row_edit = $result_edit->fetch_assoc()) {
        $edit_mode = true;
        $edit_task_value = $row_edit['task'];
    }
    $stmt->close();
}

// Proses Ubah Status (Selesai / Belum Selesai)
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $result = $conn->query("SELECT status FROM tasks WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        $new_status = ($row['status'] == 'Belum Selesai') ? 'Selesai' : 'Belum Selesai';
        $conn->query("UPDATE tasks SET status = '$new_status' WHERE id = $id");
    }
    header("Location: index.php");
    exit();
}

// Proses Hapus Task
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM tasks WHERE id = $id");
    header("Location: index.php");
    exit();
}

// Ambil Semua Data Task
$result = $conn->query("SELECT * FROM tasks ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi To-Do List PHP & MySQL (Tambah & Edit)</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0 rounded-4">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4 fw-bold text-primary">
                            <i class="fa-solid fa-list-check me-2"></i> To-Do List App
                        </h3>

                        <!-- Form Input / Edit Task -->
                        <form action="" method="POST" class="input-group mb-4">
                            <?php if ($edit_mode): ?>
                                <input type="hidden" name="id" value="<?= $edit_id; ?>">
                                <input type="text" name="task" class="form-control" value="<?= htmlspecialchars($edit_task_value); ?>" placeholder="Edit kegiatan..." required autocomplete="off">
                                <button class="btn btn-success px-4" type="submit" name="update_task">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Update
                                </button>
                                <a href="index.php" class="btn btn-secondary" title="Batal Edit">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            <?php else: ?>
                                <input type="text" name="task" class="form-control" placeholder="Tambahkan kegiatan baru..." required autocomplete="off">
                                <button class="btn btn-primary px-4" type="submit" name="add_task">
                                    <i class="fa-solid fa-plus me-1"></i> Tambah
                                </button>
                            <?php endif; ?>
                        </form>

                        <!-- Tabel Daftar Task -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="50%">Kegiatan</th>
                                        <th width="20%" class="text-center">Status</th>
                                        <th width="25%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td>
                                                    <span class="<?= ($row['status'] == 'Selesai') ? 'text-decoration-line-through text-muted' : ''; ?>">
                                                        <?= htmlspecialchars($row['task']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($row['status'] == 'Selesai'): ?>
                                                        <span class="badge bg-success">Selesai</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Belum Selesai</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <!-- Tombol Toggle Status -->
                                                    <a href="index.php?toggle=<?= $row['id']; ?>" class="btn btn-sm <?= ($row['status'] == 'Selesai') ? 'btn-secondary' : 'btn-success'; ?>" title="Ubah Status">
                                                        <i class="fa-solid <?= ($row['status'] == 'Selesai') ? 'fa-rotate-left' : 'fa-check'; ?>"></i>
                                                    </a>
                                                    <!-- Tombol Edit -->
                                                    <a href="index.php?edit=<?= $row['id']; ?>" class="btn btn-sm btn-warning text-white" title="Edit">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>
                                                    <!-- Tombol Hapus -->
                                                    <a href="index.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus tugas ini?')" title="Hapus">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada kegiatan yang ditambahkan.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
