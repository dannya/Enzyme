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


class ClassifyUi extends BaseUi {
  public $id      = 'classify';

  private $user   = null;


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Classify');
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'classifier')) {
      return $buf;
    }

    // get revision data
    $revisions = Enzyme::getProcessedRevisions('marked');

    // display revisions
    if (!$revisions) {
      // no 'marked' revisions
      $buf = '<p class="prompt">' .
                _('No revisions available') .
             '</p>';

    } else {
      // get author data
      $authors          = Enzyme::getAuthors($revisions);

      // get common area classifications
      $classifications  = Enzyme::getClassifications();

      $buf              = null;
      $counter          = 1;

      foreach ($revisions as $revision) {
        $key = 'commit-item-' . $counter++;
        $buf .= Ui::displayRevision('classify', $key, $revision, $authors, $classifications);
      }
    }

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/classifyui.js');
  }


  public function getStyle() {
    return array('/css/reviewui.css');
  }


  public function drawFooter() {
    // draw status/action area
    $buf = $this->classifyKey() . Ui::statusArea($this->id);

    return $buf;
  }


  private function classifyKey() {
    // get areas and types
    $areas = Enzyme::getAreas();
    $types = Enzyme::getTypes();

    $buf = '<div id="classify-key">
              <ol id="classify-key-areas">';

    foreach ($areas as $area) {
      $buf .= '<li>' . $area . '</li>';
    }

    $buf .=  '</ol>

              <ol id="classify-key-types" style="display:none;">';

    foreach ($types as $type) {
      $buf .= '<li>' . $type . '</li>';
    }

    $buf .=  '</ol>

              <div id="classify-key-selector">
                <input id="classify-key-button-areas" type="button" class="classify-key-button selected" value="' . _('Areas') . '" onclick="changeKey(\'areas\');" />
                <input id="classify-key-button-types" type="button" class="classify-key-button" value="' . _('Types') . '" onclick="changeKey(\'types\');" />
              </div>
            </div>';

    return $buf;
  }
}

?>