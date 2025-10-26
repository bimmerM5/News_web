<?php
namespace App\Queries;

class ArticleQueries
{
    public static function getPublishedArticles(): string
    {
        return "SELECT a.article_id, a.title, a.summary, a.created_at, c.category_name
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.category_id
                WHERE a.status = 'published'
                ORDER BY a.created_at DESC
                LIMIT :per OFFSET :off";
    }

    public static function getPublishedArticlesByCategory(): string
    {
        return "SELECT a.article_id, a.title, a.summary, a.created_at
                FROM articles a
                WHERE a.status = 'published' AND a.category_id = :cid
                ORDER BY a.created_at DESC
                LIMIT :per OFFSET :off";
    }

    public static function getByIdWithDetails(): string
    {
        return "SELECT a.*, c.category_name, u.username
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.category_id
                LEFT JOIN users u ON a.user_id = u.user_id
                WHERE a.article_id = ?";
    }

    public static function getContent(): string
    {
        return "SELECT content FROM article_contents WHERE article_id = ?";
    }

    public static function getMedia(): string
    {
        return "SELECT media_url FROM article_media WHERE article_id = ? AND media_type = 'image' ORDER BY media_id ASC";
    }

    public static function countPublishedArticles(): string
    {
        return "SELECT COUNT(*) FROM articles a WHERE a.status = 'published'";
    }

    public static function countPublishedArticlesByCategory(): string
    {
        return "SELECT COUNT(*) FROM articles WHERE status = 'published' AND category_id = :cid";
    }

    public static function getUserArticles(): string
    {
        return "SELECT article_id, title, status, created_at FROM articles WHERE user_id = ? ORDER BY created_at DESC";
    }
}
