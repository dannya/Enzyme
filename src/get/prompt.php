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


if (isset($_REQUEST['prompt']) && ($_REQUEST['prompt'] == 'stats')) {
  $string = _('Statistics for this issue need generating...');
} else {
  $string = _('Select settings and submit to start processing...');
}


$buf = '<p class="prompt">' .
          $string .
       '</p>';


echo Ui::drawHtmlPage($buf, null, array('/css/common.css'));

?>