<?php
/**
 * Plugin Name: AddStars
 * Version: 1.0.2
 * Requires at least: 4.6
 * Requires PHP: 5.2
 * Tested up to: 5.9
 * Author: AddStars
 * Author URI: http://www.addstars.com/
 * Description: Allows you to insert the AddStars code in the header of your WordPress blog
 * License: GPLv2 or later
 */

/*  Copyright 2022 AddStars

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Insert Headers and Footers Class
 */
class InsertAdstarsHeader {

	static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new InsertAdstarsHeader();
		}
		return self::$instance;
	}
	/**
	 * Constructor
	 */
	public function __construct() {
		$file_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );

		// Plugin Details
		$this->plugin                           = new stdClass;
		$this->plugin->name                     = 'addstars'; // Plugin Folder
		$this->plugin->displayName              = 'AddStars'; // Plugin Name
		$this->plugin->version                  = $file_data['Version'];
		$this->plugin->folder                   = plugin_dir_path( __FILE__ );
		$this->plugin->url                      = plugin_dir_url( __FILE__ );
		$this->plugin->db_welcome_dismissed_key = $this->plugin->name . '_welcome_dismissed_key';
		$this->body_open_supported              = function_exists( 'wp_body_open' ) && version_compare( get_bloginfo( 'version' ), '5.2', '>=' );
		$this->pre_script 						= 'https://app.addstars.io/get/reviews?id=';
		$this->post_script 						= ' defer';

		// Hooks
		add_action( 'plugins_loaded', array( $this, 'requireAdmin' ) ); 
		add_action( 'admin_init', array( &$this, 'registerSettings' ) );
		add_action( 'admin_menu', array( &$this, 'adminPanelsAndMetaBoxes' ) );
		add_action( 'admin_notices', array( &$this, 'dashboardNotices' ) );
		add_action( 'wp_ajax_' . $this->plugin->name . '_dismiss_dashboard_notices', array( &$this, 'dismissDashboardNotices' ) );

		// Frontend Hooks
		add_action( 'wp_head', array( &$this, 'frontendHeader' ) );
		}
	
	/**
	 * Require the admin files.
	 *
	 * @return void
	 */
	function requireAdmin() {
		if ( ! is_admin() ) {
			// Only load in admin section.
			return;
		}
		/* require_once $this->plugin->folder . 'inc/admin/class-review.php'; */
	}

	/**
	 * Show relevant notices for the plugin
	 */
	function dashboardNotices() {
		global $pagenow;

		if (
			! get_option( $this->plugin->db_welcome_dismissed_key )
			&& current_user_can( 'manage_options' )
		) {
			if ( ! ( 'options-general.php' === $pagenow && isset( $_GET['page'] ) && 'addstars' === $_GET['page'] ) ) {
				$setting_page = admin_url( 'options-general.php?page=' . $this->plugin->name );
				// load the notices view
				include_once( $this->plugin->folder . '/views/dashboard-notices.php' );
			}
		}
	}

	/**
	 * Dismiss the welcome notice for the plugin
	 */
	function dismissDashboardNotices() {
		check_ajax_referer( $this->plugin->name . '-nonce', 'nonce' );
		// user has dismissed the welcome notice
		update_option( $this->plugin->db_welcome_dismissed_key, 1 );
		exit;
	}

	/**
	 * Register Settings
	 */
	function registerSettings() {
		register_setting( $this->plugin->name, 'ihaf_insert_header', 'trim' );
	}

	/**
	 * Register the plugin settings panel
	 */
	function adminPanelsAndMetaBoxes() {
		add_submenu_page( 'options-general.php', $this->plugin->displayName, $this->plugin->displayName, 'manage_options', $this->plugin->name, array( &$this, 'adminPanel' ) );
	}

	/**
	 * Output the Administration Panel
	 * Save POSTed data from the Administration Panel into a WordPress option
	 */
	function adminPanel() {
		/*
		 * Only users with manage_options can access this page.
		 *
		 * The capability included in add_settings_page() means WP should deal
		 * with this automatically but it never hurts to double check.
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Sorry, you are not allowed to access this page.', 'addstars' ) );
		}

		// only users with `unfiltered_html` can edit scripts.
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			$this->errorMessage = '<p>' . __( 'Sorry, only have read-only access to this page. Ask your administrator for assistance editing.', 'addstars' ) . '</p>';
		}

		// Save Settings
		if ( isset( $_REQUEST['submit'] ) ) {
			// Check permissions and nonce.
			if ( ! current_user_can( 'unfiltered_html' ) ) {
				// Can not edit scripts.
				wp_die( __( 'Sorry, you are not allowed to edit this page.', 'addstars' ) );
			} elseif ( ! isset( $_REQUEST[ $this->plugin->name . '_nonce' ] ) ) {
				// Missing nonce
				$this->errorMessage = __( 'nonce field is missing. Settings NOT saved.', 'addstars' );
			} elseif ( ! wp_verify_nonce( $_REQUEST[ $this->plugin->name . '_nonce' ], $this->plugin->name ) ) {
				// Invalid nonce
				$this->errorMessage = __( 'Invalid nonce specified. Settings NOT saved.', 'addstars' );
			} else {
				// Save
				// $_REQUEST has already been slashed by wp_magic_quotes in wp-settings 
				// so do nothing before saving
				update_option( 'ihaf_insert_header', sanitize_text_field($_REQUEST['ihaf_insert_header']) );
				update_option( $this->plugin->db_welcome_dismissed_key, 1 );
				$this->message = __( 'Settings Saved.', 'addstars' );
			}
		}

		// Get latest settings
		$this->settings = array(
			'ihaf_insert_header' => esc_html( wp_unslash( get_option( 'ihaf_insert_header' ) ) ),
		);

		// Load Settings Form
		include_once( $this->plugin->folder . '/views/settings.php' );
	}

	/**
	 * Outputs script / CSS to the frontend header
	 */
	function frontendHeader() {
	
		wp_enqueue_script('addstars', sanitize_text_field($this->pre_script.get_option( 'ihaf_insert_header' )), array(), NULL, false );

		return;
	}

}

/**
 * Instantiate the class a single time.
 *
 * @since 1.6.1
 */
function insert_addstars_header() {
	return InsertAdstarsHeader::get_instance();
}

insert_addstars_header();