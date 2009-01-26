<?php

// [mailcollectorform destination="path/to/page"]
function mailcollector_form_shortcode($atts, $content = null) {
  global $MailCollector;
  
	$atts = shortcode_atts(array(
		'destination' => '/',
		'button' => 'Get it',
	), $atts);
  
	return $MailCollector->form($atts['destination'], $atts['button']);
}
add_shortcode('mailcollectorform', 'mailcollector_form_shortcode');


?>