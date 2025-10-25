<?php
namespace App\Models;

use App\Core\Database;

abstract class BaseModel
{
    protected \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }
}


