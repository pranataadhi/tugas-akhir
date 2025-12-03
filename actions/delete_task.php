<?php
// actions/delete_task.php

// Panggil koneksi database
require_once '../config/database.php';

// Cek apakah ada parameter ID di URL
if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    // ===========================================================
    // [VULNERABILITY DI SINI]
    // Kita menyambungkan variabel $task_id langsung ke query SQL.
    // Ini adalah SQL Injection klasik.
    // ===========================================================
    $sql = "DELETE FROM tasks WHERE id = " . $task_id;
    $db->query($sql);

    // Prepared Statement (Aman dari SQL Injection)
    // $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
    // $stmt->execute([$task_id]);
}

// Kembalikan ke halaman utama
header("Location: ../index.php");
exit;
