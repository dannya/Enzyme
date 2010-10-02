CREATE TABLE IF NOT EXISTS `commits_reviewed` (
  `revision` int(8) NOT NULL,
  `marked` tinyint(1) NOT NULL,
  `reviewer` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `reviewed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` int(2) DEFAULT NULL,
  `area` int(2) DEFAULT NULL,
  `classifier` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `classified` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `unique` (`revision`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;