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


class Webstats {
  public static function track() {
    if (defined('WEBSTATS_URL') && defined('WEBSTATS_ID')) {
      $buf =     '<script type="text/javascript">
                    var pkBaseURL = (("https:" == document.location.protocol) ? "https://' . WEBSTATS_URL . '/" : "http://' . WEBSTATS_URL . '/");
                    document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));

                    try {
                      var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", ' . WEBSTATS_ID . ');
                      piwikTracker.trackPageView();
                      piwikTracker.enableLinkTracking();
                    } catch( err ) {}
                  </script>
                  <noscript>
                    <p>
                      <img src="http://' . WEBSTATS_URL . '/piwik.php?idsite=' . WEBSTATS_ID . '" style="border:0" alt="" />
                    </p>
                  </noscript>';

      return $buf;
    }
  }


  public static function manual($params = null) {
    if (defined('WEBSTATS_URL') && defined('WEBSTATS_ID')) {
      $t = new PiwikTracker(WEBSTATS_ID, 'http://' . WEBSTATS_URL);

      // set params?
      $title = null;
      if (isset($params['title'])) {
        $title = $params['title'];
      }

      // set url?
      if (isset($params['url'])) {
        $t->setUrl($params['url']);
      }

      // track
      $t->doTrackPageView($title);
    }
  }
}

?>