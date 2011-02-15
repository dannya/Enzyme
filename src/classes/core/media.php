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


class Media {
  public static function draw($media) {
    // get filesize and filetype
    $media['type'] = @strtoupper(end(explode('.', $media['file'])));

    if (is_file(BASE_DIR . $media['file'])) {
      $media['size'] = Ui::filesize(filesize(BASE_DIR . $media['file']));
    } else {
      $media['size'] = false;
    }

    // show link?
    if ($media['size']) {
      $link  = '<div class="link">
                  <a href="' . BASE_URL . $media['file'] . '" title="' . strip_tags($string) . '">' .
                    sprintf(_('Download <b>%s</b> video (%s, %s)'), $media['name'], $size, $media['type']) .
               '  </a>
                </div>';
    } else {
      $link  = '<div class="link">' .
                  $media['name'] .
               '</div>';
    }

    // draw
    $buf   = '<div class="video">
                <div class="v">
                  <object height="350" width="425">
                    <param value="http://www.youtube.com/v/' . $media['youtube'] . '" name="movie" />
                    <param name="wmode" value="opaque" />
                    <embed height="350" width="425" type="application/x-shockwave-flash" wmode="opaque" src="http://www.youtube.com/v/' . $media['youtube'] . '"></embed>
                  </object>
                </div>' .
                $link .
             '</div>';

    return $buf;
  }


  public static function makeClickable($media) {
    // create file link "breadcrumb"
    $fileLink   = null;
    $fileParts  = preg_split('/\//', $media['file'], null, PREG_SPLIT_NO_EMPTY);

    foreach ($fileParts as $filePart) {
      $tmp = @end(sscanf($filePart, '%d-%d-%d'));

      if (!empty($tmp)) {
        // date detected
        $fileLink .= '/<a href="#" class="media-item-file-link" onclick="changeMediaDate(\'' . $media['date'] . '\', ' . $media['number'] . ');">' . $filePart . '</a>';

      } else {
        $fileLink .= '/' . $filePart;
      }
    }

    return $fileLink;
  }


  public static function getBasePath($media, $stripLevels = 1) {
    // allow array or direct string to be passed
    if (is_array($media)) {
      $media = $media['file'];
    }

    // create file link "breadcrumb"
    $fileParts  = preg_split('/\//', $media, null, PREG_SPLIT_NO_EMPTY);

    while ($stripLevels-- > 0) {
      array_pop($fileParts);
    }

    return '/' . implode('/', $fileParts) . '/';
  }
}

?>