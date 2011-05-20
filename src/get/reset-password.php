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
if (!isset($_REQUEST['username'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// load user details (should also change language to user's preference)
$user           = new User(false, $_REQUEST['username']);
$json['valid']  = $user->load();


// check if user found
if (!$json['valid']) {
  App::returnHeaderJson();
}


// determine actions to take
if (!empty($_REQUEST['code']) && !empty($_REQUEST['new_password'])) {
  // check code is valid
  if (($user->data['reset_code'] != $_REQUEST['code']) ||
      (time() > strtotime($user->data['reset_timeout']))) {

    App::returnHeaderJson(true, array('success' => false));
  }

  // change password
  $user->data['password']       = $user->getHash(trim($_REQUEST['new_password']));

  // unset reset details
  $user->data['reset_ip']       = null;
  $user->data['reset_code']     = null;
  $user->data['reset_timeout']  = null;

  // save details
  $json['success'] = $user->save();


} else {
  // generate and store "change password" link
  $user->data['reset_ip']       = $_SERVER['REMOTE_ADDR'];
  $user->data['reset_code']     = App::randomString(20);
  $user->data['reset_timeout']  = Date('Y-m-d H:i:s', strtotime('Now + 6 hours'));

  $user->save();


  // define email message
  $to       = array('name'    => $user->getName(),
                    'address' => $user->data['email']);

  $message  = sprintf(_('%s, someone at the IP address %s has requested a password reset on your account.'), $user->data['firstname'], $user->data['reset_ip']) . "\n\n" .
              sprintf(_('If you have requested the password reset, please go to %s'), BASE_URL . '/reset/' . $user->data['reset_code']) . "\n" .
                      _('This link is valid for 6 hours, and one password change only.') . "\n\n" .
                      _('Be sure to change your password immediately after logging in by going to "Settings" at the top right.') . "\n\n" .
                      _('If you have not requested the password reset, please ignore this email.') . "\n" .
              sprintf(_('If you get any more unrequested reset messages, please contact %s'), ADMIN_EMAIL) . "\n\n" .
              sprintf(_('Thanks, the %s team'), PROJECT_NAME);


  // send email
  $email            = new Email($to, sprintf('%s Reset Password', PROJECT_NAME), $message);
  $json['success']  = $email->send();
}


// report success
App::returnHeaderJson();

?>