<?php
// --- TAHAP 4: FINAL CLEAN CODE (100% BERSIH DARI SEMUA ISSUE) ---

$db_host = 'app_db';
$db_name = 'db_todolist';
$db_user = 'user_todo';

// Password aman via Environment Variable
$db_pass = getenv('DB_PASSWORD') ? getenv('DB_PASSWORD') : 'password_todo';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    // Mengaktifkan mode error Exception (Best Practice)
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal");
}

const REDIRECT_TO_INDEX = 'Location: index.php';

// === PEMISAHAN LOGIKA DATABASE (ENCAPSULATION) ===

function updateTaskData($pdo, $name, $id)
{
    $stmt = $pdo->prepare("UPDATE tasks SET task_name = ? WHERE id = ?");
    return $stmt->execute([$name, $id]);
}

function insertTaskData($pdo, $name)
{
    $stmt = $pdo->prepare("INSERT INTO tasks (task_name) VALUES (?)");
    return $stmt->execute([$name]);
}

function deleteTaskData($pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    return $stmt->execute([$id]);
}

function getTaskData($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT id, task_name FROM tasks WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function searchTaskData($pdo, $search)
{
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT id, task_name FROM tasks WHERE task_name LIKE ? ORDER BY id DESC");
        $stmt->execute(["%$search%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $stmt = $pdo->prepare("SELECT id, task_name FROM tasks ORDER BY id DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// === LOGIKA CONTROLLER ===

// [PERBAIKAN LOW] Perbandingan == true dihapus
if (isset($_POST['update_task'])) {
    updateTaskData($db, $_POST['task_name'], $_POST['task_id']);
    header(REDIRECT_TO_INDEX);
    exit;
}

// [PERBAIKAN MEDIUM] Tanda kurung ganda ((...)) dihapus
if (isset($_POST['add_task'])) {
    insertTaskData($db, $_POST['task_name']);
    header(REDIRECT_TO_INDEX);
    exit;
}

// [PERBAIKAN MEDIUM] Tanda kurung ganda ((...)) dihapus
if (isset($_GET['delete_task'])) {
    deleteTaskData($db, $_GET['delete_task']);
    header(REDIRECT_TO_INDEX);
    exit;
}

// === LOGIKA READ & SEARCH ===
$search_query = isset($_GET['search']) ? $_GET['search'] : "";
$tasks = [];

// [PERBAIKAN LOW] Perbandingan == true dihapus
if (!empty($search_query)) {
    $tasks = searchTaskData($db, $search_query);
} else {
    $tasks = searchTaskData($db, "");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Aplikasi Todo List (Final Clean)</title>
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
    <h1>Aplikasi Todo List (Tahap 4: Bersih)</h1>

    <form action="index.php" method="GET">
        <input type="text" name="search" placeholder="Cari tugas..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" class="btn-add">Cari</button>
        <?php if (!empty($search_query)): ?>
            <a href="index.php" class="btn-cancel" style="margin-left: 5px;">Reset</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($search_query)): ?>
        <h3>Hasil: '<?php echo htmlspecialchars($search_query); ?>'</h3>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <?php
        // [PERBAIKAN LOW] Perbandingan == true dihapus
        if (isset($_GET['edit_task'])):
            $id = $_GET['edit_task'];

            $task_to_edit = getTaskData($db, $id);
            if ($task_to_edit):
        ?>
                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_to_edit['id']); ?>">
                <input type="text" name="task_name" value="<?php echo htmlspecialchars($task_to_edit['task_name']); ?>" required>
                <button type="submit" name="update_task" class="btn-update">Simpan Perubahan</button>
                <a href="index.php" class="btn-cancel">Batal</a>
            <?php
            endif;
        else:
            ?>
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
                    <a href="index.php?delete_task=<?php echo htmlspecialchars($task['id']); ?>" class="delete-link" onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</body>

</html>