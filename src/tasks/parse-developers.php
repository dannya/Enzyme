#!/usr/bin/php -q
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


include(dirname(__FILE__) . '/../autoload.php');


// ensure script can only be run from the command-line
if (!COMMAND_LINE) {
  exit;
}


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

?>