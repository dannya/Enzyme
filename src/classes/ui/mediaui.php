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


class MediaUi extends BaseUi {
  public $id                    = 'media';

  private $media                = array();
  private $dates                = array();


  public function __construct($user) {
    $this->user               = $user;

    // set title
    $this->title              = _('Media');

    // get available media
    $this->media              = Digest::loadDigestMedia();


    // get possible dates for new media (unpublished, and 4 weeks into future)
    $tmpDigests = Digest::loadDigests('issue', 'latest', null, null, array('published' => 0));
    $tmpDate    = Digest::getLastIssueDate(null, true, true);

    if ($tmpDigests) {
      foreach ($tmpDigests as $digest) {
        $this->dates[$digest['date']] = $digest['date'];
      }
    }

    for ($i = 0; $i < 4; $i++) {
      $tmpDate = date('Y-m-d', strtotime($tmpDate . ' + 1 week'));

      if (!isset($this->dates[$tmpDate])) {
        $this->dates[$tmpDate] = $tmpDate;
      }
    }

    ksort($this->dates);
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'feature-editor')) {
      return $buf;
    }


    // draw
    return $this->drawMedia();
  }


  public function getScript() {
    return array('/js/frame/mediaui.js');
  }


  public function getStyle() {
    return array('/css/frame/mediaui.css');
  }


  private function drawMedia() {
    // title and "add media" button
    $buf   = '<h3>' .
                _('Media') .
             '  <span>
                  <input type="button" value="' . _('Add media') . '" title="' . _('Add media') . '" onclick="addMedia();" />
                </span>
              </h3>';


    // draw new media form
    $buf  .= '<div id="add-media-form" class="clearfix" style="display:none;">
                <form enctype="multipart/form-data" method="post" action="' . BASE_URL . '/get/upload-media.php?upload" target="uploadTarget">
                  <div id="media_new" class="media-item media-item-new">
                    <span id="new-icon" class="image">&nbsp;</span>' .
                    Ui::htmlSelector('new-type', Media::getTypes(), null, "changeNewMediaType(event);") .
                    Ui::htmlSelector('new-date', $this->dates) .

             '      <input id="new-caption" type="text" class="prompt" name="caption" value="' . _('Caption') . '" alt="' . _('Caption') . '" onfocus="inputPrompt(event);" onblur="inputPrompt(event);" />

                    <input id="new-name" style="display:none;" type="text" class="prompt" name="name" value="' . _('Name') . '" alt="' . _('Name') . '" onfocus="inputPrompt(event);" onblur="inputPrompt(event);" />
                    <input id="new-youtube" style="display:none;" type="text" class="media-item-youtube prompt" name="youtube" value="' . _('Youtube') . '" alt="' . _('Youtube') . '" onfocus="inputPrompt(event);" onblur="inputPrompt(event);" />

                    <span class="media-item-file">file</span>
                    <input id="new-file" type="file" name="new-file" size="28" />

                    <iframe id="uploadTarget" name="uploadTarget" src="" style="display:none;"></iframe>

                    <input type="submit" value="' . _('Add') . '" title="' . _('Add') . '" onclick="addMediaForm(event);" />
                  </div>
                </form>
              </div>';


    // draw items
    $buf  .= '<div id="media">';

    foreach ($this->media as $mediaOnDate) {
      $thisDate = reset($mediaOnDate);

      $buf  .= '<div id="media_' . $thisDate['date'] . '" class="media-item-container">
                  <h4>' .
                    sprintf(_('%s (%s)'), Date::get('full', $thisDate['date']), $thisDate['date']) .
               '  </h4>

                  <div>';

      // sort by number
      usort($mediaOnDate, 'MediaUi::sortMediaByNumber');

      foreach ($mediaOnDate as $media) {
        // create file link "breadcrumb"
        $fileLink = Media::makeClickable($media);

        // draw
        $buf  .= Media::drawItem($media, true);
      }

      $buf  .= '  </div>
                </div>';
    }

    $buf  .= '</div>';

    return $buf;
  }


  private static function sortMediaByNumber($a, $b) {
    return ($a['number'] > $b['number']);
  }
}

?>