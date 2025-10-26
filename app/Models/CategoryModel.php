<?php
namespace App\Models;

use App\Queries\CategoryQueries;

class CategoryModel extends BaseModel
{
    public function listAll(): array
    {
        $sql = CategoryQueries::listAll();
        return $this->pdo->query($sql)->fetchAll();
    }

    public function listWithTotals(): array
    {
        $sql = CategoryQueries::listWithTotals();
        return $this->pdo->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = CategoryQueries::find();
        $s = $this->pdo->prepare($sql);
        $s->execute([$id]);
        $row = $s->fetch();
        return $row ?: null;
    }

    public function create(string $name, string $description): void
    {
        $sql = CategoryQueries::create();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $description]);
    }

    public function update(int $id, string $name, string $description): void
    {
        $sql = CategoryQueries::update();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $description, $id]);
    }

    public function delete(int $id): void
    {
        $sql = CategoryQueries::delete();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }
}


