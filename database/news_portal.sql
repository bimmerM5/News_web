-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 07, 2025 lúc 05:37 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `news_portal`
--

DELIMITER $$
--
-- Thủ tục
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `insert_testdata` ()   BEGIN
    INSERT INTO categories (category_id, category_name, description) VALUES
    (10, 'Technology', 'Tech and IT news'),
    (11, 'Health', 'Health and wellness news');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_add_comment` (IN `p_article_id` INT, IN `p_user_id` INT, IN `p_content` TEXT)   BEGIN
    INSERT INTO comments (article_id, user_id, content)
    VALUES (p_article_id, p_user_id, p_content);
    -- Trigger trg_after_insert_comment sẽ ghi log 'created'
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_assign_role` (IN `p_user_id` INT, IN `p_role_name` VARCHAR(50))   BEGIN
    INSERT INTO user_roles (user_id, role_id)
    SELECT p_user_id, r.role_id
    FROM roles r
    WHERE r.role_name = p_role_name
    ON DUPLICATE KEY UPDATE role_id = VALUES(role_id);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cleanup_test_data` ()   BEGIN
    SET FOREIGN_KEY_CHECKS = 0;

    DELETE FROM comments       WHERE article_id IN (100, 101);
    DELETE FROM likes          WHERE article_id IN (100, 101);
    DELETE FROM views          WHERE article_id IN (100, 101);

    DELETE FROM article_tags   WHERE article_id IN (100, 101);
    DELETE FROM article_contents WHERE article_id IN (100, 101);
    DELETE FROM article_media  WHERE article_id IN (100, 101);

    DELETE FROM articles       WHERE article_id IN (100, 101);

    DELETE FROM tags           WHERE tag_id IN (10, 11);
    DELETE FROM categories     WHERE category_id IN (10, 11);

    DELETE FROM user_profiles  WHERE user_id IN (10, 11);
    DELETE FROM user_roles     WHERE user_id IN (10, 11);
    DELETE FROM users          WHERE user_id IN (10, 11);

    SET FOREIGN_KEY_CHECKS = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_create_article` (IN `p_title` VARCHAR(255), IN `p_summary` TEXT, IN `p_content` TEXT, IN `p_user_id` INT, IN `p_category_id` INT)   BEGIN
    DECLARE v_article_id INT;

    INSERT INTO articles (title, summary, user_id, category_id)
    VALUES (p_title, p_summary, p_user_id, p_category_id);

    SET v_article_id = LAST_INSERT_ID();

    INSERT INTO article_contents (article_id, content)
    VALUES (v_article_id, p_content);

    -- Trả về id mới tạo (tùy nhu cầu)
    SELECT v_article_id AS article_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_articles_by_category` (IN `p_category_id` INT)   BEGIN
    SELECT
        a.article_id,
        a.title,
        a.summary,
        a.status,
        a.created_at,
        a.updated_at,
        u.username AS author_username,
        c.category_name AS category_name
    FROM articles a
    JOIN users u      ON a.user_id = u.user_id
    JOIN categories c ON a.category_id = c.category_id
    WHERE a.category_id = p_category_id
      AND a.status = 'published'
    ORDER BY a.created_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_comments` (IN `p_article_id` INT)   BEGIN
    SELECT
        c.comment_id,
        u.username,
        up.full_name,
        c.content,
        c.created_at
    FROM comments c
    JOIN users u       ON c.user_id = u.user_id
    LEFT JOIN user_profiles up ON up.user_id = u.user_id
    WHERE c.article_id = p_article_id
    ORDER BY c.created_at ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_user_articles` (IN `p_user_id` INT)   BEGIN
    SELECT a.article_id, a.title, a.status, a.created_at
    FROM articles a
    WHERE a.user_id = p_user_id
    ORDER BY a.created_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_publish_article` (IN `p_article_id` INT)   BEGIN
    UPDATE articles
    SET status = 'published'
    WHERE article_id = p_article_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_register_user` (IN `p_username` VARCHAR(50), IN `p_password_hash` VARCHAR(255), IN `p_email` VARCHAR(100), IN `p_full_name` VARCHAR(100))   BEGIN
    DECLARE v_new_user_id INT;

    INSERT INTO users (username, password_hash, email)
    VALUES (p_username, p_password_hash, p_email);

    SET v_new_user_id = LAST_INSERT_ID();

    INSERT INTO user_profiles (user_id, full_name)
    VALUES (v_new_user_id, p_full_name);

    INSERT INTO user_roles (user_id, role_id)
    SELECT v_new_user_id, r.role_id
    FROM roles r
    WHERE r.role_name = 'reader'
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_toggle_like` (IN `p_article_id` INT, IN `p_user_id` INT)   BEGIN
    IF EXISTS (
        SELECT 1 FROM likes
        WHERE article_id = p_article_id AND user_id = p_user_id
    ) THEN
        DELETE FROM likes WHERE article_id = p_article_id AND user_id = p_user_id;
    ELSE
        INSERT INTO likes (article_id, user_id)
        VALUES (p_article_id, p_user_id);
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_truncate_all` ()   BEGIN
    SET FOREIGN_KEY_CHECKS = 0;

    TRUNCATE TABLE article_tags;
    TRUNCATE TABLE article_media;
    TRUNCATE TABLE article_contents;
    TRUNCATE TABLE comments;
    TRUNCATE TABLE comment_logs;
    TRUNCATE TABLE likes;
    TRUNCATE TABLE views;
    TRUNCATE TABLE user_profiles;
    TRUNCATE TABLE user_roles;

    TRUNCATE TABLE articles;
    TRUNCATE TABLE categories;
    TRUNCATE TABLE tags;
    TRUNCATE TABLE users;
    TRUNCATE TABLE roles;

    SET FOREIGN_KEY_CHECKS = 1;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `articles`
--

CREATE TABLE `articles` (
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `articles`
--

INSERT INTO `articles` (`article_id`, `user_id`, `category_id`, `title`, `summary`, `status`, `created_at`, `updated_at`) VALUES
(32, 1, 10, 'Hơn 1,6 triệu hộ mất điện do bão Kalmaegi', 'Hơn 1,6 triệu hộ dân bị mất điện khi bão Kalmaegi quét qua các tỉnh miền Trung, Tây Nguyên gây mưa to, gió mạnh đêm 6 rạng sáng 7/11.', 'published', '2025-11-07 11:17:38', '2025-11-07 11:18:02'),
(33, 1, 10, 'Đường sắt Bắc Nam bị chia cắt, 9 tàu khách phải nằm chờ', 'Bão Kalmaegi gây xói nền đường ray tại cung Phước Lãnh - Vân Canh khiến tuyến đường sắt Bắc Nam qua tỉnh Đăk Lăk, Gia Lai bị ách tắc.', 'published', '2025-11-07 11:20:09', '2025-11-07 11:20:12'),
(34, 1, 6, 'Cổ đông Tesla thông qua gói thù lao 1.000 tỷ USD cho Elon Musk', 'Gói thù lao kỷ lục dành cho CEO vừa được cổ đông Tesla chấp thuận, có thể giúp Musk trở thành người đầu tiên sở hữu 1.000 tỷ USD.', 'published', '2025-11-07 11:22:06', '2025-11-07 11:31:06'),
(35, 1, 6, 'Sự cố khiến phi hành gia Trung Quốc hoãn trở về Trái Đất', 'Ba phi hành gia Wang Jie, Chen Zhongrui và Chen Dong sẽ ở lại trạm Thiên Cung lâu hơn do khoang tàu để bay về Trái Đất của họ bị mảnh rác vũ trụ đâm trúng hôm 5/11.', 'published', '2025-11-07 11:23:33', '2025-11-07 11:31:03'),
(36, 1, 6, 'Đụng độ ở biên giới Afghanistan - Pakistan, 5 người thiệt mạng', 'Giao tranh xảy ra ở biên giới Afghanistan và Pakistan, dù đàm phán ngừng bắn đang diễn ra, khiến ít nhất 5 người thiệt mạng.', 'published', '2025-11-07 11:24:40', '2025-11-07 11:31:01'),
(37, 1, 9, 'Chiếc xửng hấp và cánh cửa tiến vào thương mại điện tử Mỹ', 'Chiếc xửng hấp bằng thép không gỉ do một doanh nghiệp Việt sản xuất trở thành sản phẩm bán chạy trên nền tảng thương mại điện tử Amazon.', 'published', '2025-11-07 11:26:37', '2025-11-07 11:30:59'),
(38, 1, 9, 'Màu sắc mới có thể xuất hiện trên iPhone 18 Pro', 'Apple được cho là đang thử nghiệm và lựa chọn giữa ba màu hoàn toàn mới cho iPhone 18 Pro và 18 Pro Max.', 'published', '2025-11-07 11:27:55', '2025-11-07 11:30:57'),
(39, 1, 9, 'Startup công nghệ thần kinh Việt Nam vào top \'đang lên\' của thế giới', 'Brain-Life, có trụ sở tại TP HCM, đứng thứ năm trong 10 startup thuộc nhóm \"ngôi sao đang lên toàn cầu\" về công nghệ thần kinh, do Startus Insights xếp hạng.', 'published', '2025-11-07 11:29:16', '2025-11-07 11:30:56'),
(40, 1, 9, 'Canon EOS R6 Mark III - máy ảnh quay chụp giá 72 triệu đồng', 'Canon EOS R6 Mark III là mẫu máy không gương lật full-frame hướng đến sự cân bằng giữa chụp ảnh và quay phim.', 'published', '2025-11-07 11:30:44', '2025-11-07 11:30:54'),
(41, 1, 10, 'Thu hồi giấy chứng nhận, ngưng hồ sơ công bố mỹ phẩm liên quan Đoàn Di Băng', 'Cục Quản lý Dược thu hồi Giấy chứng nhận \"Thực hành tốt sản xuất mỹ phẩm\" của Công ty Cổ phần nhà máy y tế EBC Đồng Nai, ngưng tiếp nhận hồ sơ công bố mỹ phẩm của công ty VB Group.', 'published', '2025-11-07 11:33:03', '2025-11-07 11:33:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `article_contents`
--

CREATE TABLE `article_contents` (
  `content_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `article_contents`
