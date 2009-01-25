<?php
header("Content-Type: text/javascript");

$out = '';

/******************************************************************************************
 * Tinker
 ******************************************************************************************/
if (isset($_REQUEST['email'])) {
  if ($MailCollector->add_mail($_REQUEST['email'])) {
    $out .= 'status: ok';
  } else {
    if ($MailCollector->mail_exists($_REQUEST['email'])) {
      $out .= 'error: "Mail already exists.", ';
      $out .= 'status: ko';
    } else {
      $out .= 'error: "Unable to add the e-mail to the db.", ';
      $out .= 'status: ko';      
    }
  }  
} else {
  $out .= 'error: "No e-mail given.", ';
  $out .= 'status: ko';
}

/******************************************************************************************
 * Output
 ******************************************************************************************/
$out = '{' . $out . '}';
echo $out;
?>