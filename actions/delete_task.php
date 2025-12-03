<?php
// actions/delete_task.php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    // Prepared Statement untuk keamanan
    $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
}

// Kembalikan ke halaman utama
header("Location: ../index.php");
exit;
?>