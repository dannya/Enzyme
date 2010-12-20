CREATE TABLE `digest_stats_i18n` (
  `date` date NOT NULL,
  `identifier` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `value` float NOT NULL,
  UNIQUE KEY `unique` (`date`,`identifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci