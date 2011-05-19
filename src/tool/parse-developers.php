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


include($_SERVER['DOCUMENT_ROOT'] . '/autoload.php');


// check authentication
$user = new User();

if (empty($user->auth)) {
  echo _('Must be logged in!');
  exit;
}


set_time_limit(3500);
ob_start();


// draw html page start
echo Ui::drawHtmlPageStart(null, array('/css/common.css'), array('/js/prototype.js'));


// load list of defined repositories
$repos = Connector::getRepositories();

foreach ($repos as $repo) {
  if (!empty($repo['accounts_file'])) {
    if ($repo['type'] == 'svn') {
      $repository = new Svn($repo);

    } else {
      // not implemented
      break;
    }

    // parse authors from set accounts file in defined repository
    $repository->setupParseDevelopers();
    $repository->parseDevelopers();
  }
}


// display summary
if ($repository->summary) {
  echo Ui::processSummary($repository->summary, true);
}


// draw html page end
echo Ui::drawHtmlPageEnd();

?>