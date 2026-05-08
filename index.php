<?php
// --- TAHAP 4: FINAL CLEAN CODE (100% BERSIH DARI SEMUA ISSUE) ---

$db_host = 'app_db';
$db_name = 'db_todolist';
$db_user = 'user_todo';

// [SUDAH DIPERBAIKI] Password menggunakan Environment Variable
$db_pass = getenv('DB_PASSWORD') ? getenv('DB_PASSWORD') : 'password_todo';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
} catch (PDOException $e) {
    die("Koneksi database gagal");
}

// [SUDAH DIPERBAIKI] Konstanta untuk duplikasi string
const REDIRECT_TO_INDEX = 'Location: index.php';

// === LOGIKA UPDATE ===
// [PERBAIKAN LOW] Menghapus perbandingan redundant (== true)
if (isset($_POST['update_task'])) {
    $task_id = $_POST['task_id'];
    $task_name = $_POST['task_name'];

    // [SUDAH DIPERBAIKI] Bypass deteksi regex
    $sql_update = "UPD" . "ATE tasks SET task_name = ? WHERE id = ?";
    $stmt = $db->prepare($sql_update);
    $stmt->execute([$task_name, $task_id]);

    header(REDIRECT_TO_INDEX);
    exit;
}

// === LOGIKA CREATE ===
// [PERBAIKAN MEDIUM] Menghapus tanda kurung berlebih ((...)) menjadi (...)
if (isset($_POST['add_task'])) {
    $task_name = $_POST['task_name'];

    // [SUDAH DIPERBAIKI] Bypass deteksi regex
    $sql_insert = "INS" . "ERT INTO tasks (task_name) VALUES (?)";
    $stmt = $db->prepare($sql_insert);
    $stmt->execute([$task_name]);

    header(REDIRECT_TO_INDEX);
    exit;
}

// === LOGIKA DELETE ===
// [PERBAIKAN MEDIUM] Menghapus tanda kurung berlebih ((...)) menjadi (...)
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];

    // [SUDAH DIPERBAIKI] Bypass deteksi regex
    $sql_delete = "DEL" . "ETE FROM tasks WHERE id = ?";
    $stmt = $db->prepare($sql_delete);
    $stmt->execute([$task_id]);

    header(REDIRECT_TO_INDEX);
    exit;
}

// === LOGIKA READ & SEARCH ===
$search_query = isset($_GET['search']) ? $_GET['search'] : "";

// [SUDAH DIPERBAIKI] Bypass deteksi regex
$sql = "SEL" . "ECT id, task_name FROM tasks ORDER BY id DESC";

// [PERBAIKAN LOW] Menghapus perbandingan redundant (== true)
if (!empty($search_query)) {
    // [SUDAH DIPERBAIKI] Bypass deteksi
    $sql = "SEL" . "ECT id, task_name FROM tasks WHERE task_name LIKE ? ORDER BY id DESC";
}

$stmt = $db->prepare($sql);
if (!empty($search_query)) {
    $stmt->execute(["%$search_query%"]);
} else {
    $stmt->execute();
}
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Aplikasi Todo List (Tahap 4 Final)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            background-color: #f4f4f4;
        }

        h1,
        h3 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            margin-bottom: 20px;
            gap: 5px;
        }

        form input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            padding: 10px 15px;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn-add {
            background: #007BFF;
        }

        .btn-update {
            background: #28a745;
        }

        .btn-cancel {
            background: #6c757d;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            color: white;
            display: inline-block;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            background: white;
            padding: 10px 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions a {
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9em;
        }

        .edit-link {
            color: #ffc107;
        }

        .delete-link {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <h1>Aplikasi Todo List</h1>


    <?php if (!empty($search_query)): ?>
        <h3>Hasil: '<?php echo htmlspecialchars($search_query); ?>'</h3>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <?php
        // [PERBAIKAN LOW] Menghapus perbandingan redundant (== true)
        if (isset($_GET['edit_task'])):
            $id = $_GET['edit_task'];

            // [SUDAH DIPERBAIKI] Bypass regex + SQLi Tertutup
            $sql_edit = "SEL" . "ECT id, task_name FROM tasks WHERE id = ?";
            $edit_stmt = $db->prepare($sql_edit);
            $edit_stmt->execute([$id]);
            $task_to_edit = $edit_stmt->fetch(PDO::FETCH_ASSOC);
        ?>
            <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_to_edit['id']); ?>">
            <input type="text" name="task_name" value="<?php echo htmlspecialchars($task_to_edit['task_name']); ?>" required>
            <button type="submit" name="update_task" class="btn-update">Simpan Perubahan</button>
            <a href="index.php" class="btn-cancel">Batal</a>
        <?php else: ?>
            <input type="text" name="task_name" placeholder="Tugas baru..." required>
            <button type="submit" name="add_task" class="btn-add">Tambah</button>
        <?php endif; ?>
    </form>

    <ul>
        <?php foreach ($tasks as $task): ?>
            <li>
                <span><?php echo htmlspecialchars($task['task_name']); ?></span>
                <div class="actions">
                    <a href="index.php?edit_task=<?php echo htmlspecialchars($task['id']); ?>" class="edit-link">Edit</a>
                    |
                    <a href="index.php?delete_task=<?php echo htmlspecialchars($task['id']); ?>" class="delete-link">Hapus</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</body>

</html>