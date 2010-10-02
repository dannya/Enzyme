CREATE TABLE IF NOT EXISTS `countries` (
  `code` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `continent` varchar(13) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `unique` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;