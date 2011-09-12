<?php

/*-------------------------------------------------------+
 | 5bx
 | Copyright 2011 Danny Allen <me@dannya.com>
 | http://www.5bx.me/
 +--------------------------------------------------------+
 | All Rights Reserved
 +--------------------------------------------------------*/


class MinifyCss {
  // from http://www.lateralcode.com/css-minifier/
  public static function minify($css) {
    $css = preg_replace('#\s+#', ' ', $css);
    $css = preg_replace('#/\*.*?\*/#s', '', $css);
    $css = str_replace('; ', ';', $css);
    $css = str_replace(': ', ':', $css);
    $css = str_replace(' {', '{', $css);
    $css = str_replace('{ ', '{', $css);
    $css = str_replace(', ', ',', $css);
    $css = str_replace('} ', '}', $css);
    $css = str_replace(';}', '}', $css);

    return trim($css);
  }
}