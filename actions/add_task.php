<?php
// actions/add_task.php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['task_name'])) {
    $task_name = $_POST['task_name'];

    // Prepared Statement untuk mencegah SQL Injection
    $stmt = $db->prepare("INSERT INTO tasks (task_name) VALUES (?)");
    $stmt->execute([$task_name]);
}

// Kembalikan ke halaman utama
header("Location: ../index.php");
exit;
