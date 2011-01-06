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
if (empty($_REQUEST['panel'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  echo _('Must be logged in!');
  exit;
}


// check we have permissions for requested panel
$panels = new Panels($user);


// get panel
$panelContent = $panels->draw($_REQUEST['panel'], false, true);


// return success
if (!empty($panelContent)) {
  App::returnHeaderJson(false, array('success' => true));

  echo $panelContent;

} else {
  // panel content not found
  App::returnHeaderJson(true, array('success' => false));
}


?>