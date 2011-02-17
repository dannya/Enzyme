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


if (isset($_REQUEST['upload'])) {
//[upload] =>
//[new-type] => image
//[new-date] => 2011-01-23
//[caption] => k
//[name] => Name
//[youtube] => Youtube

  // process uploaded playlist
  $upload    = new Media($_REQUEST['new-type']);
  $uploaded  = $upload->put($_REQUEST['new-date']);


  // if successful, insert new media record into db
  if ($uploaded) {
    $table      = 'digest_intro_media';

    // get next available number
    $theNumber  = Db::count($table, array('date' => $_REQUEST['new-date'])) + 1;

    // set record data
    if ($_REQUEST['new-type'] == 'image') {
      $data = array('date'      => $_REQUEST['new-date'],
                    'number'    => $theNumber,
                    'type'      => $_REQUEST['new-type'],
                    'name'      => $_REQUEST['caption'],
                    'file'      => $uploaded['file']);

    } else if ($_REQUEST['new-type'] == 'video') {
      $data = array('date'      => $_REQUEST['new-date'],
                    'number'    => $theNumber,
                    'type'      => $_REQUEST['new-type'],
                    'name'      => $_REQUEST['name'],
                    'file'      => $uploaded['file'],
                    'youtube'   => $_REQUEST['youtube']);
    }

    // do insert
    $success = Db::insert($table, $data);

    // refresh parent page
    echo '<script type="text/javascript">
            top.location = top.location;
          </script>';
  }
}

?>