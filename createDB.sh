#!/bin/bash

#Database: `live_demo`
DROP DATABASE IF EXISTS `live_demo`;
CREATE DATABASE IF NOT EXISTS `live_demo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER live_user IDENTIFIED BY 'live_pwd';
GRANT ALL PRIVILEGES ON live_demo.* TO 'live_user'@'%' IDENTIFIED BY 'live_pwd';
USE `live_demo`;

#表的结构 `tb_account`
CREATE TABLE `tb_account` (
  `userid` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `sex` int(11) DEFAULT '-1',
  `avatar` varchar(254) DEFAULT NULL,
  `frontcover` varchar(255) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


#表的结构 `tb_room`
CREATE TABLE `tb_room` (
  `userid` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `frontcover` varchar(255) DEFAULT NULL,
  `location` varchar(128) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


#表的结构 `tb_vod`
CREATE TABLE `tb_vod` (
  `userid` varchar(50) NOT NULL,
  `file_id` varchar(150) NOT NULL,
  `frontcover` varchar(255) DEFAULT NULL,
  `location` varchar(128) DEFAULT NULL,
  `play_url` varchar(255) DEFAULT NULL,
  `like_count` int(11) NOT NULL DEFAULT '0',
  `viewer_count` int(11) NOT NULL DEFAULT '0',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hls_play_url` varchar(255) DEFAULT NULL,
  `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`userid`,`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;