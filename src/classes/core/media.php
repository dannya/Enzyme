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
  private $ext        = null;
  private $error      = false;

  private $file       = false;
  private $filename   = false;

  private $allowed    = array('image' => array('png'   => 1,
                                               'gif'   => 1,
                                               'jpg'   => 1),

                              'video' => array('avi'   => 1,
                                               'mpeg'  => 1));


  public function __construct($type) {
    // check type is valid
    if (!isset($this->allowed[$type])) {
        trigger_error(_('Upload type not valid'));

        $this->error = true;
        return false;
    }

    // check files
    foreach ($_FILES as $file) {
      // get file extension
      $this->ext        = explode('.', $file['name']);
      $this->ext        = strtolower(end($this->ext));

      $this->file       = $file['tmp_name'];
      $this->filename   = $file['name'];

      // check filetype is allowed
      if (!isset($this->allowed[$type][$this->ext])) {
        trigger_error(sprintf(_('Filetype %s not allowed'), $this->ext));

        $this->error = true;
        return false;
      }
    }
  }


  public function put($date) {
    if ($this->error) {
      return false;
    }

    // set data
    $uploadDir              = '/issues/' . $date . '/files/';

    $newFile['extension']   = $this->ext;
    $newFile['filename']    = $this->filename;
    $newFile['file']        = $uploadDir . $this->filename;


    // ensure target directory is available and writable
    $uploadDir = DIGEST_BASE_DIR . $uploadDir;

    if (!is_dir($uploadDir)) {
      // create media directory
      mkdir($uploadDir, 0777, true);
    }
    if (!is_writable($uploadDir)) {
      // make writable
      chmod($uploadDir, 0777);
    }


    // move uploaded file
    $success = move_uploaded_file($this->file, $uploadDir . $newFile['filename']);

    if ($success) {
      return $newFile;

    } else {
      return false;
    }
  }


  public static function getTypes() {
    $types = array('image' => _('Image'),
                   'video' => _('Video'));

    return $types;
  }


  public static function draw($media) {
    // get file extension
    $media['ext'] = @strtoupper(end(explode('.', $media['file'])));


    // determine link type
    $link  = '<div class="link">' .
                $media['name'] .
             '</div>';

    if ($media['type'] == 'video') {
      if (is_file(BASE_DIR . $media['file'])) {
        $media['size'] = Ui::filesize(filesize(BASE_DIR . $media['file']));
      } else {
        $media['size'] = false;
      }

      // show link?
      if ($media['size']) {
        $link  = '<div class="link">
                    <a href="' . BASE_URL . $media['file'] . '" title="' . strip_tags($string) . '">' .
                      sprintf(_('Download <b>%s</b> video (%s, %s)'), $media['name'], $size, $media['ext']) .
                 '  </a>
                  </div>';
      }
    }


    // draw
    $buf = null;

    if ($media['type'] == 'image') {
      $buf   = '<div class="image">
                  <img src="' . DIGEST_URL . $media['file'] . '" alt="" />' .
                  $link .
               '</div>';

    } else if ($media['type'] == 'video') {
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
    }

    return $buf;
  }


  public static function makeClickable($media) {
    // create file link "breadcrumb"
    $fileLink   = null;
    $dateLinked = false;

    $fileParts  = preg_split('/\//', $media['file'], null, PREG_SPLIT_NO_EMPTY);

    foreach ($fileParts as $filePart) {
      $tmp = @end(sscanf($filePart, '%d-%d-%d'));

      if (!empty($tmp) && !$dateLinked) {
        // date detected
        $fileLink .= '/<a href="#" class="media-item-file-link" onclick="changeMediaDate(\'' . $media['date'] . '\', ' . $media['number'] . ');">' . $filePart . '</a>';

        $dateLinked = true;

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