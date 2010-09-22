<?php

/*-------------------------------------------------------+
| Enzyme
| Copyright 2010 Danny Allen <danny@enzyme-project.org>
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


include($_SERVER['DOCUMENT_ROOT'] . '/autoload.inc');


// ensure needed params are set
if (!isset($_REQUEST['page'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// instantiate class
if ($_REQUEST['page'] == 'review') {
  $ui = new ReviewUi();
} else if ($_REQUEST['page'] == 'classify') {
  $ui = new ClassifyUi();

} else {
  exit;
}


// draw page
echo $ui->draw();

?>