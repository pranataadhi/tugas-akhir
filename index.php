<?php
require_once 'vendor/autoload.php'; // Autoload dari Composer
use App\Config;
use App\TaskManager;

$db = Config::getConnection();
// Cek koneksi, jika gagal (misal saat build docker image tanpa DB) jangan crash
$tasks = [];
if ($db) {
    $manager = new TaskManager($db);
    $search_query = isset($_GET['search']) ? $_GET['search'] : '';

    if ($search_query) {
        $tasks = $manager->searchTasks($search_query);
    } else {
        $tasks = $manager->getAllTasks();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Aplikasi Todo List</title>
</head>

<body>
    <h1>Aplikasi Todo List</h1>

    <form action="index.php" method="GET">
        <input type="text" name="search" placeholder="Cari tugas..." value="<?php echo htmlspecialchars($search_query ?? ''); ?>">
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
                <a href="actions/delete_task.php?id=<?php echo $task['id']; ?>">Hapus</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>

</html>