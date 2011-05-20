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


// get available features
$availableFeatures = Db::reindex(Digest::loadDigestFeatures(null, 'ready'), 'date', false, false);


$buf = null;


// draw all scheduled
if (isset($availableFeatures[$_REQUEST['date']])) {
  $buf  .= '<h3>' .
              _('Scheduled Feature Articles') .
           '</h3>

            <div id="features">';

  foreach ($availableFeatures[$_REQUEST['date']] as $feature) {
    $buf  .= drawFeature($feature);
  }

  $buf  .= '</div>';

  // remove drawn features so we don't show them again
  unset($availableFeatures[$_REQUEST['date']]);
}


// draw all other articles?
if ($availableFeatures) {
  $availableFeatures = flattenArray($availableFeatures);

  $buf  .= '<h3>' .
              _('Other Feature Articles') .
           '</h3>

            <div id="features">';

  foreach ($availableFeatures as $feature) {
    $buf  .= drawFeature($feature);
  }

  $buf  .= '</div>';
}


// output
if ($buf) {
  echo $buf;

} else {
  // none found, show prompt
  echo '<p class="prompt-compact">' .
          _('No features ready for selection') .
       '</p>';
}



// utility functions
function flattenArray($array) {
  $newArray = array();

  foreach ($array as $items) {
    $newArray = array_merge($newArray, $items);
  }

  return $newArray;
}


function drawFeature($feature) {
  $buf = '<div id="feature_' . $feature['date'] . '_' . $feature['number'] . '" class="feature">
            <div class="feature-expand" onclick="expandFeature(\'' . $feature['date'] . '\', ' . $feature['number'] . ');" title="' . _('Expand') . '">
              &nbsp;
            </div>

            <div class="feature-intro">' .
              $feature['intro'] .
         '  </div>
            <div class="feature-body">' .
              nl2br($feature['body']) .
         '  </div>

            <input class="feature-insert" type="button" value="' . _('Insert') . '" title="' . _('Insert') . '" onclick="insertFeature(\'' . $feature['date'] . '\', ' . $feature['number'] . ', \'' . $_REQUEST['date'] . '\');" />
          </div>';

  return $buf;
}


?>