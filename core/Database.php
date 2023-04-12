<?php 

namespace App\Core;

use PDO;

class Database {
    protected static $instance = null;
    protected $pdo;

    protected function __construct() {
        $this->pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getPdo() {
        return $this->pdo;
    }
}