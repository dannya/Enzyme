<?php

/*-------------------------------------------------------+
 | Enzyme
 | Copyright 2010-2011 Danny Allen <danny@enzyme-project.org>
 | http://www.enzyme-project.org/
 +--------------------------------------------------------+
 | This program is released as free software under the
 | Affero GPL license. You can redistribute it and/or
 | modify it under the terms of this license which you
 | can read by viewing the included agpl.txt or online
 | at www.gnu.org/licenses/agpl.html. Removal of this
 | copyright header is strictly prohibited without
 | written permission from the original author(s).
 +--------------------------------------------------------*/


include($_SERVER['DOCUMENT_ROOT'] . '/autoload.php');


// set url?
if (!empty($_REQUEST['page'])) {
  $url      = Config::getSetting('enzyme', 'HELP_URL') . '/' . $_REQUEST['page'];
  $cacheKey = 'help_' . $_REQUEST['page'];

} else {
  $url      = Config::getSetting('enzyme', 'HELP_URL');
  $cacheKey = 'help_index';
}


// clear cache?
if (!empty($_REQUEST['refresh'])) {
  Cache::deletePartial('help_');
}


// check in cache first
$content = Cache::load($cacheKey);

if (!$content) {
  // get page
  $page = Dom::file_get_html($url);


  // extract content
  $content = $page->find(Config::getSetting('enzyme', 'HELP_CONTAINER'));
  $content = reset($content);
  $content = $content->innertext;

  unset($page);


  // get common path which we will rewrite to a local link
  $commonPath = parse_url(Config::getSetting('enzyme', 'HELP_URL'));
  $commonPath = rtrim($commonPath['path'], '/') . '/';


  // rewrite links
  $pattern = array('<a href="' . $commonPath,
                   'class="external');
  $replace = array('<a class="i" href="' . BASE_URL . '/get/help.php?page=',
                   'target="_blank" class="external');

  $content = str_replace($pattern, $replace, $content);


  // store in cache for 30 minutes
  Cache::save($cacheKey, $content, false, 1800);
}


// output content
echo Ui::drawHtmlPage($content, null, array('/css/common.css'), array(), 'help');

?>