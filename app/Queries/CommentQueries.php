<?php
namespace App\Queries;

class CommentQueries
{
    public static function listForArticle(): string
    {
        return "SELECT c.comment_id, c.content, c.created_at, u.username
                FROM comments c
                JOIN users u ON u.user_id = c.user_id
                WHERE c.article_id = ?
                ORDER BY c.created_at ASC";
    }

    public static function create(): string
    {
        return "CALL sp_add_comment(?, ?, ?)";
    }

    public static function createDirect(): string
    {
        return "INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)";
    }

    public static function update(): string
    {
        return "UPDATE comments SET content = ? WHERE comment_id = ? AND user_id = ?";
    }

    public static function delete(): string
    {
        return "DELETE FROM comments WHERE comment_id = ? AND user_id = ?";
    }

    public static function findById(): string
    {
        return "SELECT c.*, u.username FROM comments c 
                JOIN users u ON u.user_id = c.user_id 
                WHERE c.comment_id = ?";
    }

    public static function countByArticle(): string
    {
        return "SELECT COUNT(*) FROM comments WHERE article_id = ?";
    }

    public static function getUserComments(): string
    {
        return "SELECT c.*, a.title as article_title 
                FROM comments c 
                JOIN articles a ON a.article_id = c.article_id 
                WHERE c.user_id = ? 
                ORDER BY c.created_at DESC";
    }
}
