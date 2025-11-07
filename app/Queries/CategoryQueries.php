<?php
namespace App\Queries;

class CategoryQueries
{
    
    public static function listAll(): string
    {
        return "SELECT * FROM categories ORDER BY category_name";
    }

    // For admin listing: keep a deterministic ascending ID order
    public static function listAllById(): string
    {
        return "SELECT * FROM categories ORDER BY category_id ASC";
    }

    public static function listWithTotals(): string
    {
        return "SELECT c.category_id, c.category_name, c.description, COUNT(a.article_id) AS total
                FROM categories c
                LEFT JOIN articles a ON a.category_id = c.category_id AND a.status = 'published'
                GROUP BY c.category_id, c.category_name, c.description
                ORDER BY c.category_name";
    }

    public static function find(): string
    {
        return "SELECT * FROM categories WHERE category_id = ?";
    }

    public static function create(): string
    {
        return "INSERT INTO categories (category_name, description) VALUES (?, ?)";
    }

    public static function update(): string
    {
        return "UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?";
    }

    public static function delete(): string
    {
        return "DELETE FROM categories WHERE category_id = ?";
    }

    public static function exists(): string
    {
        return "SELECT COUNT(*) FROM categories WHERE category_id = ?";
    }

    public static function hasArticles(): string
    {
        return "SELECT COUNT(*) FROM articles WHERE category_id = ?";
    }

    public static function getArticleIdsByCategory(): string
    {
        return "SELECT article_id FROM articles WHERE category_id = ?";
    }
}
