-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-12-16 10:55:36
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `library_system`
--

-- --------------------------------------------------------

--
-- 資料表結構 `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL COMMENT '書籍唯一ID',
  `title` varchar(255) NOT NULL COMMENT '書名',
  `author` varchar(255) NOT NULL COMMENT '作者',
  `isbn` varchar(20) NOT NULL COMMENT '國際標準書號 (ISBN)',
  `publisher` varchar(255) DEFAULT NULL COMMENT '出版社',
  `publication_year` year(4) DEFAULT NULL COMMENT '出版年份',
  `status` enum('在庫','已借出') NOT NULL DEFAULT '在庫' COMMENT '書籍狀態 (在庫/已借出)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '建立時間',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '最後更新時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='書籍資料表';

--
-- 傾印資料表的資料 `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `publisher`, `publication_year`, `status`, `created_at`, `updated_at`) VALUES
(1, '哈利波特：神秘的魔法石', 'J.K. 羅琳', '978-986-10-8260-2', '皇冠文化', '1997', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(2, '魔戒：魔戒現身', 'J.R.R. 托爾金', '978-957-33-2895-3', '聯經出版', '1954', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(3, '小王子', '安東尼·聖艾修伯里', '978-957-33-3113-7', '晨星出版', '1943', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(4, '1984', '喬治·歐威爾', '978-957-33-2900-4', '志文出版社', '1949', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:17:32'),
(5, '傲慢與偏見', '珍·奧斯汀', '978-957-33-3120-5', '遠流出版', '0000', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(6, '三體', '劉慈欣', '978-957-33-3130-4', '果麥文化', '2008', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(7, '人類大歷史：從動物到上帝', '尤瓦爾·諾亞·赫拉利', '978-986-359-026-1', '天下文化', '2014', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(8, '原子習慣', '詹姆斯·克利爾', '978-986-175-526-7', '遠見天下', '2018', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(9, '設計的心理學', '唐納德·諾曼', '978-957-13-7313-1', '遠流出版', '1988', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(10, '時間簡史', '史蒂芬·霍金', '978-957-33-2898-4', '大塊文化', '1988', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(11, '槍炮、病菌與鋼鐵', '賈德·戴蒙', '978-957-33-2899-1', '時報文化', '1997', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(12, '窮爸爸富爸爸', '羅伯特·清崎', '978-957-33-2901-1', '高寶書版', '1997', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(13, '影響力', '羅伯特·席爾迪尼', '978-957-33-2902-8', '天下文化', '1984', '在庫', '2025-12-16 09:04:43', '2025-12-16 09:04:43'),
(14, '思考，快與慢', '丹尼爾·康納曼', '978-957-33-2903-5', '天下文化', '2011', '在庫', '2025-12-16 09:04:44', '2025-12-16 09:04:44'),
(15, '異鄉人', '阿爾貝·卡繆', '978-957-33-2904-2', '志文出版社', '1942', '在庫', '2025-12-16 09:04:44', '2025-12-16 09:04:44'),
(16, '百年孤寂', '加布列·賈西亞·馬奎斯', '978-957-33-2905-9', '志文出版社', '1967', '在庫', '2025-12-16 09:04:44', '2025-12-16 09:04:44'),
(17, '追風箏的孩子', '卡勒德·胡賽尼', '978-957-33-2906-6', '木馬文化', '2003', '在庫', '2025-12-16 09:04:44', '2025-12-16 09:04:44'),
(18, '解憂雜貨店', '東野圭吾', '978-957-33-2907-3', '皇冠文化', '2012', '在庫', '2025-12-16 09:04:44', '2025-12-16 09:04:44'),
(19, '房思琪的初戀樂園', '林奕含', '978-986-93582-7-5', '遊牧民', '2017', '在庫', '2025-12-16 09:04:44', '2025-12-16 09:24:08'),
(20, '被討厭的勇氣', '岸見一郎, 古賀史健', '978-957-9164-92-1', '究竟出版', '2013', '在庫', '2025-12-16 09:04:44', '2025-12-16 09:04:44');

-- --------------------------------------------------------

--
-- 資料表結構 `borrowing_records`
--

CREATE TABLE `borrowing_records` (
  `id` int(11) NOT NULL COMMENT '借閱紀錄唯一ID',
  `book_id` int(11) NOT NULL COMMENT '關聯的書籍ID',
  `user_id` int(11) NOT NULL COMMENT '借閱人的使用者ID',
  `borrow_date` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '借出日期',
  `due_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '應歸還日期',
  `return_date` timestamp NULL DEFAULT NULL COMMENT '實際歸還日期 (NULL表示尚未歸還)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='借閱紀錄表';

--
-- 傾印資料表的資料 `borrowing_records`
--

INSERT INTO `borrowing_records` (`id`, `book_id`, `user_id`, `borrow_date`, `due_date`, `return_date`) VALUES
(1, 4, 4, '2025-12-16 09:13:06', '2025-12-30 02:13:06', '2025-12-16 09:17:32'),
(2, 19, 4, '2025-12-16 09:21:31', '2025-12-30 02:21:31', '2025-12-16 09:24:08');

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL COMMENT '使用者唯一ID',
  `username` varchar(50) NOT NULL COMMENT '使用者名稱 (登入用)',
  `email` varchar(100) NOT NULL COMMENT '電子郵件',
  `password` varchar(255) NOT NULL COMMENT '密碼 (需加密儲存)',
  `role` enum('user','admin') NOT NULL DEFAULT 'user' COMMENT '使用者角色 (user/admin)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '註冊時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='使用者帳號表';

--
-- 傾印資料表的資料 `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(3, 'yu', '1091361@gm.hnvs.cy.edu.tw', '$2y$10$rsD63Dwv70lZfQzb6JYo/ejVy70VgU/8y5iXetqmLnISiLXRSLooG', 'admin', '2025-12-16 08:56:18'),
(4, 'wu', '227@nkust.edu.tw', '$2y$10$1HbFIh5KDNoTOFSXeFmzD.xyaSW1Uopwld.snoxBy.1neLEWWsSma', 'user', '2025-12-16 09:04:19');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- 資料表索引 `borrowing_records`
--
ALTER TABLE `borrowing_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `user_id` (`user_id`);

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '書籍唯一ID', AUTO_INCREMENT=21;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `borrowing_records`
--
ALTER TABLE `borrowing_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '借閱紀錄唯一ID', AUTO_INCREMENT=3;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '使用者唯一ID', AUTO_INCREMENT=5;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `borrowing_records`
--
ALTER TABLE `borrowing_records`
  ADD CONSTRAINT `borrowing_records_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrowing_records_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
