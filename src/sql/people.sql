CREATE TABLE `people` (
  `account` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `nickname` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dob` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `continent` varchar(13) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `motivation` tinyint(1) DEFAULT NULL,
  `employer` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `colour` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `unqiue` (`account`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci