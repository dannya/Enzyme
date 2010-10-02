CREATE TABLE IF NOT EXISTS `commits` (
  `revision` int(8) NOT NULL,
  `date` datetime NOT NULL,
  `author` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `basepath` text COLLATE utf8_unicode_ci NOT NULL,
  `msg` text COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `unique` (`revision`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;