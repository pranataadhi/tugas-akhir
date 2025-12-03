<?php

namespace App;

use PDO;

class TaskManager
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllTasks()
    {
        $sql = "SELECT * FROM tasks ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchTasks($query)
    {
        $sql = "SELECT * FROM tasks WHERE task_name LIKE ? ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$query%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTask($taskName)
    {
        if (empty($taskName)) return false;
        $stmt = $this->db->prepare("INSERT INTO tasks (task_name) VALUES (?)");
        return $stmt->execute([$taskName]);
    }

    public function deleteTask($id)
    {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
