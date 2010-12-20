CREATE TABLE `digest_stats_extended` (
  `date` date NOT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `identifier` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value` float unsigned NOT NULL,
  UNIQUE KEY `unique` (`date`,`type`,`identifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci