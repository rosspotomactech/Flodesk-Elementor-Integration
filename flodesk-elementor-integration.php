<?php
/**
 * Plugin Name: Flodesk Integration for Elementor Forms
 * Description: Integrates Elementor Pro Forms with Flodesk REST API, including field mapping and conditional segment assignment.
 * Version: 1.0.1
 * Tested up to: 7.0.2
 * Requires PHP: 7.4
 * Author: Potomac Technologies, LLC
 * Author URI: https://potomactech.net
 * Text Domain: flodesk-elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'FLODESK_ELEMENTOR_PATH', plugin_dir_path( __FILE__ ) );

// Check GitHub for new releases and install them through the WordPress updater
$puc_file = FLODESK_ELEMENTOR_PATH . 'plugin-update-checker/plugin-update-checker.php';

if ( file_exists( $puc_file ) ) {
	require_once $puc_file;

	$flodeskUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/rosspotomactech/Flodesk-Elementor-Integration/',
		__FILE__,
		'flodesk-elementor-integration'
	);

	// Install updates from the release ZIP built by the GitHub Action
	$flodeskUpdateChecker->getVcsApi()->enableReleaseAssets();
}

// Load classes
require_once FLODESK_ELEMENTOR_PATH . 'includes/class-flodesk-api.php';
require_once FLODESK_ELEMENTOR_PATH . 'includes/class-flodesk-settings.php';

// Initialize Admin Settings Page
if ( is_admin() ) {
	new Flodesk_Settings();
}

/**
 * Register Elementor Pro Form Action
 */
add_action( 'elementor_pro/forms/actions/register', function( $form_actions_registrar ) {
	require_once FLODESK_ELEMENTOR_PATH . 'includes/class-flodesk-elementor-action.php';
	$form_actions_registrar->register( new Flodesk_Elementor_Action() );
} );
