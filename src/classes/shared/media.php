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

  private static $max = array('width'   => 550,
                              'height'  => 600);

  private static $allowed   = array('image' => array('png'   => 1,
                                                     'gif'   => 1,
                                                     'jpg'   => 1),

                                    'video' => array('avi'   => 1,
                                                     'mpeg'  => 1));


  public function __construct($type) {
    // check type is valid
    if (!isset(self::$allowed[$type])) {
        trigger_error(_('Upload type not valid'));

        $this->error = true;
        return false;
    }

    // check files
    foreach ($_FILES as $file) {
      // get file extension
      $this->ext        = App::getExtension($file['name']);
      $this->file       = $file['tmp_name'];
      $this->filename   = $file['name'];

      // check filetype is allowed
      if (!isset(self::$allowed[$type][$this->ext])) {
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
    $newFile['thumbnail']   = null;


    // ensure target directory is available and writable
    $uploadDir = DIGEST_BASE_DIR . $uploadDir;

    if (!is_dir($uploadDir)) {
      // create media directory
      mkdir($uploadDir, 0775, true);
      chgrp('commit-digest');
    }
    if (!is_writable($uploadDir)) {
      // make writable
      chmod($uploadDir, 0775);
      chgrp('commit-digest');
    }


    // move uploaded file
    $success = move_uploaded_file($this->file, $uploadDir . $newFile['filename']);

    if ($success) {
      // create thumbnail?
      if ($thumbnail = $this->resize($newFile['file'])) {
        $newFile['thumbnail'] = $thumbnail;
      }

      // return details
      return $newFile;

    } else {
      return false;
    }
  }


  private static function resize($originalImage) {
    // do we need to resize?
    list($width, $height) = getimagesize(DIGEST_BASE_DIR . $originalImage);

    if (($width <= self::$max['width']) && ($height <= self::$max['height'])) {
      // don't resize
      return false;
    }


    // resize...
    $xscale = $width / self::$max['width'];
    $yscale = $height / self::$max['height'];

    if ($yscale > $xscale) {
        $newWidth = round($width * (1 / $yscale));
        $newHeight = round($height * (1 / $yscale));

    } else {
        $newWidth = round($width * (1 / $xscale));
        $newHeight = round($height * (1 / $xscale));
    }


    // get extension
    $ext          = App::getExtension($originalImage);


    // initialise image
    $imageResized = imagecreatetruecolor($newWidth, $newHeight);

    if ($ext == 'png') {
      $imageTmp   = imagecreatefrompng(DIGEST_BASE_DIR . $originalImage);
    } else if ($ext == 'gif') {
      $imageTmp   = imagecreatefromgif(DIGEST_BASE_DIR . $originalImage);
    } else if (($ext == 'jpg') || ($ext == 'jpeg')) {
      $imageTmp   = imagecreatefromjpeg(DIGEST_BASE_DIR . $originalImage);

    } else {
      // unknown file type
      return false;
    }


    // do resize
    imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);


    // save resized image
    $outputPath = App::stripExtension($originalImage) . '_thumb.' . $ext;

    if ($ext == 'gif') {
      imagegif($imageResized, DIGEST_BASE_DIR . $outputPath);
    } else if ($ext == 'png') {
      imagepng($imageResized, DIGEST_BASE_DIR . $outputPath, 95);
    } else if (($ext == 'jpg') || ($ext == 'jpeg')) {
      imagejpeg($imageResized, DIGEST_BASE_DIR . $outputPath, 95);
    }


    // return path of resized image
    return $outputPath;
  }


  public static function getTypes() {
    $types = array('image' => _('Image'),
                   'video' => _('Video'));

    return $types;
  }


  public static function draw($media) {
    // get file extension
    $media['ext'] = strtoupper(App::getExtension($media['file']));


    // determine link type
    $link  = '<div class="link">' .
                $media['name'] .
             '</div>';

    if ($media['type'] == 'video') {
      if (is_file(BASE_DIR . $media['file'])) {
        $media['size'] = Ui::filesize(filesize(BASE_DIR . $media['file']), 1000);
      } else {
        $media['size'] = false;
      }

      // show link?
      if ($media['size']) {
        $link  = '<div class="link">
                    <a href="' . BASE_URL . $media['file'] . '" title="' . strip_tags($string) . '">' .
                        sprintf(_('Download <b>%s</b> video (%s, %s)'), $media['name'], $media['size'], $media['ext']) .
                 '  </a>
                  </div>';
      }
    }


    // draw
    $buf = null;

    if ($media['type'] == 'image') {
      // show thumbnail and link to larger image?
      if (!empty($media['thumbnail'])) {
        $image = '<a href="' . DIGEST_URL . $media['file'] . '" target="_blank">
                    <img src="' . DIGEST_URL . $media['thumbnail'] . '" alt="" />
                  </a>';

      } else {
        $image = '<img src="' . DIGEST_URL . $media['file'] . '" alt="" />';
      }

      $buf   = '<div class="img">' .
                  $image .
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


  public static function drawItem($media, $edit = false) {
    $fileLink = self::makeClickable($media);

    // draw
    if ($edit) {
      // editable
      $buf   = '  <div id="media_' . $media['date'] . '_' . $media['number'] . '" class="media-item">
                    <span class="' . $media['type'] . '">&nbsp;</span>
                    <input type="text" class="media-item-number" value="' . $media['number'] . '" name="number" onchange="saveChange(\'' . $media['date'] . '\', ' . $media['number'] . ', event);" />
                    <input type="text" class="media-item-name" value="' . $media['name'] . '" name="name" onchange="saveChange(\'' . $media['date'] . '\', ' . $media['number'] . ', event);" />';

      if ($media['type'] == 'video') {
        $buf  .= '  <input type="text" class="media-item-youtube" value="' . $media['youtube'] . '" name="youtube" onchange="saveChange(\'' . $media['date'] . '\', ' . $media['number'] . ', event);" />';
      }

      $buf  .= '    <span class="media-item-file">' . $fileLink . '</span>

                    <input id="media_' . $media['date'] . '_' . $media['number'] . '-close-preview" style="display:none;" type="button" value="' . _('Close preview') . '" onclick="previewMedia(\'' . $media['date'] . '\', ' . $media['number'] . ')" />
                    <input id="media_' . $media['date'] . '_' . $media['number'] . '-preview" type="button" value="' . _('Preview') . '" onclick="previewMedia(\'' . $media['date'] . '\', ' . $media['number'] . ')" />
                  </div>';
    } else {
      // display
      $buf   = '  <div id="media_' . $media['date'] . '_' . $media['number'] . '" class="media-item">
                    <span class="' . $media['type'] . '">&nbsp;</span>
                    <input type="text" class="tag" value="[' . $media['type'] . $media['number'] . ']" />
                    <span class="name">' . $media['name'] . '</span>

                    <input id="media_' . $media['date'] . '_' . $media['number'] . '-close-preview" style="display:none;" type="button" value="' . _('Close preview') . '" onclick="previewMedia(\'' . $media['date'] . '\', ' . $media['number'] . ')" />
                    <input id="media_' . $media['date'] . '_' . $media['number'] . '-preview" type="button" value="' . _('Preview') . '" onclick="previewMedia(\'' . $media['date'] . '\', ' . $media['number'] . ')" />
                  </div>';
    }

    return $buf;
  }


  public static function load($date, $reindex = false) {
    // sanity check
    if (!$date) {
      return false;
    }

    // $date can be single date string, or array of date strings
    $media = Db::load('digest_intro_media', array('date' => $date), null, '*', false);

    // reindex
    if ($reindex) {
      $media = Db::reindex($media, 'date', false, false);
    }

    return $media;
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

      } else if (($tmpExt = App::getExtension($filePart)) && (strlen($tmpExt) == 3) || (strlen($tmpExt) == 4)) {
        // filename
        $fileLink .= '/<a id="media-filename-' . $media['date'] . '-' . $media['number'] . '" href="#" class="media-item-file-link" onclick="changeMediaFilename(\'' . $media['date'] . '\', ' . $media['number'] . ', \'' . $filePart . '\');">' . $filePart . '</a>';

      } else {
        // normal segment
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


  public static function validFilename($filename) {
    $ext = App::getExtension($filename);

    foreach (self::$allowed as $items) {
      foreach ($items as $item => $null) {
        if ($item == $ext) {
          return true;
        }
      }
    }

    return false;
  }
}

?>