--

INSERT INTO `article_contents` (`content_id`, `article_id`, `content`) VALUES
(32, 32, 'Theo Tổng công ty Điện lực miền Trung (EVNCPC), đến 7h sáng nay, đơn vị ghi nhận 427 vụ sự cố, sa thải lưới điện trung áp; hơn 18.700 trạm biến áp bị mất điện. EVNCPC đã khôi phục 53 điểm sự cố và cấp điện trở lại cho hơn 315.000 khách hàng (trên 26%).\r\n\r\nTrên lưới 110 kV có 31 sự cố gây mất điện tại nhiều trạm biến áp. Riêng Gia Lai có 13/30 trạm ảnh hưởng; Phú Yên (PC Đăk Lăk) có 4/26 trạm. EVNCPC đã huy động hơn 1.300 nhân viên từ các công ty thành viên, tập trung khôi phục cấp điện sau bão số 13.'),
(33, 33, 'Từ Quảng Ngãi đến Đăk Lăk hôm qua mưa lớn do ảnh hưởng của bão Kalmaegi. Cung đường Phước Lãnh - Vân Canh bị xói lở khoảng 100 m, sâu 9 m, nền đường bị mất toàn bộ khiến đường ray bị treo. Một số đoạn khác bị xói lở nhẹ, đổ cột tín hiệu, đổ cây.\r\nDo đoạn đường sắt hư hỏng, nhiều tàu khách, tàu hàng phải nằm chờ trên tuyến. Cụ thể từ TP HCM ra Hà Nội, các đoàn tàu phải dừng chờ dọc đường gồm SE8, SE6 chờ tại ga Tuy Hòa, SE22 chờ tại ga Đông Tác, SE4 chờ tại ga Phú Hiệp, SE2 chờ tại ga Hảo Sơn.\r\n\r\nCác tàu SE1, SE3, SE45 từ Hà Nội, Đà Nẵng đi TP HCM chờ tại ga Diêu Trì; SE7 chờ tại ga Phù Cát (Gia Lai).\r\n\r\nNgày 7/11, ngành đường sắt dự kiến chuyển tải hành khách trên 8 đoàn tàu đi qua khu vực, từ ga Tuy Hòa (Đăk Lăk) đến ga Diêu Trì (Gia Lai); đồng thời thông báo hủy các chuyến tàu SE21/SE22 (Sài Gòn - Đà Nẵng) và SE5/SE6 (Hà Nội - Sài Gòn) ngày 7/11. Hành khách được trả vé không thu phí tại nhà ga.\r\nKalmaegi - cơn bão mạnh hiếm gặp trong tháng 11 - đổ bộ vào khu vực từ Quảng Ngãi đến Đăk Lăk tối 6/11, gây gió mạnh cấp 11-13, giật cấp 15, mưa rất lớn, mất điện diện rộng và nhiều thiệt hại về hạ tầng.'),
(34, 34, 'Ngày 6/11, hãng xe điện Mỹ Tesla thông báo hơn 75% cổ đông đã bỏ phiếu ủng hộ đề xuất gói thù lao mới nhất cho CEO Elon Musk trong cuộc họp đại hội cổ đông. Tỷ lệ này chưa gồm 15% cổ phần mà Musk đang nắm giữ.\r\n\r\nKhi kết quả được công bố, những người trong phòng họp reo hò và hô vang khẩu hiệu. Musk ngay sau đó cũng cảm ơn các cổ đông và hội đồng quản trị (HĐQT) Tesla: \"Tôi rất trân trọng việc này\".\r\nTại Tesla, Musk không nhận lương, mà được thưởng cổ phiếu tùy vào kết quả hoạt động của công ty. Cách đây hai tháng, HĐQT Tesla đề xuất trao cho Musk gói thù lao lớn nhất lịch sử doanh nghiệp Mỹ. Theo đó, ông có thể nhận thêm 423,7 triệu cổ phiếu Tesla trong 10 năm tới nếu đạt các mục tiêu cụ thể. Giá trị số cổ phiếu này có thể lên tới 1.000 tỷ USD, nếu vốn hóa Tesla đạt 8.500 tỷ USD. Vốn hóa của hãng hiện là 1.400 tỷ USD.\r\n\r\nGói thưởng được chia làm 12 phần, tương ứng với một loạt chỉ tiêu hoạt động hoặc tài chính. Gói này đồng nghĩa Musk có thể được nhận tới 275 triệu USD một ngày, lớn hơn bất kỳ gói thù lao nào khác trong lịch sử cho CEO.'),
(35, 35, 'Theo Live Science, bộ ba phi hành gia Trung Quốc tạm thời mắc kẹt trong không gian do tàu vũ trụ chở họ quay về bị một vật thể nghi mảnh rác vũ trụ đâm vào vài giờ trước khi khởi hành. Các nhà chức trách đang tìm hiểu chính xác chuyện gì xảy ra, nhưng cho tới nay, chưa có thông tin về mức độ thiệt hại ở khoang tàu hoặc thời gian các phi hành gia sẽ bay về Trái Đất.\r\n\r\nPhi hành gia Wang Jie, Chen Zhongrui và Chen Dong thuộc phi hành đoàn Thần Châu 20 sống trên trạm vũ trụ Thiên Cung từ ngày 24/4 và theo lịch trình họ sẽ quay về Trái Đất hôm 5/11 sau khi bàn giao quyền quản lý cho các đồng nghiệp từ nhiệm vụ Thần Châu 21 đến trạm hôm 31/10. Tuy nhiên, hôm qua, đại diện của Cơ quan vũ trụ có người lái Trung Quốc (CMSA) thông báo tạm hoãn chuyến bay trở về của họ trong một bài đăng trên mạng Weibo. Lý do tạm hoãn là nghi ngờ khoang tàu chở phi hành đoàn bay về bị mảnh rác vũ trụ nhỏ va phải. CMSA đang tiến hành phân tích va chạm và đánh giá rủi ro để đảm bảo sức khỏe và an toàn của 6 phi hành gia trên trạm.\r\nTàu vũ trụ bị hư hại nhiều khả năng vẫn đang ghép nối với trạm Thiên Cung từ sau khi chở phi hành đoàn Thần Châu 20 hồi tháng 4. Theo Ars Technica, con tàu bao gồm 3 khoang có thể tách rời, gồm module cung cấp điện và lực đẩy, khu sinh hoạt của phi hành đoàn và module bay về có dù hỗ trợ. Nếu bất kỳ khoang nào không an toàn, tàu có thể tách rời và bay về Trái Đất mà không chở phi hành gia. Trong trường hợp này, theo CNSA, phi hành đoàn Thần Châu 20 sẽ quay về Trái Đất trên khoang tàu Thần Châu 21.\r\n\r\nTrong suốt nhiệm vụ, chỉ huy phi hành đoàn Thần Châu 20 là Chen Dong phá vỡ kỷ lục đối với phi hành gia Trung Quốc ở nhiều ngày nhất trong vũ trụ với hơn 400 ngày trên quỹ đạo. Chuyến bay trở về bị trì hoãn sẽ giúp Dong kéo dài kỷ lục này. Tình huống của Dong tương tự phi hành gia NASA Frank Rubio, từng phá vỡ kỷ lục người Mỹ ở trong không gian lâu nhất (371 ngày) vào tháng 9/2023 sau khi module bay về của ông bị hư hỏng do va chạm với thiên thạch.\r\n\r\nĐây không phải lần đầu tiên trạm Thiên Cung va chạm với mảnh rác vũ trụ. Một trong các tấm pin quang điện của trạm từng bị mảnh rác vũ trụ đâm trúng năm 2023, gây mất điện một phần. Kết quả là các phi hành gia phải lắp thêm tấm chắn bên ngoài trạm trong những chuyến đi bộ không gian gần đây. Trạm ISS cũng bị thiệt hại do mảnh rác vũ trụ và thường xuyên phải điều chỉnh vị trí để né vật thể lớn. Một số bộ phận của trạm như cánh tay robot Canadarm2 từng bị hỏng do va chạm với mảnh rác nhỏ hơn trong vài năm qua.\r\n\r\nLượng rác vũ trên quỹ đạo Trái Đất đang gia tăng nhanh chóng theo số lượng tàu vũ trụ quanh hành tinh. Nhiều chuyên gia lo ngại nếu xu hướng này tiếp diễn, con người có thể tiến đến mốc không thể vãn hồi, trong đó chuỗi va chạm dây chuyền sẽ khiến quỹ đạo thấp của Trái Đất không thể sử dụng được nữa.'),
(36, 36, 'Ông Ali Mohammed Haqmal, người đứng đầu cơ quan thông tin tỉnh Kandahar của Afghanistan, cho biết giao tranh diễn ra trong thời gian ngắn hôm 6/11. Người dân địa phương cũng nói rằng cuộc đụng độ chỉ kéo dài khoảng 10-15 phút.\r\n\r\n\"Pakistan đã sử dụng vũ khí hạng nhẹ và hạng nặng, nhắm vào các khu vực dân sự\", nguồn tin quân sự Afghanistan giấu tên cho hay. Một quan chức bệnh viện huyện Spin Boldak, tỉnh Kandahar, nói rằng ít nhất 5 người đã thiệt mạng, gồm 4 phụ nữ và một người đàn ông, và 6 người bị thương.\r\nHai bên quy trách nhiệm cho nhau về sự việc. \"Trong khi vòng đàm phán thứ ba đã bắt đầu tại Istanbul, lực lượng Pakistan lại một lần nữa nổ súng vào Spin Boldak. Vì tôn trọng nhóm đàm phán và để tránh thương vong cho dân thường, lực lượng của chúng tôi chưa có phản ứng nào\", phát ngôn viên chính quyền Taliban Zabihullah Mujahid cho biết.\r\n\r\nHamdullah Fitrat, phó phát ngôn viên chính quyền Taliban, thêm rằng \"không rõ lý do Pakistan nổ súng\" và khẳng định các cuộc đàm phán ở Thổ Nhĩ Kỳ vẫn diễn ra.'),
(37, 37, '\"Không ai trong chúng tôi ngờ đến, nhưng đó lại là sản phẩm được ưa chuộng nhất, bán với giá 30 USD\", ông Nguyễn Xuân Minh, Giám đốc thương mại điện tử quốc tế của Sunhouse, chia sẻ tại Hội nghị thường niên 2025 của Amazon Global Selling Việt Nam, ngày 6/11.\r\n\r\nĐưa sản phẩm lên sàn để bán tại Mỹ từ năm 2022, Sunhouse trải qua nhiều khó khăn trước khi gặt hái quả ngọt. \"Chúng tôi đưa những sản phẩm bán chạy nhất trong nước lên mạng, tự tin sẽ thành công, để rồi không như kỳ vọng vì khác biệt về văn hóa, nhu cầu người dùng\", ông cho hay.\r\n\r\nSau những bỡ ngỡ ban đầu, doanh nghiệp này đã có những thành tựu đầu tiên khi \"hiểu được thị trường, hiểu được người Mỹ\". Doanh số trên Amazon tăng gấp ba so với cùng kỳ năm ngoái, phần lớn từ chiếc xửng hấp, \"không phải sản phẩm quen thuộc của Sunhouse, cũng không quá phổ biến với người dùng Việt\".\r\n\r\nCâu chuyện của Sunhouse được đưa ra làm ví dụ sinh động cho làn sóng xuất khẩu mới của doanh nghiệp Việt. Theo ông Jim Yang, Giám đốc phát triển thị trường Amazon Global Selling châu Á - Thái Bình Dương, \"Việt Nam là cái tên gây chú ý trong xuất khẩu thương mại điện tử toàn cầu\".\r\nTừ một quốc gia gia công - sản xuất, Việt Nam dần thành nơi kiến tạo thương hiệu. \"Điều này được thúc đẩy bởi bốn lợi thế chính: tầm nhìn quốc gia rõ ràng và sự hỗ trợ của chính sách, chuyển đổi số được đẩy mạnh, vị trí chiến lược và kết nối thương mại, tinh thần doanh nghiệp sôi động\", chuyên gia đến từ Amazon nhận định. Ông cho rằng \"Việt Nam đang ở giai đoạn vàng\" nhờ sự hỗ trợ của Chính phủ hội tụ cùng làn sóng đổi mới, số hóa và tinh thần kinh doanh mạnh mẽ.\r\n\r\nTheo thống kê của Amazon, tính đến 31/7, lượng sản phẩm do các nhà bán hàng Việt cung cấp trên chợ điện tử này tăng gần 35% so với cùng kỳ năm trước. Số doanh nghiệp đăng ký thương hiệu qua Amazon cũng tăng gần 30%, cho thấy sự chuyển mình của cộng đồng xuất khẩu Việt Nam trên kênh thương mại điện tử xuyên biên giới.'),
(38, 38, 'Sau khi gây bất ngờ với màu cam Cosmic Orange rực rỡ trên iPhone 17 Pro và 17 Pro Max, Apple có thể sẽ tiếp tục xu hướng loại bỏ các màu trung tính truyền thống như đen hay xám để ưu tiên tông màu ấm và đậm cho dòng sản phẩm cao cấp.\r\n\r\nTheo nguồn tin rò rỉ uy tín Instant Digital, ba ứng viên đang bước vào \"chung kết\" là nâu cà phê, tím và đỏ rượu, trước khi hãng chọn ra màu chính thức để đưa vào sản xuất trên iPhone 18 dòng Pro.\r\nTrong đó, nâu cà phê được cho là biến thể của màu vàng sa mạc từng xuất hiện trước đây. Đỏ rượu với sắc đỏ sẫm pha thêm nâu hoặc tím tương tự rượu vang là màu từng được đồn sẽ có mặt trên iPhone 17 Pro. Trong khi đó, màu tím đã xuất hiện trên iPhone 14 Pro, nhưng nếu được chọn sẽ điều chỉnh sắc độ để phù hợp hơn với thiết bị mới.\r\n\r\nInstant Digital được đánh giá tin cậy khi từng đưa ra thông tin chính xác về nút Camera Button trên iPhone 16, màu vàng trên iPhone 14 và mặt lưng kính nhám của iPhone 15. Thông tin rò rỉ phần nào cho thấy chiến lược táo bạo của Apple trong việc làm mới diện mạo dòng Pro.\r\niPhone 18 Pro dự kiến vẫn giữ nguyên thiết kế hiện tại, nhưng phần kính cường lực ở mặt lưng sẽ được làm trong suốt. Máy sẽ có hàng loạt nâng cấp lớn như chip A20 Pro với công nghệ đóng gói CoWoS, modem 5G tự phát triển, camera có khẩu độ biến thiên, Dynamic Island thu nhỏ...\r\n\r\nNăm tới, Apple cũng có thể tung ra mẫu iPhone gập đầu tiên, trong khi iPhone Air và iPhone SE thế hệ mới sẽ ra mắt trễ hơn vào mùa xuân 2027.'),
(39, 39, 'Bảng xếp hạng được thực hiện từ giữa năm và vừa được Startus Insights công bố cho thấy startup của Việt Nam đứng thứ năm trong top 10 về công nghệ thần kinh mới nổi, đồng thời là đại diện Việt Nam duy nhất trong danh sách. Một số tên tuổi được nhắc đến như NeuroX (Anh), NeurTX (Mỹ), NeuFit ANZ (Mỹ), Neurovia Bioelectronics (Thụy Sĩ), Nodas (Nhật Bản) - các công ty chuyên về giải pháp, thiết bị theo dõi yếu tố về thần kinh\r\nStartus Insights của Áo là nền tảng khám phá thông tin chuyên sâu về công ty khởi nghiệp và đổi mới sáng tạo toàn cầu. Họ cho biết đã phân tích dữ liệu từ 7 triệu công ty khởi nghiệp, hơn 20.000 xu hướng công nghệ cùng hơn 150 triệu bằng sáng chế, bài báo và báo cáo thị trường để xây dựng \"bản đồ nhiệt\" về khởi nghiệp để thực hiện bảng xếp hạng.\r\n\r\nTheo nền tảng, các công ty khởi nghiệp về NeuroTech (công nghệ thần kinh) trong năm 2025 đang định nghĩa lại cách con người tương tác với não bộ, thông qua kết hợp khoa học thần kinh với công nghệ mới. \"Những startup này sẽ giải quyết thách thức quan trọng trong giao diện não - máy tính, điều trị sức khỏe tâm thần và chẩn đoán thần kinh. Những tiến bộ của họ cho phép thực hiện mọi thứ, từ kích thích thần kinh không xâm lấn đến đánh giá nhận thức dựa trên AI\", trang này đánh giá..\r\nBrain-Life được thành lập đầu năm 2024 với ba tiến sĩ gồm Vi Chí Thành, Đào Công Nguyên và Trần Văn Xuân, đặt trụ sở tại TP HCM. Trong đó, ông Thành tốt nghiệp tiến sĩ ngành khoa học máy tính tại Đại học Bristol (Anh) năm 2014 và chọn về nước lập nghiệp. Ông đứng sau 45 công trình nghiên cứu khoa học, giành giải Best Paper tại hội nghị quốc tế về tương tác giữa công nghệ với người dùng ACM CHI, hiện là giảng viên tại Đại học Quốc tế - Đại học Quốc gia TP HCM và Đại học Sussex (Anh).\r\n\r\nHồi tháng 7, công ty giới thiệu nguyên mẫu thiết bị đeo Brain-Life Focus+, sử dụng cảm biến thu thập dữ liệu não, AI phân tích và ra lời khuyên theo thời gian thực cho người dùng. Điểm đặc biệt của thiết bị là theo dõi không xâm lấn, sử dụng hệ thống cảm biến điện não đồ (EEG), cảm biến quang học đo lưu lượng máu não (fNIRS) và cảm biến đo nhịp tim (PPG) để ghi nhận dữ liệu về sóng điện não, lưu lượng máu não và nhịp tim. Ba chỉ số này phản ánh trạng thái tập trung, mệt mỏi, stress hay kiệt sức, sau đó đưa ra các cảnh báo chuyên sâu cho người dùng.'),
(40, 40, 'Cùng với thị trường toàn cầu, EOS R6 Mark III ra mắt tại Việt Nam ngày 6/11. So với \"đàn anh\" R6 Mark II, máy không thay đổi nhiều về ngoại hình, vẫn sử dụng hợp kim magie và chất liệu vỏ có khả năng chống chịu thời tiết. Sản phẩm có kích thước 138,4 x 98,4 x 88,4 mm, trọng lượng 699 gram khi gắn thẻ và pin, cao hơn mức 670 gram trên R6 Mark II. Cảm giác cầm nắm thực tế khá chắc chắn, dễ thao tác.\r\nHệ thống điều khiển trên máy không có nhiều thay đổi, khi vẫn có mặt của vòng xoay tùy chọn chế độ, phím thao tác quay phim nhanh hay phím tắt/bật máy. Canon không trang bị màn hình phụ hiển thị thông số phía trên cho sản phẩm như trên các mẫu tầm trung và thấp của hãng.\r\nMặt sau cũng không có nhiều thay đổi, khi vẫn có mặt của các phím chức năng cùng \"bánh xe\" chọn tính năng hoặc duyệt ảnh nhanh. Số lượng phím không nhiều, khá dễ làm quen và thao tác. Màn hình Live View có thể gập vào thân máy hoặc xoay theo nhiều hướng, gồm cả hướng ra phía trước phục vụ selfie hoặc quay video.\r\nThay đổi vật lý đáng kể trên R6 Mark III là việc loại bỏ cổng micro-HDMI (Type D) và thay bằng cổng HDMI đầy đủ. Điều này được đánh giá quan trọng, khi cổng micro-HDMI khá \"mỏng manh\", kết nối không ổn định và dễ bị hỏng. Việc chuyển sang cổng HDMI tiêu chuẩn giúp kết nối an toàn và bền bỉ với các màn hình ngoài và đầu ghi.\r\n\r\nMột điểm mới khác là khe cắm thẻ nhớ. R6 Mark II dùng hai khe cắm SD, nhưng R6 Mark III loại bỏ một khe và thay bằng CFexpress Type B, nhằm giúp máy xử lý băng thông video 7K RAW chụp tốc độ cao 40 fps (khung hình/giây). Trong khi đó, khe SD UHS-II thứ hai cung cấp tùy chọn sao lưu, ghi tràn hoặc sử dụng thẻ SD cho việc chụp ảnh thông thường, quay video ở định dạng nén. Dù vậy, việc trang bị CFexpress Type B cũng buộc người dùng phải chi thêm tiền để mua loại thẻ này nếu muốn khai thác hết khả năng của máy, khiến chi phí tổng thể bị \"đội\" lên. Tại Việt Nam, các mẫu CFexpress Type B hiện có giá trên ba triệu đồng cho dung lượng từ 128 GB.\r\nCanon trang bị cho R6 Mark III màn hình cảm ứng 3 inch 1,62 triệu điểm xoay lật hoàn toàn cùng ống ngắm điện tử EVF 3,69 triệu điểm ảnh tương tự R6 Mark II, thấp hơn đối thủ ngang tầm là Nikon Z6 III với EVF 5,76 triệu điểm và màn hình LCD 3,2 inch 2,1 triệu điểm. Thực tế cho thấy, khả năng hiển thị trên màn hình LCD không quá nổi bật.\r\nSo với \"đàn anh\" EOS R6 Mark II, máy ảnh mới được nâng cấp cảm biến CMOS full-frame 32,5 megapixel thay vì 24 megapixel. Độ phân giải tăng giúp người dùng ghi lại chi tiết hình ảnh sắc nét hơn, cũng như hậu kỳ tốt hơn.\r\n\r\nMáy cũng trang bị hệ thống lấy nét tự động Dual Pixel CMOS AF \"vay mượn\" từ Canon EOS R5 II và thậm chí cả mẫu máy ảnh flagship EOS R1, giúp nhận diện và \"bám nét\" chủ thể chính xác hơn, mở rộng khả năng nhận diện ngoài con người, như động vật hay phương tiện. Máy cho khả năng chống rung thân IBIS 8,5 stop khu vực trung tâm và 7,5 stop khu vực ngoại vi.\r\n\r\nCanon EOS R6 III có thể chụp ảnh RAW và JPEG độ phân giải đầy đủ bằng EVF với 40 fps khi tự động lấy nét hoàn toàn, ngang mẫu cao cấp EOS R1 và nhanh hơn 10 fps so với Canon EOS R5 II 45 megapixel. Khi sử dụng màn trập cơ, máy vẫn chụp rất nhanh với tốc độ 12 fps, tương đương với flagship EOS R1.'),
(41, 41, 'Hai công ty EBC Đồng Nai và VB Group đều liên quan 4 loại mỹ phẩm mà ca sĩ Đoàn Di Băng quảng cáo, đang bị thu hồi. Trong đó Công ty TNHH thương mại dịch vụ VB Group (TP HCM) do ông Nguyễn Quốc Vũ - chồng Đoàn Di Băng - đứng tên pháp nhân, phân phối sản phẩm, còn EBC Đồng Nai chịu trách nhiệm sản xuất.\r\n\r\nTheo Cục Quản lý Dược, công ty EBC Đồng Nai đã không tuân thủ các nguyên tắc, tiêu chuẩn \"Thực hành tốt sản xuất mỹ phẩm\" (CGMP) của Hiệp hội các nước Đông Nam Á. CGMP (Cosmetic Good Manufacturing Practices) là chứng chỉ thực hành tốt sản xuất mỹ phẩm bao gồm những nguyên tắc chung, quy định, hướng dẫn các nội dung cơ bản về điều kiện, quy trình sản xuất nhằm đảm bảo sản phẩm đạt chất lượng và an toàn cho người dùng. Vì vậy, cơ quan này thu hồi giấy chứng nhận thực hành tốt của EBC Đồng Nai.\r\n\r\nCác Sở Y tế TP HCM, Đồng Nai sẽ quản lý, giám sát mỹ phẩm được sản xuất trước ngày giấy chứng nhận bị thu hồi, đồng thời chuyển hồ sơ sai phạm của hai công ty trên đến cơ quan công an để điều tra.\r\n\r\nCơ quan chức năng cũng ngừng tiếp nhận hồ sơ công bố mỹ phẩm của công ty VB Group trong thời gian 6 tháng, kể từ ngày 27/5. Những hồ sơ nộp trước ngày 27/5 không còn giá trị. Hết thời hạn trên, công ty muốn công bố sản phẩm phải nộp lại hồ sơ theo quy định.');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `article_media`
--

