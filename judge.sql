-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-06-01 15:09:25
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
-- 資料庫： `judge`
--

-- --------------------------------------------------------

--
-- 資料表結構 `administer`
--

CREATE TABLE `administer` (
  `aid` int(5) NOT NULL,
  `aname` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `administer`
--

INSERT INTO `administer` (`aid`, `aname`) VALUES
(1, 5);

-- --------------------------------------------------------

--
-- 資料表結構 `judge_data`
--

CREATE TABLE `judge_data` (
  `jid` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jname` varchar(10) NOT NULL,
  `jtitle` varchar(10) NOT NULL,
  `jphone` varchar(10) NOT NULL,
  `jemail` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `judge_data`
--

INSERT INTO `judge_data` (`jid`, `jname`, `jtitle`, `jphone`, `jemail`) VALUES
('j1', 'lll', 'teacher', '0900000012', '123@gmail.com'),
('j2', 'emily', 'teacher', '0911111111', '123@gmail.com'),
('j3', 'kelly', 'professor', '0912345678', '123@gmail.com'),
('j4', 'lisa', 'teacher', '0912345678', '112@gmail.com');

-- --------------------------------------------------------

--
-- 資料表結構 `post`
--

CREATE TABLE `post` (
  `postdate` varchar(100) NOT NULL,
  `word` varchar(100) NOT NULL,
  `paper` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `post`
--

INSERT INTO `post` (`postdate`, `word`, `paper`) VALUES
('2025/1/5', '下一個夢想實踐家就是你!\r\n\r\n跨出創新道路，釋放創意潛能，點燃創業夢想，\r\n\r\n透過業師助陣的系列工作坊，你將學會如何將創意轉化為可行計劃，\r\n\r\n並在競賽中驗證你的實力，成為未來市場上不可或缺的創', 'https://localhost/pics/paper1.jpg'),
('2025-01-06 06:15:43', '123', 'https://localhost/pics/sky.jpg'),
('2025-01-06 07:56:29', 'ssss', 'https://localhost/pics/self.jpg');

-- --------------------------------------------------------

--
-- 資料表結構 `scores`
--

CREATE TABLE `scores` (
  `score` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jid` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Team_ID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `scores`
--

INSERT INTO `scores` (`score`, `comment`, `jid`, `Team_ID`) VALUES
('78', 'good', 'j1', 't11101'),
('89', 'good', 'j1', 't11102'),
('90', 'good', 'j1', 't11103'),
('45', '123', 'j2', 't11101'),
('56', '123', 'j2', 't11102'),
('67', '123', 'j2', 't11103'),
('78', '123', 'j2', 't11104');

-- --------------------------------------------------------

--
-- 資料表結構 `student`
--

CREATE TABLE `student` (
  `Stu_ID` varchar(10) NOT NULL,
  `Stu_Name` varchar(512) DEFAULT NULL,
  `Stu_Department_Grade` varchar(512) DEFAULT NULL,
  `Stu_Phone` varchar(512) DEFAULT NULL,
  `Stu_Email` varchar(512) DEFAULT NULL,
  `Team_ID` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `student`
--

INSERT INTO `student` (`Stu_ID`, `Stu_Name`, `Stu_Department_Grade`, `Stu_Phone`, `Stu_Email`, `Team_ID`) VALUES
('as', 'qw', 'qw', '0900000000', 'qw@gmail.com', 't11104'),
('qw', 'sds', 'qw', '0900000000', 'qw@gmail.com', 't11104'),
('s01', 'Lisa', 'csie111', '111', 'e@111', 't11101'),
('s02', 'Jenny', 'csie111', '222', 'ed@222', 't11101'),
('s03', 'Jimmy', 'cme108', '333', 'e@333', 't11103'),
('s04', 'Denny', 'math106', '444', 'e@444', 't11102'),
('s05', 'Ian', 'math111', '555', 'e@555', 't11102'),
('s06', 'Lily', 'math111', '666', 'e@666', 't11103'),
('s07', 'Anna', 'cme108', '777', 'e@777', 't11102'),
('s08', 'Monica', 'csie108', '888', 'e@888', 't11101'),
('s09', 'Dora', 'csie108', '999', 'e@999', 't11103'),
('s21', 'toto', 'csie111', '2121', '123@1', 't11104');

-- --------------------------------------------------------

--
-- 資料表結構 `teacher`
--

CREATE TABLE `teacher` (
  `Teacher_ID` varchar(10) NOT NULL,
  `Teacher_Name` varchar(255) NOT NULL,
  `Teacher_Department` varchar(255) DEFAULT NULL,
  `Teacher_Phone` varchar(20) DEFAULT NULL,
  `Teacher_Email` varchar(255) DEFAULT NULL,
  `Teacher_Password` varchar(255) NOT NULL,
  `Team_ID` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `teacher`
--

INSERT INTO `teacher` (`Teacher_ID`, `Teacher_Name`, `Teacher_Department`, `Teacher_Phone`, `Teacher_Email`, `Teacher_Password`, `Team_ID`) VALUES
('t00001', 'Amy', 'nuk_csie', '0111', 'e@0111', 'p111', 't11101'),
('t00002', 'Jakson', 'ntnu_edu', '0222', 'ed@0222', 'p222', 't11102'),
('t00003', 'Benson', 'nuk_math', '0333', 'e@0333', 'p333', 't11103'),
('t00004', 'ddd', 'nuk_csie', '0909099999', '1@hhh', '123', 't11104');

-- --------------------------------------------------------

--
-- 資料表結構 `team`
--

CREATE TABLE `team` (
  `Team_ID` varchar(50) NOT NULL,
  `Team_Name` varchar(255) NOT NULL,
  `Team_Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `team`
--

INSERT INTO `team` (`Team_ID`, `Team_Name`, `Team_Password`) VALUES
('t11101', '123', 'p123'),
('t11102', '456', 'p456'),
('t11103', '789', 'p789'),
('t11104', 'qww', '123');

-- --------------------------------------------------------

--
-- 資料表結構 `win_works`
--

CREATE TABLE `win_works` (
  `wwork_ID` varchar(10) NOT NULL,
  `wwork_Name` varchar(255) NOT NULL,
  `wwork_Year` varchar(10) NOT NULL,
  `wwork_Grade` varchar(10) NOT NULL,
  `wwork_Description` text DEFAULT NULL,
  `wwork_Code` varchar(2048) DEFAULT NULL,
  `wwork_Video` varchar(2048) DEFAULT NULL,
  `wwork_Poster` varchar(2048) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `win_works`
--

INSERT INTO `win_works` (`wwork_ID`, `wwork_Name`, `wwork_Year`, `wwork_Grade`, `wwork_Description`, `wwork_Code`, `wwork_Video`, `wwork_Poster`) VALUES
('w10701', 'The Starry Night', '2018', 'First Plac', '描繪了在法國普羅旺斯地區聖雷米的一家精神病院朝東的窗戶所看到的夜晚景色，此外還包括了一個想像中的村莊。', 'https://zh.wikipedia.org/zh-tw/%E6%98%9F%E5%A4%9C', 'https://zh.wikipedia.org/wiki/%E6%96%87%E6%A3%AE%E7%89%B9%C2%B7%E6%A2%B5%E9%AB%98', 'C:/xampp/htdocs/project/st/pics/sky.jpg'),
('w10804', 'The Scream', '2019', 'First Plac', '通常是指挪威畫家愛德華·孟克在1893年的作品，是表現主義繪畫風格的代表作，表達了強烈的「存在性焦慮」', 'https://zh.wikipedia.org/zh-tw/%E5%90%B6%E5%96%8A_(%E7%B9%AA%E7%95%AB)', 'https://zh.wikipedia.org/wiki/%E7%88%B1%E5%BE%B7%E5%8D%8E%C2%B7%E8%92%99%E5%85%8B', 'C:/xampp/htdocs/project/st/pics/scream.jpg'),
('w10901', 'Girl with a Pearl Earring', '2020', 'First Plac', '十七世紀荷蘭畫家楊·維梅爾的作品。畫作以少女戴著的珍珠耳環作為視角的焦點，現時畫作存放在海牙的莫瑞泰斯住宅中。', 'https://zh.wikipedia.org/zh-tw/%E6%88%B4%E7%8F%8D%E7%8F%A0%E8%80%B3%E7%92%B0%E7%9A%84%E5%B0%91%E5%A5%B3', 'https://zh.wikipedia.org/wiki/%E6%89%AC%C2%B7%E5%BC%97%E7%BE%8E%E5%B0%94', 'C:/xampp/htdocs/project/st/pics/pearl.jpg'),
('w11002', 'Sunflowers', '2021', 'First Plac', '是荷蘭畫家梵谷繪畫的一系列靜物油畫。當中有2幅繪有15朵向日葵，1幅繪有14朵向日葵，另有兩幅繪有12朵向日葵。', 'https://zh.wikipedia.org/zh-tw/%E5%90%91%E6%97%A5%E8%91%B5_(%E6%A2%B5%E9%AB%98)', 'https://zh.wikipedia.org/wiki/%E6%96%87%E6%A3%AE%E7%89%B9%C2%B7%E6%A2%B5%E9%AB%98', 'C:/xampp/htdocs/project/st/pics/sunflower.jpg');

-- --------------------------------------------------------

--
-- 資料表結構 `works`
--

CREATE TABLE `works` (
  `Work_ID` varchar(10) NOT NULL,
  `Work_Name` varchar(255) NOT NULL,
  `Work_Description` text DEFAULT NULL,
  `Work_Code` varchar(2048) DEFAULT NULL,
  `Work_Video` varchar(2048) DEFAULT NULL,
  `Work_Poster` varchar(2048) DEFAULT NULL,
  `Team_ID` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `works`
--

INSERT INTO `works` (`Work_ID`, `Work_Name`, `Work_Description`, `Work_Code`, `Work_Video`, `Work_Poster`, `Team_ID`) VALUES
('w11101', 'Monna Lisa', '是一幅文藝復興時期畫家李奧納多·達文西所繪的肖像畫與其代表作。被認為是義大利文藝復興時期的傑作，也被視為世上最有名、訪問量最大且最常被寫、唱、模仿的藝術作品。', 'https://zh.wikipedia.org/zh-tw/%E8%92%99%E5%A8%9C%E4%B8%BD%E8%8E%8E', 'https://zh.wikipedia.org/wiki/%E5%88%97%E5%A5%A5%E7%BA%B3%E5%A4%9A%C2%B7%E8%BE%BE%C2%B7%E8%8A%AC%E5%A5%87', 'http://localhost/pics/monalisa.jpg', 't11101'),
('w11102', 'Vitruvian Man', '是李奧納多·達·文西在1490 年左右繪製的一幅畫。 受古羅馬建築師維特魯威的著作啟發，這幅畫描繪了一個裸體男子處於兩個重疊的位置，他的胳膊和腿分開以他的足和手指各為端點，並外接圓形和方形。', 'https://zh.wikipedia.org/zh-tw/%E7%BB%B4%E7%89%B9%E9%B2%81%E5%A8%81%E4%BA%BA', 'https://zh.wikipedia.org/wiki/%E5%88%97%E5%A5%A5%E7%BA%B3%E5%A4%9A%C2%B7%E8%BE%BE%C2%B7%E8%8A%AC%E5%A5%87', 'http://localhost/pics/body.jpg', 't11102'),
('w11103', 'The Last Supper', '是一幅廣為人知的大型壁畫，文藝復興時期由李奧納多·達·文西繪於米蘭天主教恩寵聖母多明我會院的食堂之牆壁上', 'https://zh.wikipedia.org/zh-tw/%E6%9C%80%E5%90%8E%E7%9A%84%E6%99%9A%E9%A4%90_(%E8%BE%BE%E8%8A%AC%E5%A5%87)', 'https://zh.wikipedia.org/wiki/%E5%88%97%E5%A5%A5%E7%BA%B3%E5%A4%9A%C2%B7%E8%BE%BE%C2%B7%E8%8A%AC%E5%A5%87', 'http://localhost/pics/lastdinner.jpg', 't11103'),
('w11104', 'sky', 'sky111', 'https://github.com/', 'https://www.youtube.com/', 'http://localhost/pics/sky.jpg', 't11104');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `judge_data`
--
ALTER TABLE `judge_data`
  ADD PRIMARY KEY (`jid`);

--
-- 資料表索引 `scores`
--
ALTER TABLE `scores`
  ADD KEY `fk_jid` (`jid`);

--
-- 資料表索引 `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`Stu_ID`),
  ADD KEY `Team_ID` (`Team_ID`);

--
-- 資料表索引 `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`Teacher_ID`),
  ADD KEY `Team_ID` (`Team_ID`);

--
-- 資料表索引 `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`Team_ID`);

--
-- 資料表索引 `win_works`
--
ALTER TABLE `win_works`
  ADD PRIMARY KEY (`wwork_ID`);

--
-- 資料表索引 `works`
--
ALTER TABLE `works`
  ADD PRIMARY KEY (`Work_ID`),
  ADD KEY `Team_ID` (`Team_ID`);

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `fk_jid` FOREIGN KEY (`jid`) REFERENCES `judge_data` (`jid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- 資料表的限制式 `works`
--
ALTER TABLE `works`
  ADD CONSTRAINT `works_ibfk_1` FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
