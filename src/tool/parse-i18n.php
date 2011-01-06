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


// check authentication
$user = new User();

if (empty($user->auth)) {
  echo _('Must be logged in!');
  exit;
}


set_time_limit(0);
ob_start();


// draw html page start
echo Ui::drawHtmlPageStart(null, array('/css/common.css'), array('/js/prototype.js'));


// get i18n teams page
$page = Dom::file_get_html(I18N_TEAMS);

foreach ($page->find('div#teamlist table.datalist tbody tr') as $row) {
  // skip header
  if (!isset($row->children[0]->children[0])) {
    continue;
  }

  // extract language string
  $code      = explode('=', $row->children[0]->children[0]->attr['href']);
  $language  = explode('(', strip_tags($row->children[0]->innertext));

  $data[] = array('code'     => end($code),
                  'language' => trim(reset($language)));
}


// insert into database
Db::insert('languages', $data, true);


// display summary
echo sprintf(_('Downloaded %d i18n teams'), count($data));


// draw html page end
echo Ui::drawHtmlPageEnd();

?>