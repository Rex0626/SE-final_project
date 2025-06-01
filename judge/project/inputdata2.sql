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



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
