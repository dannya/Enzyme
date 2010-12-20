CREATE TABLE `users` (
  `username` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `language` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en_US',
  `permissions` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `paths` text COLLATE utf8_unicode_ci NOT NULL,
  `interface` enum('keyboard','mouse') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'mouse',
  `classify_user_filter` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `reset_ip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reset_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reset_timeout` datetime DEFAULT NULL,
  UNIQUE KEY `unique` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci