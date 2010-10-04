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


// ensure needed params are set
if (empty($_REQUEST['data'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// only allow setup without being an authenticated user
// when there is no data in the settings table (ie. first run)
$existingSettings = Cache::load('settings');
if (!$existingSettings) {
  $existingSettings = Db::load('settings', false);
}

if ($existingSettings) {
  // settings exist, authenticate
  $user = new User();

  if (empty($user->auth)) {
    App::returnHeaderJson(true, array('login' => false));
  }

  // also check that user has admin permissions
  if (!$user->hasPermission('admin')) {
    App::returnHeaderJson(true, array('admin' => false));
  }
}


// extract only valid settings
parse_str($_REQUEST['data'], $data);

$validSettings  = Enzyme::getAvailableSettings();
$settings       = array();

foreach ($validSettings as $theSetting => $null) {
  if (isset($data[$theSetting])) {
    $data[$theSetting] = trim($data[$theSetting]);

    // process values to expected format...
    if (($theSetting == 'DOMAIN') || ($theSetting == 'DISPLAY_URL')) {
      // strip trailing slash
      $data[$theSetting] = rtrim($data[$theSetting], '/');
    }

    if (($theSetting == 'DISPLAY_URL') && (strpos($data[$theSetting], 'http://') === false)) {
      // prepend http://
      $data[$theSetting] = 'http://' . $data[$theSetting];
    } else if (($theSetting == 'DOMAIN') && (strpos($data[$theSetting], 'http://') !== false)) {
      // remove http://
      $data[$theSetting] = str_replace('http://', null, $data[$theSetting]);
    }


    // set setting
    $settings[] = array('setting' => $theSetting,
                        'value'   => $data[$theSetting]);

  } else {
    // still insert blank / not found items as empty setting rows to prevent issues later
    $settings[] = array('setting'     => $theSetting,
                        'value'   => '');
  }
}


if (!$existingSettings) {
  // insert settings into database
  $json['success'] = Db::insert('settings', $settings, true);

} else {
  // rewrite existing settings
  $json['success'] = Db::saveMulti('settings', $settings);
}


// clear settings cache
Cache::delete('settings');


// report success
App::returnHeaderJson();

?>