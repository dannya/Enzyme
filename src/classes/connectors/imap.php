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


class Imap extends Connector {
  public function __construct($repo) {
    // setup summary, repo details
    parent::__construct($repo);
  }


  public function setupInsertRevisions() {
    // setup summary
    parent::setupInsertRevisions();
  }


  public function insertRevisions() {
    // check we have initialised
    if ($this->initialised != 'insertRevisions') {
      throw new Exception('Call setupInsertRevisions() on this object first');
    }

    $hostname = '{' . $this->repo['hostname'] . ':' . $this->repo['port'] . '/imap/ssl}';

    // connect to inbox
    $inbox = imap_open($hostname, $this->repo['username'], $this->repo['password'], null, 1) or die(imap_last_error());

    // get all emails
    $emails = imap_search($inbox, 'ALL');

    if ($emails) {
      foreach ($emails as $emailNumber) {
        // initialise
        $parsed['commit']       = array();
        $parsed['commitFiles']  = array();
        $parsed['tmpPaths']     = array();


        // get message header
        $header = imap_fetch_overview($inbox, $emailNumber, 0);
        $header = reset($header);


        // check if we should include this commit
        // (kde-commits mailing list prefixes subjects with [ when it is a Git commit...)
        if (!isset($header->subject) || trim($header->subject[0]) != '[') {
          // delete email message
          imap_delete($inbox, $emailNumber);

          // increment summary counter
          ++$this->summary['skipped']['value'];
          continue;

        } else {
          // set repository name and type
          $tmp                            = explode(']', $header->subject);
          $parsed['commit']['repository'] = ltrim(reset($tmp), '[');
          $parsed['commit']['format']     = 'git';
        }


        // get message body
        $bodyText = trim(imap_fetchbody($inbox, $emailNumber, 1));
        $body     = explode("\n", $bodyText);


        // use parser based on inferred format
        $delimiter = strpos($bodyText, 'diff ');
        if ($delimiter === false) {
          $delimiter = strlen($bodyText);
        }

        if (strpos(substr($bodyText, 0, $delimiter), 'Date: ') !== false) {
          $parseSuccess = $this->parseFormat1($inbox, $emailNumber, $body, $parsed);

        } else {
          $parseSuccess = $this->parseFormat2($inbox, $emailNumber, $body, $parsed);
        }


        // determine base commit path
        $parsed['commit']['basepath'] = Enzyme::getBasePath($parsed['tmpPaths']);


        // insert modified/added/deleted files?
        if ($parseSuccess && $parsed['commitFiles']) {
          foreach ($parsed['commitFiles'] as $commitFile) {
            // add revision ID
            $commitFile['revision'] = $parsed['commit']['revision'];

            // insert into db
            Db::insert('commit_files', $commitFile, true);
          }
        }


        if ($parseSuccess) {
          // insert commit into database
          Db::insert('commits', $parsed['commit'], true);

          // report successful process/insertion
          Ui::displayMsg(sprintf(_('Processed revision %s'), $parsed['commit']['revision']));

          // delete email message
          //imap_delete($inbox, $emailNumber);

          // increment summary counter
          ++$this->summary['processed']['value'];

        } else {
          // report failed process/insertion
          Ui::displayMsg(sprintf(_('Failed to process revision %s'), $parsed['commit']['revision']), 'error');

          // increment summary counter
          ++$this->summary['failed']['value'];
        }
      }
    }


    // close connection to inbox
    imap_close($inbox);
  }


