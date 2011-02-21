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
if (!isset($_REQUEST['date']) || !isset($_REQUEST['number'])) {
  App::returnHeaderJson(true, array('missing' => true));
}



// load media
$filter = array('date'    => $_REQUEST['date'],
                'number'  => $_REQUEST['number']);

$media  = Db::load('digest_intro_media', $filter, 1);


// report success
App::returnHeaderJson(false, array('success' => true));


// draw media preview HTML
$buf = Media::draw($media);

if (!empty($_REQUEST['mode']) && ($_REQUEST['mode'] == 'lightbox')) {
  echo '<div class="preview-container">' .
          $buf .
       '</div>';

} else {
  echo $buf;
}

?>