<?php
require_once '../vendor/autoload.php';

use App\Config;
use App\TaskManager;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['task_name'])) {
    $db = Config::getConnection();
    if ($db) {
        $manager = new TaskManager($db);
        $manager->addTask($_POST['task_name']);
    }
}
header("Location: ../index.php");
exit;
