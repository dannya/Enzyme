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
if (!isset($_REQUEST['username']) || !isset($_REQUEST['data']) || !isset($_REQUEST['dataType'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('login' => false));
}


// check permissions
if (!$user->hasPermission('admin')) {
  App::returnHeaderJson(true, array('permission' => false));
}


// process data
if (($_REQUEST['dataType'] == 'new-user') || ($_REQUEST['dataType'] == 'approve-application')) {
  // process data into format for database saving
  parse_str($_REQUEST['data'], $tmpData);

  // extract valid fields
  $validFields = array('username', 'email', 'firstname', 'lastname', 'permission-admin',
                       'permission-editor', 'permission-reviewer', 'permission-classifier',
                       'permission-translator', 'paths');

  foreach ($tmpData as $key => $value) {
    if (in_array($key, $validFields)) {
      if (strpos($key, 'permission-') !== false) {
        // compile permissions
        if ($value == "true") {
          $permissions[] = str_replace('permission-', null, $key);
        }

      } else {
        $data[$key] = $value;
      }
    }
  }

  // generate and set a password?
  if ($_REQUEST['dataType'] == 'approve-application') {
    $tmpPassword      = App::randomString();
    $data['password'] = Db::getHash(trim($tmpPassword));
  }

  // condense permissions into single db field
  $data['permissions'] = implode(', ', $permissions);

  // set account as active
  $data['active'] = true;

  // insert new user record
  $json['success'] = Db::insert('users', $data);

  // remove application (if user creation is successful)?
  if (($_REQUEST['dataType'] == 'approve-application') &&
      $json['success'] && !empty($data['email'])) {

    Db::delete('applications', array('email' => $data['email']));

    // send approve email
    $to       = array('name'    => $data['firstname'] . ' ' . $data['lastname'],
                      'address' => $data['email']);
    $message  = sprintf('%s, your application for %s has been successful!', $data['firstname'], $data['permissions']) . "\n" .
                sprintf('To get started, please go to %s and login with the following details:', 'http://' . DOMAIN . '/') . "\n\n" .
                sprintf('  Username: %s', $data['username']) . "\n" .
                sprintf('  Password: %s', $tmpPassword) . "\n\n" .
                        'Be sure to change your password immediately after logging in by going to "Settings" at the top right.' . "\n" .
                sprintf('If you have any questions, please contact %s', ADMIN_EMAIL) . "\n\n" .
                sprintf('Thanks, the %s team', PROJECT_NAME);

    $email    = new Email($to, sprintf('%s Application Successful', PROJECT_NAME), $message);
    $email->send();
  }

} else if ($_REQUEST['dataType'] == 'decline-application') {
  // remove application
  if (!empty($data['email'])) {
    $json['success'] = Db::delete('applications', array('email' => $data['email']));

    // send decline email
    parse_str($_REQUEST['data'], $tmpData);

    $to       = array('name'    => $tmpData['firstname'] . ' ' . $tmpData['lastname'],
                      'address' => $tmpData['email']);
    $message  = sprintf('%s, your application at %s has been declined.', $tmpData['firstname'], PROJECT_NAME) . "\n" .
                sprintf('If you have any questions, please contact %s', ADMIN_EMAIL) . "\n\n" .
                sprintf('Regards, the %s team', PROJECT_NAME);

    $email    = new Email($to, sprintf('%s Application Declined', PROJECT_NAME), $message);
    $email->send();
  }

} else if (strpos($_REQUEST['dataType'], 'permission-') !== false) {
  // extract permission
  $permission = str_replace('permission-', null, $_REQUEST['dataType']);

  // load target user
  $user->load($_REQUEST['username']);

  // add permission
  $json['success'] = $user->changePermission($permission, $_REQUEST['data']);

} else {
  $allowedTypes = array('active', 'username', 'email', 'firstname', 'lastname', 'paths');

  if (in_array($_REQUEST['dataType'], $allowedTypes)) {
    $filter = array('username' => $_REQUEST['username']);
    $values = array($_REQUEST['dataType'] => $_REQUEST['data']);

    // save data
    $json['success'] = Db::save('users', $filter, $values);

  } else {
    $json['success'] = false;
  }
}


// report success
App::returnHeaderJson();

?>