<?php
header("Content-Type: text/javascript");

$out = '';

/******************************************************************************************
 * Tinker
 ******************************************************************************************/
$destination = $_REQUEST['destination'];
if (strpos($destination, "http://") !== 0) {
  $destination = get_bloginfo('url') . $destination;
}
header('Location: ' . $destination);

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

$out = '<!--' . $out . '-->';
$out = '<html><head><title>Redirect...</title><meta http-equiv="refresh" content="0;url=' . $destination . '" /></head><body></body></html>';

/******************************************************************************************
 * Output
 ******************************************************************************************/
$out = '{' . $out . '}';
echo $out;
?>