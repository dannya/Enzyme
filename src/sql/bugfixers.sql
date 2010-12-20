CREATE TABLE `bugfixers` (
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `account` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `unique` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci