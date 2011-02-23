CREATE TABLE `repositories` (
  `id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `priority` int(2) unsigned NOT NULL DEFAULT '1',
  `enabled` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
  `type` enum('svn','imap') COLLATE utf8_unicode_ci NOT NULL,
  `hostname` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `port` int(5) unsigned DEFAULT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accounts_file` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `web_viewer` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `unique` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci