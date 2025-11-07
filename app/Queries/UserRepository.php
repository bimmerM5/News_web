<?php
namespace App\Queries;

use App\Core\Database;

/**
 * User Repository - Tuân thủ đầy đủ OOP principles
 */
class UserRepository implements UserRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM users ORDER BY username";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = UserQueries::create();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['username'] ?? '',
            $data['password_hash'] ?? '',
            $data['email'] ?? ''
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = UserQueries::update();
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['username'] ?? '',
            $data['email'] ?? '',
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function findByUsername(string $username): ?array
    {
        $sql = UserQueries::findByUsername();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function getProfile(int $userId): ?array
    {
        $sql = UserQueries::getProfile();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function getUserArticles(int $userId): array
    {
        $sql = UserQueries::getUserArticles();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function isAdmin(int $userId): bool
    {
        $sql = UserQueries::checkAdminRole();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return (bool)$stmt->fetchColumn();
    }

    public function register(string $username, string $passwordHash, string $email, ?string $fullName = null): int
    {
        $sql = AdminQueries::registerUser();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username, $passwordHash, $email, $fullName]);
        return (int)$this->pdo->lastInsertId();
    }
}

