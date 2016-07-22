-- Adminer 4.2.4 MySQL dump
SET NAMES utf8;
DROP TABLE IF EXISTS `dir`;
CREATE TABLE `dir` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parent` int(11) NOT NULL,
  `share` tinyint(1) NOT NULL,
  `share_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mktime` datetime NOT NULL,
  `owner` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `file`;
CREATE TABLE `file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `server_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(20) NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dir` int(11) NOT NULL,
  `private_key` varchar(255) CHARACTER SET utf32 COLLATE utf32_unicode_ci NOT NULL,
  `share` tinyint(1) NOT NULL,
  `share_id` varchar(60) CHARACTER SET utf32 COLLATE utf32_unicode_ci NOT NULL,
  `mktime` datetime NOT NULL,
  `owner` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `used_space` bigint(20) NOT NULL,
  `file_space` bigint(20) NOT NULL,
  `level` tinyint(4) NOT NULL,
  `joined` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- 2016-05-27 16:19:08
