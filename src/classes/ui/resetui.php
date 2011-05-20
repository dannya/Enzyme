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


class ResetUi {
  public $id      = 'reset';
  public $title   = null;

  private $code   = null;


  public function __construct() {
    $this->title = _('Reset Password');

    // get code
    if (!empty($_REQUEST['code'])) {
      $this->code = $_REQUEST['code'];
    } else {
      $this->code = null;
    }

    // check if code is valid
    if (empty($this->code) || (strlen($this->code) != 20)) {
      $this->code = false;

    } else {
      $valid = Db::load('users', array('reset_code' => $this->code));

      if (empty($valid['reset_timeout']) || (time() > strtotime($valid['reset_timeout']))) {
        $this->code = false;
      }
    }
  }


  public function draw() {
    // ensure a valid code has been passed
    if (empty($this->code)) {
      return _('Invalid reset code provided');
    }

    $buf   = '<form id="reset" method="post" action="">
                <div id="login-box" class="r">
                  <div class="row">
                    <div class="left">' . _('Username') . '</div>
                    <div class="right">
                      <input id="reset-username" type="text" class="ex" name="reset-username" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="left">' . _('New password') . '</div>
                    <div class="right">
                      <input id="reset-password" type="password" class="ex" name="reset-password" />
                    </div>
                  </div>

                  <div class="row button">
                    <input id="reset-button" type="button" onclick="resetPassword(\'' . $this->code . '\');" value="' . _('Reset password') . '" />
                  </div>
                </div>
              </form>

              <div id="login-footer">
                <a href="http://enzyme-project.org/" target="_blank">&nbsp;</a>
              </div>';

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/loginui.js');
  }


  public function getStyle() {
    return array('/css/loginui.css');
  }
}

?>