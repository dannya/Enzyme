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


class FeaturesUi extends BaseUi {
  public $id                    = 'features';

  private $features             = array();
  private $featureEditors       = array();
  private $availableStatuses    = array();


  public function __construct($user) {
    $this->user               = $user;

    // set title
    $this->title              = _('Features');

    // get available features
    $this->features           = Digest::loadDigestFeatures();

    // get available feature editors
    $this->featureEditors     = Digest::getUsersByPermission('feature-editor');

    // get available statuses
    $this->availableStatuses  = Digest::getStatuses();
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'feature-editor')) {
      return $buf;
    }


    // draw
    $buf = '<h3>' .
              _('Pending Feature Articles') .
           '  <span>
                <input type="button" onclick="createNewFeature();" value="' . _('Create new feature') . '" title="' . _('Create new feature') . '" />
              </span>
            </h3>

            <div id="features">';

    foreach ($this->features as $feature) {
      $buf  .= '<div id="feature-1" class="feature">
                  <div class="feature-info">
                    <span class="feature-editor">' .
                      _('Editor') . ' ' . Ui::htmlSelector('boo', $this->featureEditors, $feature['author'], 'alert(\'boo\')') .
               '    </span>
                  </div>

                  <div class="feature-extra">
                    <span class="feature-status">' .
                      _('Status') . ' ' . Ui::htmlSelector('boo', $this->availableStatuses, $feature['status'], 'alert(\'boo\')') .
               '    </span>
                    <span class="feature-target">' .
                      _('Target') . ' <input type="text" value="' . $feature['date'] . '">
                    </span>
                  </div>

                  <div class="feature-body">
                    <textarea id="intro-1" class="intro-message" rows="1">' . $feature['intro'] . '</textarea>
                    <textarea id="body-1" class="body" rows="8">' . $feature['body'] . '</textarea>
                  </div>
                </div>';
    }

    $buf  .= '</div>';

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/featuresui.js');
  }


  public function getStyle() {
    return array('/css/featuresui.css');
  }
}

?>