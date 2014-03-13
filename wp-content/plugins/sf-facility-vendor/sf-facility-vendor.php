<?php
/*
Plugin Name: Facility Vendor (Plugin for AFS)
Version: 1.0
Plugin URI: http://studiofidelis.com/
Description: Adds contactor and vendor functionality. 
Author: StudioFidelis.com / Daniel Schuring
Author URI: http://studiofidelis.com/
Plugin Author: Daniel Schuring
Plugin Author URI: http://studiofidelis.com/
Text Domain: facility-vendor
Domain Path: /lang
*/

define ('SF_FV_URL', plugins_url( '', __FILE__) );
define( 'SF_FV_PATH', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) );

//Setup Cron
register_activation_hook( __FILE__, 'sf_fv_activation_cron' );
function sf_fv_activation_cron() {
	wp_schedule_event( time(), 'hourly', 'sf_fv_hourly_event' );
}
register_deactivation_hook( __FILE__, 'sf_fv_deactivation_cron' );
function sf_fv_deactivation_cron() {
	wp_clear_scheduled_hook( 'sf_fv_hourly_event' ); //remove it
}

// Run the load plugin function
load_sf_fv_plugin();

function load_sf_fv_plugin() {
	
	// Base class
	require_once SF_FV_PATH.'/SF_FV.class.php'; //the base class
	
	// Models
	require_once SF_FV_PATH.'/classes/SF_Facility_Post_Type.php';
	require_once SF_FV_PATH.'/classes/SF_Contractor_Post_Type.php';
	require_once SF_FV_PATH.'/classes/SF_Project_Post_Type.php';
	require_once SF_FV_PATH.'/classes/SF_Message_Post_Type.php';
	require_once SF_FV_PATH.'/classes/SF_Proposal_Post_Type.php';
	require_once SF_FV_PATH.'/classes/SF_Endorsements.php';
	require_once SF_FV_PATH.'/classes/SF_Taxonomies.php'; //Load taxonomies after all Post Types are loaded
	
	// Controllers
	require_once SF_FV_PATH.'/classes/SF_Settings.php';
	require_once SF_FV_PATH.'/classes/SF_Users.php'; 
	require_once SF_FV_PATH.'/classes/SF_Project_Forms.php';
	require_once SF_FV_PATH.'/classes/SF_Message_Forms.php';
	require_once SF_FV_PATH.'/classes/SF_Proposal_Forms.php';
	require_once SF_FV_PATH.'/classes/SF_Search.php';
	require_once SF_FV_PATH.'/classes/SF_Tasks.php';
	
	
	//Template tags
	require_once SF_FV_PATH.'/library/template-tags.php';
	
	// Initialize
	SF_FV::init(); //base class init
	
	SF_Settings::init();
	SF_Facility::init();
	SF_Contractor::init();
	SF_Project::init();
	SF_Message::init();
	SF_Proposal::init();
	SF_Endorsements::init();
	SF_Users::init();
	SF_Taxonomies::init();
	SF_Project_Forms::init();
	SF_Message_Forms::init();
	SF_Proposal_Forms::init();
	SF_Search::init();
	SF_Tasks::init();
	
}
