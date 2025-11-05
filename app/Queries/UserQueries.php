<?php
namespace App\Queries;

class UserQueries
{
    public static function findByUsername(): string
    {
        return "SELECT user_id, username, password_hash FROM users WHERE username = ?";
    }

    public static function getProfile(): string
    {
        return "SELECT u.user_id, u.username, up.full_name, up.avatar_url, up.bio
                FROM users u 
                LEFT JOIN user_profiles up ON up.user_id = u.user_id
                WHERE u.user_id = ?";
    }

    public static function getUserArticles(): string
    {
        return "SELECT article_id, title, status, created_at FROM articles WHERE user_id = ? ORDER BY created_at DESC";
    }

    public static function create(): string
    {
        return "INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)";
    }

    public static function update(): string
    {
        return "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
    }

    public static function updatePassword(): string
    {
        return "UPDATE users SET password_hash = ? WHERE user_id = ?";
    }

    public static function upsertProfile(): string
    {
        return "INSERT INTO user_profiles (user_id, full_name, avatar_url, bio) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                full_name = VALUES(full_name), 
                avatar_url = VALUES(avatar_url), 
                bio = VALUES(bio)";
    }

    public static function checkAdminRole(): string
    {
        return "SELECT 1
                FROM user_roles ur
                JOIN roles r ON r.role_id = ur.role_id
                WHERE ur.user_id = ? AND r.role_name = 'admin' LIMIT 1";
    }
}
