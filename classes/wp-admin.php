<?php

add_action('admin_menu', 'mailcollector_admin_menu_hook');
function mailcollector_admin_menu_hook() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('edit.php', __('MailCollector'), __('ImageContest'), 'manage_options', 'imagecontest-administration', 'imagecontest_admin');
}

/******************************************************************************************
 * Administration Panel for ImageContest Plugin
 */
function imagecontest_admin() {
	global $ImageContest;
	
	echo '<div class="wrap">';
	echo '<h2>Mail Collector for Good50x70</h2>';
	
	echo '<h3>Mail lists</h2>';
	
	
	echo '</div>';
}


?>