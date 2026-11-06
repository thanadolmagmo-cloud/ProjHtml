-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2026 at 11:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nongkhai`
--

-- --------------------------------------------------------

--
-- Table structure for table `information`
--

CREATE TABLE `information` (
  `username` varchar(20) NOT NULL,
  `idcard` varchar(13) NOT NULL,
  `membername` varchar(100) NOT NULL,
  `nickname` varchar(50) NOT NULL,
  `sex` enum('male','female') NOT NULL,
  `birthdate` int(2) NOT NULL,
  `birthmonth` int(2) NOT NULL,
  `birthyear` varchar(4) NOT NULL,
  `address` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `fav1` varchar(50) DEFAULT NULL,
  `fav2` varchar(50) DEFAULT NULL,
  `fav3` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `information`
--

INSERT INTO `information` (`username`, `idcard`, `membername`, `nickname`, `sex`, `birthdate`, `birthmonth`, `birthyear`, `address`, `email`, `mobile`, `fav1`, `fav2`, `fav3`) VALUES
('admin', '1439900524297', 'ธนดล', 'แม็ก', 'male', 10, 1, '2555', 'เมือง', 'thanadolmagmo@gmail.com', '0659022369', 'อ่านหนังสือ', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `username` varchar(20) NOT NULL COMMENT 'ชื่อผู้ใช้งาน',
  `password` text NOT NULL COMMENT 'รหัสผ่าน',
  `name` varchar(50) NOT NULL COMMENT 'ชื่อ-นามสกุล',
  `usertype` varchar(20) NOT NULL COMMENT 'ชนิดผู้ใช้งาน'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`username`, `password`, `name`, `usertype`) VALUES
('admin', '098', 'magmo', 'admin'),
('Jing', 'abc', 'Jaji', 'member'),
('member', '789', 'Tongtip', 'member'),
('ฺBubu', '456', 'Baba', 'member');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `information`
--
ALTER TABLE `information`
  ADD PRIMARY KEY (`idcard`),
  ADD UNIQUE KEY `uq_information_username` (`username`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
