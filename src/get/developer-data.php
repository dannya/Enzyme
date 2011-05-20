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
  App::returnHeaderJson(true, array('login' => false));
}


// ensure user has privileges
if (!$user->hasPermission('admin')) {
  App::returnHeaderJson(true, array('permission' => false));
}


// define valid
$validOperators = array('eq', 'lt', 'gt', 'start', 'end', 'contain');


if ($_REQUEST['context'] == 'draw') {
  // draw requested developer data:
  // check params are valid
  if ((empty($_REQUEST['field']) || !isset(Developer::$fields[$_REQUEST['field']])) ||
      (empty($_REQUEST['operator']) || !in_array($_REQUEST['operator'], $validOperators)) ||
      empty($_REQUEST['value'])) {

    App::returnHeaderJson(true, array('error' => true));
  }


  // create filter for loading developer data based on selected operator
  $filter     = array($_REQUEST['field'] => array('type'  => $_REQUEST['operator'],
                                                  'value' => $_REQUEST['value']));


  // load developer data
  $developers = Enzyme::getPeopleInfo($filter, true);


  // return success, number of results
  App::returnHeaderJson(false, array('success'  => true,
                                     'results'  => count($developers)));


  // draw rows
  $buf = null;

  foreach ($developers as $account => $developer) {
    $buf .= DevelopersUi::drawRow($developer);
  }

  echo $buf;


} else if ($_REQUEST['context'] == 'save') {
  // save developer data:
  // check params are valid
  if ((empty($_REQUEST['field']) ||
      !isset(Developer::$fields[$_REQUEST['field']])) ||
      empty($_REQUEST['account'])) {

    App::returnHeaderJson(true, array('error' => true));
  }

  // make empty values be saved as NULL in db
  if ($_REQUEST['value'] === '') {
    $_REQUEST['value'] = null;
  }

  // do save
  $json['success'] = Db::save('developers',
                              array('account'           => $_REQUEST['account']),
                              array($_REQUEST['field']  => $_REQUEST['value']));

  // return success
  App::returnHeaderJson();


} else if ($_REQUEST['context'] == 'delete') {
  // delete developer record:
  // check params are valid
  if (empty($_REQUEST['account'])) {
    App::returnHeaderJson(true, array('error' => true));
  }

  // delete record
  $json['success'] = Db::delete('developers', array('account' => $_REQUEST['account']));

  // return success
  App::returnHeaderJson();
}

?>