<?php
namespace App\Queries;

class AdminQueries
{
    public static function registerUser(): string
    {
        return "CALL sp_register_user(?, ?, ?, ?)";
    }

    public static function createArticle(): string
    {
        return "CALL sp_create_article(?, ?, ?, ?, ?)";
    }

    public static function publishArticle(): string
    {
        return "CALL sp_publish_article(?)";
    }

    public static function toggleLike(): string
    {
        return "CALL sp_toggle_like(?, ?)";
    }
}