  private function parseFormat1(&$inbox, $emailNumber, $body, &$parsed) {
    // initialise line counter
    $i = 0;

    // check for added/deleted files at start of message
    while (isset($body[$i]) && (substr($body[$i], 0, 6) != 'commit') && (substr($body[$i], 0, 10) != 'Git commit')) {
      $body[$i] = trim($body[$i]);

      if (!empty($body[$i])) {
        // extract path and operation
        $tmp = preg_split('/\s+/', $body[$i], -1, PREG_SPLIT_NO_EMPTY);

        if (!empty($tmp[1])) {
          $tmpPath = '/' . $tmp[1];

          $parsed['commitFiles'][$tmpPath]  = array('operation' => $tmp[0],
                                                    'path'      => $tmpPath);

          // remember path so we can calculate basepath later
          $parsed['tmpPaths'][] = $tmpPath;
        }
      }

      ++$i;
    }


    // revision
    if (!isset($body[$i])) {
      // log
      return false;
    }


    // extract revision
    preg_match('/[a-z0-9]{40}/', $body[$i], $matches);
    $parsed['commit']['revision'] = reset($matches);


    // extract branch?
    if (substr($body[$i + 1], 0, 6) == 'branch') {
      $parsed['commit']['branch'] = trim(str_replace('branch ', null, $body[++$i]));
    }


    // account for merges
    if (substr($body[$i + 1], 0, 5) == 'Merge') {
      $fileDelimiter = 'diff --cc';
      ++$i;

    } else {
      // no merge found
      $fileDelimiter = 'diff --git';
    }


    // author
    $tmp = explode('<', $body[$i + 1]);
    $tmp = rtrim(str_replace('>', null, end($tmp)));

    $parsed['commit']['author'] = Enzyme::getAuthorInfo('account', $tmp, 'email');

    if (empty($parsed['commit']['author'])) {
      // cannot find email => username, log and set as email address
      $parsed['commit']['author'] = $tmp;
    }


    // extract date
    $parsed['commit']['date'] = date('Y-m-d H:i:s', strtotime(trim(str_replace('Date:', null, $body[$i + 2]))));


    // extract message text
    $i            = $i + 3;
    $totalLines   = count($body);

    $parsed['commit']['msg'] = null;

    while (isset($body[$i]) &&
           (strpos($body[$i], $fileDelimiter) === false) &&
           ($i < $totalLines)) {

      $parsed['commit']['msg'] .= $body[$i] . "\n";
      ++$i;
    }

    $parsed['commit']['msg'] = Enzyme::processCommitMsg($parsed['commit']['revision'], trim($parsed['commit']['msg']));


    // get modified files
    while ($i < $totalLines) {
      if (strpos($body[$i], 'diff --git') !== FALSE) {
        $tmp         = preg_split('/\s+/', $body[$i]);
        $tmp         = ltrim($tmp[2], 'a');

        // add to files list?
        if (!isset($parsed['commitFiles'][$tmp])) {
          $parsed['commitFiles'][$tmp]  = array('operation' => 'M',
                                                'path'      => $tmp);

          // remember path so we can calculate basepath later
          $parsed['tmpPaths'][] = $tmp;
        }
      }

      ++$i;
    }


    return true;
  }


  private function parseFormat2(&$inbox, $emailNumber, $body, &$parsed) {
    // initialise line counter
    $i = 0;


    // extract revision
    preg_match('/[a-z0-9]{40}/', $body[$i], $matches);
    $parsed['commit']['revision'] = reset($matches);


    // extract date
    if (strpos($body[$i + 1], 'Committed on ') !== false) {
      $pattern        = array('Committed on ', 'at');
      $replace        = null;

      $date           = DateTime::createFromFormat('d/m/y H:i', trim(str_replace($pattern, $replace, $body[$i + 1]), '.'));
      $extractedDate  = $date->format('Y-m-d H:i:s');

      $parsed['commit']['date'] = $extractedDate;
      ++$i;

    } else {
      // get date from headers
      $header        = imap_header($inbox, $emailNumber);

      $extractedDate = strtotime($header->date);
      $parsed['commit']['date'] = date('Y-m-d H:i:s', $extractedDate);
    }


    // protect against errors in date parsing
    if (!$extractedDate) {
      return false;
    }


    // extract author and branch
    while (substr($body[$i + 1], 0, 6) !== 'Pushed') {
      // handle text breaking onto next line
      ++$i;
    }

    $tmp = preg_split('/\s+/', $body[$i + 1]);

    $parsed['commit']['author'] = $tmp[2];
    $parsed['commit']['branch'] = end($tmp);


    // pattern for file diff listings
    $filePattern  = '/[MADI]{1}\s+[\+\-][0-9]{0,4}\s+[\+\-][0-9]{0,4}/';


    // extract message text
    $i += 3;
    $totalLines = count($body);

    $parsed['commit']['msg']  = null;

    while (isset($body[$i]) &&
           (preg_match($filePattern, $body[$i]) != 1) &&
           (substr($body[$i], 0, 7) != 'http://')) {

      $parsed['commit']['msg'] .= rtrim($body[$i], '=') . "\n";
      ++$i;
    }

    $parsed['commit']['msg'] = Enzyme::processCommitMsg($parsed['commit']['revision'], trim($parsed['commit']['msg']));


    // get modified files
    while ($i < $totalLines) {
      if (preg_match($filePattern, $body[$i]) == 1) {
        $tmp      = preg_split('/\s+/', $body[$i]);
        $tmpFile  = trim($tmp[3]);

        if (substr($tmpFile, -1) == '=') {
          // filename has split onto 2 lines, handle this
          $tmpFile = rtrim($tmpFile, '=') . rtrim(rtrim(rtrim($body[++$i]), '='));
        }

        // add to files list?
        if (!isset($parsed['commitFiles'][$tmpFile])) {
          $parsed['commitFiles'][$tmpFile]  = array('operation' => $tmp[0],
                                                    'path'      => $tmpFile);

          // remember path so we can calculate basepath later
          $parsed['tmpPaths'][] = $tmpFile;
        }

      } else if (substr($body[$i], 0, 7) == 'http://') {
        // no more files
        break;
      }

      ++$i;
    }


    return true;
  }
}

?>