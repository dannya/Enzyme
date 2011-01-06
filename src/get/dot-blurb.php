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


// ensure date is provided
if (empty($_REQUEST['date'])) {
  exit;
}


// get dot blurb
$dotBlurb = Enzyme::getDotBlurb($_REQUEST['date']);

if (empty($dotBlurb)) {
  exit;
}


// draw
$buf = '<h3>' .
          _('Dot Synopsis') .
       '</h3>

        <div id="dot-blurb">
          <input type="text" value="' . sprintf(_('KDE Commit-Digest for %s'), date('jS F Y', strtotime($_REQUEST['date']))) . '" />
          <textarea>' . $dotBlurb . '</textarea>
        </div>';


// output
echo $buf;

?>