CREATE TABLE `links` (
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `area` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('program','project','other','external') COLLATE utf8_unicode_ci NOT NULL,
  `url` text COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `UNIQUE` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci