<?php
// --- 1. Konfigurasi Database ---
// Disarankan menggunakan environment variable, tapi untuk demo ini kita tetap pakai variabel
$db_host = 'app_db';
$db_name = 'db_todolist';
$db_user = 'user_todo';
$db_pass = 'password_todo'; 

try {
    // Menggunakan PDO dengan mode error exception
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Jangan menampilkan $e->getMessage() di produksi karena bisa membocorkan struktur DB
    die("Koneksi database gagal."); 
}

// --- 2. Logika Aplikasi (Backend) ---

// UPDATE dengan Prepared Statement
if (isset($_POST['update_task'], $_POST['task_name'], $_POST['task_id'])) {
    $task_id = $_POST['task_id'];
    $task_name = $_POST['task_name'];
    
    // Perbaikan: Gunakan placeholder (?) atau named parameter (:name)
    $stmt = $db->prepare("UPDATE tasks SET task_name = ? WHERE id = ?");
    $stmt->execute([$task_name, $task_id]);
    
    header("Location: index.php"); 
    exit;
}

// CREATE dengan Prepared Statement
if (isset($_POST['add_task'], $_POST['task_name'])) {
    $task_name = $_POST['task_name'];
    
    $stmt = $db->prepare("INSERT INTO tasks (task_name) VALUES (?)");
    $stmt->execute([$task_name]);

    header("Location: index.php"); 
    exit;
}

// DELETE dengan Prepared Statement
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];
    
    $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);

    header("Location: index.php");
    exit;
}

// READ & SEARCH dengan Prepared Statement
$search_query = $_GET['search'] ?? "";
if (!empty($search_query)) {
    // Perbaikan: Placeholder digunakan di dalam LIKE
    $sql = "SELECT * FROM tasks WHERE task_name LIKE ? ORDER BY id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute(["%$search_query%"]);
} else {
    $sql = "SELECT * FROM tasks ORDER BY id DESC";
    $stmt = $db->query($sql);
}

$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<span><?php echo htmlspecialchars($task['task_name'], ENT_QUOTES, 'UTF-8'); ?></span>