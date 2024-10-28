<?php
/*
Copyright (C) 2017  Black Dog Developers  support@blackdogdevelopers.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'ONTRAPORT_VISUALBUILDER_VERSION', '1.0.0' );
define( 'ONTRAPORT_VISUALBUILDER_FILE', __FILE__ );
define( 'ONTRAPORT_VISUALBUILDER_PATH', plugin_dir_path( ONTRAPORT_VISUALBUILDER_FILE ) );
define( 'ONTRAPORT_VISUALBUILDER_URL', plugin_dir_url( ONTRAPORT_VISUALBUILDER_FILE ) );

register_activation_hook( ONTRAPORT_VISUALBUILDER_FILE, array( 'ONTRAPORT_VISUALBUILDER', 'activate' ) );
register_deactivation_hook( ONTRAPORT_VISUALBUILDER_FILE, array( 'ONTRAPORT_VISUALBUILDER', 'deactivate' ) );

final class ONTRAPORT_VISUALBUILDER {

	/**
	 * Plugin instance.
	 *
	 * @var ONTRAPORT_VISUALBUILDER
	 * @access private
	 */
	private static $instance = null;

	/**
	 * Array of membership levels pulled from ontraport
	 *
	 * @var ONTRAPORT_VISUALBUILDER
	 * @access private
	 */
	private $bdd_opvb_membership_levels;

	/**
	 * Get plugin instance.
	 *
	 * @return ONTRAPORT_VISUALBUILDER
	 * @static
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @access private
	 */
	private function __construct() {
		add_action( 'admin_init', array( $this, 'check_version' ) );

		global $wp_session;

		self::includes();

		// Need to handle missing functions from user perspective.
		if ( ! function_exists( 'get_currentuserinfo' ) ) {
			function get_currentuserinfo() {
				return wp_get_current_user();
			}
		}

		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action('vc_after_init', array($this, 'fetch_membership_levels'), 1);
		add_action('vc_after_init', array($this, 'init_templates'));
	}

	/**
	 * Fetch membership levels from ontraport via pilot press plugin.
	 * Store levels in WordPress transient storage
	 *
	 * @access private
	 */
	public function fetch_membership_levels() {
		$pp = new PilotPress();
		$pp->load_settings();

		$this->bdd_opvb_membership_levels = get_transient( 'bdd_opvb_membership_levels' );

		if ( $this->bdd_opvb_membership_levels ) {
			$op_levels = $pp->get_setting( 'membership_levels', 'oap', true );

			foreach ( $op_levels as $level ) {
				$this->opvb_membership_levels[ $level ] = $level;
			}

			set_transient( 'bdd_opvb_membership_levels', $this->opvb_membership_levels );
		}
	}

	/**
	 * Initialize the plugin templates with hooks into Visual Builder
	 *
	 * @access private
	 */
	public function init_templates() {

		$attributes = array(
			'type' => 'checkbox',
			'heading' => __( 'Show this element to the selected membership levels only.', 'bdd-ontraport-visualbuilder' ),
			'param_name' => 'opvb_allowed_membership',
			'value' => $this->opvb_membership_levels,
			'description' => __( 'Show this element to the selected membership levels only.', 'bdd-ontraport-visualbuilder' ),
			'group' => 'Ontraport',
		);

		vc_add_param( 'vc_row_inner', $attributes );
		vc_add_param( 'vc_column', $attributes );

		$dir = plugin_dir_path( __FILE__ ) . 'templates';
		vc_set_shortcodes_templates_dir( $dir );
	}

	/**
	 * Check if the current user has access to the page element
	 *
	 * @param string comma separated list of membership levels that have access to the page element
	 * @access public
	 */
	public static function is_visible( $levels ) {
		$allowed = false;
		if ( strlen( $levels ) === 0 ) {
			return true; // There is no levels specified so show it to all.
		} else {
			$levels = explode( ',', $levels );
		}

		// Look through every level selected and check to see
		if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
			$allowed = true; // Editors & Admins can see it always
		}

		if ( is_array( $_SESSION['membership_level'] ) ) {
			foreach ( $levels as $level ) {
				if ( in_array( $level, $_SESSION['membership_level'], true ) ) {
					$allowed = true;
				}
			}
		}

		return $allowed;
	}

	/**
	 * Find an appopriate template for the page element
	 *
	 * @param string Name of the page element
	 * @access public
	 */
	public static function template_source( $template ) {
		// Check theme for template
		$source = locate_template( 'vc_templates' . '/' . $template . '.php' );

		if ( ! $source ) {
			// use default if theme template not found
			$vc = Vc_Manager::getInstance();
			$default_path = $vc->getDefaultShortcodesTemplatesDir();
			$source = $default_path . '/' . $template . '.php';
		}

		return $source;
	}

	/**
	 * Load plugin function files here.
	 */
	public function includes() {
		require_once( ABSPATH . 'wp-includes/pluggable.php' );
		require_once( ABSPATH . 'wp-content/plugins/pilotpress/pilotpress.php' );
	}

	/**
	 * Code you want to run when all other plugins loaded.
	 */
	public function init() {
		load_plugin_textdomain( 'bdd-ontraport-visualbuilder', false, ONTRAPORT_VISUALBUILDER_PATH . 'languages' );
	}

	/**
	 * Run when activate plugin.
	 */
	public static function activate() {
	}

	/**
	 * Run when deactivate plugin.
	 */
	public static function deactivate() { }

	/**
	 * Check to see if the plugin can work if not deactivate it
	 *
	 * @access public
	 */
	public static function check_version() {
		if ( ! self::compatible_version() ) {
			if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
				deactivate_plugins( plugin_basename( __FILE__ ) ); // Turn ourself off
				add_action( 'admin_notices', array( $this, 'disabled_notice' ) ); // Display a message that it's not valid

				// Stop WP from displaying "Plugin Activated" message
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}
	}

	/**
	 * Build the message to display in admin if plugin is disabled
	 *
	 * @access public
	 */
	function disabled_notice() {
		// Setup Visual Composer URL
		$vcname = __( 'Visual Composer: Page Builder for WordPress', 'bdd-ontraport-visualbuilder' );
		$vcurl = esc_url( 'https://codecanyon.net/item/visual-composer-page-builder-for-wordpress/242431' );
		/* translators: %1$s: Visual Composer plugin URL
		   translators: %2$s: Name of Visual Composer */
		$vclink = sprintf( __( '<a href="%1$s" target="_blank">%2$s</a>', 'bdd-ontraport-visualbuilder' ), $vcurl, $vcname );

		// Setup PilotPress URL
		$ppname = __( 'PilotPress', 'bdd-ontraport-visualbuilder' );
		$ppurl = esc_url( 'https://wordpress.org/plugins/pilotpress/' );
		/* translators: %1$s: Pilot Press plugin URL
		   translators: %2$s: Name of PilotPress */
		$pplink = sprintf( __( '<a href="%1$s" target="_blank">%2$s</a>', 'bdd-ontraport-visualbuilder' ), $ppurl, $ppname );

		// Build error message
		/* translators: %1$s: Visual Composer link
		   translators: %2$s: PilotPress link */
		$disabled_notice = sprintf( __( 'The Ontraport Visual Composer Plugin requires both %1$s and %2$s!', 'bdd-ontraport-visualbuilder' ), $vclink, $pplink );

		$error_message = '<div class="notice notice-error is-dismissible"><p>' . $disabled_notice . '</p></div>';

		// Echo error message
		$allowed_tags = wp_kses_allowed_html( 'post' );
		echo wp_kses( $error_message, $allowed_tags );
	}

	/**
	 * Check to see if the plugin dependencies are active
	 *
	 * @access public
	 */
	static function compatible_version() {
		$valid = true;

		// Check for Visual Composer
		if ( is_plugin_active( 'js_composer/js_composer.php' ) ) {
			$valid = false;
		}

		// Check for Pilot Press
		if ( ! is_plugin_active( 'pilotpress/pilotpress.php' ) ) {
			$valid = false;
		}

		// Add sanity checks for other version requirements here
		return $valid;
	}
}
