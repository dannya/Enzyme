CREATE TABLE `commit_files` (
  `revision` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `operation` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `path` text COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `unique` (`revision`,`operation`,`path`(100))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci