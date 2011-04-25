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
  private $developers   = array();


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Developers');

    // load developer data
    $this->developers = Enzyme::getPeopleInfo(false, true);
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'admin')) {
      return $buf;
    }


    // create interact bar elements
    $interactType   = Ui::htmlSelector('interact-type', array('search'      => _('Search'),
                                                              'filter'      => _('Filter')));

    $interactField  = Ui::htmlSelector('interact-type', array('account'     => _('Account'),
                                                              'nickname'    => _('Nickname'),
                                                              'dob'         => _('DOB'),
                                                              'gender'      => _('Gender'),
                                                              'continent'   => _('Continent'),
                                                              'country'     => _('Country'),
                                                              'location'    => _('Location'),
                                                              'latitude'    => _('Latitude'),
                                                              'longitude'   => _('Longitude'),
                                                              'motivation'  => _('Motivation'),
                                                              'employer'    => _('Employer'),
                                                              'colour'      => _('Colour')));

    $interactOp     = Ui::htmlSelector('interact-op', array('eq'  => '=',
                                                            'lt'  => '&lt;',
                                                            'gt'  => '&gt;'));

    $interactValue  = '<input id="interact-value" type="type" value="" />';


    // draw
    $buf = '<h3>' .
              _('Developers') .
           '  <span>
                <span class="status">' .
                  sprintf(_('%d developer records'), count($this->developers)) .
           '    </span>
                <input type="button" title="' . _('Add new developer record') . '" value="' . _('Add new developer record') . '" onclick="addUser();" />
              </span>
            </h3>

            <div id="interact-bar">' .
              $interactType . '<i>' . _('where') . '</i>' . $interactField . $interactOp . $interactValue .
           '</div>

            <div id="developers-container">
              <table id="developers">
                <thead>
                  <tr>
                    <th>&nbsp;</th>
                    <th>' . _('Account') . '</th>
                    <th>' . _('Nickname') . '</th>
                    <th>' . _('DOB') . '</th>
                    <th>' . _('Gender') . '</th>
                    <th>' . _('Continent') . '</th>
                    <th>' . _('Country') . '</th>
                    <th>' . _('Location') . '</th>
                    <th>' . _('Latitude') . '</th>
                    <th>' . _('Longitude') . '</th>
                    <th>' . _('Motivation') . '</th>
                    <th>' . _('Employer') . '</th>
                    <th>' . _('Colour') . '</th>
                  </tr>
                </thead>

                <tbody id="users-body">';

    $i = 0;
    foreach ($this->developers as $account => $developer) {
      if ($i++ > 50) {
        break;
      }

      $buf .= $this->drawRow($developer);
    }

    // draw hidden row, to allow creation of new users
    $buf  .= $this->drawRow(null);

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


  private function drawRow($developer = null) {
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
                <td>' .
                  $accountButton .
             '  </td>

                <td class="account">' . $developer['account'] . '</td>
                <td class="nickname">' . $developer['nickname'] . '</td>
                <td class="dob">' . $developer['dob'] . '</td>
                <td class="gender">' . $developer['gender'] . '</td>
                <td class="continent">' . $developer['continent'] . '</td>
                <td class="country">' . $developer['country'] . '</td>
                <td class="location">' . $developer['location'] . '</td>
                <td class="latitude">' . $developer['latitude'] . '</td>
                <td class="longitude">' . $developer['longitude'] . '</td>
                <td class="motivation">' . $developer['motivation'] . '</td>
                <td class="employer">' . $developer['employer'] . '</td>
                <td class="colour">' . $developer['colour'] . '</td>
              </tr>';

    return $buf;
  }
}

?>