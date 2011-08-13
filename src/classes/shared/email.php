<?php

/*-------------------------------------------------------+
 | PHPzy (Web Application Framework)
 | Copyright 2010-2011 Danny Allen <me@dannya.com>
 | http://www.dannya.com/
 +--------------------------------------------------------+
 | This program is released as free software under the
 | Affero GPL license. You can redistribute it and/or
 | modify it under the terms of this license which you
 | can read by viewing the included agpl.txt or online
 | at www.gnu.org/licenses/agpl.html. Removal of this
 | copyright header is strictly prohibited without
 | written permission from the original author(s).
 +--------------------------------------------------------*/


class Email {
  public $to       = null;
  public $from     = null;

  public $subject  = null;
  public $message  = null;
  public $isHtml   = false;

  private $send    = true;


  public function __construct(array $to, $subject, $message, $isHtml = false, $from = null) {
    // check that a SMTP server has been set
    if (!Config::getSetting('enzyme', 'SMTP')) {
      trigger_error(_('SMTP mail server has not been set!'));
      return false;
    }


    // set values
    $this->to       = $to;

    if ($from) {
      $this->from   = $from;
    } else {
      $this->from   = 'no-reply@' . str_replace('http://', null, Config::getSetting('enzyme', 'ENZYME_URL'));
    }

    $this->subject  = $subject;
    $this->message  = $message;
    $this->isHtml   = $isHtml;


    // setup SMTP server
    ini_set('SMTP',           Config::getSetting('enzyme', 'SMTP'));
    ini_set('sendmail_from',  $this->from);
    ini_set('smtp_port',      25);


    // process address(es)
    if (isset($this->to[0])) {
      // multiple:
      foreach ($this->to as $name => $address) {
        // check that email is valid
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
          $this->send = false;
          return false;
        }

        $this->fullAddresses[] = $name . ' <' . $address . '>';
        $this->addresses[]     = $address;
      }

    } else {
      // single:
      // check that email is valid
      if (!filter_var($this->to['address'], FILTER_VALIDATE_EMAIL)) {
        $this->send = false;
        return false;
      }

      $this->fullAddresses[] = $this->to['name'] . ' <' . $this->to['address'] . '>';
      $this->addresses[]     = $this->to['address'];
    }
  }


  public function send() {
    if (!$this->send) {
      return false;
    }

    // create HTML page structure around message?
    if ($this->isHtml && (stripos($this->message, '<html') === false)) {
      $this->message = '<html>
                        <head>
                          <title>' . $this->subject . '</title>
                        </head>
                        <body>' .
                          $this->message .
                       '</body>
                        </html>';
    }

    $headers = null;

    // HTML mail needs content-type header set
    if ($this->isHtml) {
      $headers .= 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    }

    // set headers
    $headers .= 'To: ' . App::implode(', ', $this->fullAddresses) . "\r\n";
    $headers .= 'From: ' . Config::$app['name'] . ' <' . $this->from . '>' . "\r\n";

    // send email
    return mail(App::implode(', ', $this->addresses), $this->subject, $this->message, $headers);
  }
}

?>