CREATE TABLE `article_media` (
  `media_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `media_url` varchar(255) NOT NULL,
  `media_type` enum('image','video') NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `size_class` varchar(20) DEFAULT 'img-medium',
  `align_class` varchar(20) DEFAULT 'img-center'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `article_media`
--

INSERT INTO `article_media` (`media_id`, `article_id`, `media_url`, `media_type`, `caption`, `size_class`, `align_class`) VALUES
(2, 7, 'uploads/img_20250918_184112_89a5d796.png', 'image', NULL, 'img-medium', 'img-center'),
(3, 7, 'uploads/img_20250918_184349_1d5a022b.png', 'image', NULL, 'img-medium', 'img-center'),
(5, 12, 'uploads/img_20250918_201406_80a17e21.png', 'image', 'hehe', 'img-medium', 'img-center'),
(6, 13, 'uploads/img_20251104_124235_28d80c47.png', 'image', 'Kính rayban mới. Nguồn: Google', 'img-medium', 'img-center'),
(7, 8, 'uploads/img_20251104_125357_2181bd91.png', 'image', 'Tổng Thống Zelinsky và Putin', 'img-large', 'img-center'),
(9, 29, 'uploads/img_20251106_000329_14459ffc.jpg', 'image', 'gabagool', 'img-medium', 'img-center'),
(10, 30, 'uploads/img_20251106_013244_6d124bdc.jpg', 'image', 'DOG', 'img-medium', 'img-center'),
(13, 32, 'uploads/img_20251107_051738_3d76174f.webp', 'image', '', 'img-medium', 'img-center'),
(14, 33, 'uploads/img_20251107_052009_3da24f94.webp', 'image', 'Đường sắt bị xói nền, treo ray tại đoạn Phước Lãnh - Vân Canh. Ảnh: VNR', 'img-medium', 'img-center'),
(15, 34, 'uploads/img_20251107_052206_cf2f01fe.webp', 'image', 'Ông Elon Musk tại Washington ngày 20/1. Ảnh: AP', 'img-medium', 'img-center'),
(16, 35, 'uploads/img_20251107_052333_2c04c15d.webp', 'image', 'Ba phi hành gia Wang Jie (trái), Chen Zhongrui (giữa), Chen Dong (phải) sẽ ở lại trạm Thiên Cung lâu hơn dự kiến do sự cố va chạm với mảnh rác vũ trụ. Ảnh: AFP', 'img-medium', 'img-center'),
(17, 36, 'uploads/img_20251107_052440_1320e424.webp', 'image', 'Lực lượng an ninh Taliban ở huyện Spin Boldak, tỉnh Kandahar, Afhganistan, hôm 16/10. Ảnh: AFP', 'img-medium', 'img-center'),
(18, 37, 'uploads/img_20251107_052637_9e0f618a.webp', 'image', '', 'img-medium', 'img-center'),
(19, 38, 'uploads/img_20251107_052755_2ef3d2d9.webp', 'image', 'Ba tùy chọn màu sắc đang được thử nghiệm trên iPhone 18 Pro. Ảnh: Apple Insider.', 'img-medium', 'img-center'),
(20, 39, 'uploads/img_20251107_052916_589badc4.webp', 'image', 'Top 10 startup công nghệ thần kinh được Startus-Insights xếp hạng.', 'img-medium', 'img-center'),
(21, 40, 'uploads/img_20251107_053044_eaf7267c.webp', 'image', '', 'img-medium', 'img-center'),
(22, 41, 'uploads/img_20251107_053303_fd035a30.webp', 'image', 'Vợ chồng ca sĩ Đoàn Di Băng và Nguyễn Quốc Vũ. Ảnh: Nguyên Thảo', 'img-medium', 'img-center');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `article_tags`
--

CREATE TABLE `article_tags` (
  `article_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(6, 'Tin quốc tế', 'Tổng hợp diễn biến nổi bật trên thế giới trong các lĩnh vực như ngoại giao, kinh tế, xung đột, môi trường và văn hóa.'),
(7, 'Thể thao', 'Tin tức các môn thể thao trong và ngoài nước, cập nhật kết quả thi đấu, chuyển nhượng và phân tích chuyên môn.'),
(8, 'Giải trí', 'Thông tin về nghệ sĩ, phim ảnh, âm nhạc, sự kiện giải trí, cùng những xu hướng mới trong showbiz.'),
(9, 'Công nghệ', 'Tin tức công nghệ mới nhất, sản phẩm mới ra mắt, xu hướng kỹ thuật số, AI, phần mềm và phần cứng máy tính.'),
(10, 'Tin trong nước', 'Cập nhật các sự kiện, tình hình kinh tế - xã hội, chính trị, giáo dục và đời sống đang diễn ra tại Việt Nam.');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Bẫy `comments`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_comment` AFTER INSERT ON `comments` FOR EACH ROW BEGIN
    INSERT INTO comment_logs(comment_id, action) VALUES (NEW.comment_id, 'created');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comment_logs`
--

CREATE TABLE `comment_logs` (
  `log_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `action` enum('created','edited','deleted') NOT NULL,
  `log_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `likes`
--

CREATE TABLE `likes` (
  `like_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'admin');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tags`
--

CREATE TABLE `tags` (
  `tag_id` int(11) NOT NULL,
  `tag_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `created_at`, `updated_at`, `role_id`) VALUES
(1, 'bimmer', 'danhtinh240521@gmail.com', '11111111', '2025-09-18 22:52:24', '2025-09-18 23:27:03', 1),
(2, 'demo', 'demo@example.com', 'demo123', '2025-09-18 23:01:42', '2025-09-18 23:01:42', NULL),
(3, 'Danius', 'zyject@gmail.com', '$2y$10$e1Tl8gIL/mNxK.6NHulj8eD73Tvu55zzqhNv0df6GA8/PoAx0bMwK', '2025-09-18 23:50:35', '2025-09-18 23:50:35', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_profiles`
--

CREATE TABLE `user_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `full_name`, `avatar_url`, `bio`) VALUES
(1, 1, 'Danh Bình Tính', NULL, NULL),
(2, 2, 'Demo User', NULL, NULL),
(3, 3, 'Zyject', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `views`
--

CREATE TABLE `views` (
  `view_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `view_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `views`
--

INSERT INTO `views` (`view_id`, `article_id`, `user_id`, `view_time`) VALUES
(103, 33, 1, '2025-11-07 11:20:36');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`article_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `article_contents`
--
ALTER TABLE `article_contents`
  ADD PRIMARY KEY (`content_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Chỉ mục cho bảng `article_media`
--
ALTER TABLE `article_media`
  ADD PRIMARY KEY (`media_id`);

--
-- Chỉ mục cho bảng `article_tags`
--
ALTER TABLE `article_tags`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `comment_logs`
--
ALTER TABLE `comment_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Chỉ mục cho bảng `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`like_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Chỉ mục cho bảng `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `tag_name` (`tag_name`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_roles` (`role_id`);

--
-- Chỉ mục cho bảng `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Chỉ mục cho bảng `views`
--
ALTER TABLE `views`
  ADD PRIMARY KEY (`view_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `articles`
--
ALTER TABLE `articles`
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT cho bảng `article_contents`
--
ALTER TABLE `article_contents`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT cho bảng `article_media`
--
ALTER TABLE `article_media`
  MODIFY `media_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `comment_logs`
--
ALTER TABLE `comment_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `likes`
--
ALTER TABLE `likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `tags`
--
ALTER TABLE `tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `views`
--
ALTER TABLE `views`
  MODIFY `view_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `article_contents`
--
ALTER TABLE `article_contents`
  ADD CONSTRAINT `article_contents_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `article_tags`
--
ALTER TABLE `article_tags`
  ADD CONSTRAINT `article_tags_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `comment_logs`
--
ALTER TABLE `comment_logs`
  ADD CONSTRAINT `comment_logs_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`comment_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Các ràng buộc cho bảng `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `views`
--
ALTER TABLE `views`
  ADD CONSTRAINT `views_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `views_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

DELIMITER $$
--
-- Sự kiện
--
CREATE DEFINER=`root`@`localhost` EVENT `ev_clean_old_data` ON SCHEDULE EVERY 1 DAY STARTS '2025-09-18 15:32:25' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    DELETE FROM views
    WHERE view_time < NOW() - INTERVAL 1 DAY;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
