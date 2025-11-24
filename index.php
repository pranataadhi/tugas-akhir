<?php
// --- Konfigurasi Database ---
// Nama service 'app_db' dari docker-compose.yml
$db_host = 'app_db';
$db_name = 'db_todolist';
$db_user = 'user_todo';
$db_pass = 'password_todo';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// --- Logika Aplikasi (CRUD) ---

// 1. CREATE (Tambah Tugas)
if (isset($_POST['add_task']) && !empty($_POST['task_name'])) {
    $task_name = $_POST['task_name'];
    $stmt = $db->prepare("INSERT INTO tasks (task_name) VALUES (?)");
    $stmt->execute([$task_name]);
    header("Location: index.php");
    exit;
}

// 2. DELETE (Hapus Tugas)
// ======================================================
// VERSI RENTAN SQL INJECTION (MODIFIKASI UNTUK SONARQUBE)
// ======================================================
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];

    // PERUBAHAN PENTING:
    // Kita melakukan penggabungan string (concatenation) LANGSUNG 
    // di dalam fungsi query(). Ini memicu aturan SonarQube Community.
    $db->query("DELETE FROM tasks WHERE id = " . $task_id);

    header("Location: index.php");
    exit;
}

// 3. READ (Baca Tugas & Pencarian) harus vuln
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
        }

        h1,
        h3 {
            text-align: center;
        }

        form {
            display: flex;
            margin-bottom: 20px;
        }

        form input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
        }

        form button {
            padding: 10px 15px;
            background: #007BFF;
            color: white;
            border: none;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        li a {
            text-decoration: none;
            color: #dc3545;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <h1>Aplikasi Todo List</h1>

    <form action="index.php" method="GET">
        <input type="text" name="search" placeholder="Cari tugas...">
        <button type="submit">Cari</button>
    </form>

    <?php
    if (!empty($search_query)) {
        // ======================================================
        // KERENTANAN XSS
        // ======================================================
        echo "<h3>Hasil pencarian untuk: " . $search_query . "</h3>";
    }
    ?>

    <form action="index.php" method="POST">
        <input type="text" name="task_name" placeholder="Tugas baru..." required>
        <button type="submit" name="add_task">Tambah</button>
    </form>

    <ul>
        <?php foreach ($tasks as $task): ?>
            <li>
                <span><?php echo htmlspecialchars($task['task_name']); // Bagian ini aman 
                        ?></span>
                <a href="index.php?delete_task=<?php echo $task['id']; ?>">Hapus</a>
            </li>
        <?php endforeach; ?>
    </ul>

</body>

</html>