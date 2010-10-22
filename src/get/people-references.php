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


include($_SERVER['DOCUMENT_ROOT'] . '/autoload.php');


// ensure date is provided


// load people references
$digest = Digest::getPeopleReferences($_REQUEST['date']);


// draw
$buf = '<h3>' .
          _('People References') .
       '  <div>
            <input type="button" title="' . _('Add person') . '" value="' . _('Add person') . '" onclick="addPerson();" />
          </div>
        </h3>

        <table id="people">
          <thead>
            <tr>
              <th>' . _('Number') . '</th>
              <th>' . _('Account') . '</th>
              <th>' . _('Name') . '</th>
            </tr>
          </thead>

          <tbody id="users-body">';

// draw references
foreach ($digest['people'] as $person) {
  $buf  .= drawRow($person);
}

// draw hidden row, to allow creation of new users
$buf  .= drawRow(null);

$buf  .= '  </tbody>
          </table>';


// output
echo $buf;


// utility functions
function drawRow($person = null) {
  if ($person) {
    $rowId    = 'row-' . $person['number'];
    $rowStyle = null;
    $onChange = ' onchange="saveChange(\'' . $person['number'] . '\', event);"';

  } else {
    // draw a blank row
    $rowId             = 'row-new-0';
    $rowStyle          = ' style="display:none;"';
    $onChange          = null;
  }


  // draw row
  $buf =   '<tr id="' . $rowId . '"' . $rowStyle . '>
              <td>
                <input type="text" value="' . $person['number'] . '" name="number"' . $onChange . ' />
              </td>
              <td>
                <input type="text" value="' . $person['account'] . '" name="account"' . $onChange . ' />
              </td>
              <td>
                <input type="text" value="' . $person['name'] . '" name="name"' . $onChange . ' />
              </td>
            </tr>';

  return $buf;
}

?>