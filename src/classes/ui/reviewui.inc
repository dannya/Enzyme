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


class ReviewUi extends BaseUi {
  public $id      = 'review';

  private $user   = null;


  public function __construct() {
    $this->title = _('Review');

    // load user (so we can check permissions and paths)
    $this->user = new User();
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'reviewer')) {
      return $buf;
    }

    // get revision data
    $revisions = Enzyme::getProcessedRevisions('unreviewed', null, null, true, ' ORDER BY revision LIMIT 100');

    // get author data
    $authors = Enzyme::getAuthors($revisions);

    // display revisions
    if (!$revisions) {
      // no 'marked' revisions
      $buf = '<p class="prompt">' .
                _('No revisions available') .
             '</p>';

    } else {
      $buf      = null;
      $counter  = 1;

      foreach ($revisions as $revision) {
        // skip this commit?
        if (empty($revision['msg'])) {
          continue;
        }

        // filter by path?
        if (!empty($revision['basepath'])) {
          if (!empty($this->user->paths)) {
            // filter commits by user review areas
            $pathFound = false;

            foreach ($this->user->paths as $path) {
              if (strpos($revision['basepath'], $path) !== false) {
                $pathFound = true;
                break;
              }
            }

            if (!$pathFound) {
              // don't show this commit if not in user path
              continue;
            }
          }
        }

        $key = 'commit-item-' . $counter++;
        $buf .= Ui::displayRevision('review', $key, $revision, $authors);
      }
    }

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/reviewui.js');
  }


  public function getStyle() {
    return array('/css/reviewui.css');
  }


  public function drawFooter() {
    // draw status/action area
    $buf = Ui::statusArea($this->id);

    return $buf;
  }
}

?>