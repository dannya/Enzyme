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


class FeaturesUi extends BaseUi {
  public $id                    = 'features';

  private $dates                = array();

  private $features             = array();
  private $featureDates         = array();

  private $featureMedia         = array();

  private $featureEditors       = array();
  private $availableStatuses    = array();


  public function __construct($user) {
    $this->user               = $user;

    // set title
    $this->title              = _('Features');

    // get available features
    $this->ideas              = Digest::loadDigestFeatures(null, 'idea');

    // get available features
    $this->features           = Digest::loadDigestFeatures();
    $this->featureDates       = array_unique(array_keys(Db::reindex($this->features, 'date')));

    // get media for available features
    if ($this->features) {
      $this->featureMedia     = Media::load($this->featureDates, true);
    }

    // get available feature editors
    $this->featureEditors     = Digest::getUsersByPermission('feature-editor');

    // get available statuses
    $this->availableStatuses  = Digest::getStatuses();

    // get possible future dates (4 weeks into future)
    $tmpDate = Digest::getLastIssueDate(null, true, true);

    for ($i = 0; $i < 4; $i++) {
      $tmpDate                = date('Y-m-d', strtotime($tmpDate . ' + 1 week'));
      $this->dates[$tmpDate]  = $tmpDate;
    }
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'feature-editor')) {
      return $buf;
    }


    // draw
    return $this->drawIdeas() .
           $this->drawPending();
  }


  public function getScript() {
    return array('/js/lightwindow.js',
                 '/js/frame/featuresui.js');
  }


  public function getStyle() {
    return array('/css/lightwindow.css',
                 '/css/featuresui.css');
  }


  private function drawIdeas() {
    $buf   = '<h3>' .
                _('Ideas for Feature Articles') .
             '  <span>
                  <input type="button" value="' . _('Create new idea') . '" title="' . _('Create new idea') . '" onclick="createNewIdea();" />
                </span>
              </h3>';

    if (!$this->ideas) {
      // only show header if no items
      $buf  .= '<p id="ideas-prompt" class="prompt-compact">' .
                  _('No items found') .
               '</p>';
    }


    // draw items
    $buf  .= '<div id="ideas">';

    if ($this->ideas) {
      foreach ($this->ideas as $idea) {
        $buf  .= '<div id="idea_' . $idea['number'] . '" class="idea">
                    <div class="idea-expand" onclick="expandIdea(' . $idea['number'] . ');" title="' . _('Expand') . '">
                      &nbsp;
                    </div>

                    <div class="idea-intro">' .
                      $idea['intro'] .
                 '  </div>

                    <div class="idea-buttons">
                      <input class="idea-claim" type="button" value="' . _('Claim') . '" title="' . _('Claim') . '" onclick="claimIdea(' . $idea['number'] . ', \'' . end($this->dates) . '\', \'' . $this->user->data['username'] . '\');" />
                      <input class="idea-delete" type="button" value="' . _('Delete') . '" title="' . _('Delete') . '" onclick="deleteIdea(' . $idea['number'] . ');" />
                    </div>
                  </div>';
      }
    }

    // draw empty row
    $buf  .= '<div id="idea_new" class="idea" style="display:none;">
                <div class="idea-save" onclick="saveIdea();" title="' . _('Save') . '">
                  &nbsp;
                </div>

                <div class="idea-intro">
                  <input id="idea-intro-new" type="text" value="" />
                </div>
              </div>';

    $buf  .= '</div>';

    return $buf;
  }


  private function drawPending() {
    $buf   = '<h3>' .
                _('Pending Feature Articles') .
             '</h3>';

    if (!$this->features) {
      // only show header if no items
      return $buf .
             '<p class="prompt-compact">' .
                _('No items found') .
             '</p>';
    }


    // draw items
    $buf  .= '<div id="features">';

    foreach ($this->features as $feature) {
      // calculate number of rows to use for each box
      $totalRows      = 16;
      $numIntroRows   = ceil(strlen($feature['intro']) / 140) + substr_count($feature['intro'], "\n");
      $numBodyRows    = $totalRows - $numIntroRows;

      $buf  .= '<div id="feature_' . $feature['date'] . '_' . $feature['number'] . '" class="feature clearfix">
                  <div class="feature-info">
                    <span class="feature-editor">' .
                      _('Editor') . ' ' . Ui::htmlSelector('author_' . $feature['date'] . '_' . $feature['number'], $this->featureEditors, $feature['author'], 'changeItem(\'' . $feature['date'] . '\', ' . $feature['number'] . ', \'author\');') .
               '    </span>
                  </div>

                  <div class="feature-extra">
                    <span class="feature-status">' .
                      _('Status') . ' ' . Ui::htmlSelector('status_' . $feature['date'] . '_' . $feature['number'], $this->availableStatuses, $feature['status'], 'changeItem(\'' . $feature['date'] . '\', ' . $feature['number'] . ', \'status\');') .
               '    </span>
                    <span class="feature-target">' .
                      _('Target') . ' ' . Ui::htmlSelector('date_' . $feature['date'] . '_' . $feature['number'], $this->dates, $feature['date'], 'changeItem(\'' . $feature['date'] . '\', ' . $feature['number'] . ', \'date\');') .
               '    </span>
                  </div>

                  <div class="feature-body">
                    <textarea id="intro_' . $feature['date'] . '_' . $feature['number'] . '" class="intro-message" rows="' . $numIntroRows . '">' . $feature['intro'] . '</textarea>
                    <textarea id="body_' . $feature['date'] . '_' . $feature['number'] . '" class="body" rows="' . $numBodyRows . '">' . $feature['body'] . '</textarea>
                  </div>' .

                  $this->drawMedia($feature['date']) .

               '  <div class="feature-save">
                    <input type="button" value="' . _('Save changes') .'" title="' . _('Save changes') .'" onclick="saveChanges(\'' . $feature['date'] . '\', ' . $feature['number'] . ');" />' .
                    Ui::drawIndicator('feature-' . $feature['number']) .
               '  </div>
                </div>';
    }

    $buf  .= '</div>';

    return $buf;
  }


  private function drawMedia($theDate) {
    if (!isset($this->featureMedia[$theDate])) {
      return false;
    }

    // draw
    $buf   = '<div class="feature-media">';

    foreach ($this->featureMedia[$theDate] as $media) {
      $buf  .= Media::drawItem($media, false);
    }

    $buf  .= '</div>';

    return $buf;
  }
}

?>