<?php

namespace App;

use PDO;
use PDOException;

class Config
{
    public static function getConnection()
    {
        $db_host = 'app_db';
        $db_name = 'db_todolist';
        $db_user = 'user_todo';
        $db_pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'password_todo';

        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            // Return null jika gagal (agar bisa ditest)
            return null;
        }
    }
}
