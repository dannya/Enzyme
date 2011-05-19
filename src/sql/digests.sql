CREATE TABLE `digests` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `type` varchar(7) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'issue',
  `version` int(1) unsigned NOT NULL DEFAULT '2',
  `language` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `synopsis` text COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `comments` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `unique2` (`date`),
  KEY `key` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=152 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci