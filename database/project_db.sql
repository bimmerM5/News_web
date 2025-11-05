-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 19, 2025 lúc 07:30 AM
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
(1, 2, 1, 'AI bứt phá 2025', 'Tổng hợp xu hướng AI nổi bật.', 'published', '2025-09-18 23:01:42', '2025-09-18 23:01:42'),
(2, 2, 2, '10 thói quen sống khỏe', 'Những thói quen nhỏ tạo khác biệt lớn.', 'published', '2025-09-18 23:01:42', '2025-09-18 23:01:42'),
(3, 2, 3, 'Chung kết bóng đá quốc gia', 'Không khí cuồng nhiệt trước giờ G.', 'published', '2025-09-18 23:01:42', '2025-09-18 23:01:42'),
(7, 1, 3, 'Barcelona hủy diệt Valencia 6-0 tại La Liga', 'Barcelona có màn trình diễn bùng nổ khi giành chiến thắng 6-0 trước Valencia tại vòng đấu mới nhất của La Liga, với ba cầu thủ cùng lập cú đúp.', 'published', '2025-09-18 23:40:21', '2025-09-18 23:40:47'),
(8, 1, 4, 'Căng thẳng Nga - Ukraine tiếp tục leo thang với giao tranh ác liệt', 'Các cuộc tấn công mới giữa Nga và Ukraine trong tuần qua đã khiến tình hình chiến sự thêm căng thẳng, với nhiều thương vong và hạ tầng bị phá hủy. Cộng đồng quốc tế tiếp tục kêu gọi hai bên đàm phán hòa bình.', 'published', '2025-09-19 00:29:59', '2025-09-19 00:30:04'),
(9, 1, 4, 'Động đất mạnh tại Nepal gây thiệt hại nặng nề', 'Một trận động đất mạnh vừa xảy ra ở miền Tây Nepal, khiến hàng chục người thiệt mạng và nhiều công trình bị sập đổ. Chính quyền và lực lượng cứu hộ đang khẩn trương triển khai công tác cứu nạn.', 'published', '2025-09-19 00:47:50', '2025-09-19 00:55:55'),
(10, 1, 3, 'Manchester City nghiền nát Man United trong derby Manchester (3-0)', 'Manchester United tiếp tục chìm sâu trong khủng hoảng phong độ sau trận thua 0-3 trước Manchester City, với Erling Haaland lập cú đúp. Đây là trận đấu cho thấy những vấn đề nghiêm trọng nơi hàng thủ và khả năng kết liễu trận đấu của Quỷ đỏ.', 'published', '2025-09-19 00:50:07', '2025-09-19 00:55:51'),
(11, 1, 2, 'Tác động lâu dài của COVID-19 lên tim mạch được cảnh báo trong báo cáo mới', 'Một báo cáo mới cho thấy COVID-19 và hậu COVID đang để lại hậu quả nghiêm trọng cho sức khỏe tim mạch của hàng triệu người trên thế giới, khiến nguy cơ bệnh tim mạch gia tăng. Các chuyên gia kêu gọi sàng lọc sớm và biện pháp phòng ngừa hiệu quả.', 'published', '2025-09-19 00:51:41', '2025-09-19 00:55:53'),
(12, 1, 3, 'Liverpool thắng nghẹt thở Atletico Madrid 3-2 nhờ bàn thắng muộn của Van Dijk', 'Liverpool mở màn chiến dịch Champions League đầy kịch tính khi đánh bại Atletico Madrid 3-2 ngay tại Anfield. Hai bàn thắng sớm của Robertson và Salah giúp chủ nhà dẫn trước, Atletico gỡ hòa nhờ đôi pha của Marcos Llorente, nhưng Virgil van Dijk đã lên tiếng bằng pha đánh đầu ở phút bù giờ để mang về ba điểm.', 'published', '2025-09-19 00:54:02', '2025-09-19 00:55:48'),
(13, 1, 1, 'Meta ra mắt kính thông minh “Ray-Ban Display” đánh dấu bước tiến mới trong AR', 'Meta vừa giới thiệu mẫu kính thông minh Ray-Ban Display tích hợp màn hình hiển thị để hỗ trợ thông báo, định vị, xem video và điều khiển bằng giọng nói. Đây là một trong những sản phẩm AR (thực tế tăng cường) được kỳ vọng sẽ giúp đưa kính thông minh từ “công nghệ thí điểm” lên thị trường đại chúng.', 'published', '2025-09-19 00:55:34', '2025-09-19 00:55:46');

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
(1, 1, 'Nội dung: LLM, agent, multimodal, bảo mật, và ứng dụng doanh nghiệp.'),
(2, 2, 'Nội dung: ngủ đủ, uống nước, vận động, thiền, dinh dưỡng cân bằng.'),
(3, 3, 'Nội dung: đội hình dự kiến, phong độ, chiến thuật và dự đoán tỉ số.'),
(7, 7, 'Trên sân nhà, Barcelona đã thể hiện sức mạnh tuyệt đối khi đánh bại Valencia với tỷ số 6-0. Đây là một trong những chiến thắng đậm nhất của đội bóng xứ Catalonia ở mùa giải 2025/26.\r\n\r\nNgay trong hiệp 1, Barca sớm áp đảo thế trận và ghi liên tiếp 3 bàn thắng. Sang hiệp 2, đoàn quân của HLV Xavi tiếp tục chơi thăng hoa và ghi thêm 3 bàn nữa, khép lại trận đấu với tỷ số không tưởng 6-0.\r\n\r\nĐiểm nhấn đặc biệt là việc có tới 3 cầu thủ cùng lập cú đúp, thể hiện sự đa dạng và hiệu quả trên hàng công của Barca. Lối chơi kiểm soát bóng, pressing tầm cao và những pha phối hợp ăn ý giúp Barcelona hoàn toàn áp đảo Valencia.\r\n\r\nChiến thắng này không chỉ mang về 3 điểm quan trọng mà còn nâng cao tinh thần toàn đội, đồng thời gửi thông điệp mạnh mẽ tới các đối thủ cạnh tranh chức vô địch La Liga.'),
(8, 8, 'Theo các nguồn tin quốc tế, giao tranh ác liệt tiếp tục diễn ra tại khu vực miền Đông Ukraine trong những ngày gần đây. Nhiều thành phố quan trọng bị ảnh hưởng bởi pháo kích, gây ra thiệt hại lớn về cơ sở hạ tầng và khiến hàng ngàn người dân phải sơ tán.\r\n\r\nPhía Nga tuyên bố đã đạt được một số tiến triển trong chiến dịch quân sự, trong khi Ukraine khẳng định họ đang phản công mạnh mẽ để giành lại quyền kiểm soát các khu vực trọng yếu. Tình hình chiến sự hiện vẫn chưa có dấu hiệu hạ nhiệt.\r\n\r\nCộng đồng quốc tế, bao gồm Liên Hợp Quốc và Liên minh Châu Âu, tiếp tục kêu gọi hai bên kiềm chế và sớm quay trở lại bàn đàm phán. Các biện pháp trừng phạt kinh tế đối với Nga vẫn được duy trì, trong khi Ukraine nhận thêm viện trợ quân sự và nhân đạo từ các quốc gia phương Tây.'),
(9, 9, 'Theo thông tin từ Cơ quan Khảo sát Địa chất Mỹ (USGS), trận động đất có độ lớn 6,4 đã xảy ra vào rạng sáng nay tại khu vực miền Tây Nepal. Rung chấn mạnh khiến nhiều ngôi nhà, trường học và công trình công cộng bị hư hại nghiêm trọng.\r\n\r\nChính quyền Nepal xác nhận ít nhất hàng chục người đã thiệt mạng và hàng trăm người khác bị thương. Nhiều khu vực bị mất điện và gián đoạn liên lạc. Lực lượng cứu hộ cùng quân đội đã được huy động để tìm kiếm và hỗ trợ những người mắc kẹt trong đống đổ nát.\r\n\r\nCác tổ chức quốc tế và nhiều quốc gia láng giềng, trong đó có Ấn Độ và Trung Quốc, bày tỏ sẵn sàng hỗ trợ Nepal về nhân lực, y tế và cứu trợ khẩn cấp. Hiện công tác cứu hộ vẫn đang được triển khai trong điều kiện thời tiết khắc nghiệt.'),
(10, 10, 'Trong trận derby Manchester tại Premier League ngày chủ nhật vừa qua, Manchester United đã bị Manchester City áp đảo từ đầu đến cuối, để thua 0-3.\r\nErling Haaland một lần nữa khẳng định bản năng sát thủ của mình với cú đúp trong hiệp hai, ở các phút 53 và 68. Phil Foden cũng có tên trên bảng tỷ số sau pha phối hợp đẹp mắt, nâng tỉ số lên 3-0.\r\nMan United chịu nhiều chỉ trích ở khâu phòng ngự: để đối phương dễ dàng áp đảo và tạo ra nhiều tình huống nguy hiểm bên trong vòng cấm.  Bên cạnh đó, những cơ hội có được ở phần sân đối thủ lại không được tận dụng hiệu quả, khiến đội bóng không thể ghi bàn danh dự.'),
(11, 11, 'Theo báo cáo xuất bản gần đây, những người từng nhiễm SARS-CoV-2 có nguy cơ phát triển các vấn đề tim mạch cao hơn so với người chưa nhiễm.\r\nCác triệu chứng có thể bao gồm viêm cơ tim, rối loạn nhịp tim, suy tim nhẹ, hoặc các dấu hiệu tổn thương mạch máu. Một trong những nguyên nhân được đề cập là phản ứng viêm kéo dài do virus để lại, cũng như các tổn thương mạch nhỏ (endothelial) do thiếu oxy hoặc do hệ miễn dịch hoạt động mạnh.\r\nBên cạnh đó, chính quyền và các tổ chức y tế quốc tế khuyến nghị:\r\n\r\nCác bệnh nhân hậu COVID nên được theo dõi sức khỏe tim mạch sau khoảng từ vài tuần đến vài tháng kể từ khi khỏi bệnh, đặc biệt nếu có triệu chứng như đau ngực, khó thở, mệt mỏi bất thường.\r\n\r\nTiêm chủng phòng COVID tiếp tục là biện pháp quan trọng để giảm mức độ nghiêm trọng của bệnh và nguy cơ hậu COVID.\r\n\r\nThay đổi lối sống: dinh dưỡng cân bằng, hoạt động thể chất, kiểm soát huyết áp và mỡ máu.'),
(12, 12, 'Liverpool khởi đầu trận đấu với phong độ đầy tự tin tại Anfield, khi Andy Robertson mở tỉ số ở phút 4 từ pha đá phạt của Mohamed Salah bị chạm chân rồi đổi hướng. Chỉ hai phút sau, Salah nhân đôi cách biệt với một pha phối hợp nhanh và cú dứt điểm lạnh lùng vào góc xa khung thành Atletico.\r\nTuy nhiên, Atletico không để Liverpool dễ dàng áp đặt thế trận. Trước khi hiệp một kết thúc, Marcos Llorente rút ngắn tỉ số với một cú xoạc chân nhẹ nhàng qua chân Ibrahima Konaté và thủ thành Alisson từ đường chuyền bên cánh.\r\nHiệp hai tiếp tục với những pha hãm thành liên tục từ Liverpool, nhưng Atletico đã có bàn gỡ hòa vào khoảng phút 81, tiếp tục là Llorente với một pha volley ngoài vòng cấm, bóng đập người Alexis Mac Allister đổi hướng khiến thủ môn Alisson bó tay.\r\nKhi trận đấu tưởng chừng như sẽ kết thúc với tỉ số hòa, Dominik Szoboszlai treo bóng từ phạt góc ở phút bù giờ (90+2), và Virgil van Dijk đánh đầu chính xác để ấn định chiến thắng nghẹt thở cho Liverpool.\r\nMột chi tiết đáng chú ý: Diego Simeone bị truất quyền chỉ đạo ngay sau bàn thắng cuối cùng sau một tình huống tranh cãi với khán giả.'),
(13, 13, 'Trong một động thái mới nhất trong cuộc đua thiết bị AR/VR, Meta vừa cho ra mắt mẫu kính thông minh Ray-Ban Display với thiết kế thời trang hơn, lấy cảm hứng từ dòng Ray-Ban nổi tiếng. Sản phẩm cho phép người dùng hiển thị các thông báo, bản đồ dẫn đường, xem video ngắn, và điều khiển qua giọng nói, hướng tới việc sử dụng thực tế trong đời sống hàng ngày thay vì chỉ làm công nghệ trình diễn.\r\nMột số đặc điểm nổi bật của Ray-Ban Display:\r\n\r\nThiết kế nhẹ, mẫu kính giống kính thời trang để giảm cảm giác “máy móc”.\r\nHỗ trợ màn hình nhỏ tích hợp bên trong kính, để hiển thị thông tin cơ bản như thông báo, hướng dẫn, video.\r\nCó điều khiển bằng giọng nói, giúp tương tác rảnh tay.\r\nGiá bán khá cao, khoảng $799, và có những hạn chế như thời lượng pin chưa tốt, phụ thuộc vào điện thoại cho một số tính năng.\r\nNgoài ra, sản phẩm này còn được xem là “bước khởi đầu” cho một thế hệ kính thông minh AR/AI có tính ứng dụng cao hơn. Các đối thủ lớn như Google, Apple, Snap cũng được cho là đang phát triển các mẫu tương tự sẽ ra mắt trong khoảng 2025-2026.');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `article_media`
--

