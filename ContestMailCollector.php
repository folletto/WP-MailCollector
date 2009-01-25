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
require_once 'lib/WPP_PageInject.class.php';

// Admin
require_once 'classes/wp-admin.php';


/************************************************************************************ CLASS
 ******************************************************************************************
 * MailCollector Class
 */
class ContestMailCollector {
	
	
}

/************************************************************************************ SETUP
 ******************************************************************************************
 * MailCollector Setup
 */
register_activation_hook(__FILE__, 'cmc_install');
function cmc_install() {
	global $wpdb;
	global $ic_version;
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
	
	$table_name = $wpdb->prefix . "imagecontest_votes";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE `$table_name` (
		  `vid` int(11) unsigned NOT NULL auto_increment,
			`round` int(11) unsigned NOT NULL,
			`uid` int(11) unsigned NOT NULL,
			`iid` int(11) unsigned NOT NULL,
			`vote` tinyint(8) NOT NULL default '1',
			`timestamp` datetime default NULL,
		  PRIMARY KEY  (`vid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		
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
$ContestMailCollector = new ContestMailCollector(); // OBJECT INIT

$ic_fallback = array(
	TEMPLATEPATH . "/contest.php",
	dirname(__FILE__) . "/templates/template.form.php"
);
$ic_page = new WPP_PageInject('contest', $ic_fallback);

$ic_fallback = array(
	TEMPLATEPATH . "/contest.list.php",
	dirname(__FILE__) . "/templates/template.list.php"
);
$ic_list = new WPP_PageInject('contest/list', $ic_fallback);

$ic_fallback = array(
	dirname(__FILE__) . "/templates/template.ajax.php"
);
$ic_ajax = new WPP_PageInject('contest/ajax', $ic_fallback);

$ic_fallback = array(
	TEMPLATEPATH . "/contest.participants.php",
	dirname(__FILE__) . "/templates/template.participants.php"
);
$ic_reg = new WPP_PageInject('contest/participants', $ic_fallback);

$ic_fallback = array(
	TEMPLATEPATH . "/contest.liveresults.php",
	dirname(__FILE__) . "/templates/template.liveresults.php"
);
$ic_res = new WPP_PageInject('contest/liveresults', $ic_fallback);

$ic_fallback = array(
	TEMPLATEPATH . "/contest.archive.php",
	dirname(__FILE__) . "/templates/template.archive.php"
);
$ic_arc = new WPP_PageInject('contest/archive', $ic_fallback);

$ic_fallback = array(
	TEMPLATEPATH . "/contest.image.php",
	dirname(__FILE__) . "/templates/template.image.php"
);
$ic_image = new WPP_PageInject('contest/image', $ic_fallback);

$ic_fallback = array(
	TEMPLATEPATH . "/contest.top.php",
	dirname(__FILE__) . "/templates/template.top.php"
);
$ic_top = new WPP_PageInject('contest/top', $ic_fallback);


?>