CREATE TABLE `commit_path_filters` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `target` enum('path','repository') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'path',
  `matched` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `area` int(2) unsigned NOT NULL,
  UNIQUE KEY `unique2` (`id`),
  UNIQUE KEY `unique` (`target`,`matched`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci