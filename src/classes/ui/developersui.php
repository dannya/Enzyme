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
  public $id                      = 'developers';

  private $user                   = array();

  private static $displayFields   = array('account'     => array('type' => 'string'),
                                          'nickname'    => array('type' => 'string'),
                                          'dob'         => array('type' => 'date'),
                                          'gender'      => array('type' => 'enum'),
                                          'continent'   => array('type' => 'enum'),
                                          'country'     => array('type' => 'string'),
                                          'location'    => array('type' => 'string'),
                                          'latitude'    => array('type' => 'float'),
                                          'longitude'   => array('type' => 'float'),
                                          'motivation'  => array('type' => 'enum'),
                                          'employer'    => array('type' => 'string'),
                                          'colour'      => array('type' => 'enum'));


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


    // define fields / string mappings
    $fields  = array('account'    => _('Account'),
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
                     'colour'     => _('Colour'));

    // add strings to display fields
    foreach ($fields as $key => $string) {
      self::$displayFields[$key]['string'] = $string;
    }


    // define enumerations



    // create interact bar elements
    $interactType     = Ui::htmlSelector('interact-type', array('search'      => _('Search'),
                                                                'filter'      => _('Filter')), null, 'changeInteractType(event);');

    $interactField    = Ui::htmlSelector('interact-field', $fields, null, 'changeInteractField(event);');

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
                    <th class="column">&nbsp;</th>';

    foreach (self::$displayFields as $key => $value) {
      $buf  .= self::drawHeader($key, $value['string']);
    }

    $buf  .= '    </tr>
                </thead>
              </table>

              <table id="developers" style="display:none;">
                <tbody id="developers-body">
                </tbody>
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


  public static function drawRow($developer) {
    // draw row
    $buf =   '<tr id="row-' . $developer['account'] . '">
                <td class="column-button">
                  <div id="active-' . $developer['account'] . '" class="account-status" title="' . _('Delete this developer record?') . '" onclick="deleteDeveloper(event);">
                    <div>&nbsp;</div>
                  </div>
                </td>';

    foreach ($developer as $key => $value) {
      if (isset(self::$displayFields[$key])) {
        $buf  .= self::drawField($key, $value);
      }
    }

    $buf  .= '</tr>';

    return $buf;
  }


  private static function drawField($key, $value = null) {
    // initialise
    $type     = 'string';
    $display  = $value;

    // run value through display method?
    if (isset(self::$displayFields[$key])) {
      if (isset(self::$displayFields[$key]['type'])) {
        $type = self::$displayFields[$key]['type'];
      }

      if ($value) {
        if (self::$displayFields[$key]['type'] == 'enum') {
          $display = App::enumToString($value);
        }
      }
    }

    return '<td class="column column-' . $key . (empty($value) ? ' empty' : '') . '" data-field="' . $key . '" data-value="' . $value . '" data-type="' . $type . '">' . $display . '</td>';
  }


  private static function drawHeader($key, $string) {
    return '<th class="column column-' . $key . '">' . $string . '</th>';
  }
}

?>