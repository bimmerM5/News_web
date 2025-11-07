# News Web (PHP MVC)

Cổng thông tin tin tức nhỏ gọn theo kiến trúc MVC, viết bằng PHP thuần (PDO) và MySQL/MariaDB. Dự án có trang công khai, xác thực người dùng, trang quản trị (bài viết/danh mục), upload nhiều ảnh với tùy chọn hiển thị từng ảnh, tìm kiếm, bình luận (AJAX), lượt thích/lượt xem và JSON API đơn giản.

## Mục lục
- Tính năng
- Kiến trúc & công nghệ
- Cấu trúc thư mục
- Cài đặt nhanh
- Cơ sở dữ liệu
- Tuyến (Routing)
- Ảnh & upload
- Ghi chú phát triển
- Khắc phục sự cố

## Tính năng
- Trang công khai: trang chủ, theo danh mục, chi tiết bài viết, tìm kiếm.
- Xác thực: đăng ký, đăng nhập, đăng xuất (session-based).
- Quản trị: quản lý danh mục và bài viết (tạo, sửa, xuất bản, xóa).
- Ảnh trong bài viết:
  - Upload nhiều ảnh/bài; lưu file vào `public/uploads`.
  - Tùy chọn theo ảnh: kích thước (`img-small`, `img-medium`, `img-large`), căn chỉnh (`img-left`, `img-center`, `img-right`), và caption.
  - Sửa/xóa ảnh ngay trong màn hình edit (xóa cả DB record và file vật lý).
- Bình luận gửi bằng AJAX (yêu cầu đăng nhập).
- Lượt thích và lượt xem bài viết.
- JSON API: danh sách bài viết, chi tiết, bình luận.

## Kiến trúc & công nghệ
- PHP 8.x 
- PDO (MySQL/MariaDB).
- Bootstrap 5.3 (CDN) + CSS tùy biến tại `public/assets/css/style.css` (có Dark/Light theme toggle).
- Apache (khuyến nghị dùng XAMPP trên Windows).

## Cấu trúc thư mục
```
app/
  Config/config.php         # cấu hình DB + base_url
  Core/                     # Router.php, Controller.php, Database.php
  Controllers/              # Home, Article, Admin, Auth, Api, Search, Profile
  Models/                   # Article, Category, Comment, User
  Queries/                  # Câu lệnh SQL + Repository Pattern (Interfaces/Repos)
  Views/                    # Giao diện PHP (layout + các trang)
database/
  news_portal.sql           # schema + dữ liệu mẫu + stored procedures/events
public/
  .htaccess                 # rewrite về index.php (RewriteBase khớp base_url)
  index.php                 # front controller + định tuyến
  assets/css/style.css      # styles (bao gồm class ảnh)
  uploads/                  # thư mục ảnh upload (tạo tự động nếu thiếu)
```

## Cài đặt nhanh
1) Yêu cầu
- PHP 8.1+ với extension PDO MySQL
- MySQL/MariaDB
- Apache (XAMPP hoặc tương đương)

2) Đặt mã nguồn vào web root
- Ví dụ XAMPP (Windows): `C:\xampp\htdocs\News_web`

3) Cấu hình kết nối DB và base URL
- Sửa `app/Config/config.php`:
```php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'news_portal',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => '/news_web/public', // chỉnh theo alias/đường dẫn của bạn
        'debug'    => true,
    ],
];
```

4) Import cơ sở dữ liệu
- Mở phpMyAdmin và import file `database/news_portal.sql` vào DB `news_portal`.
- File này đã bao gồm schema, dữ liệu mẫu, stored procedures và event dọn dẹp view.

5) Cấu hình Apache rewrite (nếu cần)
- Đảm bảo `public/.htaccess` có `RewriteBase` khớp `base_url`. Ví dụ:
```
RewriteBase /news_web/public/
```

6) Phân quyền thư mục upload
- Bảo đảm `public/uploads` tồn tại và cho phép web server ghi.

7) Mở ứng dụng
- Truy cập: `http://localhost/news_web/public` (hoặc theo `base_url` bạn chỉnh).

