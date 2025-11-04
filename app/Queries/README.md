# Cấu trúc Queries - Quản lý SQL riêng biệt

## Tổng quan

Dự án đã được tổ chức lại để tách biệt các câu SQL khỏi Model bằng cách tạo các lớp Queries chuyên dụng.

## Cấu trúc thư mục

```
app/
├── Queries/           # Thư mục chứa các lớp quản lý SQL
│   ├── ArticleQueries.php
│   ├── UserQueries.php
│   ├── CategoryQueries.php
│   └── CommentQueries.php
└── Models/            # Các Model đã được cập nhật
    ├── ArticleModel.php
    ├── UserModel.php
    ├── CategoryModel.php
    └── CommentModel.php
```

## Ưu điểm của cách tiếp cận này

### ✅ **Tách biệt rõ ràng**
- SQL queries được tách riêng khỏi logic business
- Model chỉ tập trung vào xử lý dữ liệu và binding parameters
- Dễ dàng tìm và quản lý các câu SQL

### ✅ **Tổ chức tốt hơn**
- Mỗi entity có lớp Queries riêng
- Các phương thức static dễ gọi và sử dụng
- Có thể mở rộng dễ dàng khi cần thêm queries mới

### ✅ **Bảo trì dễ dàng**
- Thay đổi SQL không ảnh hưởng đến logic Model
- Có thể tái sử dụng queries ở nhiều nơi
- Dễ dàng debug và optimize queries

### ✅ **Hiệu suất tốt**
- Không cần đọc file như cách sử dụng file SQL
- Class tĩnh được load một lần
- Truy cập nhanh hơn

## Cách sử dụng

### Trong Model:

```php
use App\Queries\ArticleQueries;

class ArticleModel extends BaseModel
{
    public function getPublishedArticles(int $page, int $perPage): array
    {
        $sql = ArticleQueries::getPublishedArticles();
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':per', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
```

### Trong Controller hoặc nơi khác:

```php
use App\Queries\ArticleQueries;

// Có thể sử dụng trực tiếp nếu cần
$sql = ArticleQueries::getPublishedArticles();
```

## Các lớp Queries đã tạo

### ArticleQueries
- `getPublishedArticles()` - Lấy bài viết đã xuất bản
- `getPublishedArticlesByCategory()` - Lấy bài viết theo category
- `getByIdWithDetails()` - Lấy chi tiết bài viết
- `getContent()` - Lấy nội dung bài viết
- `getMedia()` - Lấy media của bài viết
- `countPublishedArticles()` - Đếm bài viết đã xuất bản
- `getUserArticles()` - Lấy bài viết của user

### UserQueries
- `findByUsername()` - Tìm user theo username
- `getProfile()` - Lấy profile user
- `getUserArticles()` - Lấy bài viết của user
- `create()` - Tạo user mới
- `update()` - Cập nhật user
- `updatePassword()` - Cập nhật password
- `upsertProfile()` - Tạo/cập nhật profile

### CategoryQueries
- `listAll()` - Lấy tất cả categories
- `listWithTotals()` - Lấy categories với số lượng bài viết
- `find()` - Tìm category theo ID
- `create()` - Tạo category mới
- `update()` - Cập nhật category
- `delete()` - Xóa category
- `exists()` - Kiểm tra category tồn tại
- `hasArticles()` - Kiểm tra category có bài viết

### CommentQueries
- `listForArticle()` - Lấy comment của bài viết
- `create()` - Tạo comment mới (stored procedure)
- `createDirect()` - Tạo comment trực tiếp
- `update()` - Cập nhật comment
- `delete()` - Xóa comment
- `findById()` - Tìm comment theo ID
- `countByArticle()` - Đếm comment của bài viết
- `getUserComments()` - Lấy comment của user

## Lưu ý

- Tất cả các phương thức trong Queries đều là `static`
- Mỗi phương thức trả về chuỗi SQL
- Model sẽ bind parameters và execute queries
- Có thể mở rộng thêm các phương thức mới khi cần
