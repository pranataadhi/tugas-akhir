<?php
require_once '../vendor/autoload.php';

use App\Config;
use App\TaskManager;

if (isset($_GET['id'])) {
    $db = Config::getConnection();
    if ($db) {
        $manager = new TaskManager($db);
        $manager->deleteTask($_GET['id']);
    }
}
header("Location: ../index.php");
exit;
