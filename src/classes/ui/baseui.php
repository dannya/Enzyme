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


abstract class BaseUi {
  public $title = null;


  public function drawSidebar() {
    // load user so we can check permissions
    $user = new User();

    // define items
    $items = array('insert'   => array('url'        => BASE_URL . '/insert/',
                                       'string'     => _('Insert'),
                                       'permission' => 'editor'),

                   'review'   => array('url'        => BASE_URL . '/review/',
                                       'string'     => _('Review'),
                                       'permission' => 'reviewer'),

                   'classify' => array('url'        => BASE_URL . '/classify/',
                                       'string'     => _('Classify'),
                                       'permission' => 'classifier'),

                   'digests'  => array('url'        => BASE_URL . '/digests/',
                                       'string'     => _('Digests'),
                                       'permission' => 'editor'),

                   'features' => array('url'        => BASE_URL . '/features/',
                                       'string'     => _('Features'),
                                       'permission' => 'feature-editor'),

                   'media'    => array('url'        => BASE_URL . '/media/',
                                       'string'     => _('Media'),
                                       'permission' => 'feature-editor'),

                   'tools'    => array('url'        => BASE_URL . '/tools/',
                                       'string'     => _('Tools'),
                                       'permission' => null),

                   'users'    => array('url'        => BASE_URL . '/users/',
                                       'string'     => _('Users'),
                                       'permission' => 'admin'));


    // show import section?
    if (defined('SHOW_INSERT') && !SHOW_INSERT) {
      unset($items['insert']);
    }


    // draw sidebar
    $buf =   '<div id="sidebar">
                <ul>';

    foreach ($items as $id => $item) {
      // show permission-dependent items?
      if ($item['permission'] && !$user->hasPermission($item['permission'])) {
        continue;
      }

      if ($id == $this->id) {
        $class = ' class="selected"';
      } else {
        $class = null;
      }

      $buf .=  '<li'. $class . '>
                  <a href="' . $item['url'] . '">' . $item['string'] . '</a>
                </li>';
    }

    $buf .=  '  </ul>
              </div>';

    return $buf;
  }
}

?>