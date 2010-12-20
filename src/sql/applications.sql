CREATE TABLE `applications` (
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `job` enum('reviewer','classifier','editor','translator') COLLATE utf8_unicode_ci NOT NULL,
  `paths` text COLLATE utf8_unicode_ci,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `unique` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci