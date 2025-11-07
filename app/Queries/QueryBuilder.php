<?php
namespace App\Queries;

/**
 * Query Builder Pattern - Cải thiện tính OOP
 * Giúp xây dựng queries linh hoạt hơn
 */
class QueryBuilder
{
    private string $select = '';
    private string $from = '';
    private string $where = '';
    private array $params = [];
    private string $orderBy = '';
    private string $limit = '';

    public function select(string $columns): self
    {
        $this->select = "SELECT $columns";
        return $this;
    }

    public function from(string $table, string $alias = ''): self
    {
        $this->from = "FROM $table" . ($alias ? " $alias" : '');
        return $this;
    }

    public function where(string $condition, $value = null): self
    {
        if ($this->where) {
            $this->where .= " AND $condition";
        } else {
            $this->where = "WHERE $condition";
        }
        
        if ($value !== null) {
            $this->params[] = $value;
        }
        
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "ORDER BY $column $direction";
        return $this;
    }

    public function limit(int $count, int $offset = 0): self
    {
        $this->limit = "LIMIT $count" . ($offset > 0 ? " OFFSET $offset" : '');
        return $this;
    }

    public function build(): array
    {
        $sql = trim(implode(' ', [
            $this->select,
            $this->from,
            $this->where,
            $this->orderBy,
            $this->limit
        ]));
        
        return ['sql' => $sql, 'params' => $this->params];
    }

    public function reset(): self
    {
        $this->select = '';
        $this->from = '';
        $this->where = '';
        $this->params = [];
        $this->orderBy = '';
        $this->limit = '';
        return $this;
    }
}

