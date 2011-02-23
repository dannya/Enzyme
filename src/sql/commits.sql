CREATE TABLE `commits` (
  `revision` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `author` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `basepath` text COLLATE utf8_unicode_ci NOT NULL,
  `msg` text COLLATE utf8_unicode_ci NOT NULL,
  `format` enum('svn','git') COLLATE utf8_unicode_ci NOT NULL,
  `repository` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `branch` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `inserted` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique` (`revision`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci