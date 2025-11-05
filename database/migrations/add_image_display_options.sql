-- Add display options for article images
ALTER TABLE `article_media`
    ADD COLUMN `size_class` VARCHAR(20) NOT NULL DEFAULT 'img-medium',
    ADD COLUMN `align_class` VARCHAR(20) NOT NULL DEFAULT 'img-center',
    ADD COLUMN `caption` TEXT NULL;
