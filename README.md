# News Web

Một cổng thông tin nhỏ theo phong cách MVC viết bằng PHP thuần, PDO và MySQL/MariaDB. Dự án hỗ trợ đăng nhập/đăng ký, danh mục, CRUD bài viết, upload ảnh với tùy chọn hiển thị cho từng ảnh, tìm kiếm và JSON API gọn nhẹ.

## Mục lục
- Tính năng
- Công nghệ sử dụng
- Cấu trúc thư mục
- Bắt đầu nhanh
- Cơ sở dữ liệu & Migration
- Bản đồ tuyến (Routing)
- Xử lý media (ảnh)
- Ghi chú phát triển
- Khắc phục sự cố

## Tính năng
- Trang công khai: trang chủ, lọc theo danh mục, chi tiết bài viết, tìm kiếm.
- Xác thực: đăng ký, đăng nhập, đăng xuất.
- Quản trị: quản lý danh mục và bài viết (tạo, sửa, xuất bản, xóa).
- Ảnh trong bài viết:
  - Upload nhiều ảnh cho mỗi bài.
  - Thiết lập kích thước theo ảnh (`img-small`, `img-medium`, `img-large`).
  - Thiết lập căn chỉnh theo ảnh (`img-left`, `img-center`, `img-right`).
  - Ghi chú/Caption tùy chọn.
  - Xóa ảnh ngay trong màn hình sửa (xóa DB và file trên ổ đĩa).
- Bình luận gửi bằng AJAX (yêu cầu đăng nhập).
- JSON API đơn giản cho danh sách bài viết, chi tiết bài viết, bình luận và like.

## Công nghệ sử dụng
- PHP 8.x (không framework, MVC tối giản)
- PDO (MySQL/MariaDB)
- Bootstrap (CDN) + CSS tùy biến (`public/assets/css/style.css`)
- Apache (khuyến nghị dùng XAMPP trên Windows)

## Cấu trúc thư mục
```
app/
  Config/config.php         # cấu hình DB + base_url của ứng dụng
  Core/                     # các thành phần khung nhỏ: Router/Controller/Database
  Controllers/              # Home, Article, Admin, Auth, Api, Search, Profile
  Models/                   # Article/Category/Comment/User (truy cập PDO)
  Queries/                  # Câu lệnh SQL gom theo miền
  Views/                    # Giao diện PHP (layout + trang)
database/
  project_db.sql            # schema + dữ liệu mẫu + stored procedures
  migrations/
    add_image_display_options.sql
public/
  index.php                 # front controller + định tuyến
  assets/css/style.css      # styles (bao gồm class ảnh)
  uploads/                  # thư mục ảnh upload (tạo khi chạy)
```

## Bắt đầu nhanh
1) Yêu cầu
- PHP 8.1+ có extension PDO MySQL
- MySQL/MariaDB
- Apache (XAMPP hoặc tương đương)

2) Đặt mã nguồn vào web root
- Ví dụ XAMPP (Windows): `C:\\xampp\\htdocs\\News_web`

3) Cấu hình kết nối DB và base URL
- Sửa `app/Config/config.php`:
```php
return [
  'db' => [
    'host' => '127.0.0.1', 'port' => 3306,
    'name' => 'news_portal', 'user' => 'root', 'pass' => '',
    'charset' => 'utf8mb4',
  ],
  'app' => [
    'base_url' => '/news_web/public', // chỉnh theo alias/đường dẫn Apache của bạn
    'debug' => true,
  ],
];
```

4) Import cơ sở dữ liệu
- Dùng phpMyAdmin: import file `database/project_db.sql` vào DB tên `news_portal`.

5) Chạy migration cho tùy chọn hiển thị ảnh
- Thực thi `database/migrations/add_image_display_options.sql` trong cùng DB. File này thêm `size_class`, `align_class`, `caption` vào `article_media`.

6) Mở ứng dụng
- Truy cập `http://localhost/news_web/public` (hoặc theo `base_url` bạn cấu hình).

## Cơ sở dữ liệu & Migration
Các bảng chính (rút gọn):
- `articles`, `article_contents`, `article_media` (URL ảnh và tùy chọn hiển thị)
- `categories`, `users`, `user_profiles`, `roles`, `user_roles`
- `comments`, `likes`, `views`

