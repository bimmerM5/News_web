<?php
namespace App\Models;

use App\Core\Database;

/**
 * Base Model - Cải thiện với Dependency Injection
 * 
 * Tuân thủ:
 * - Dependency Inversion Principle: Nhận PDO qua constructor
 * - Open/Closed Principle: Có thể extend mà không modify
 * - Liskov Substitution Principle: Subclasses có thể thay thế BaseModel
 */
abstract class BaseModel
{
    protected \PDO $pdo;

    /**
     * Constructor với Dependency Injection
     * Cho phép inject PDO từ bên ngoài (hữu ích cho testing)
     */
    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * Getter cho PDO (nếu cần truy cập từ bên ngoài)
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
}


