<?php
/**
 * Plugin Name: Ontraport VisualBuilder
 * Description: Integrate Ontraport membership levels with WPBakery Visual Builder.
 * Plugin URI: https://blackdogdevelopers.com/portfolio/ontraport-visual-composer/
 * Author: Black Dog Developers
 * Author URI: http://blackdogdevelopers.com
 * Version: 1.0
 * Text Domain: bdd-ontraport-visualbuilder
 * Domain Path: languages
 * Licence: GPLv2
 *
 * @package ONTRAPORT_VISUALBUILDER
 */

/*
Copyright (C) 2017  Black Dog Developers  support@blackdogdevelopers.com
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( defined( 'ONTRAPORT_VISUALBUILDER_VERSION' ) ) {
	return;
}

function ontraport_visual_builder() {
	require_once( dirname( __FILE__ ) . '/class-ontraport-visualbuilder.php' );
	return ONTRAPORT_VISUALBUILDER::get_instance();
}

$GLOBALS['ONTRAPORT_VISUALBUILDER'] = ontraport_visual_builder();
