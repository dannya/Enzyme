<?php

class Email {
  public $to       = null;
  public $from     = null;

  public $subject  = null;
  public $message  = null;
  public $isHtml   = false;


  public function __construct(array $to, $subject, $message, $isHtml = false, $from = null) {
    // check that a SMTP server has been set
    if (!defined('SMTP')) {
      trigger_error(_('SMTP mail server has not been set!'));
      return false;
    }


    // set values
    $this->to       = $to;

    if ($from) {
      $this->from   = $from;
    } else {
      $this->from   = 'no-reply@' . str_replace('http://', null, ENZYME_URL);
    }

    $this->subject  = $subject;
    $this->message  = $message;
    $this->isHtml   = $isHtml;


    // setup SMTP server
    ini_set('SMTP',           SMTP);
    ini_set('sendmail_from',  $this->from);
    ini_set('smtp_port',      25);


    // process address(es)
    if (isset($this->to[0])) {
      // multiple
      foreach ($this->to as $name => $address) {
        $this->fullAddresses[] = $name . ' <' . $address . '>';
        $this->addresses[]     = $address;
      }

    } else {
      // single
      $this->fullAddresses[] = $this->to['name'] . ' <' . $this->to['address'] . '>';
      $this->addresses[]     = $this->to['address'];
    }
  }


  public function send() {
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
      $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    }

    // set headers
    $headers .= 'To: ' . App::implode(', ', $this->fullAddresses) . "\r\n";
    $headers .= 'From: ' . APP_NAME . ' <' . $this->from . '>' . "\r\n";

    // send email
    return mail(App::implode(', ', $this->addresses), $this->subject, $this->message, $headers);
  }
}

?>