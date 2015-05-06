<?php
/**
 * Official LiveDive WordPress plugin
 * @package LiveDive
 * @version 1.0.0
 */
/*
Plugin Name: UX Screensharing & Chat by LiveDive
Plugin URI: http://wordpress.org/extend/plugins/livedive/?utm_source=wp-plugin&utm_medium=link&utm_campaign=plugins
Description: This plugin connects your site with LiveDive, the insanely easy way to talk to your site visitors and watch them use your site live.
Author: LiveDive
Version: 1.0
Author URI: http://livedive.co/
License: GPLv2
*/

/*  Copyright 2015 LiveDive (email : team@livedive.co)

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

class WP_LiveDive {
	// var $options_name = "livedive_options";
	var $options;
	var $options_name = "livedive_options";
	var $settings_path;
	var $namespace;
	var $plugin_path;

	public function __construct() {
		$this->namespace = "livedive";
		$this->plugin_path = plugin_dir_path( __FILE__ );

		$this->settings_path = 'options-general.php?page=' . $this->namespace;
		
		// Fetch options
		$this->options = get_option( $this->options_name );

		// insert frontend code
		add_action( 'wp_footer', array( &$this, 'livedive_frontend_code' ) );

		// Activation and Deactivation
		register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
		
		// Options page for configuration
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

		// Register admin settings
		add_action( 'admin_init', array( &$this, 'admin_register_settings' ) );
		
		// Register activation redirect
		add_action( 'admin_init', array( &$this, 'do_activation_redirect' ) );
		
		// Add settings link on plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'plugin_action_links' ) );
	}

	// ------------------- //
	// -- FRONTEND CODE -- //
	// ------------------- //

	/**
	 * Include the LiveDive JavaScript snippet or show set up message
	 */
	public function livedive_frontend_code() {
		// Check if the ID is set and is an integer
		// include( plugin_dir_path( __FILE__ ) . "/code/frontend.php" );
		// include( plugins_url( "/code/frontend.php", __FILE__ ) );

		// $site_id = '62e2d5581ecc46a5875831ff9694d268';
		// include( plugin_dir_path( __FILE__ ) . "/code/frontend.php" );
		
		if ( ! $this->get_option( 'is_disabled' ) ) {
			if ( $this->get_option( 'livedive_site_id' ) ) { 
				$site_id = $this->get_option( 'livedive_site_id' );
				include( plugin_dir_path( __FILE__ ) . "/code/frontend.php" );
			} else {
				echo '<!-- Please set your LiveDive Site ID -->';
			}
		}
	}


	// ------------------- //
	// -- SETTINGS PAGE -- //
	// ------------------- //

	/**
	 * Sets default options upon activation
	 *
	 * Hook into register_activation_hook action
	 *
	 * @uses update_option()
	 */
	public function activate() {
		// Set default options
		if ( ! isset( $this->options['livedive_site_id'] ) ) { $this->options['livedive_site_id'] = ""; }
		
		// Redirect to settings page
		$this->options['do_redirect'] = true;

		// Save options
		update_option( $this->options_name, $this->options );
		
		// Redirect to settings page
		wp_redirect($this->settings_path);
	}
	
	/**
	 * Clean up after deactivation
	 *
	 * Hook into register_deactivation_hook action
	 */
	public function deactivate() {
		// Deactivation stuff here...
	}

	/**
	 * Performs a redirect to the settings page if the flag is set.
	 * To be called on admin_init action.
	 *
	 * @uses wp_redirect()
	 */
	public function do_activation_redirect() {
		if ( $this->get_option( 'do_redirect' ) ) {
			// Prevent future redirecting
			$this->delete_option( 'do_redirect' );
			
			// Only redirect if it's a single activation
			if( ! isset( $_GET['activate-multi'] ) ) {
				wp_redirect( $this->settings_path );
			}
		}
	}

	/**
	 * Define the admin menu options for this plugin
	 * 
	 * @uses add_action()
	 * @uses add_options_page()
	 */
	public function admin_menu() {
		$page_hook = add_options_page( 'LiveDive Settings', $this->friendly_name, 'manage_options', $this->namespace, array( &$this, 'admin_options_page' ) );
		
		// Add admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
	}
	
	/**
	 * The admin section options page rendering method
	 * 
	 * @uses current_user_can()
	 * @uses wp_die()
	 */
	public function admin_options_page() {
		// Ensure the user has sufficient permissions
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		include( plugin_dir_path( __FILE__ ) . "/code/options.php" );
	}
	
	/**
	 * Add links on the plugin page
	 *
	 * @param array $links An array of existing action links
	 * 
	 * @uses current_user_can()
	 * @return array Returns the new array of links
	 */
	public function plugin_action_links( $links ) {
		// Ensure the user has sufficient permissions
		if ( current_user_can( 'manage_options' ) )  {
			$settings_link = '<a href="' . $this->settings_path . '">Settings</a>';
			array_unshift($links, $settings_link);
		}
		
		return $links;
	}

	/**
	 * Register all the settings for the options page (Settings API)
	 *
	 * @uses register_setting()
	 * @uses add_settings_section()
	 * @uses add_settings_field()
	 */
	public function admin_register_settings() {
		register_setting( $this->options_name, $this->options_name, array( &$this, 'validate_settings' ) );
		add_settings_section( 'livedive_code_settings', 'LiveDive Site Settings', array( &$this, 'admin_section_code_settings' ), $this->namespace );
		add_settings_field( 'livedive_site_id', 'Site ID', array( &$this, 'admin_option_site_id' ), $this->namespace, 'livedive_code_settings' );
		add_settings_field( 'livedive_is_disabled', 'Disabled', array( &$this, 'admin_option_is_disabled' ), $this->namespace, 'livedive_code_settings' );
	}

	/**
	 * Validates user supplied settings and sanitizes the input
	 *
	 * @param array $input The set of option parameters
	 *
	 * @return array Returns the set of sanitized options to save to the database
	 */
	public function validate_settings( $input ) {
		$options = $this->options;
		
		if ( isset( $input['site_id'] ) ) {
			// Remove padded whitespace
			$site_id = trim( $input['site_id'] );
			
			// Only allow a proper siteid (UUID with dashes removed) or a blank string
			if ( $site_id == "" || preg_match("/^[0-9A-F]{8}[0-9A-F]{4}4[0-9A-F]{3}[89AB][0-9A-F]{3}[0-9A-F]{12}$/i", $site_id)) {
				$options['livedive_site_id'] = $site_id;
			} else {
				add_settings_error( 'site_id', $this->namespace . '_site_id_error', "Please enter a valid site ID", 'error' );
			}

			// Only allow an integer or blank string
			// if ( is_int( $site_id ) || ctype_digit( $site_id ) || $site_id == "" ) {
			// 	$options['site_id'] = $site_id;
			// } else {
			// 	add_settings_error( 'site_id', $this->namespace . '_site_id_error', "Please enter a valid site ID", 'error' );
			// }
		}

		if ( isset( $input['is_disabled'] ) ) {
			$options['is_disabled'] = $input['is_disabled'] == "1";
		} else {
			$options['is_disabled'] = false;
		}
		
		return $options;
	}
	
	/** 
	 * Output the input for the site ID option
	 */
	public function admin_option_site_id() {
		echo "<input type='text' name='livedive_options[site_id]' size='20' value='{$this->get_option( 'livedive_site_id' )}'>";
	}
	
	/** 
	 * Output the input for the disabled option
	 */
	public function admin_option_is_disabled() {
		echo "<input type='checkbox' name='livedive_options[is_disabled]' value='1' " . 
			checked( 1, $this->get_option( 'is_disabled' ), false ) . " /> " .
			"Disable tracking code on all pages";
	}
	
	/** 
	 * Output the description for the Tracking Code settings section
	 */
	public function admin_section_code_settings() {
		echo '<p>Enter your site ID in the form below to install your tracking code.</p>';
		echo '<p><em>Where do I find my ID?</em> <a href="https://admin.livedive.co/settings/siteid" target="_new">Get my Site ID</a></p>';
		// echo '<p>Your site ID can be found in your <a href="https://admin.livedive.co/settings/siteid">Account Settings</a></p>';
	}

	/**
	 * Load stylesheet for the admin options page
	 * 
	 * @uses wp_enqueue_style()
	 */
	function admin_enqueue_scripts() {
		wp_enqueue_style( "{$this->namespace}_admin_css", plugins_url( "/css/admin.css", __FILE__ ) );
	}



	/**
	 * Lookup an option from the options array
	 *
	 * @param string $key The name of the option you wish to retrieve
	 *
	 * @return mixed Returns the option value or NULL if the option is not set or empty
	 */
	public function get_option( $key ) {
		if ( isset( $this->options[ $key ] ) && $this->options[ $key ] != "" ) {
			return $this->options[ $key ];
		} else {
			return NULL;
		}
	}
	
	/**
	 * Deletes an option from the options array
	 *
	 * @param string $key The name of the option you wish to delete
	 *
	 * @uses update_option()
	 */
	public function delete_option( $key ) {
		unset( $this->options[ $key ] );
		update_option( $this->options_name, $this->options );
	}
	
	/**
	 * Lookup the site ID from the options array
	 *
	 * @return mixed Returns the site ID or NULL if it is not set or empty
	 */
	public function site_id() {
		return $this->get_option( 'livedive_site_id' );
	}




	static function instance() {
		global $WP_LiveDive;
		
		// Only instantiate the Class if it hasn't been already
		if( ! isset( $WP_LiveDive ) ) $WP_LiveDive = new WP_LiveDive();
	}
}

if( !isset( $WP_LiveDive ) ) {
	WP_LiveDive::instance();
}

?>