<?php
// --- 1. Konfigurasi Database ---
$db_host = 'app_db';
$db_name = 'db_todolist';
$db_user = 'user_todo';

// [SKENARIO: HIGH (Security Hotspot)] 
// Menyimpan password langsung dalam bentuk plain-text di source code (Hardcoded Credentials).
$db_pass = 'password_todo'; 

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// --- 2. Logika Aplikasi (Backend) ---

// UPDATE
if (isset($_POST['update_task']) && !empty($_POST['task_name']) && !empty($_POST['task_id'])) {
    $task_id = $_POST['task_id'];
    $task_name = $_POST['task_name'];
    
    // [SKENARIO: BLOCKER (Vulnerability)]
    // SQL Injection: Variabel langsung disisipkan ke query tanpa prepared statement.
    $sql = "UPDATE tasks SET task_name = '$task_name' WHERE id = $task_id";
    $db->query($sql);
    
    // [SKENARIO: MEDIUM (Code Smell)] Duplikasi String "Location: index.php"
    // [SKENARIO: LOW (Code Smell)] Trailing whitespace (Ada spasi kosong di ujung baris ini) -> 
    header("Location: index.php"); 
    exit;
}

// CREATE
if (isset($_POST['add_task']) && !empty($_POST['task_name'])) {
    $task_name = $_POST['task_name'];
    
    // [SKENARIO: BLOCKER (Vulnerability)]
    // SQL Injection: Input user mentah masuk ke database.
    $sql = "INSERT INTO tasks (task_name) VALUES ('$task_name')";
    $db->query($sql);

    // [SKENARIO: MEDIUM (Code Smell)] String ini dihitung duplikasi oleh SonarQube
    // [SKENARIO: LOW (Code Smell)] Trailing whitespace (Ada spasi kosong di ujung baris ini) -> 
    header("Location: index.php"); 
    exit;
}

// DELETE
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];
    
    // [SKENARIO: BLOCKER (Vulnerability)]
    // SQL Injection: ID dari URL langsung dieksekusi, hacker bisa manipulasi URL.
    $sql = "DELETE FROM tasks WHERE id = " . $task_id;
    $db->query($sql);

    // [SKENARIO: MEDIUM (Code Smell)] Duplikasi string ketiga kalinya
    header("Location: index.php");
    exit;
}

// PERSIAPAN EDIT
$task_to_edit = null;
if (isset($_GET['edit_task'])) {
    $id = $_GET['edit_task'];
    
    // [SKENARIO: BLOCKER (Vulnerability)]
    // SQL Injection pada fitur Select edit.
    $sql = "SELECT * FROM tasks WHERE id = " . $id;
    $stmt = $db->query($sql);
    $task_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// READ & SEARCH
$search_query = "";
$sql = "SELECT * FROM tasks ORDER BY id DESC";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    
    // [SKENARIO: BLOCKER (Vulnerability)]
    // SQL Injection: Hacker bisa memasukkan payload ' OR 1=1 -- di kolom pencarian.
    $sql = "SELECT * FROM tasks WHERE task_name LIKE '%$search_query%' ORDER BY id DESC";
}

$stmt = $db->query($sql);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Todo List (Skenario Rentan)</title>
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
    <h1>Aplikasi Todo List (Skenario Rentan)</h1>

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