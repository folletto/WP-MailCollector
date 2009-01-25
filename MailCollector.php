<?php
/*
Plugin Name: MailCollector
Plugin URI: http://digitalhymn.com
Description: Gathers e-mail addresses to be used in ML.
Author: Davide 'Folletto' Casali
Version: 0.1
Author URI: http://digitalhymn.com
******************************************************************************************
* Originally designed for Good50x70.org
* This plugins handlers mail gathering.
* 
* 
* 
*/ 

$cmc_version = "0.1";

// Libs
require_once 'lib/wordpress.portal.php';

// Admin
require_once 'classes/wp-admin.php';


/************************************************************************************ CLASS
 ******************************************************************************************
 * MailCollector Class
 */
class MailCollector {
	
	function form() {
	  /****************************************************************************************************
     * Show form.
     * 
     * @return    array of emails
     */
    $out = '';
    
    $out .= '<label>';
    $out .= '<input type="text" maxlength="50" />';
    $out .= '</label>';
    
    return $out;
  }
	
	function add_mail($email, $notes = "") {
	  /****************************************************************************************************
     * Adds the specified mail to the database.
     * 
     * @param     e-mail string
     * @return    insert ID on success, false on fail
     */
    global $wpdb;
    
    $out = false;
    
    $email = $wpdb->escape($email);
    $notes = $wpdb->escape($notes);
    
    if (!$this->mail_exists($email)) {
      $query = "
  			INSERT INTO " . $wpdb->prefix . "mailcollector
  				(email, notes, timestamp)
  			VALUES
  				('$email', '$notes', NOW())
  	  ";

      $result = $wpdb->query($query);

      if ($result > 0) {
  			$out = mysql_insert_id();
  		}
    }
    
    return $out;
	}
	
	function mail_exists($email) {
	  /****************************************************************************************************
     * Check if a mail exists.
     * 
     * @return    boolean
     */
    global $wpdb;
    $out = array();
    
    $query = 'SELECT email FROM ' . $wpdb->prefix . 'mailcollector As mc
      WHERE
        email = \'' . $email . '\'
    ';
    
    $out = $wpdb->get_results($query);
    
    return sizeof($out) > 0;
  }
	
	function get_list() {
	  /****************************************************************************************************
     * Get the mail list from DB.
     * 
     * @return    array of emails
     */
    global $wpdb;
    $out = array();
    
    $query = 'SELECT * FROM ' . $wpdb->prefix . 'mailcollector As mc
      WHERE
        flag > 0
      ORDER BY
        email ASC
    ';
    
    $out = $wpdb->get_results($query);
    
    return $out;
  }
	
	function get_emails() {
	  /****************************************************************************************************
     * Get the mail list from DB.
     * 
     * @return    array of emails
     */
    $out = array();
    
    $list = $this->get_list();
    
    foreach ($list as $item) {
      $out[] = $item->email;
    }
    
    return $out;
  }
}

/************************************************************************************ SETUP
 ******************************************************************************************
 * MailCollector Setup
 */
register_activation_hook(__FILE__, 'cmc_install');
function cmc_install() {
	global $wpdb;
	global $cmc_version;
	$out = -1;
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	$table_name = $wpdb->prefix . "mailcollector";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE `$table_name` (
		  `mid` int(11) unsigned NOT NULL auto_increment,
		  `email` varchar(50) NOT NULL default '',
			`notes` text,
			`timestamp` datetime default NULL,
			`flag` tinyint(8) NOT NULL default '1',
		  PRIMARY KEY  (`mid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		echo $sql;
		// *** Create table using delta diff (supports upgrades)
		dbDelta($sql);
		
		$out++;
	}
	
	// ****** Completing
	if ($out > 0) {
		add_option("cmc_version", $cmc_version);
	}
	
	return $out;
}

/************************************************************************************** RUN
 ******************************************************************************************
 * ImageContest Runtime
 */
$MailCollector = new MailCollector(); // OBJECT INIT

wpp::add_virtual_page('mailcollector/api/add', array(
	//get_template_directory() . "/mailcollector.php",
	dirname(__FILE__) . "/virtual/api.add.php"  
));



?>