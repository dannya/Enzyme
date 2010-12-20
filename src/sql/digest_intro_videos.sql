CREATE TABLE `digest_intro_videos` (
  `date` date NOT NULL,
  `number` int(2) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `youtube` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `unique` (`date`,`number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci