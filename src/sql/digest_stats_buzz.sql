CREATE TABLE `digest_stats_buzz` (
  `date` date NOT NULL,
  `type` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `identifier` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value` int(6) unsigned NOT NULL,
  UNIQUE KEY `unique` (`date`,`identifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci