<?php
namespace App\Queries;

class ArticleQueries
{
    public static function getPublishedArticles(): string
    {
        return "SELECT a.article_id, a.title, a.summary, a.created_at, c.category_name,
                (SELECT am.media_url FROM article_media am 
                 WHERE am.article_id = a.article_id AND am.media_type = 'image' 
                 ORDER BY am.media_id ASC LIMIT 1) as thumb
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.category_id
                WHERE a.status = 'published'
                ORDER BY a.created_at DESC
                LIMIT :per OFFSET :off";
    }

    public static function getPublishedArticlesByCategory(): string
    {
        return "SELECT a.article_id, a.title, a.summary, a.created_at,
                (SELECT am.media_url FROM article_media am 
                 WHERE am.article_id = a.article_id AND am.media_type = 'image' 
                 ORDER BY am.media_id ASC LIMIT 1) as thumb
                FROM articles a
                WHERE a.status = 'published' AND a.category_id = :cid
                ORDER BY a.created_at DESC
                LIMIT :per OFFSET :off";
    }

    public static function getByIdWithDetails(): string
    {
        return "SELECT a.*, c.category_name, u.username,
                (SELECT COUNT(*) FROM views v WHERE v.article_id = a.article_id) as views_count
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.category_id
                LEFT JOIN users u ON a.user_id = u.user_id
                WHERE a.article_id = ?";
    }

    public static function addView(): string
    {
        return "INSERT INTO views (article_id, user_id) VALUES (?, ?)";
    }

    public static function getContent(): string
    {
        return "SELECT content FROM article_contents WHERE article_id = ?";
    }

    public static function getMedia(): string
    {
        return "SELECT media_url FROM article_media WHERE article_id = ? AND media_type = 'image' ORDER BY media_id ASC";
    }

    public static function incrementViews(): string
    {
        return "UPDATE articles SET views = views + 1 WHERE article_id = ?";
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

    public static function searchArticles(): string
    {
        return "SELECT a.article_id, a.title, a.summary, a.created_at,
                       (SELECT am.media_url 
                        FROM article_media am 
                        WHERE am.article_id = a.article_id 
                          AND am.media_type='image' 
                        ORDER BY am.media_id ASC 
                        LIMIT 1) AS thumb
                FROM articles a
                WHERE a.status='published' 
                  AND (a.title LIKE :kw OR a.summary LIKE :kw)
                ORDER BY a.created_at DESC
                LIMIT :per OFFSET :off";
    }

    public static function countSearchResults(): string
    {
        return "SELECT COUNT(*) 
                FROM articles 
                WHERE status='published' 
                  AND (title LIKE :kw OR summary LIKE :kw)";
    }

    public static function getAllArticlesForAdmin(): string
    {
        return "SELECT a.article_id, a.title, a.status, a.created_at, c.category_name
                FROM articles a LEFT JOIN categories c ON a.category_id=c.category_id
                ORDER BY a.created_at DESC";
    }

    public static function getById(): string
    {
        return "SELECT * FROM articles WHERE article_id=?";
    }

    public static function createMedia(): string
    {
        return "INSERT INTO article_media(article_id, media_url, media_type) VALUES(?, ?, 'image')";
    }

    public static function updateArticle(): string
    {
        return "UPDATE articles SET title=?, summary=?, category_id=? WHERE article_id=?";
    }

    public static function updateContent(): string
    {
        return "UPDATE article_contents SET content=? WHERE article_id=?";
    }

    public static function deleteArticle(): string
    {
        return "DELETE FROM articles WHERE article_id=?";
    }

    public static function deleteMedia(): string
    {
        return "DELETE FROM article_media WHERE article_id=? AND media_type='image'";
    }
}
