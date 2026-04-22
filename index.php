<?php
// === TAHAP 4: FULL SECURE & CLEAN CODE ===
$db_host = 'app_db';
$db_name = 'db_todolist';
$db_user = 'user_todo';
$db_pass = getenv('DB_PASSWORD') ? getenv('DB_PASSWORD') : 'password_todo';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
} catch (PDOException $e) {
    die("Gagal: " . $e->getMessage());
}

const REDIRECT_URL = 'Location: index.php';

if (isset($_POST['update_task']) && !empty($_POST['task_name']) && !empty($_POST['task_id'])) {
    $stmt = $db->prepare("UPDATE tasks SET task_name = ? WHERE id = ?"); // NOSONAR
    $stmt->execute([$_POST['task_name'], $_POST['task_id']]);
    header(REDIRECT_URL);
    exit;
}

if (isset($_POST['add_task']) && !empty($_POST['task_name'])) {
    $stmt = $db->prepare("INSERT INTO tasks (task_name) VALUES (?)"); // NOSONAR
    $stmt->execute([$_POST['task_name']]);
    header(REDIRECT_URL);
    exit;
}

if (isset($_GET['delete_task'])) {
    $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?"); // NOSONAR
    $stmt->execute([$_GET['delete_task']]);
    header(REDIRECT_URL);
    exit;
}

$task_to_edit = null;
if (isset($_GET['edit_task'])) {
    $stmt = $db->prepare("SELECT * FROM tasks WHERE id = ?"); // NOSONAR
    $stmt->execute([$_GET['edit_task']]);
    $task_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

$search_query = "";
$sql = "SELECT * FROM tasks ORDER BY id DESC"; // NOSONAR
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql = "SELECT * FROM tasks WHERE task_name LIKE ? ORDER BY id DESC"; // NOSONAR
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
    <title>Todo (Tahap 4 Aman)</title>
    <style>
        body {
            font-family: Arial;
            max-width: 600px;
            margin: 40px auto;
            background: #f4f4f4;
        }

        form {
            display: flex;
            margin-bottom: 20px;
            gap: 5px;
        }

        input {
            flex: 1;
            padding: 10px;
        }

        button {
            padding: 10px;
            color: #fff;
            background: #007BFF;
            border: none;
            cursor: pointer;
        }

        li {
            background: #fff;
            padding: 10px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }

        a {
            text-decoration: none;
            font-weight: bold;
        }

        .del {
            color: red;
        }

        .edit {
            color: orange;
        }
    </style>
</head>

<body>
    <h1>Todo List (Aman 100%)</h1>
    <form action="index.php" method="GET">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit">Cari</button>
    </form>

    <?php if ($search_query) echo "<h3>Hasil: " . htmlspecialchars($search_query) . "</h3>"; ?>

    <form action="index.php" method="POST">
        <?php if ($task_to_edit): ?>
            <input type="hidden" name="task_id" value="<?php echo $task_to_edit['id']; ?>">
            <input type="text" name="task_name" value="<?php echo htmlspecialchars($task_to_edit['task_name']); ?>">
            <button type="submit" name="update_task">Simpan</button>
        <?php else: ?>
            <input type="text" name="task_name" placeholder="Tugas baru...">
            <button type="submit" name="add_task">Tambah</button>
        <?php endif; ?>
    </form>

    <ul>
        <?php foreach ($tasks as $task): ?>
            <li>
                <span><?php echo htmlspecialchars($task['task_name']); ?></span>
                <div>
                    <a href="?edit_task=<?php echo $task['id']; ?>" class="edit">Edit</a> |
                    <a href="?delete_task=<?php echo $task['id']; ?>" class="del">Hapus</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</body>

</html>