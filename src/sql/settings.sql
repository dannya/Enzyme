CREATE TABLE IF NOT EXISTS `settings` (
  `setting` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `unique` (`setting`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;