CREATE TABLE `article_media` (
  `media_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `media_url` varchar(255) NOT NULL,
  `media_type` enum('image','video') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `article_media`
--

INSERT INTO `article_media` (`media_id`, `article_id`, `media_url`, `media_type`) VALUES
(2, 7, 'uploads/img_20250918_184112_89a5d796.png', 'image'),
(3, 7, 'uploads/img_20250918_184349_1d5a022b.png', 'image'),
(4, 8, 'uploads/img_20250918_192959_cfe1f10b.png', 'image'),
(5, 12, 'uploads/img_20250918_201406_80a17e21.png', 'image');

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
(1, 'Tech', 'Tin công nghệ'),
(2, 'Health', 'Sức khỏe'),
(3, 'Sports', 'Thể thao'),
(4, 'World', 'Tin thế giới');

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
-- Đang đổ dữ liệu cho bảng `comments`
--

INSERT INTO `comments` (`comment_id`, `article_id`, `user_id`, `content`, `created_at`) VALUES
(1, 3, 1, 'hay', '2025-09-18 23:02:29'),
(2, 7, 1, 'tuyệt vời', '2025-09-18 23:44:15');

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

--
-- Đang đổ dữ liệu cho bảng `comment_logs`
--

INSERT INTO `comment_logs` (`log_id`, `comment_id`, `action`, `log_time`) VALUES
(1, 1, 'created', '2025-09-18 23:02:29'),
(2, 2, 'created', '2025-09-18 23:44:15');

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
  ADD PRIMARY KEY (`media_id`),
  ADD KEY `article_id` (`article_id`);

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
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `article_contents`
--
ALTER TABLE `article_contents`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `article_media`
--
ALTER TABLE `article_media`
  MODIFY `media_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `comment_logs`
--
ALTER TABLE `comment_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `view_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Các ràng buộc cho bảng `article_media`
--
ALTER TABLE `article_media`
  ADD CONSTRAINT `article_media_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE;

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
