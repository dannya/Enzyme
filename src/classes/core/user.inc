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
    if (!$username || !$password) {
      if (isset($_POST[$this->userField]) && isset($_POST[$this->userField])) {
        // trim username, password
        $username = strtolower(trim($_POST[$this->userField]));
        $password = strtolower(trim($_POST[$this->passField]));
      }
    }

    // has a logout been explicitly requested?
    if (isset($_REQUEST['logout'])) {
      $this->logout();
    }

    if (!isset($_SESSION[APP_ID . '_user'])) {
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

    // process arrays stored as strings
    if (!empty($this->data['paths'])) {
      $this->paths       = preg_split('/[\s,]+/', $this->data['paths']);
    }
    if (!empty($this->data['permissions'])) {
      $this->permissions = preg_split('/[\s,]+/', $this->data['permissions']);
    }

    if ($this->data) {
      $this->auth = true;
    } else {
      $this->auth = false;
    }
  }


  public function save() {
    if (!isset($this->data['username'])) {
      return false;
    }

    // serialise arrays as strings for storage
    if (!empty($this->paths)) {
      $this->data['paths']        = implode(', ', $this->paths);
    }
    if (!empty($this->permissions)) {
      $this->data['permissions']  = implode(', ', $this->permissions);
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


  public function changePassword($oldPassword, $newPassword) {
    if ($this->getHash(trim($oldPassword)) == $this->data['password']) {
      // change password
      $this->data['password'] = $this->getHash(trim($newPassword));

      // save changes
      return $this->save();
    }

    return false;
  }


  public function hasPermission($permission) {
    return in_array($permission, $this->permissions);
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
    $this->permissions = array_flip($this->permissions);

    if ($state == true) {
      // add permission
      $this->permissions[$permission] = $permission;

    } else {
      // remove permission
      unset($this->permissions[$permission]);
    }

    $this->permissions = array_flip($this->permissions);

    // save
    return $this->save();
  }


  public function getHash($string) {
    return Db::getHash($string);
  }


  public function getDate($type, $date) {
    if ($type == 'full') {
      $formats = array('us' => 'F jS Y',
                       'uk' => 'jS F Y');

    } else if ($type == 'short') {
      $formats = array('us' => 'm/d/Y',
                       'uk' => 'd/m/Y');
    }

    // check if timestamp given
    if (!is_int($date)) {
      $date = strtotime($date);
    }

    if (isset($formats[$this->data['location']])) {
      return date($formats[$this->data['location']], $date);
    } else {
      return date($formats[DEFAULT_LOCATION], $date);
    }
  }


  public static function logout() {
    // unset variables, destroy session
    unset($_SESSION[APP_ID . '_user']);

    session_unset();
    session_destroy();

    // redirect to welcome page
    Ui::redirect('/');
  }
}

?>