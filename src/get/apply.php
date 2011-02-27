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
if (empty($_REQUEST['data'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// extract only valid fields
parse_str($_REQUEST['data'], $data);

$validFields  = array('apply-job', 'apply-paths', 'apply-firstname', 'apply-lastname', 'apply-email', 'apply-message');
$fields       = array();

foreach ($validFields as $theField) {
  if (!empty($data[$theField])) {
    $fields[str_replace('apply-', null, $theField)] = $data[$theField];
  }
}


// insert application into database
$json['success'] = Db::insert('applications', $fields, false, false, true, true);


// send email to Enzyme-instance admin on successful application
if ($json['success']) {
  // define email message
  $to       = array('name'    => _('Enzyme administrator'),
                    'address' => ADMIN_EMAIL);

  $message  = sprintf(_('A new application for %s has been made by %s (%s).'), $fields['job'], $fields['firstname'] . ' ' . $fields['lastname'], $fields['email']) . "\n";
              sprintf(_('Login at %s to decline or approve this application.'), ENZYME_URL);

  // send email
  $email    = new Email($to, sprintf('New Application at %s', PROJECT_NAME), $message);
  $email->send();
}


// report success
App::returnHeaderJson();

?>