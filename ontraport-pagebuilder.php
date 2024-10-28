<?php
/**
 * Plugin Name: Ontraport Page Builder
 * Description: Integrate Ontraport membership levels with WPBakery Page Builder.
 * Plugin URI: https://blackdogdevelopers.com/portfolio/ontraport-page-builder/
 * Author: Black Dog Developers
 * Author URI: http://blackdogdevelopers.com
 * Version: 1.0
 * Text Domain: bdd-ontraport-pagebuilder
 * Domain Path: languages
 * Licence: GPLv2
 *
 * @package ONTRAPORT_PAGEBUILDER
 */

/*
Copyright (C) 2017  Black Dog Developers  support@blackdogdevelopers.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'ONTRAPORT_PAGEBUILDER_VERSION' ) ) {
	return;
}

function ontraport_page_builder() {
	require_once( dirname( __FILE__ ) . '/class-ontraport-pagebuilder.php' );
	return ONTRAPORT_PAGEBUILDER::get_instance();
}

$GLOBALS['ONTRAPORT_PAGEBUILDER'] = ontraport_page_builder();
