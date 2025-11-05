ALTER TABLE articles ADD COLUMN views INT DEFAULT 0;

-- Cập nhật views cho các bài viết hiện có (nếu cần)
UPDATE articles SET views = 0 WHERE views IS NULL;