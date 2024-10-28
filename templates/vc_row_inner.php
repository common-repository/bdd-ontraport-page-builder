<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
$template = basename( __FILE__ );

if ( ONTRAPORT_VISUALBUILDER::is_visible( $atts['opvb_allowed_membership'] ) ) {
	include( ONTRAPORT_VISUALBUILDER::template_source( $this->getShortcode() ) );
}
