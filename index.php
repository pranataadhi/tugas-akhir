<?php
// index.php

// --- 1. Konfigurasi Database ---
$db_host = 'app_db';
$db_name = 'db_todolist';
$db_user = 'user_todo';

// [VULNERABILITY 1] Hardcoded Password
// SonarCloud akan mendeteksi ini sebagai "Critical Security Hotspot" atau Blocker
$db_pass = 'password_todo';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// --- 2. Logika Aplikasi (Backend) ---

// CREATE
if (isset($_POST['add_task']) && !empty($_POST['task_name'])) {
    $task_name = $_POST['task_name'];
    $stmt = $db->prepare("INSERT INTO tasks (task_name) VALUES (?)");
    $stmt->execute([$task_name]);
    header("Location: index.php");
    exit;
}

// DELETE
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];

    // [VULNERABILITY 2] SQL Injection
    // Menyambungkan variabel langsung ke query tanpa prepare
    $sql = "DELETE FROM tasks WHERE id = " . $task_id;
    $db->query($sql);

    header("Location: index.php");
    exit;
}

// READ
$search_query = "";
$sql = "SELECT * FROM tasks ORDER BY id DESC";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql = "SELECT * FROM tasks WHERE task_name LIKE ? ORDER BY id DESC";
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
    <title>Aplikasi Todo List (Rentan)</title>
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
        }

        form input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
        }

        form button {
            padding: 10px 15px;
            background: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 0 4px 4px 0;
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
        }

        li a {
            text-decoration: none;
            color: #dc3545;
            margin-left: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>Aplikasi Todo List</h1>

    <form action="index.php" method="GET">
        <input type="text" name="search" placeholder="Cari tugas..." value="<?php echo $search_query; ?>">
        <button type="submit">Cari</button>
    </form>

    <?php if (!empty($search_query)): ?>
        <h3>Hasil pencarian untuk: '<?php echo $search_query; ?>'</h3>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <input type="text" name="task_name" placeholder="Tugas baru..." required>
        <button type="submit" name="add_task">Tambah</button>
    </form>

    <ul>
        <?php foreach ($tasks as $task): ?>
            <li>
                <span><?php echo $task['task_name']; ?></span>
                <a href="index.php?delete_task=<?php echo $task['id']; ?>">Hapus</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>

</html>