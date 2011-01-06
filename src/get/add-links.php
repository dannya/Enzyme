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


// ensure needed params are set
if (empty($_REQUEST['date']) || empty($_REQUEST['data'])) {
  App::returnHeaderJson(true, array('missing' => true));

} else {
  // define string to be returned
  $json['data'] = $_REQUEST['data'];
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('login' => false));
}


// ensure user has privileges
if (!$user->hasPermission('admin')) {
  App::returnHeaderJson(true, array('permission' => false));
}


// load links (lowercase)
$links = Cache::loadSave('linksLowercase', 'Enzyme::loadLinks', array(true));


// split data into individual words
$words = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', $json['data'], -1, PREG_SPLIT_NO_EMPTY);


// check for links in words
$done = array();

foreach ($words as $word) {
  $comparison = strtolower($word);

  if (!isset($done[$comparison]) && isset($links[$comparison]) && !empty($links[$comparison]['url'])) {
    // word is a link, replace (using preg_replace because we want to limit to first match only)
    $json['data'] = preg_replace('/' . preg_quote($word) . '/',
                                 '<a href="' . $links[$comparison]['url'] . '">' . $links[$comparison]['name'] . '</a>',
                                 $json['data'],
                                 1);

    // ensure we don't replace this word again
    $done[$comparison] = true;
  }
}


// save changes
$table   = 'digests';
$filter  = array('date'      => $_REQUEST['date']);
$data    = array('synopsis'  => $json['data']);

$json['success'] = Db::save($table, $filter, $data);


// report success (and data!)
App::returnHeaderJson();

?>