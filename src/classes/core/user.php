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


class User {
  public $auth        = false;
  public $data        = null;

  public $paths       = null;
  public $permissions = null;

  public $authFail    = null;
  public $fillFail    = null;

  private $userField  = 'login-username';
  private $passField  = 'login-password';


  public function __construct($redirect = true, $username = null, $password = null) {
    if (!isset($_SESSION)) {
      session_start();
    }

    // set values
    if (!$username && !$password) {
      if (isset($_POST[$this->userField]) && isset($_POST[$this->userField])) {
        // trim username, password
        $username = trim($_POST[$this->userField]);
        $password = trim($_POST[$this->passField]);
      }

    } else if (!$password) {
      // set up object so we can do a manual load
      $this->data['username'] = $username;
      return;

    } else {
      // trim input values
      $username = trim($username);
      $password = trim($password);
    }

    // has a logout been explicitly requested?
    if (isset($_REQUEST['logout'])) {
      $this->logout();
    }


    if (!isset($_SESSION[APP_ID . '_user'])) {
      // setup language
      App::setLanguage();

      // login?
      if ($username && $password) {
        // check if username and password are valid
        if ($id = $this->authenticate($username, $password)) {
          // password is correct, store user in session
          $_SESSION[APP_ID . '_user'] = $id;

          if ($redirect) {
            Ui::redirect('/');
          }

        } else {
          // password incorrect
          $this->authFail = true;
        }

      } else {
        // both fields have not been filled
        $this->fillFail = true;
      }

    } else {
      // user has been authenticated, check user type
      $this->load($_SESSION[APP_ID . '_user']);

      // add user to logged in status
      Track::user($this->data['username']);
    }
  }


  public function load($username = null) {
    if (!$username) {
      if (!isset($this->data['username'])) {
        return false;
      }

      $username = $this->data['username'];
    }

    // load user data
    $this->data = Db::load('users', array('username' => $username), 1);

    // stop if no user data found
    if (!$this->data) {
      return false;
    }

    // process arrays stored as strings
    if (!empty($this->data['paths'])) {
      $this->paths       = App::splitCommaList($this->data['paths']);
    }
    if (!empty($this->data['permissions'])) {
      $this->permissions = App::splitCommaList($this->data['permissions']);
    }

    // set if authenticated
    if ($this->data) {
      $this->auth = true;
    } else {
      $this->auth = false;
    }

    // change language?
    if ($this->data['language'] != DEFAULT_LANGUAGE) {
      App::setLanguage($this->data['language']);
    } else {
      App::setLanguage();
    }

    // return successful load
    return true;
  }


  public function save() {
    if (!isset($this->data['username'])) {
      return false;
    }

    // serialise arrays as strings for storage
    if (!empty($this->paths)) {
      $this->data['paths']        = App::combineCommaList($this->paths);
    }
    if (!empty($this->permissions)) {
      $this->data['permissions']  = App::combineCommaList($this->permissions);
    }

    // save changes in database
    return Db::save('users', array('username' => $this->data['username']), $this->data);
  }


  public function authenticate($username, $password) {
    $filter = array('username'  => $username,
                    'password'  => $this->getHash($password));

    $tmp = Db::load('users', $filter, 1);

    // found a valid user row (that is also active)?
    if ($tmp && !empty($tmp['active'])) {
      $this->auth = true;
      $this->data = $tmp;

      return $tmp['username'];

    } else {
      return false;
    }
  }


  public function changePassword($oldPassword, $newPassword, $save = true) {
    if ($this->getHash(trim($oldPassword)) == $this->data['password']) {
      // change password
      $this->data['password'] = $this->getHash(trim($newPassword));

      // save changes?
      if ($save) {
        return $this->save();
      } else {
        return true;
      }
    }

    return false;
  }


  public function hasPermission($permissions) {
    if (!is_array($permissions)) {
      // convert to array for iteration
      $permissions = array($permissions);
    }

    foreach ($permissions as $permission) {
      if (in_array($permission, $this->permissions)) {
        return true;
      }
    }

    return false;
  }


  public function changePermission($permission, $state) {
    // if string given, convert to boolean
    if (is_string($state)) {
      if ($state == 'true') {
        $state = true;
      } else if ($state == 'false') {
        $state = false;
      } else {
        return false;
      }
    }

    // make permission change
    if ($state == true) {
      // add permission
      $this->permissions = App::addToCommaList($this->permissions, $permission);

    } else {
      // remove permission
      $this->permissions = App::removeFromCommaList($this->permissions, $permission);
    }

    // save
    return $this->save();
  }


  public function getHash($string) {
    return Db::getHash($string);
  }


  public function getName() {
    return App::getName($this->data);
  }


  public function getStats() {
    // set week date boundaries
    $start  = date('Y-m-d H:i:s', strtotime('Today - 1 week'));
    $end    = date('Y-m-d H:i:s');


    // get number of reviewed (total)
    $tmp   = Db::sql('SELECT COUNT(revision) AS count FROM commits_reviewed
                      WHERE reviewer = "' . $this->data['username'] . '"', true);

    $stats['reviewed']['total'] = $tmp[0]['count'];

    // get number of reviewed (week)
    $tmp   = Db::sql('SELECT COUNT(revision) AS count FROM commits_reviewed
                      WHERE reviewer = "' . $this->data['username'] . '"
                      AND reviewed > "' . $start . '"
                      AND reviewed <= "' . $end . '"', true);

    $stats['reviewed']['week'] = $tmp[0]['count'];


    // get number of selected
    $tmp   = Db::sql('SELECT COUNT(revision) AS count FROM commits_reviewed
                      WHERE reviewer = "' . $this->data['username'] . '"
                      AND marked = 1', true);

    $stats['selected']['total']         = $tmp[0]['count'];
    $stats['selectedPercent']['total']  = (($stats['selected']['total'] / ($stats['reviewed']['total'] ? $stats['reviewed']['total'] : 1)) * 100);

    // get number of selected (week)
    $tmp   = Db::sql('SELECT COUNT(revision) AS count FROM commits_reviewed
                      WHERE reviewer = "' . $this->data['username'] . '"
                      AND marked = 1
                      AND reviewed > "' . $start . '"
                      AND reviewed <= "' . $end . '"', true);

    $stats['selected']['week']          = $tmp[0]['count'];
    $stats['selectedPercent']['week']   = (($stats['selected']['week'] / ($stats['reviewed']['week'] ? $stats['reviewed']['week'] : 1)) * 100);


    // get number of classified
    $tmp   = Db::sql('SELECT COUNT(revision) AS count FROM commits_reviewed
                      WHERE classifier = "' . $this->data['username'] . '"', true);

    $stats['classified']['total'] = $tmp[0]['count'];

    // get number of reviewed (week)
    $tmp   = Db::sql('SELECT COUNT(revision) AS count FROM commits_reviewed
                      WHERE classifier = "' . $this->data['username'] . '"
                      AND classified > "' . $start . '"
                      AND classified <= "' . $end . '"', true);

    $stats['classified']['week'] = $tmp[0]['count'];


    return $stats;
  }


  public static function logout($redirect = true) {
    // unset variables, destroy session
    unset($_SESSION[APP_ID . '_user']);

    @session_unset();
    @session_destroy();

    // redirect to welcome page?
    if ($redirect) {
      Ui::redirect('/');
    }
  }
}

?>