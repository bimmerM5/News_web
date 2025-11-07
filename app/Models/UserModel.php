<?php
namespace App\Models;

use App\Queries\UserRepositoryInterface;
use App\Queries\UserRepository;

/**
 * User Model - Cải thiện với Repository Pattern
 */
class UserModel extends BaseModel
{
    private UserRepositoryInterface $repository;

    public function __construct(?\PDO $pdo = null, ?UserRepositoryInterface $repository = null)
    {
        parent::__construct($pdo);
        $this->repository = $repository ?? new UserRepository($this->pdo);
    }

    public function findByUsername(string $username): ?array
    {
        return $this->repository->findByUsername($username);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->repository->findByEmail($email);
    }

    public function getProfile(int $userId): ?array
    {
        return $this->repository->getProfile($userId);
    }

    public function getUserArticles(int $userId): array
    {
        return $this->repository->getUserArticles($userId);
    }

    /**
     * Kiểm tra user có phải admin không
     */
    public function isAdmin(int $userId): bool
    {
        return $this->repository->isAdmin($userId);
    }

    /**
     * Đăng ký user mới
     */
    public function register(string $username, string $passwordHash, string $email, ?string $fullName = null): int
    {
        return $this->repository->register($username, $passwordHash, $email, $fullName);
    }
}


