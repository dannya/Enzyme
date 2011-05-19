CREATE TABLE `developer_survey` (
  `account` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `section` enum('project','contributor','motivation') COLLATE utf8_unicode_ci NOT NULL,
  `q` int(2) unsigned NOT NULL,
  `string` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `a1` int(1) unsigned NOT NULL,
  `a2` int(1) unsigned DEFAULT NULL,
  `a3` int(1) unsigned DEFAULT NULL,
  `a4` int(1) unsigned DEFAULT NULL,
  `a5` int(1) unsigned DEFAULT NULL,
  UNIQUE KEY `account` (`account`,`section`,`q`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci