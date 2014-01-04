<?php

/*-------------------------------------------------------+
 | Enzyme
 | Copyright 2010-2013 Danny Allen <danny@enzyme-project.org>
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


class Developer {
  public $data                  = null;
  public $privacy               = null;
  public $access                = null;

  public static $fieldSections  = array('core'            => array('account', 'name', 'email', 'nickname', 'dob', 'gender', 'nationality', 'motivation', 'employer', 'colour'),
                                        'geographic'      => array('continent', 'country', 'location', 'latitude', 'longitude'),
                                        'social'          => array('homepage', 'blog', 'lastfm', 'microblog_type', 'microblog_user'),
                                        'system'          => array('access_ip', 'access_code', 'access_timeout'));

  // type:      datatype (string, float, enum)
  // display:   where the field is displayed ('all', 'admin', 'hidden')
  // editable:  whether this value can be changed within Enzyme
  // privacy:   whether privacy can be enabled/disabled for this field (wrap in array to represent enum)
  public static $fields         = array('account'         => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => false,
                                                                   'privacy'  => false),
                                        'name'            => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => false),
                                        'email'           => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'email'),
                                        'nickname'        => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'nickname'),
                                        'dob'             => array('type'     => 'date',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => array('dob')),
                                        'gender'          => array('type'     => 'enum',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'gender'),
                                        'nationality'     => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'nationality'),
                                        'motivation'      => array('type'     => 'enum',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'motivation'),
                                        'employer'        => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'employer'),
                                        'colour'          => array('type'     => 'enum',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'colour'),

                                        'continent'       => array('type'     => 'enum',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'continent'),
                                        'country'         => array('type'     => 'enum',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'country'),
                                        'location'        => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'location'),
                                        'latitude'        => array('type'     => 'float',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'location'),
                                        'longitude'       => array('type'     => 'float',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'location'),

                                        'homepage'        => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'homepage'),
                                        'blog'            => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'blog'),
                                        'lastfm'          => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'lastfm'),
                                        'microblog_type'  => array('type'     => 'enum',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'microblog'),
                                        'microblog_user'  => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true,
                                                                   'privacy'  => 'microblog'),

                                        'access_ip'       => array('type'     => 'string',
                                                                   'display'  => 'hidden',
                                                                   'editable' => false,
                                                                   'stored'   => 'privacy'),
                                        'access_code'     => array('type'     => 'string',
                                                                   'display'  => 'hidden',
                                                                   'editable' => true,
                                                                   'stored'   => 'privacy'),
                                        'access_timeout'  => array('type'     => 'string',
                                                                   'display'  => 'hidden',
                                                                   'editable' => true,
                                                                   'stored'   => 'privacy'));

  // use internal data (that cannot be modified externally) when saving
  private $internalData     = null;
  private $internalPrivacy  = null;


  public function __construct($value = null, $field = 'account', $createBuffer = false) {
    // load in constructor?
    if ($value) {
      $this->load($value, $field, $createBuffer);
    }
  }


  public function load($value = null, $field = 'account', $createBuffer = false) {
    if (!$value) {
      if (!isset($this->data['account'])) {
        return false;
      }

      $field = 'account';
      $value = $this->data['account'];
    }

    // load developer privacy
    $privacy = Db::load('developer_privacy', array($field => $value), 1);

    // stop if no developer privacy record found
    if (!$privacy) {
      return false;
    }

    // if loading by access_code, ensure code has not expired
    if ($field == 'access_code') {
      if (empty($privacy['access_timeout']) || (time() > strtotime($privacy['access_timeout']))) {
        return false;

      } else if ($createBuffer && (time() > (strtotime($privacy['access_timeout']) - 1800))) {
        // create buffer of 30 mins so user doesn't run out of time while completing form
        $privacy['access_timeout'] = Date('Y-m-d H:i:s', strtotime('Now + 30 minutes'));

        Db::saveSingleField('developer_privacy',
                            array($field => $value),
                            array('access_timeout' => $privacy['access_timeout']));
      }
    }


    // load developer data
    if ($this->data = Db::load('developers', array('account' => $privacy['account']), 1)) {
      // set privacy settings to each data value
      foreach (self::$fields as $id => $spec) {
        if (!isset($spec['privacy'])) {
          continue;
        }

        if ($spec['privacy'] === false) {
          // set privacy as irrelevant for this field
          $this->privacy[$id] = null;

        } else if (is_array($spec['privacy'])) {
          $spec['privacy'] = reset($spec['privacy']);

          // note that we cast to int here if possible (not bool, as done below)!
          if (is_numeric($privacy[$spec['privacy']])) {
            $this->privacy[$id] = (int)$privacy[$spec['privacy']];
          } else {
            $this->privacy[$id] = $privacy[$spec['privacy']];
          }

        } else {
          // cast to boolean for ease of use
          $this->privacy[$id] = (bool)$privacy[$spec['privacy']];
        }
      }

      // make terms_accepted version available too
      $this->privacy['terms_accepted'] = $privacy['terms_accepted'];


      // set immutable data for use when saving
      // (privacy is raw, true db representation - not mapped onto data values like $this->privacy)
      $this->internalData     = $this->data;
      $this->internalPrivacy  = $privacy;


      // set access details (if this method of loading was used)
      if ($field == 'access_code') {
        $this->access = array('ip'      => $privacy['access_ip'],
                              'code'    => $privacy['access_code'],
                              'timeout' => $privacy['access_timeout']);
      }
    }


    // return success of loading data
    return (bool)$this->data;
  }


  public function save() {
    // make empty values be null
    foreach ($this->internalData as &$value) {
      $value = trim($value);

      if (empty($value)) {
        $value = null;
      }
    }

    // save changes to internal data structures into database
    return Db::save('developers', array('account' => $this->internalData['account']), $this->internalData);
  }


  public function changeValue($field, $newValue, $save = false) {
    // check that field is valid, and is a sane field to change
    if (!isset(self::$fields[$field]) ||
        ($field == 'account') || ($field == 'access_ip') || ($field == 'access_code') || ($field == 'access_timeout')) {

      return false;
    }


    // change privacy value
    $this->internalData[$field] = $newValue;


    if ($save) {
      // save new value to database
      return $this->save();

    } else {
      return true;
    }
  }


  public function changeValues($data, $save = false) {
    // ensure we only try and save valid fields
    foreach ($data as $field => $value) {
      if (!isset(self::$fields[$field]) ||
        ($field == 'account') || ($field == 'access_ip') || ($field == 'access_code') || ($field == 'access_timeout')) {

        continue;
      }

      $this->internalData[$field] = $value;
    }

    if ($save) {
      // save new value to database
      return $this->save();

    } else {
      return true;
    }
  }


  public function changePrivacy($field, $newValue, $save = false) {
    // check that privacy can be changed for this field
    if (!isset(self::$fields[$field]) ||
        !isset(self::$fields[$field]['privacy']) || (self::$fields[$field]['privacy'] === false)) {

      return false;
    }


    // ensure field is a sane field to change
    if (($field == 'account') || ($field == 'access_ip') || ($field == 'access_code') || ($field == 'access_timeout')) {
      return false;
    }


    // set "real" privacy field name
    if (is_array(self::$fields[$field]['privacy'])) {
      $isEnum   = true;
      $field    = reset(self::$fields[$field]['privacy']);

    } else {
      $isEnum   = false;
      $field    = self::$fields[$field]['privacy'];
      $newValue = Db::quote($newValue);
    }


    // change privacy value
    $this->internalPrivacy[$field] = $newValue;


    if ($save) {
      // save new value to database
      return Db::saveSingleField('developer_privacy',
                                 array('account' => $this->internalPrivacy['account']),
                                 array($field => $newValue),
                                 $isEnum);

    } else {
      return true;
    }
  }


  public static function getFieldStrings() {
    $fields  = array('account'        => _('Account'),
                     'name'           => _('Name'),
                     'email'          => _('Email'),
                     'nickname'       => _('Nickname'),
                     'dob'            => _('Date of Birth'),
                     'gender'         => _('Gender'),
                     'nationality'    => _('Nationality'),
                     'motivation'     => _('Motivation'),
                     'employer'       => _('Employer'),
                     'colour'         => _('Favourite colour'),

                     'continent'      => _('Continent'),
                     'country'        => _('Country'),
                     'location'       => _('Location'),
                     'latitude'       => _('Latitude'),
                     'longitude'      => _('Longitude'),

                     'homepage'       => _('Homepage URL'),
                     'blog'           => _('Blog URL'),
                     'lastfm'         => _('Last.fm username'),
                     'microblog_type' => _('Microblog service'),
                     'microblog_user' => _('Microblog username'),

                     'access_ip'      => _('Access IP'),
                     'access_code'    => _('Access code'),
                     'access_timeout' => _('Access timeout'));

    return $fields;
  }


  // context can be:
  //  - 'all'
  //  - 'category'
  //  - 'key' (default)
  public static function enumToString($context = 'key', $key = null, $enhanced = false) {
    $keys                   = array();

    // map enums to i18n strings
    $keys['gender']         = array('male'            => _('Male'),
                                    'female'          => _('Female'));

    $keys['motivation']     = array('volunteer'       => _('Volunteer'),
                                    'commercial'      => _('Commercial'));

    $keys['colour']         = array('red'             => _('Red'),
                                    'blue'            => _('Blue'),
                                    'green'           => _('Green'),
                                    'black'           => _('Black'),
                                    'yellow'          => _('Yellow'),
                                    'purple'          => _('Purple'),
                                    'brown'           => _('Brown'),
                                    'grey'            => _('Grey'),
                                    'orange'          => _('Orange'),
                                    'pink'            => _('Pink'),
                                    'white'           => _('White'));

    $keys['continent']      = array('europe'          => _('Europe'),
                                    'africa'          => _('Africa'),
                                    'asia'            => _('Asia'),
                                    'oceania'         => _('Oceania'),
                                    'north-america'   => _('North America'),
                                    'south-america'   => _('South America'));

    if ($enhanced) {
      $keys['country']      = Digest::getCountries('simple');
    } else {
      $keys['country']      = Digest::getCountries('basic');
    }

    $keys['microblog_type'] = array('twitter'         => _('twitter.com'),
                                    'identica'        => _('identi.ca'));

    // return...
    if ($context == 'all') {
      // return all
      return $keys;

    } else if ($context == 'category') {
      // return a whole category
      if (isset($keys[$key])) {
        return $keys[$key];
      }

    } else if ($key) {
      // return a single key
      foreach ($keys as $section) {
        if (isset($section[$key])) {
          return $section[$key];
        }
      }

      return $key;
    }

    return false;
  }
}

?>