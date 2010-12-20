CREATE TABLE `digest_stats_developers` (
  `date` date NOT NULL,
  `identifier` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `num_lines` int(6) unsigned NOT NULL,
  `num_commits` int(6) unsigned NOT NULL,
  `num_files` int(6) unsigned NOT NULL,
  UNIQUE KEY `unique` (`date`,`identifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci