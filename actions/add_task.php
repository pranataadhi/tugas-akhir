<?php
// actions/add_task.php

// Panggil koneksi database
require_once '../config/database.php';

// Cek apakah ada request POST
if (isset($_POST['add_task']) && !empty($_POST['task_name'])) {
    $task_name = $_POST['task_name'];

    // Prepared Statement (Aman dari SQL Injection)
    $stmt = $db->prepare("INSERT INTO tasks (task_name) VALUES (?)");
    $stmt->execute([$task_name]);
}

// Kembalikan ke halaman utama
header("Location: ../index.php");
exit;
