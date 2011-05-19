CREATE TABLE `digest_intro_people` (
  `date` date NOT NULL,
  `number` int(2) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `account` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `unique` (`date`,`number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci