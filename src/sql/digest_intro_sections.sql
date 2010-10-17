CREATE TABLE IF NOT EXISTS `digest_intro_sections` (
  `date` date NOT NULL,
  `number` int(2) NOT NULL,
  `type` enum('message','comment') COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `intro` text COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `unique` (`date`,`number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;