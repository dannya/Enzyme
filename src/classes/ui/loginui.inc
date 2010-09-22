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


class LoginUi {
  public $id      = 'login';


  public function __construct() {
    $this->title = _('Login');
  }


  public function draw() {
    $buf = '<form id="authenticate" method="post" action="">
              <div id="login-box" class="r">
                <div class="row">
                  <div class="left">' . _('Username') . '</div>
                  <div class="right">
                    <input id="login-username" type="text" class="ex" name="login-username" />
                  </div>
                </div>
                <div class="row">
                  <div class="left">' . _('Password') . '</div>
                  <div class="right">
                    <input id="login-password" type="password" class="ex" name="login-password" />
                  </div>
                </div>

                <div class="row button">
                  <input type="button" onclick="$(\'authenticate\').submit();" value="' . _('Login') . '" />
                </div>
              </div>
            </form>

            <script type="text/javascript">
              // submit form on enter keypress
              $("authenticate").observe("keydown", function(event) {
                if (event.keyCode === 13) {
                  $("authenticate").submit();
                }
              });

              // focus username box
              if ($("login-username")) {
                $("login-username").focus();
              }
            </script>';

    return $buf;
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array();
  }
}

?>