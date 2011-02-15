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


  public function __construct($user) {
    $this->user               = $user;

    // set title
    $this->title              = _('Media');

    // get available media
    $this->media              = Digest::loadDigestMedia();
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
    return array('/css/mediaui.css');
  }


  private function drawMedia() {
    $buf   = '<h3>' .
                _('Media') .
             '  <span>
                  <input type="button" value="' . _('Add media') . '" title="' . _('Add media') . '" onclick="addMedia();" />
                </span>
              </h3>';

    // draw items
    $buf  .= '<div id="media">';

    foreach ($this->media as $mediaOnDate) {
      $thisDate = reset($mediaOnDate);

      $buf  .= '<div id="media_' . $thisDate['date'] . '" class="media-item-container">
                  <h4>' .
                    sprintf(_('%s (%s)'), Date::get('full', $thisDate['date']), $thisDate['date']) .
               '  </h4>

                  <div>';

      foreach ($mediaOnDate as $media) {
        // create file link "breadcrumb"
        $fileLink = Media::makeClickable($media);

        // draw
        $buf  .= '  <div id="media_' . $media['date'] . '_' . $media['number'] . '" class="media-item">
                      <span class="' . $media['type'] . '">&nbsp;</span>
                      <input type="text" class="media-item-number" value="' . $media['number'] . '" name="number" onchange="saveChange(\'' . $media['date'] . '\', ' . $media['number'] . ', event);" />
                      <input type="text" class="media-item-name" value="' . $media['name'] . '" name="name" onchange="saveChange(\'' . $media['date'] . '\', ' . $media['number'] . ', event);" />
                      <input type="text" class="media-item-youtube" value="' . $media['youtube'] . '" name="youtube" onchange="saveChange(\'' . $media['date'] . '\', ' . $media['number'] . ', event);" />
                      <span class="media-item-file">' . $fileLink . '</span>

                      <input id="media_' . $media['date'] . '_' . $media['number'] . '-close-preview" style="display:none;" type="button" value="' . _('Close preview') . '" onclick="previewMedia(\'' . $media['date'] . '\', ' . $media['number'] . ')" />
                      <input id="media_' . $media['date'] . '_' . $media['number'] . '-preview" type="button" value="' . _('Preview') . '" onclick="previewMedia(\'' . $media['date'] . '\', ' . $media['number'] . ')" />
                    </div>';
      }

      $buf  .= '  </div>
                </div>';
    }

    $buf  .= '</div>';

    return $buf;
  }
}

?>