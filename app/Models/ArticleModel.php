<?php
namespace App\Models;

class ArticleModel extends BaseModel
{
    /**
 * Lấy 1 bài viết theo ID kèm thông tin danh mục và danh sách media (nếu có).
 * Trả về: array|null
 */
public function getByIdWithDetails(int $id): ?array
{
    // Lấy bài viết theo ID (chỉ bài publish), lấy toàn bộ cột hiện có trong bảng articles
    $sql = "
        SELECT a.*, c.category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.category_id
        WHERE a.article_id = :id AND a.status = 'published'
        LIMIT 1
    ";
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':id', (int)$id, \PDO::PARAM_INT);
    $st->execute();
    $row = $st->fetch();

    if (!$row) {
        return null;
    }

    // Lấy danh sách media kèm theo (nếu có)
    $sqlMedia = "
        SELECT media_id, media_type, media_url
        FROM article_media
        WHERE article_id = :id
        ORDER BY media_id ASC
    ";
    $stm = $this->pdo->prepare($sqlMedia);
    $stm->bindValue(':id', (int)$id, \PDO::PARAM_INT);
    $stm->execute();
    $row['media'] = $stm->fetchAll();

    return $row;
}

    /**
     * Tạo WHERE và danh sách tham số một cách nhất quán.
     * Trả về: [$whereSql, $binds]  (binds là mảng [':cid'=>..., ':kw1'=>..., ':kw2'=>...])
     */
    private function buildWhere(?int $catId, string $q): array
    {
        $whereParts = ['a.status = "published"'];
        $binds = [];

        if ($catId !== null && $catId > 0) {
            $whereParts[] = 'a.category_id = :cid';
            $binds[':cid'] = (int)$catId;
        }

        $q = trim($q);
        if ($q !== '') {
            // Dùng 2 placeholder khác nhau để tránh HY093 khi native prepares
            $whereParts[] = '(a.title LIKE :kw1 OR a.summary LIKE :kw2)';
            $like = '%' . $q . '%';
            $binds[':kw1'] = $like;
            $binds[':kw2'] = $like;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $whereParts);
        return [$whereSql, $binds];
    }

    /**
     * Bind các tham số động vào statement theo đúng danh sách đã có.
     */
    private function bindParams(\PDOStatement $st, array $binds): void
    {
        foreach ($binds as $name => $val) {
            $st->bindValue($name, is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            // Lưu ý: Khi dùng bindValue theo kiểu này cần truyền cả giá trị:
            // nhưng PDO::bindValue cần 3 tham số (name, value, type). Viết lại cho đúng:
        }
    }

    /**
     * Lưu ý: bindParams ở trên bị thiếu value khi set type,
     * viết lại đúng:
     */
    private function bindParamsFixed(\PDOStatement $st, array $binds): void
    {
        foreach ($binds as $name => $val) {
            $st->bindValue($name, $val, is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
    }

    /**
     * Danh sách bài viết đã publish (phân trang + lọc danh mục)
     * Trả về: [array $rows, int $total]
     */
    public function getPublishedArticles(int $page, int $per, ?int $catId = null): array
    {
        $per    = max(1, (int)$per);
        $offset = max(0, (int)(($page - 1) * $per));

        // KHÔNG có từ khóa ở hàm này
        [$where, $binds] = $this->buildWhere($catId, '');

        // COUNT
        $sqlCount = "SELECT COUNT(*) FROM articles a {$where}";
        $stc = $this->pdo->prepare($sqlCount);
        $this->bindParamsFixed($stc, $binds);
        $stc->execute();
        $total = (int)$stc->fetchColumn();

        // LIST: chèn LIMIT/OFFSET đã ép kiểu thẳng vào SQL
        $sql = "
            SELECT
                a.article_id,
                a.title,
                a.summary,
                a.created_at,
                c.category_name,
                (
                    SELECT am.media_url
                    FROM article_media am
                    WHERE am.article_id = a.article_id
                      AND am.media_type = 'image'
                    ORDER BY am.media_id ASC
                    LIMIT 1
                ) AS thumb
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.category_id
            {$where}
            ORDER BY a.created_at DESC
            LIMIT {$per} OFFSET {$offset}
        ";
        $st = $this->pdo->prepare($sql);
        $this->bindParamsFixed($st, $binds);
        $st->execute();
        $rows = $st->fetchAll();

        return [$rows, $total];
    }

    /**
     * Tìm kiếm bài publish theo từ khóa (title/summary), có phân trang & lọc danh mục
     * Trả về: [array $rows, int $total]
     */
    public function searchPublished(string $q, int $page, int $per, ?int $catId = null): array
    {
        $per    = max(1, (int)$per);
        $offset = max(0, (int)(($page - 1) * $per));

        [$where, $binds] = $this->buildWhere($catId, $q);

        // COUNT
        $sqlCount = "SELECT COUNT(*) FROM articles a {$where}";
        $stc = $this->pdo->prepare($sqlCount);
        $this->bindParamsFixed($stc, $binds);
        $stc->execute();
        $total = (int)$stc->fetchColumn();

        // LIST
        $sql = "
            SELECT
                a.article_id,
                a.title,
                a.summary,
                a.created_at,
                c.category_name,
                (
                    SELECT am.media_url
                    FROM article_media am
                    WHERE am.article_id = a.article_id
                      AND am.media_type = 'image'
                    ORDER BY am.media_id ASC
                    LIMIT 1
                ) AS thumb
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.category_id
            {$where}
            ORDER BY a.created_at DESC
            LIMIT {$per} OFFSET {$offset}
        ";
        $st = $this->pdo->prepare($sql);
        $this->bindParamsFixed($st, $binds);
        $st->execute();
        $rows = $st->fetchAll();

        return [$rows, $total];
    }

    
}
