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


// manage UI
$ui = new EnzymeUi();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en" lang="en">
  <head id="head">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <?php
      echo $ui->drawTitle();
      echo $ui->drawMeta();
      echo $ui->drawStyle();
      echo $ui->drawScript();
    ?>
  </head>

  <body id="body">
    <?php
      echo $ui->drawHeader();
      echo $ui->drawSidebar();
    ?>
    <div id="content">
      <?php
        echo $ui->frame->draw();
      ?>
    </div>
    <?php
      echo $ui->drawFooter();
    ?>
<?php

// track webstats
echo Webstats::track();

?>
  </body>
</html>