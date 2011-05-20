#!/usr/bin/php -q
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


include(dirname(__FILE__) . '/../autoload.php');


// ensure script can only be run from the command-line
if (!COMMAND_LINE) {
  exit;
}


// allow parameters to be passed via command-line
$params = getopt('a:b:');

if (!empty($params['a']) && !empty($params['b'])) {
  $start  = $params['a'];
  $end    = $params['b'];

} else {
  // set dates
  $start  = date('Y-m-d');
  $end    = date('Y-m-d', strtotime('today + 1 day'));
}


// do insert
Enzyme::insertRevisions($start, $end);

?>