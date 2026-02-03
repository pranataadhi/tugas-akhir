<?php
// index.php

// --- 1. Konfigurasi Database ---
$db_host = 'app_db';
$db_name = 'db_todolist';
$db_user = 'user_todo';

// [SONARQUBE: Security Hotspot] Hardcoded Password
// Menyimpan password langsung di code adalah larangan keras.
$db_pass = 'password_todo'; 

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    // [BAD PRACTICE] Error reporting dimatikan supaya error SQL tidak terlihat di layar (tapi menyulitkan debug)
    // $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// --- 2. Logika Aplikasi (Backend) ---

// UPDATE (Simpan Perubahan Tugas)
if (isset($_POST['update_task']) && !empty($_POST['task_name']) && !empty($_POST['task_id'])) {
    $task_id = $_POST['task_id'];
    $task_name = $_POST['task_name'];
    
    // [SONARQUBE: Injection] SQL Injection
    // Variabel langsung dimasukkan ke query ($task_name) tanpa sanitasi.
    $sql = "UPDATE tasks SET task_name = '$task_name' WHERE id = $task_id";
    $db->query($sql);
    
    // [SONARQUBE: Code Smell] String Duplication & Trailing Whitespace
    // Ada spasi kosong di setelah titik koma (;) yang tidak berguna.
    header("Location: index.php"); 
    exit;
}

// CREATE (Tambah Tugas)
if (isset($_POST['add_task']) && !empty($_POST['task_name'])) {
    $task_name = $_POST['task_name'];
    
    // [SONARQUBE: Injection] SQL Injection
    $sql = "INSERT INTO tasks (task_name) VALUES ('$task_name')";
    $db->query($sql);

    // [SONARQUBE: Code Smell] String Duplication & Trailing Whitespace
    // String "Location: index.php" ditulis ulang (padahal bisa pakai konstanta).
    // Perhatikan ada spasi ghaib di ujung baris ini v
    header("Location: index.php"); 
    exit;
}

// DELETE (Hapus Tugas)
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];
    
    // [SONARQUBE: Injection] SQL Injection
    $sql = "DELETE FROM tasks WHERE id = " . $task_id;
    $db->query($sql);

    // [SONARQUBE: Code Smell] String Duplication & Trailing Whitespace
    // Lagi-lagi duplikasi string dan spasi kosong di ujung.
    header("Location: index.php"); 
    exit;
}

// PERSIAPAN EDIT
$task_to_edit = null;
if (isset($_GET['edit_task'])) {
    $id = $_GET['edit_task'];
    
    // [SONARQUBE: Injection] SQL Injection
    $sql = "SELECT * FROM tasks WHERE id = " . $id;
    $stmt = $db->query($sql);
    $task_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// READ (Tampil & Cari Tugas)
$search_query = "";
$sql = "SELECT * FROM tasks ORDER BY id DESC";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    // [SONARQUBE: Injection] SQL Injection pada Search
    $sql = "SELECT * FROM tasks WHERE task_name LIKE '%$search_query%' ORDER BY id DESC";
}

$stmt = $db->query($sql);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Todo List (Vulnerable)</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; background-color: #f4f4f4; }
        h1, h3 { text-align: center; color: #333; }
        form { display: flex; margin-bottom: 20px; gap: 5px; }
        form input[type="text"] { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 15px; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .btn-add { background: #007BFF; }
        .btn-update { background: #28a745; }
        .btn-cancel { background: #6c757d; text-decoration: none; padding: 10px 15px; border-radius: 4px; color: white; display: inline-block;}
        ul { list-style: none; padding: 0; }
        li { background: white; padding: 10px 15px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
        .actions { display: flex; gap: 10px; }
        .actions a { text-decoration: none; font-weight: bold; font-size: 0.9em; }
        .edit-link { color: #ffc107; } 
        .delete-link { color: #dc3545; } 
    </style>
</head>
<body>
    <h1>Aplikasi Todo List (Banyak Error)</h1>

    <form action="index.php" method="GET">
        <input type="text" name="search" placeholder="Cari tugas..." value="<?php echo $search_query; ?>">
        <button type="submit" class="btn-add">Cari</button>
        <?php if(!empty($search_query)): ?>
            <a href="index.php" class="btn-cancel" style="margin-left: 5px;">Reset</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($search_query)): ?>
        <h3>Hasil: '<?php echo $search_query; ?>'</h3>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <?php if ($task_to_edit): ?>
            <input type="hidden" name="task_id" value="<?php echo $task_to_edit['id']; ?>">
            <input type="text" name="task_name" value="<?php echo $task_to_edit['task_name']; ?>" required>
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
                <span><?php echo $task['task_name']; ?></span>
                <div class="actions">
                    <a href="index.php?edit_task=<?php echo $task['id']; ?>" class="edit-link">Edit</a>
                    |
                    <a href="index.php?delete_task=<?php echo $task['id']; ?>" class="delete-link">Hapus</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>

<!-- ini salah satu contoh aplikasi Todo List yang memiliki beberapa kerentanan keamanan
  dan praktik coding yang kurang baik. Beberapa masalah yang ada di dalam kode ini meliputi: -->