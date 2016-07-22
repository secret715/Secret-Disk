SET NAMES utf8;

ALTER TABLE `member`
ADD `used_space` bigint NOT NULL AFTER `rekey`,
ADD `file_space` bigint NOT NULL AFTER `used_space`;

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

