CREATE TABLE `digest_intro_sections` (
  `date` date NOT NULL,
  `number` int(2) DEFAULT NULL,
  `type` enum('message','comment') COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('idea','contacting','more-info','proofread','ready','selected') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'idea',
  `author` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `intro` text COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `unique` (`date`,`number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci