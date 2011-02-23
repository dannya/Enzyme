CREATE TABLE `digest_intro_media` (
  `date` date NOT NULL,
  `number` int(2) NOT NULL,
  `type` enum('image','video') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'image',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `file` text COLLATE utf8_unicode_ci NOT NULL,
  `thumbnail` text COLLATE utf8_unicode_ci,
  `youtube` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `unique` (`date`,`number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci