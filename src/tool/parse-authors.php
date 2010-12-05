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


include($_SERVER['DOCUMENT_ROOT'] . '/autoload.php');


// check authentication
$user = new User();

if (empty($user->auth)) {
  echo _('Must be logged in!');
  exit;
}


set_time_limit(0);
ob_start();


// draw html page start
echo Ui::drawHtmlPageStart(null, array('/css/common.css'), array('/js/prototype.js'));


// initialise summary
$summary['skipped']['title']    = _('Skipped: %d');
$summary['skipped']['value']    = 0;
$summary['added']['title']      = _('Added: %d');
$summary['added']['value']      = 0;
$summary['malformed']['title']  = _('Malformed: %d');
$summary['malformed']['value']  = 0;


// get existing authors data from db
$existingAuthors = Enzyme::getAuthors();


// get fresh authors data
$cmd    = 'svn cat --non-interactive ' . Enzyme::getRepoCmdAuth() . REPOSITORY . ACCOUNTS_FILE;
$data   = shell_exec(escapeshellcmd($cmd));
$data   = preg_split("/(\r?\n)/", $data);


// append accounts (if file present)
if (is_file(BASE_DIR . '/data/append_accounts.txt')) {
  $data = array_merge(file(BASE_DIR . '/data/append_accounts.txt'), $data);
}


// iterate through file line-by-line, inserting into database where not present
foreach ($data as $theAuthor) {
  if (empty($theAuthor)) {
    continue;
  }


  // split into parts by 2 or more spaces
  $elements = preg_split('/[ ][ ]+/', $theAuthor);


  if (empty($elements[2]) && !empty($elements[1])) {
    // split first field again on first space,
    // account names can't contain spaces anyway!
    $tmpAccount  = explode(' ', $elements[0]);
    $tmpAccount  = reset($tmpAccount);
    $elements[0] = str_replace($tmpAccount . ' ', null, $elements[0]);

    array_unshift($elements, $tmpAccount);
  }


  // check all elements are present
  if (count($elements) != 3) {
    // report malformed entry
    Ui::displayMsg(sprintf(_('Entry "%s" malformed, not added'), $theAuthor), 'error');

    // increment summary counter
    ++$summary['malformed']['value'];
    continue;
  }


  // set data
  $author['account']  = $elements[0];
  $author['name']     = $elements[1];
  $author['email']    = $elements[2];


  // check if author has already been processed
  if (isset($existingAuthors[$author['account']])) {
    if (!empty($_POST['show_skipped'])) {
      Ui::displayMsg(sprintf(_('Skipping: %s'), $theAuthor));
    }

    // increment summary counter
    ++$summary['skipped']['value'];
    continue;
  }


  // insert into database
  Db::insert('authors', $author, true);

  // report success
  Ui::displayMsg(sprintf(_('Added %s (%s) to authors table'), $author['name'], $author['account']));

  // increment summary counter
  ++$summary['added']['value'];
}


// display summary
echo Ui::processSummary($summary, true);


// draw html page end
echo Ui::drawHtmlPageEnd();

?>