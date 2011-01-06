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

$validScript = array('common');


// set language (for strings)
App::setLanguage();


// determine the script file to include
if (isset($_GET['script']) && in_array($_GET['script'], $validScript)) {
  header('Content-type: application/javascript');

  include_once(BASE_DIR . '/js/includes/' . $_GET['script'] . '.php');
}

?>