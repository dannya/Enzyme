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

  private $jobs   = array();


  public function __construct() {
    $this->title = sprintf(_('Welcome to Enzyme @ %s'), PROJECT_NAME);

    // get list of available jobs
    $this->jobs  = Enzyme::getAvailableJobs();
  }


  public function draw() {
    // prefill username if login attempt was unsuccessful
    if (isset($_REQUEST['login-username'])) {
      $username = $_REQUEST['login-username'];
    } else {
      $username = null;
    }

    $buf   = '<div id="login-column-1">
                <h2>' . _('Login') . '</h2>

                <form id="authenticate" method="post" action="">
                  <div id="login-box" class="r">
                    <div class="row">
                      <div class="left">' . _('Username') . '</div>
                      <div class="right">
                        <input id="login-username" type="text" class="ex" name="login-username" value="' . $username . '" />
                      </div>
                    </div>
                    <div class="row">
                      <div class="left">' . _('Password') . '</div>
                      <div class="right">
                        <input id="login-password" type="password" class="ex" name="login-password" />
                      </div>
                    </div>

                    <div class="row button">
                      <a href="#" onclick="forgotPassword(event);">' . _('Forgot password?') . '</a>
                      <input type="button" onclick="$(\'authenticate\').submit();" value="' . _('Login') . '" />
                    </div>
                  </div>
                </form>
              </div>

              <div id="login-column-2">
                <h2>' . _('Volunteer') . '</h2>
                <div id="jobs">';

    foreach ($this->jobs as $job => $jobData) {
      $buf  .= '<h3>' .
                  $jobData['title'] .
               '  <div>
                    <input type="button" onclick="apply(event, \'' . $job . '\');" value="' . _('Apply!') . '" />
                  </div>
                </h3>
                <p>' .
                  $jobData['description'] .
               '</p>';

      $jobNames[$job] = $jobData['title'];
    }

    $buf  .= '</div>';


    // draw hidden apply form
    $jobSelector = Ui::htmlSelector('apply-job', $jobNames, null, 'checkPathsInput(event);');

    $buf  .= '  <div id="apply" style="display:none;">
                  <form id="apply-form" action="">
                    <div class="row">
                      <div class="left">' . _('Job') . '</div>
                      <div class="right">' .
                        $jobSelector .
               '      </div>
                    </div>
                    <div class="row">
                      <div class="left">' . _('Paths') . '</div>
                      <div class="right">
                        <input id="apply-paths" type="text" disabled="disabled" class="optional prompt" onfocus="inputPrompt(event);" onblur="inputPrompt(event);" value="' . sprintf(_('Optional. (e.g. %s)'), '/koffice/, /kdebase/') . '" />
                      </div>
                    </div>
                    <div class="row">
                      <div class="left">' . _('First name') . '</div>
                      <div class="right">
                        <input id="apply-firstname" type="text" />
                      </div>
                    </div>
                    <div class="row">
                      <div class="left">' . _('Last name') . '</div>
                      <div class="right">
                        <input id="apply-lastname" type="text" />
                      </div>
                    </div>
                    <div class="row">
                      <div class="left">' . _('Email') . '</div>
                      <div class="right">
                        <input id="apply-email" type="text" />
                      </div>
                    </div>
                    <div class="row tall">
                      <div class="left">' . _('Message') . '</div>
                      <div class="right">
                        <textarea id="apply-message"></textarea>
                      </div>
                    </div>

                    <div class="row button">
                      <a href="#" class="n" onclick="cancelApply(event);">' . _('Cancel') . '</a>
                      <input type="button" onclick="submitApply(event);" value="' . _('Apply!') . '" />
                    </div>
                  </form>
                </div>

                <div id="success-message" style="display:none;">' .
                  _('Thanks for your application!') . '<br /><br />' .
                  _('The administrator will email you at the address provided when your application has been reviewed.') .
               '</div>
              </div>

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