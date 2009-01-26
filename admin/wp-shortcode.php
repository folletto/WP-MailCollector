<?php

// [mailcollectorform destination="path/to/page"]
function mailcollector_form_shortcode($atts, $content = null) {
  global $MailCollector;
  
	$atts = shortcode_atts(array(
		'destination' => '/',
	), $atts);
  
	return $MailCollector->form($atts['destination']);
}
add_shortcode('mailcollectorform', 'mailcollector_form_shortcode');


?>