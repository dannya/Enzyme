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


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Review');
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
      // get globally-ignored paths
      $ignorePaths  = Enzyme::getIgnoredPaths();

      $buf          = null;
      $counter      = 1;

      foreach ($revisions as $revision) {
        // skip this commit?
        if (empty($revision['msg'])) {
          continue;
        }

        // filter by path?
        if (!empty($revision['basepath'])) {
          // filter by globally-ignored paths
          $pathIgnored = false;

          foreach ($ignorePaths as $ignorePath) {
            if (strpos($revision['basepath'], $ignorePath) !== false) {
              $pathIgnored = true;
              break;
            }
          }

          // filter commits by user review areas?
          if (!empty($this->user->paths)) {
            $pathUserIgnored = true;

            foreach ($this->user->paths as $path) {
              if (strpos($revision['basepath'], $path) !== false) {
                $pathUserIgnored = false;
                break;
              }
            }
          }

          // don't show this commit if in global ignore path, or not in user path
          if ($pathIgnored || (isset($pathUserIgnored) && $pathUserIgnored)) {
            continue;
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