CREATE TABLE `commit_bugs` (
  `revision` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `bug` int(7) unsigned NOT NULL,
  `date` datetime DEFAULT NULL,
  `title` text COLLATE utf8_unicode_ci,
  `product` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `component` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `votes` int(5) unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resolution` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `unique` (`revision`,`bug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci