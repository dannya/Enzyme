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


class DevelopersUi extends BaseUi {
  public $id            = 'developers';

  private $user         = array();


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Developers');
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'admin')) {
      return $buf;
    }


    // create interact bar elements
    $interactType     = Ui::htmlSelector('interact-type', array('search'      => _('Search'),
                                                                'filter'      => _('Filter')), null, 'changeInteractType(event);');

    $interactField    = Ui::htmlSelector('interact-field', array('account'    => _('Account'),
                                                                 'nickname'   => _('Nickname'),
                                                                 'dob'        => _('DOB'),
                                                                 'gender'     => _('Gender'),
                                                                 'continent'  => _('Continent'),
                                                                 'country'    => _('Country'),
                                                                 'location'   => _('Location'),
                                                                 'latitude'   => _('Latitude'),
                                                                 'longitude'  => _('Longitude'),
                                                                 'motivation' => _('Motivation'),
                                                                 'employer'   => _('Employer'),
                                                                 'colour'     => _('Colour')), null, 'changeInteractField(event);');

    $interactOp       = Ui::htmlSelector('interact-op', array('eq'      => _('equals'),
                                                              'lt'      => _('less than'),
                                                              'gt'      => _('greater than'),
                                                              'start'   => _('starts with'),
                                                              'end'     => _('ends with'),
                                                              'contain' => _('contains')));

    $interactValue    = '<input id="interact-value" type="text" value="" />';
    $interactButton   = '<input id="interact-button" type="button" value="' . _('Go') . '" onclick="interactSearch(event);" />';
    $interactSpinner  = '<img id="interact-spinner" style="display:none;" src="' . BASE_URL . '/img/spinner.gif" alt="" />';
    $interactResults  = '<span id="interact-results" style="display:none;"></span>';


    // draw
    $buf = '<h3>' .
              _('Developers') .
           '  <span>
                <span class="status">' .
                  sprintf(_('%d developer records'), Db::count('developers', false)) .
           '    </span>
                <input type="button" title="' . _('Add new developer record') . '" value="' . _('Add new developer record') . '" onclick="addUser();" />
              </span>
            </h3>

            <form id="interact-bar" action="">' .
              $interactType . '<i>' . _('where') . '</i>' . $interactField . $interactOp . $interactValue . $interactButton . $interactSpinner . $interactResults .
           '</form>

            <div id="developers-container">
              <p id="developers-prompt" class="prompt">' .
                _('Perform a search to begin...') .
           '  </p>

              <p id="developers-again" class="prompt" style="display:none;">' .
                _('No results found - try a less restrictive search...') .
           '  </p>

              <table id="developers-headers" style="display:none;">
                <thead>
                  <tr>
                    <th class="column">&nbsp;</th>

                    <th class="column column-account">' . _('Account') . '</th>
                    <th class="column column-nickname">' . _('Nickname') . '</th>
                    <th class="column column-dob">' . _('DOB') . '</th>
                    <th class="column column-gender">' . _('Gender') . '</th>
                    <th class="column column-continent">' . _('Continent') . '</th>
                    <th class="column column-country">' . _('Country') . '</th>
                    <th class="column column-location">' . _('Location') . '</th>
                    <th class="column column-latitude">' . _('Latitude') . '</th>
                    <th class="column column-longitude">' . _('Longitude') . '</th>
                    <th class="column column-motivation">' . _('Motivation') . '</th>
                    <th class="column column-employer">' . _('Employer') . '</th>
                    <th class="column column-colour">' . _('Colour') . '</th>
                  </tr>
                </thead>
              </table>

              <table id="developers" style="display:none;">
                <tbody id="developers-body">';

    // draw hidden row, to allow creation of new users
    $buf  .= self::drawRow(null);

    $buf  .= '    </tbody>
                </table>
              </div>';


    return $buf;
  }


  public function getScript() {
    return array('/js/frame/developersui.js');
  }


  public function getStyle() {
    return array('/css/developersui.css');
  }


  public static function drawRow($developer = null) {
    if ($developer) {
      $rowId    = 'row-' . $developer['account'];
      $rowStyle = null;
      $pathsId  = ' id="paths-' . $developer['account'] . '"';

      // set onchange function
      $onChange = ' onchange="saveChange(\'' . $developer['account'] . '\', event);"';

      // set account status button
      $buttonClass = 'inactive';
      $buttonState = 'true';
      $buttonTitle = _('Delete this developer record?');



      // don't allow user to disable their own account!
      $accountButton    = '<div id="active-' . $developer['account'] . '" class="account-status ' . $buttonClass . '" title="' . $buttonTitle . '" onclick="setAccountActive(\'' . $developer['account'] . '\', ' . $buttonState . ');">
                             <div>&nbsp;</div>
                           </div>';
      $usernameOnChange = $onChange;


    } else {
      // draw a blank row
      $rowId             = 'row-new-0';
      $rowStyle          = ' style="display:none;"';
      $pathsId           = null;
      $onChange          = null;
      $usernameOnChange  = null;
      $pathsState        = null;

      $accountButton     = '<div class="account-status" title="' . _('Save new account?') . '" onclick="saveNewAccount(event);">
                              <div>&nbsp;</div>
                            </div>';
    }


    // draw row
    $buf =   '<tr id="' . $rowId . '"' . $rowStyle . '>
                <td class="column">' .
                  $accountButton .
             '  </td>

                <td class="column column-account' . (empty($developer['account']) ? ' empty' : '') . '">' . $developer['account'] . '</td>
                <td class="column column-nickname' . (empty($developer['nickname']) ? ' empty' : '') . '">' . $developer['nickname'] . '</td>
                <td class="column column-dob' . (empty($developer['dob']) ? ' empty' : '') . '">' . $developer['dob'] . '</td>
                <td class="column column-gender' . (empty($developer['gender']) ? ' empty' : '') . '">' . self::enumToString($developer['gender']) . '</td>
                <td class="column column-continent' . (empty($developer['continent']) ? ' empty' : '') . '">' . self::enumToString($developer['continent']) . '</td>
                <td class="column column-country' . (empty($developer['country']) ? ' empty' : '') . '">' . $developer['country'] . '</td>
                <td class="column column-location' . (empty($developer['location']) ? ' empty' : '') . '">' . $developer['location'] . '</td>
                <td class="column column-latitude' . (empty($developer['latitude']) ? ' empty' : '') . '">' . $developer['latitude'] . '</td>
                <td class="column column-longitude' . (empty($developer['longitude']) ? ' empty' : '') . '">' . $developer['longitude'] . '</td>
                <td class="column column-motivation' . (empty($developer['motivation']) ? ' empty' : '') . '">' . self::enumToString($developer['motivation']) . '</td>
                <td class="column column-employer' . (empty($developer['employer']) ? ' empty' : '') . '">' . $developer['employer'] . '</td>
                <td class="column column-colour' . (empty($developer['colour']) ? ' empty' : '') . '">' . self::enumToString($developer['colour']) . '</td>
              </tr>';

    return $buf;
  }


  public static function enumToString($key) {
    // map enums to i18n strings
    $keys = array('male'            => _('Male'),
                  'female'          => _('Female'),

                  'europe'          => _('Europe'),
                  'africa'          => _('Africa'),
                  'asia'            => _('Asia'),
                  'oceania'         => _('Oceania'),
                  'north-america'   => _('North America'),
                  'south-america'   => _('South America'),

                  'volunteer'       => _('Volunteer'),
                  'commercial'      => _('Commercial'),

                  'red'             => _('Red'),
                  'blue'            => _('Blue'),
                  'green'           => _('Green'),
                  'black'           => _('Black'),
                  'yellow'          => _('Yellow'),
                  'purple'          => _('Purple'),
                  'brown'           => _('Brown'));

    // return
    if (isset($keys[$key])) {
      return $keys[$key];

    } else {
      return $key;
    }
  }
}

?>