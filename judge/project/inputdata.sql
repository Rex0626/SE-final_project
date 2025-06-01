-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2024-01-01 20:59:11
-- 伺服器版本： 10.4.27-MariaDB
-- PHP 版本： 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `final_project`
--

-- --------------------------------------------------------

--
-- 資料表結構
--

-- 建立 team 表
CREATE TABLE `team` (
  `Team_ID` VARCHAR(50) PRIMARY KEY,       -- 隊伍編號，主鍵
  `Team_Name` VARCHAR(255) NOT NULL,
  `Team_Password` VARCHAR(255) NOT NULL      -- 隊伍名稱
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 team 資料
INSERT INTO `team` (`Team_ID`, `Team_Name`, `Team_Password`) VALUES
('t11101', '123', 'p123'),
('t11102', '456', 'p456'),
('t11103', '789', 'p789');

-- 建立 student 表
CREATE TABLE `student` (
  `Stu_ID` varchar(10) PRIMARY KEY, 
  `Stu_Name` varchar(512) DEFAULT NULL,
  `Stu_Department_Grade` varchar(512) DEFAULT NULL,
  `Stu_Phone` varchar(512) DEFAULT NULL,
  `Stu_Email` varchar(512) DEFAULT NULL,
  `Team_ID` varchar(50),
  FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 student 資料
INSERT INTO `student` (`Stu_ID`, `Stu_Name`, `Stu_Department_Grade`, `Stu_Phone`, `Stu_Email`, `Team_ID`) VALUES
('s01', 'Lisa', 'csie111', '111', 'e@111', 't11101'),
('s02', 'Jenny', 'csie111', '222', 'ed@222', 't11101'),
('s03', 'Jimmy', 'cme108', '333', 'e@333', 't11103'),
('s04', 'Denny', 'math106', '444', 'e@444', 't11102'),
('s05', 'Ian', 'math111', '555', 'e@555', 't11102'),
('s06', 'Lily', 'math111', '666', 'e@666', 't11103'),
('s07', 'Anna', 'cme108', '777', 'e@777', 't11102'),
('s08', 'Monica', 'csie108', '888', 'e@888', 't11101'),
('s09', 'Dora', 'csie108', '999', 'e@999', 't11103');

-- 建立 teacher 表
CREATE TABLE `teacher` (
  `Teacher_ID` VARCHAR(10) PRIMARY KEY,             -- 教師編號，主鍵
  `Teacher_Name` VARCHAR(255) NOT NULL,            -- 教師姓名
  `Teacher_Department` VARCHAR(255) DEFAULT NULL,  -- 教師單位
  `Teacher_Phone` VARCHAR(20) DEFAULT NULL,        -- 教師手機號碼
  `Teacher_Email` VARCHAR(255) DEFAULT NULL,       -- 教師電子郵件
  `Teacher_Password` VARCHAR(255) NOT NULL,        -- 教師密碼
  `Team_ID` VARCHAR(50),                           -- 隊伍編號
  FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 student 資料
INSERT INTO `teacher` (`Teacher_ID`, `Teacher_Name`, `Teacher_Department`, `Teacher_Phone`, `Teacher_Email`, `Teacher_Password`, `Team_ID`) VALUES
('t01', 'Amy', 'nuk_csie', '0111', 'e@0111', 'p111', 't11101'),
('t02', 'Jakson', 'ntnu_edu', '0222', 'ed@0222', 'p222', 't11102'),
('t03', 'Benson', 'nuk_math', '0333', 'e@0333', 'p333', 't11103');

-- 建立 works 表
CREATE TABLE `works` (
  `Work_ID` varchar(10) PRIMARY KEY,
  `Work_Name` VARCHAR(255) NOT NULL,
  `Work_Description` TEXT,
  `Work_Code` VARCHAR(2048),
  `Work_Video` VARCHAR(2048),
  `Work_Poster` VARCHAR(2048),
  `Team_ID` VARCHAR(50),
  FOREIGN KEY (`Team_ID`) REFERENCES `team` (`Team_ID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 works 資料
INSERT INTO `works` (`Work_ID`, `Work_Name`, `Work_Description`, `Work_Code`, `Work_Video`, `Work_Poster`, `Team_ID`) 
VALUES 
('w11101', 'Monna Lisa', 
 '是一幅文藝復興時期畫家李奧納多·達文西所繪的肖像畫與其代表作。被認為是義大利文藝復興時期的傑作，也被視為世上最有名、訪問量最大且最常被寫、唱、模仿的藝術作品。', 
 'https://zh.wikipedia.org/zh-tw/%E8%92%99%E5%A8%9C%E4%B8%BD%E8%8E%8E', 
 'https://zh.wikipedia.org/wiki/%E5%88%97%E5%A5%A5%E7%BA%B3%E5%A4%9A%C2%B7%E8%BE%BE%C2%B7%E8%8A%AC%E5%A5%87', 
 'C:/xampp/htdocs/project/st/pics/monalisa.jpg', 't11101'),
('w11102', 'Vitruvian Man', 
 '是李奧納多·達·文西在1490 年左右繪製的一幅畫。 受古羅馬建築師維特魯威的著作啟發，這幅畫描繪了一個裸體男子處於兩個重疊的位置，他的胳膊和腿分開以他的足和手指各為端點，並外接圓形和方形。', 
 'https://zh.wikipedia.org/zh-tw/%E7%BB%B4%E7%89%B9%E9%B2%81%E5%A8%81%E4%BA%BA', 
 'https://zh.wikipedia.org/wiki/%E5%88%97%E5%A5%A5%E7%BA%B3%E5%A4%9A%C2%B7%E8%BE%BE%C2%B7%E8%8A%AC%E5%A5%87', 
 'C:/xampp/htdocs/project/st/pics/body.jpg', 't11102'),
('w11103', 'The Last Supper', 
 '是一幅廣為人知的大型壁畫，文藝復興時期由李奧納多·達·文西繪於米蘭天主教恩寵聖母多明我會院的食堂之牆壁上', 
 'https://zh.wikipedia.org/zh-tw/%E6%9C%80%E5%90%8E%E7%9A%84%E6%99%9A%E9%A4%90_(%E8%BE%BE%E8%8A%AC%E5%A5%87)', 
 'https://zh.wikipedia.org/wiki/%E5%88%97%E5%A5%A5%E7%BA%B3%E5%A4%9A%C2%B7%E8%BE%BE%C2%B7%E8%8A%AC%E5%A5%87', 
 'C:/xampp/htdocs/project/st/pics/lastdinner.jpg', 't11103');


 CREATE TABLE `win_works` (
  `wwork_ID` varchar(10) PRIMARY KEY,
  `wwork_Name` VARCHAR(255) NOT NULL,
  `wwork_Year` VARCHAR(10) NOT NULL,
  `wwork_Grade` VARCHAR(10) NOT NULL,
  `wwork_Description` TEXT,
  `wwork_Code` VARCHAR(2048),
  `wwork_Video` VARCHAR(2048),
  `wwork_Poster` VARCHAR(2048)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 win_works 資料
INSERT INTO `win_works` (`wwork_ID`, `wwork_Name`, `wwork_Year`, `wwork_Grade`, `wwork_Description`, `wwork_Code`, `wwork_Video`, `wwork_Poster`) 
VALUES 
('w10701', 'The Starry Night', '2018' ,'First Place',
 '描繪了在法國普羅旺斯地區聖雷米的一家精神病院朝東的窗戶所看到的夜晚景色，此外還包括了一個想像中的村莊。', 
 'https://zh.wikipedia.org/zh-tw/%E6%98%9F%E5%A4%9C', 
 'https://zh.wikipedia.org/wiki/%E6%96%87%E6%A3%AE%E7%89%B9%C2%B7%E6%A2%B5%E9%AB%98', 
 'C:/xampp/htdocs/project/st/pics/sky.jpg'),
('w10804', 'The Scream', '2019' ,'First Place',
 '通常是指挪威畫家愛德華·孟克在1893年的作品，是表現主義繪畫風格的代表作，表達了強烈的「存在性焦慮」', 
 'https://zh.wikipedia.org/zh-tw/%E5%90%B6%E5%96%8A_(%E7%B9%AA%E7%95%AB)', 
 'https://zh.wikipedia.org/wiki/%E7%88%B1%E5%BE%B7%E5%8D%8E%C2%B7%E8%92%99%E5%85%8B', 
 'C:/xampp/htdocs/project/st/pics/scream.jpg'),
 ('w10901', 'Girl with a Pearl Earring', '2020' ,'First Place',
 '十七世紀荷蘭畫家楊·維梅爾的作品。畫作以少女戴著的珍珠耳環作為視角的焦點，現時畫作存放在海牙的莫瑞泰斯住宅中。', 
 'https://zh.wikipedia.org/zh-tw/%E6%88%B4%E7%8F%8D%E7%8F%A0%E8%80%B3%E7%92%B0%E7%9A%84%E5%B0%91%E5%A5%B3', 
 'https://zh.wikipedia.org/wiki/%E6%89%AC%C2%B7%E5%BC%97%E7%BE%8E%E5%B0%94', 
 'C:/xampp/htdocs/project/st/pics/pearl.jpg'),
 ('w11002', 'Sunflowers', '2021' ,'First Place',
 '是荷蘭畫家梵谷繪畫的一系列靜物油畫。當中有2幅繪有15朵向日葵，1幅繪有14朵向日葵，另有兩幅繪有12朵向日葵。', 
 'https://zh.wikipedia.org/zh-tw/%E5%90%91%E6%97%A5%E8%91%B5_(%E6%A2%B5%E9%AB%98)', 
 'https://zh.wikipedia.org/wiki/%E6%96%87%E6%A3%AE%E7%89%B9%C2%B7%E6%A2%B5%E9%AB%98', 
 'C:/xampp/htdocs/project/st/pics/sunflower.jpg');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
