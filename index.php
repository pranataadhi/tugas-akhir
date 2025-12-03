<?php
// Import koneksi database
require_once 'config/database.php';

// --- Logika READ (Hanya untuk menampilkan data) ---
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
    <title>Aplikasi Todo List</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <h1>Aplikasi Todo List</h1>

    <form action="index.php" method="GET">
        <input type="text" name="search" placeholder="Cari tugas..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit">Cari</button>
    </form>

    <?php if (!empty($search_query)): ?>
        <h3>Hasil pencarian untuk: '<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>'</h3>
    <?php endif; ?>

    <form action="actions/add_task.php" method="POST">
        <input type="text" name="task_name" placeholder="Tugas baru..." required>
        <button type="submit" name="add_task">Tambah</button>
    </form>

    <ul>
        <?php foreach ($tasks as $task): ?>
            <li>
                <span><?php echo htmlspecialchars($task['task_name']); ?></span>
                <a href="actions/delete_task.php?id=<?php echo $task['id']; ?>" onclick="return confirm('Yakin hapus?');">Hapus</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>

</html>