## Cơ sở dữ liệu
- Bảng chính (rút gọn):
  - `articles`, `article_contents`, `article_media` (lưu `media_url`, `media_type`, `caption`, `size_class`, `align_class`)
  - `categories`, `users`, `user_profiles`, `roles`, `user_roles`
  - `comments`, `likes`, `views`, `tags`, `article_tags`
- Stored procedures tiêu biểu (đã có trong `database/news_portal.sql`):
  - `sp_create_article`, `sp_publish_article`, `sp_add_comment`, `sp_get_comments`
  - `sp_get_user_articles`, `sp_get_articles_by_category`, `sp_toggle_like`
- Event: `ev_clean_old_data` dọn dẹp dữ liệu view cũ định kỳ (ví dụ mỗi ngày).

## Tuyến (Routing)
Tất cả khai báo tại `public/index.php`:

Public
- `GET /` → `HomeController@index`
- `GET /article/{id}` → `ArticleController@show`
- `GET /category/{id}` → `ArticleController@category`
- `GET /search` → `SearchController@index`
- `GET /user/{id}` → `ProfileController@show`

Auth
- `GET /auth/login` → form đăng nhập
- `POST /auth/login` → xử lý đăng nhập
- `GET /auth/register` → form đăng ký
- `POST /auth/register` → xử lý đăng ký
- `POST /auth/logout` → đăng xuất

Admin
- `GET /admin/categories` → danh sách
- `GET /admin/categories/create` → tạo mới
- `POST /admin/categories/store` → lưu mới
- `GET /admin/categories/{id}/edit` → sửa
- `POST /admin/categories/{id}/update` → cập nhật
- `POST /admin/categories/{id}/delete` → xóa
- `GET /admin/articles` → danh sách
- `GET /admin/articles/create` → tạo mới
- `POST /admin/articles/store` → lưu (hỗ trợ upload nhiều ảnh + tùy chọn từng ảnh)
- `GET /admin/articles/{id}/edit` → sửa (nội dung, tùy chọn ảnh)
- `POST /admin/articles/{id}/update` → cập nhật (có thể xóa/thêm ảnh)
- `POST /admin/articles/{id}/delete` → xóa bài
- `POST /admin/articles/{id}/publish` → xuất bản

API (JSON)
- `GET /api/articles`
- `GET /api/article/{id}`
- `GET /api/comments?article_id={id}`
- `POST /api/comments` (body JSON: `{article_id, content}`; yêu cầu đăng nhập)
- `POST /api/toggle-like` (body JSON: `{article_id}`; yêu cầu đăng nhập)

## Ảnh & upload
- Tên file khi upload: `img_YYYYMMDD_HHMMSS_<rand>.ext`, lưu dưới `public/uploads`.
- Tùy chọn hiển thị theo ảnh được lưu trong `article_media`:
  - `size_class`: `.img-small` | `.img-medium` | `.img-large`
  - `align_class`: `.img-left` | `.img-center` | `.img-right`
  - `caption`: mô tả ngắn (tùy chọn)
- Frontend render ảnh bằng `<figure>`/`<figcaption>` với class phù hợp (xem `app/Views/article/show.php`).
- CSS lớp ảnh nằm trong `public/assets/css/style.css`.
- Khi xóa ảnh trong trang sửa, ứng dụng xóa bản ghi DB và file vật lý.

## Ghi chú phát triển
- Router/Controller/Database tự viết, không dùng Composer/autoloader.
- Tổ chức SQL theo lớp `app/Queries/*` và áp dụng Repository Pattern (`RepositoryInterface`, `*Repository`) để tách SQL khỏi nghiệp vụ của Model.
- View là PHP thuần, layout ở `app/Views/layout` (có Dark/Light theme toggle trong header/footer).
- Menu quản trị chỉ hiển thị cho tài khoản có vai trò admin (`UserModel::isAdmin`).

## Khắc phục sự cố
- 404 sau khi đổi thư mục/alias
  - Cập nhật `app/Config/config.php` → `base_url` khớp đường dẫn mới.
- Lỗi kết nối DB
  - Kiểm tra thông tin trong `app/Config/config.php` và database `news_portal` đã import từ `database/news_portal.sql`.


