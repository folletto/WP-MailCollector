<?php

add_action('admin_menu', 'mailcollector_admin_menu_hook');
function mailcollector_admin_menu_hook() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('edit.php', __('MailCollector Administration'), __('MailCollector'), 'manage_options', 'mailcollector-administration', 'mailcollector_admin');
}

/******************************************************************************************
 * Administration Panel for ImageContest Plugin
 */
function mailcollector_admin() {
	global $MailCollector;
	
	echo '<div class="wrap">';
	echo '<h2>Mail Collector</h2>';
	
	echo '<h3>Complete list of mails</h2>';
	
	//echo '<ol style="list-style-type: decimal; padding: 0 0 0 30px">';
	//echo '<li>' . join($MailCollector->get_emails(), "</li>\n<li>") . '</li>';
	echo '' . join($MailCollector->get_emails(), ", ") . '';
	//echo '</ol>';
	
	echo '</div>';
}


?>