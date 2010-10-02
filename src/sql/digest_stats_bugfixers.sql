CREATE TABLE IF NOT EXISTS `digest_stats_bugfixers` (
  `date` date NOT NULL,
  `identifier` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `value` int(6) unsigned NOT NULL,
  UNIQUE KEY `unique` (`date`,`identifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;