Các stored procedure nằm trong `project_db.sql`:
- `sp_create_article`, `sp_publish_article`, `sp_add_comment`, `sp_toggle_like`, v.v… dùng cho luồng Admin/Bình luận.

Migration hiển thị ảnh (`database/migrations/add_image_display_options.sql`) thêm:
- `article_media.size_class` (varchar, mặc định `img-medium`)
- `article_media.align_class` (varchar, mặc định `img-center`)
- `article_media.caption` (TEXT, cho phép NULL)

Ghi chú về cột cũ:
- Nếu bảng `article_media` của bạn còn cột `size` và `align` cũ, code hiện tại không dùng. Bạn có thể map rồi xóa an toàn, ví dụ:
```sql
UPDATE article_media SET size_class = CASE size
  WHEN 'small' THEN 'img-small'
  WHEN 'medium' THEN 'img-medium'
  WHEN 'large' THEN 'img-large'
  WHEN 'thumb' THEN 'img-small'
  ELSE size_class END;
UPDATE article_media SET align_class = CASE align
  WHEN 'left' THEN 'img-left'
  WHEN 'right' THEN 'img-right'
  WHEN 'center' THEN 'img-center'
  ELSE align_class END;
ALTER TABLE article_media DROP COLUMN size, DROP COLUMN align;
```

## Bản đồ tuyến (Routing)
Định nghĩa tại `public/index.php`:

Public
- `GET /` → `HomeController@index`
- `GET /article/{id}` → `ArticleController@show`
- `GET /category/{id}` → `ArticleController@category`
- `GET /search` → `SearchController@index`

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
- `POST /admin/articles/store` → lưu (hỗ trợ upload nhiều ảnh)
- `GET /admin/articles/{id}/edit` → sửa bài
- `POST /admin/articles/{id}/update` → cập nhật (sửa nội dung, đổi tùy chọn ảnh, xóa/Thêm ảnh)
- `POST /admin/articles/{id}/delete` → xóa bài
- `POST /admin/articles/{id}/publish` → xuất bản

API (JSON)
- `GET /api/articles`
- `GET /api/article/{id}`
- `GET /api/comments?article_id={id}`
- `POST /api/comments` (body: `{article_id, content}`; yêu cầu đăng nhập)
- `POST /api/toggle-like` (body: `{article_id}`; yêu cầu đăng nhập)

## Xử lý media (ảnh)
- Ảnh được lưu trong `public/uploads` với tên duy nhất: `img_YYYYMMDD_HHMMSS_<rand>.ext`.
- Tùy chọn theo ảnh lưu ở `article_media` (`size_class`, `align_class`, `caption`).
- Render bằng thẻ `<figure>` và `<figcaption>` kết hợp class:
  - Kích thước: `.img-small`, `.img-medium`, `.img-large`
  - Căn chỉnh: `.img-left`, `.img-center`, `.img-right`
- CSS liên quan nằm ở `public/assets/css/style.css`.
- Xóa ảnh trong màn hình sửa sẽ xóa cả bản ghi DB và file thật trong `public/uploads`.

## Ghi chú phát triển
- Toàn bộ SQL gom trong `app/Queries/*`. Model chỉ bind tham số và thực thi. Xem `app/Queries/README.md`.
- Front controller (`public/index.php`) tự đăng ký route qua `Router` tùy biến (không dùng Composer/autoloader).
- View là PHP thuần; layout ở `app/Views/layout`.

## Khắc phục sự cố
Lỗi Class not found: `App\\Queries\\AdminQueries`
- Đảm bảo `public/index.php` có `require_once app/Queries/AdminQueries.php` (repo đã thêm).

Lỗi thiếu cột `size_class`/`align_class`/`caption`
- Hãy chạy migration `database/migrations/add_image_display_options.sql` sau khi import `project_db.sql`.

Xóa dòng có `media_id` sai
- Ví dụ xóa dòng `media_id = 0 AND article_id = 8`:
```sql
DELETE FROM article_media WHERE media_id = 0 AND article_id = 8 LIMIT 1;
```

Ảnh không hiển thị
- Kiểm tra `app/Config/config.php` → `base_url` đúng với cấu hình Apache.
- Đảm bảo Apache phục vụ thư mục `public/` và `uploads/` tồn tại, có quyền ghi.

---
Chúc bạn phát triển vui vẻ! Cần mở rộng (tags, WYSIWYG, phân trang, REST auth) cứ mở issue/trao đổi thêm.
