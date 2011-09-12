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
  public $id      = 'developers';

  private $user   = array();


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
    $fields = Developer::getFieldStrings();

    // add strings to display fields
    foreach ($fields as $key => $string) {
      Developer::$fields[$key]['string'] = $string;
    }


    // create interact bar elements
    $interactType     = Ui::htmlSelector('interact-type', array('search'  => _('Search'),
                                                                'filter'  => _('Filter')), null, 'changeInteractType(event);');

    $interactField    = Ui::htmlSelector('interact-field', $fields, null, 'changeInteractField(event);');

    $interactOp       = Ui::htmlSelector('interact-op', array('eq'      => _('equals'),
                                                              'lt'      => _('less than'),
                                                              'gt'      => _('greater than'),
                                                              'start'   => _('starts with'),
                                                              'end'     => _('ends with'),
                                                              'contain' => _('contains')), 'contain');

    $interactValue    = '<input id="interact-value" type="text" value="" />';
    $interactButton   = '<input id="interact-button" type="button" value="' . _('Go') . '" onclick="interactSearch(event);" />';
    $interactSpinner  = '<img id="interact-spinner" style="display:none;" src="' . BASE_URL . '/img/spinner.gif" alt="" />';
    $interactResults  = '<span id="interact-results" style="display:none;"></span>';


    // draw
    $buf = '<h3>' .
              _('Developers') .
           '  <span>
                <span id="developers-num-records" class="status">' .
                  sprintf(_('%d developer records'), Db::count('developers', false)) .
           '    </span>
                <input type="button" title="' . _('Add new developer record') . '" value="' . _('Add new developer record') . '" onclick="addDeveloper();" />
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

    foreach (Developer::$fields as $key => $value) {
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


    // create select boxes for enum data types
    $enums = Developer::enumToString('all');

    foreach ($enums as $type => $section) {
      $buf .= Ui::htmlSelector('enum-' . $type, $section, null, null, null, 'display:none;', true);
    }


    return $buf;
  }


  public function getScript() {
    return array('/js/frame/developersui.js');
  }


  public function getStyle() {
    return array('/css/frame/developersui.css');
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
      if (isset(Developer::$fields[$key]) && (Developer::$fields[$key]['display'] != 'hidden')) {
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
    if (isset(Developer::$fields[$key])) {
      if (isset(Developer::$fields[$key]['type'])) {
        $type = Developer::$fields[$key]['type'];
      }

      if ($value) {
        if (Developer::$fields[$key]['type'] == 'enum') {
          $display = Developer::enumToString('key', $value);
        }
      }
    }

    return '<td class="column column-' . $key . (empty($value) ? ' empty' : '') . '" data-field="' . $key . '" data-value="' . $value . '" data-type="' . $type . '">' . $display . '</td>';
  }


  private static function drawHeader($key, $string) {
    if (isset(Developer::$fields[$key]) && (Developer::$fields[$key]['display'] != 'hidden')) {
      return '<th class="column column-' . $key . '">' . $string . '</th>';
    }
  }
}

?>