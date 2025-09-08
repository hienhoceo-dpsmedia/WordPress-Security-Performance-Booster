<?php
/**
 * WordPress Security & Performance Booster
 * 
 * A comprehensive WordPress plugin that enhances security and performance by:
 * - Disabling WordPress updates (core, plugins, themes)
 * - Blocking spam (comments, pingbacks, trackbacks, XML-RPC)
 * - Reducing server load and cleaning notification spam
 * - Providing multi-language support (Vietnamese, German, French)
 * - Offering modern admin interface with selective feature control
 *
 * @package WordPress_Plugins
 * @subpackage WP_Security_Performance_Booster
 * @version 1.0.2
 * @author HỒ QUANG HIỂN <hello@dps.media>
 * @copyright 2024 DPS.MEDIA
 * @license GPL-2.0-or-later
 * @link https://dps.media/
 * 
 * @wordpress-plugin
 * Plugin Name: WordPress Security & Performance Booster
 * Plugin URI:  https://github.com/hienhoceo-dpsmedia/WordPress-Security-Performance-Booster
 * Description: Comprehensive security and performance enhancement plugin that disables updates, prevents spam (comments, pingbacks, trackbacks, XML-RPC), reduces server load, and cleans notification spam. Perfect for expert users and development environments.
 * Version:     1.0.2
 * Author:      HỒ QUANG HIỂN
 * Author URI:  https://dps.media/
 * Text Domain: wp-security-performance-booster
 * Domain Path: /languages
 * Requires at least: 4.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Network: false
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Developer: HỒ QUANG HIỂN
 * Company: DPS.MEDIA
 * Email: hello@dps.media
 * Website: dps.media
 * Support: dps.media/support
 * 
 * Copyright 2024 HỒ QUANG HIỂN (email: hello@dps.media)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * Define the plugin version
 */
if ( ! defined( 'WPSPB_VERSION' ) ) {
    define( 'WPSPB_VERSION', '1.0.2' );
}

/**
 * Define plugin path
 */
if ( ! defined( 'WPSPB_PLUGIN_PATH' ) ) {
	define( 'WPSPB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * Define plugin URL
 */
if ( ! defined( 'WPSPB_PLUGIN_URL' ) ) {
	define( 'WPSPB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}


/**
 * Main Plugin Class
 *
 * The WP_Security_Performance_Booster class
 *
 * @package WordPress_Security_Performance_Booster
 * @since   1.0.0
 * @author  HỒ QUANG HIỂN <hello@dps.media>
 */
class WP_Security_Performance_Booster {
	
	/**
	 * Plugin instance
	 *
	 * @var WP_Security_Performance_Booster
	 * @since 1.0.0
	 */
	private static $instance = null;
	
	/**
	 * Get plugin instance
	 *
	 * @since 1.0.0
	 * @return WP_Security_Performance_Booster
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * The WP_Security_Performance_Booster class constructor
	 * initializing required stuff for the plugin
	 *
	 * @since 1.0.0
	 * @author HỒ QUANG HIỂN <hello@dps.media>
	 */
	private function __construct() {
		// Check WordPress version compatibility
		if ( ! $this->is_wordpress_version_compatible() ) {
			add_action( 'admin_notices', array( $this, 'wordpress_version_notice' ) );
			return;
		}
		
		// Check PHP version compatibility
		if ( ! $this->is_php_version_compatible() ) {
			add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
			return;
		}
		
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		
		// Debug logging for plugin initialization
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Plugin initialization hooks added' );
		}
		
		// Performance optimization
		add_action( 'admin_init', array( $this, 'optimize_admin_performance' ), 1 );
		
        // Activation/deactivation hooks are registered at file scope via wrappers
        // to ensure they are always available during activation.
	}
	
	/**
	 * Check WordPress version compatibility
	 *
	 * @since 1.0.0
	 * @return bool True if compatible, false otherwise
	 */
	private function is_wordpress_version_compatible() {
		global $wp_version;
		return version_compare( $wp_version, '4.0', '>=' );
	}

	/**
	 * Check PHP version compatibility
	 *
	 * @since 1.0.0
	 * @return bool True if compatible, false otherwise
	 */
	private function is_php_version_compatible() {
		return version_compare( PHP_VERSION, '7.4', '>=' );
	}

	/**
	 * Display WordPress version compatibility notice
	 *
	 * @since 1.0.0
	 */
	public function wordpress_version_notice() {
		global $wp_version;
		$message = sprintf(
			/* translators: 1: Plugin name, 2: Required WordPress version, 3: Current WordPress version */
			esc_html__( '%1$s requires WordPress version %2$s or higher. You are currently running WordPress %3$s. Please upgrade WordPress.', 'wp-security-performance-booster' ),
			'<strong>WordPress Security & Performance Booster</strong>',
			'4.0',
			$wp_version
		);
		printf( '<div class="notice notice-error"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Display PHP version compatibility notice
	 *
	 * @since 1.0.0
	 */
	public function php_version_notice() {
		$message = sprintf(
			/* translators: 1: Plugin name, 2: Required PHP version, 3: Current PHP version */
			esc_html__( '%1$s requires PHP version %2$s or higher. You are currently running PHP %3$s. Please upgrade PHP.', 'wp-security-performance-booster' ),
			'<strong>WordPress Security & Performance Booster</strong>',
			'7.4',
			PHP_VERSION
		);
		printf( '<div class="notice notice-error"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Plugin uninstall method
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {
		// Check user capabilities
		if ( ! current_user_can( 'delete_plugins' ) ) {
			return;
		}
		
		// Include uninstall.php for complete cleanup
		if ( file_exists( plugin_dir_path( __FILE__ ) . 'uninstall.php' ) ) {
			include_once plugin_dir_path( __FILE__ ) . 'uninstall.php';
		}
	}

	/**
	 * Initialize plugin
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Initialize features based on settings
		$this->init_features_conditionally();
		
		// Debug logging for init method
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Init method called - features conditionally initialized' );
		}
	}

	/**
	 * Plugin activation
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		// Check user capabilities
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		
		// Verify database tables first
		$this->verify_database_integrity();
		
		// Clean up old settings from previous versions
		$this->cleanup_legacy_settings();
		
		// Set default options
		$default_settings = $this->get_default_settings();
		add_option( 'wpspb_settings', $default_settings );
		add_option( 'wpspb_version', WPSPB_VERSION );
		add_option( 'wpspb_activation_time', time() );
		
		// Debug logging for activation
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $json = function_exists( 'wp_json_encode' ) ? wp_json_encode( $default_settings ) : json_encode( $default_settings );
            error_log( 'WPSPB: Plugin activated with default settings - ' . $json );
		}
		
		// Flush rewrite rules
		flush_rewrite_rules();
		
		// Ensure clean state for fresh installations
		$this->fresh_install_cleanup();
		
		// Optimize database on activation
		$this->optimize_database_on_activation();
		
		// Log activation for debugging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WordPress Security & Performance Booster: Plugin activated successfully with database verification.' );
		}
	}
	
	/**
	 * Optimize database on plugin activation
	 * Performs initial database optimization to improve performance
	 *
	 * @since 1.0.0
	 */
	private function optimize_database_on_activation() {
		global $wpdb;
		
		// Clean up orphaned transients immediately
		$this->cleanup_orphaned_transients();
		
		// Optimize options table
		$this->cleanup_large_autoload_options();
		
		// Clean up any existing plugin transients
		delete_transient( 'wpspb_cache' );
		delete_transient( 'wpspb_status_cache' );
		delete_site_transient( 'wpspb_cache' );
		delete_site_transient( 'wpspb_status_cache' );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Database optimization completed during activation' );
		}
	}
	
	/**
	 * Fresh installation cleanup
	 * Ensures clean state for new installations
	 *
	 * @since 1.0.0
	 */
	public function fresh_install_cleanup() {
		// Check if this is a fresh install (no previous settings)
		$existing_settings = get_option( 'wpspb_settings' );
		
		if ( false === $existing_settings ) {
			// This is a fresh install - ensure clean state
			$this->cleanup_legacy_settings();
			
			// Clear any existing update transients that might interfere
			delete_transient( 'update_core' );
			delete_transient( 'update_plugins' );
			delete_transient( 'update_themes' );
			delete_site_transient( 'update_core' );
			delete_site_transient( 'update_plugins' );
			delete_site_transient( 'update_themes' );
			
			// Clear object cache
			wp_cache_flush();
			
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WPSPB: Fresh installation cleanup completed' );
			}
		}
	}

	/**
	 * Plugin deactivation
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
		// Check user capabilities
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		
		// Clear any cached transients
		delete_transient( 'wpspb_cache' );
		delete_transient( 'wpspb_status_cache' );
		
		// Clear update-related transients to ensure updates work after deactivation
		delete_transient( 'update_core' );
		delete_transient( 'update_plugins' );
		delete_transient( 'update_themes' );
		delete_site_transient( 'update_core' );
		delete_site_transient( 'update_plugins' );
		delete_site_transient( 'update_themes' );
		
		// Re-enable WordPress update schedules
		if ( ! wp_next_scheduled( 'wp_version_check' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'wp_version_check' );
		}
		if ( ! wp_next_scheduled( 'wp_update_plugins' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'wp_update_plugins' );
		}
		if ( ! wp_next_scheduled( 'wp_update_themes' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'wp_update_themes' );
		}
		
		// Flush rewrite rules
		flush_rewrite_rules();
		
		// Clear object cache
		wp_cache_flush();
		
		// Log deactivation for debugging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WordPress Security & Performance Booster: Plugin deactivated successfully with cleanup.' );
		}
	}

	/**
	 * Admin initialization
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {
		if ( ! function_exists( 'remove_action' ) ) {
			return;
		}

		// Register settings
		register_setting( 'wpspb_settings', 'wpspb_settings', array( $this, 'sanitize_settings' ) );

		if ( current_user_can( 'update_core' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'add_adminbar_items' ), 100 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_css_overrides' ) );
		}
		
		// Get current settings
		$settings = get_option( 'wpspb_settings', $this->get_default_settings() );
		
		// Debug logging for admin_init method
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $json = function_exists( 'wp_json_encode' ) ? wp_json_encode( $settings ) : json_encode( $settings );
            error_log( 'WPSPB: Admin init method called with settings - ' . $json );
		}
		
		// Apply settings-based functionality
		if ( isset( $settings['disable_updates'] ) && $settings['disable_updates'] ) {
			$this->init_update_blocking_features();
		}
		
		if ( isset( $settings['disable_comments'] ) && $settings['disable_comments'] ) {
			$this->init_anti_spam_features();
		}
		
		if ( isset( $settings['disable_xmlrpc'] ) && $settings['disable_xmlrpc'] ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}
		
		if ( isset( $settings['hide_notifications'] ) && $settings['hide_notifications'] ) {
			$this->init_notification_cleaning();
		}
		
		if ( isset( $settings['disable_pingbacks'] ) && $settings['disable_pingbacks'] ) {
			// Disable pingbacks and trackbacks (part of anti-spam but separate setting)
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'wp_headers', array($this, 'disable_pingback_header') );
			add_filter( 'bloginfo_url', array($this, 'disable_pingback_url'), 10, 2 );
			add_filter( 'bloginfo', array($this, 'disable_pingback_url'), 10, 2 );
			add_filter( 'xmlrpc_methods', array($this, 'disable_xmlrpc_pingback_methods') );
			add_action( 'init', array($this, 'disable_pingbacks_completely'), 1 );
		}
		
		if ( isset( $settings['clean_dashboard'] ) && $settings['clean_dashboard'] ) {
			add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
		}
	}

	/**
	 * Initialize features based on user settings
	 *
	 * @since 1.0.0
	 */
	private function init_features_conditionally() {
		$settings = get_option( 'wpspb_settings', $this->get_default_settings() );
		
		// First, deactivate all features to ensure clean state
		$this->deactivate_all_features();
		
		// Debug logging for feature initialization
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $json = function_exists( 'wp_json_encode' ) ? wp_json_encode( $settings ) : json_encode( $settings );
            error_log( 'WPSPB: Re-initializing features with settings - ' . $json );
		}
		
		// Only initialize features if they are enabled in settings
		if ( isset( $settings['disable_updates'] ) && $settings['disable_updates'] ) {
			$this->init_update_blocking_features();
		}
		
		if ( isset( $settings['disable_comments'] ) && $settings['disable_comments'] ) {
			$this->init_anti_spam_features();
		}
		
		if ( isset( $settings['disable_xmlrpc'] ) && $settings['disable_xmlrpc'] ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}
		
		if ( isset( $settings['hide_notifications'] ) && $settings['hide_notifications'] ) {
			$this->init_notification_cleaning();
		}
		
		if ( isset( $settings['disable_pingbacks'] ) && $settings['disable_pingbacks'] ) {
			// Disable pingbacks and trackbacks (part of anti-spam but separate setting)
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'wp_headers', array($this, 'disable_pingback_header') );
			add_filter( 'bloginfo_url', array($this, 'disable_pingback_url'), 10, 2 );
			add_filter( 'bloginfo', array($this, 'disable_pingback_url'), 10, 2 );
			add_filter( 'xmlrpc_methods', array($this, 'disable_xmlrpc_pingback_methods') );
			add_action( 'init', array($this, 'disable_pingbacks_completely'), 1 );
		}
		
		if ( isset( $settings['clean_dashboard'] ) && $settings['clean_dashboard'] ) {
			add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
		}
	}

	/**
	 * Deactivate all plugin features
	 * This method removes all WordPress hooks and filters applied by this plugin
	 *
	 * @since 1.0.0
	 */
	private function deactivate_all_features() {
		// Remove update blocking features
		remove_filter( 'pre_transient_update_themes', array($this, 'last_checked_atm') );
		remove_filter( 'pre_site_transient_update_themes', array($this, 'last_checked_atm') );
		remove_filter( 'pre_transient_update_plugins', array($this, 'last_checked_atm') );
		remove_filter( 'pre_site_transient_update_plugins', array($this, 'last_checked_atm') );
		remove_filter( 'pre_transient_update_core', array($this, 'last_checked_atm') );
		remove_filter( 'pre_site_transient_update_core', array($this, 'last_checked_atm') );
        remove_filter( 'schedule_event', array($this, 'filter_cron_events') );
		remove_action( 'pre_set_site_transient_update_plugins', array($this, 'last_checked_atm') );
		remove_action( 'pre_set_site_transient_update_themes', array($this, 'last_checked_atm') );
		
		// Remove automatic update filters
		remove_filter( 'auto_update_translation', '__return_false' );
		remove_filter( 'automatic_updater_disabled', '__return_true' );
		remove_filter( 'allow_minor_auto_core_updates', '__return_false' );
		remove_filter( 'allow_major_auto_core_updates', '__return_false' );
		remove_filter( 'allow_dev_auto_core_updates', '__return_false' );
		remove_filter( 'auto_update_core', '__return_false' );
		remove_filter( 'wp_auto_update_core', '__return_false' );
		remove_filter( 'auto_core_update_send_email', '__return_false' );
		remove_filter( 'send_core_update_notification_email', '__return_false' );
		remove_filter( 'auto_update_plugin', '__return_false' );
		remove_filter( 'auto_update_theme', '__return_false' );
		remove_filter( 'automatic_updates_send_debug_email', '__return_false' );
		remove_filter( 'automatic_updates_is_vcs_checkout', '__return_true' );
		remove_filter( 'automatic_updates_send_debug_email ', '__return_false' );
		remove_filter( 'pre_http_request', array($this, 'block_request') );
		remove_filter( 'pre_option_update_core', '__return_null' );
		
		// Remove comment-related filters
		remove_filter( 'comments_open', '__return_false' );
		remove_filter( 'pings_open', '__return_false' );
		remove_filter( 'comments_array', '__return_empty_array' );
		remove_filter( 'feed_links_show_comments_feed', '__return_false' );
		
		// Remove XML-RPC filters
		remove_filter( 'xmlrpc_enabled', '__return_false' );
		remove_filter( 'wp_headers', array($this, 'disable_pingback_header') );
		remove_filter( 'bloginfo_url', array($this, 'disable_pingback_url') );
		remove_filter( 'bloginfo', array($this, 'disable_pingback_url') );
		remove_filter( 'xmlrpc_methods', array($this, 'disable_xmlrpc_pingback_methods') );
		
		// Remove actions
		remove_action( 'admin_init', array($this, 'disable_comments_post_types_support') );
		remove_action( 'init', array($this, 'disable_pingbacks_completely') );
		remove_action( 'admin_menu', array($this, 'disable_comments_admin_menu') );
		remove_action( 'admin_init', array($this, 'disable_comments_admin_menu_redirect') );
		remove_action( 'admin_init', array($this, 'disable_comments_dashboard') );
		remove_action( 'init', array($this, 'disable_comments_admin_bar') );
		remove_action( 'admin_enqueue_scripts', array($this, 'hide_plugin_notifications') );
		remove_action( 'admin_head', array($this, 'hide_admin_notices_css') );
		remove_action( 'wp_dashboard_setup', array($this, 'remove_dashboard_widgets') );
		
		// Remove site status and admin modifications
		remove_filter( 'site_status_tests', array( $this, 'site_status_tests' ) );
		remove_action( 'admin_notices', 'update_nag', 3 );
		remove_action( 'network_admin_notices', 'update_nag', 3 );
		remove_action( 'admin_notices', 'maintenance_nag' );
		remove_action( 'network_admin_notices', 'maintenance_nag' );
		
		// Clear all cached transients and force fresh update checks
		delete_transient( 'update_core' );
		delete_transient( 'update_plugins' );
		delete_transient( 'update_themes' );
		delete_site_transient( 'update_core' );
		delete_site_transient( 'update_plugins' );
		delete_site_transient( 'update_themes' );
		
		// Debug logging for feature deactivation
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: All features deactivated - preparing to reactivate WordPress updates' );
		}
		
		// Force WordPress to re-enable update functions immediately
		$this->force_wordpress_updates_reactivation();
	}
	
	/**
	 * Trigger immediate update check
	 * Forces WordPress to check for updates right now
	 *
	 * @since 1.0.0
	 */
	private function trigger_immediate_update_check() {
		// Clear all update transients first
		delete_site_transient( 'update_core' );
		delete_site_transient( 'update_plugins' );
		delete_site_transient( 'update_themes' );
		delete_transient( 'update_core' );
		delete_transient( 'update_plugins' );
		delete_transient( 'update_themes' );
		
		// Include WordPress update functions
		if ( ! function_exists( 'wp_version_check' ) ) {
			require_once ABSPATH . 'wp-includes/update.php';
		}
		
		// Force immediate checks
		wp_version_check( array(), true ); // Force check
		wp_update_plugins( array(), true ); // Force check
		wp_update_themes( array(), true ); // Force check
		
		// Trigger WordPress to recheck everything
		do_action( 'wp_version_check' );
		do_action( 'wp_update_plugins' );
		do_action( 'wp_update_themes' );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Triggered immediate update check - updates should now be visible' );
		}
	}
	
	/**
	 * Force WordPress updates reactivation
	 * This method aggressively restores WordPress update functionality
	 *
	 * @since 1.0.0
	 */
	private function force_wordpress_updates_reactivation() {
		// Re-add essential WordPress update actions that may have been removed
		if ( ! has_action( 'init', 'wp_schedule_update_checks' ) ) {
			add_action( 'init', 'wp_schedule_update_checks' );
		}
		
		// Force re-enable update notifications
		add_action( 'admin_notices', 'update_nag', 3 );
		add_action( 'network_admin_notices', 'update_nag', 3 );
		add_action( 'admin_notices', 'maintenance_nag' );
		add_action( 'network_admin_notices', 'maintenance_nag' );
		
		// Re-schedule WordPress update events if they don't exist
		if ( ! wp_next_scheduled( 'wp_version_check' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'wp_version_check' );
		}
		if ( ! wp_next_scheduled( 'wp_update_plugins' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'wp_update_plugins' );
		}
		if ( ! wp_next_scheduled( 'wp_update_themes' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'wp_update_themes' );
		}
		
		// Force immediate update checks to populate data
		if ( function_exists( 'wp_update_plugins' ) ) {
			wp_update_plugins();
		}
		if ( function_exists( 'wp_update_themes' ) ) {
			wp_update_themes();
		}
		if ( function_exists( 'wp_version_check' ) ) {
			wp_version_check();
		}
		
		// Force WordPress to reload plugin and theme data
		if ( function_exists( 'get_plugins' ) ) {
			wp_cache_delete( 'plugins', 'plugins' );
		}
		if ( function_exists( 'wp_get_themes' ) ) {
			wp_cache_delete( 'themes', 'themes' );
		}
		
		// Clear any remaining update-related caches
		wp_cache_delete( 'alloptions', 'options' );
		
		// Debug logging for update reactivation
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: WordPress updates forcefully reactivated - checking for available updates...' );
			
			// Log current update transients
			$update_core = get_site_transient( 'update_core' );
			$update_plugins = get_site_transient( 'update_plugins' );
			$update_themes = get_site_transient( 'update_themes' );
			
			error_log( 'WPSPB: Current update transients - Core: ' . ( $update_core ? 'Exists' : 'Empty' ) . ', Plugins: ' . ( $update_plugins ? 'Exists' : 'Empty' ) . ', Themes: ' . ( $update_themes ? 'Exists' : 'Empty' ) );
		}
	}
	
	/**
	 * Nuclear update reactivation method
	 * This is the most aggressive approach to restore WordPress updates
	 * when the disable updates feature is turned off
	 *
	 * @since 1.0.0
	 */
	private function nuclear_update_reactivation() {
		// Step 1: Remove all constants that may block updates
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) ) {
			// Can't undefine constants, but we can work around them
		}
		
		// Step 2: Clear all update-related transients and cached data
		$transients_to_clear = array(
			'update_core',
			'update_plugins', 
			'update_themes',
			'update_plugins_last_checked',
			'update_themes_last_checked',
			'doing_cron',
			'wpspb_cache',
			'wpspb_status_cache'
		);
		
		foreach ( $transients_to_clear as $transient ) {
			delete_transient( $transient );
			delete_site_transient( $transient );
			// Also clear from object cache
			wp_cache_delete( $transient, 'transient' );
			wp_cache_delete( $transient, 'site-transient' );
		}
		
		// Step 3: Clear all WordPress caches
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
		
		// Step 4: Force re-enable WordPress native update functions
		if ( ! function_exists( 'wp_version_check' ) ) {
			require_once ABSPATH . 'wp-includes/update.php';
		}
		
		// Step 5: Re-add all essential WordPress update hooks
		if ( ! has_action( 'init', 'wp_schedule_update_checks' ) ) {
			add_action( 'init', 'wp_schedule_update_checks' );
		}
		
		// Step 6: Re-schedule all update cron events
		$cron_events = array(
			'wp_version_check' => 'twicedaily',
			'wp_update_plugins' => 'twicedaily', 
			'wp_update_themes' => 'twicedaily',
			'wp_maybe_auto_update' => 'twicedaily'
		);
		
		foreach ( $cron_events as $hook => $recurrence ) {
			// Clear existing schedules
			wp_clear_scheduled_hook( $hook );
			// Re-schedule with immediate first run
			wp_schedule_event( time() + 10, $recurrence, $hook );
		}
		
		// Step 7: Re-enable admin notices for updates
		add_action( 'admin_notices', 'update_nag', 3 );
		add_action( 'network_admin_notices', 'update_nag', 3 );
		add_action( 'admin_notices', 'maintenance_nag' );
		add_action( 'network_admin_notices', 'maintenance_nag' );
		
		// Step 8: Force immediate update checks in background
		if ( function_exists( 'wp_version_check' ) ) {
			wp_version_check( array(), true );
		}
		if ( function_exists( 'wp_update_plugins' ) ) {
			wp_update_plugins( array(), true );
		}
		if ( function_exists( 'wp_update_themes' ) ) {
			wp_update_themes( array(), true );
		}
		
		// Step 9: Trigger all update-related actions
		do_action( 'wp_version_check' );
		do_action( 'wp_update_plugins' );
		do_action( 'wp_update_themes' );
		
		// Step 10: Reset user capabilities that may have been modified
		global $current_user;
		if ( $current_user && isset( $current_user->allcaps ) ) {
			$current_user->allcaps['update_plugins'] = 1;
			$current_user->allcaps['update_themes'] = 1;
			$current_user->allcaps['update_core'] = 1;
		}
		
		// Step 11: Force WordPress to reload core files
		if ( function_exists( 'get_plugins' ) ) {
			wp_cache_delete( 'plugins', 'plugins' );
			get_plugins(); // Force reload
		}
		if ( function_exists( 'wp_get_themes' ) ) {
			wp_cache_delete( 'themes', 'themes' );
			wp_get_themes(); // Force reload
		}
		
		// Step 12: Immediately check for updates via AJAX/background request
		$this->force_immediate_background_update_check();
		
		// Step 13: Log the nuclear reactivation
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: NUCLEAR UPDATE REACTIVATION COMPLETED - WordPress updates should now be fully visible' );
			
			// Log final update transients
			$update_core = get_site_transient( 'update_core' );
			$update_plugins = get_site_transient( 'update_plugins' );
			$update_themes = get_site_transient( 'update_themes' );
			
			error_log( 'WPSPB: Final update transients - Core: ' . ( $update_core ? 'Exists' : 'Empty' ) . ', Plugins: ' . ( $update_plugins ? 'Exists' : 'Empty' ) . ', Themes: ' . ( $update_themes ? 'Exists' : 'Empty' ) );
		}
	}
	
	/**
	 * Force immediate background update check
	 * Triggers update checks via WordPress internal mechanisms
	 *
	 * @since 1.0.0
	 */
	private function force_immediate_background_update_check() {
		// Method 1: Use wp_remote_get to trigger update checks
		if ( function_exists( 'wp_remote_get' ) ) {
			// Trigger core update check
			wp_remote_get( 'https://api.wordpress.org/core/version-check/1.7/', array(
				'timeout' => 30,
				'blocking' => false, // Don't wait for response
				'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url()
			));
			
			// Small delay between requests
			usleep( 100000 ); // 0.1 second
			
			// Trigger plugin update check
			wp_remote_post( 'https://api.wordpress.org/plugins/update-check/1.1/', array(
				'timeout' => 30,
				'blocking' => false,
				'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
				'body' => array(
					'plugins' => json_encode( get_option( 'active_plugins', array() ) )
				)
			));
		}
		
		// Method 2: Use WordPress internal functions with force parameter
		if ( function_exists( 'wp_update_plugins' ) ) {
			// Force plugin update check
			wp_update_plugins( get_site_transient( 'update_plugins' ), true );
		}
		
		if ( function_exists( 'wp_update_themes' ) ) {
			// Force theme update check
			wp_update_themes( get_site_transient( 'update_themes' ), true );
		}
		
		if ( function_exists( 'wp_version_check' ) ) {
			// Force core update check
			wp_version_check( get_site_transient( 'update_core' ), true );
		}
		
		// Method 3: Schedule immediate cron execution
		if ( function_exists( 'spawn_cron' ) ) {
			spawn_cron();
		}
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Background update checks triggered - updates should populate within 30 seconds' );
		}
	}
	
	/**
	 * Get default settings
	 *
	 * @since 1.0.0
	 * @return array Default plugin settings
	 */
	private function get_default_settings() {
		return array(
			'disable_updates'    => false,
			'disable_comments'   => false,
			'disable_xmlrpc'     => false,
			'hide_notifications' => false,
			'disable_pingbacks'  => false,
			'clean_dashboard'    => false,
		);
	}
	
	/**
	 * Verify database integrity
	 * Checks if WordPress database tables are properly set up
	 *
	 * @since 1.0.0
	 * @return bool True if database is healthy, false otherwise
	 */
	private function verify_database_integrity() {
		global $wpdb;
		
		// Check core WordPress tables
		$required_tables = array(
			$wpdb->options,
			$wpdb->posts,
			$wpdb->users,
			$wpdb->usermeta,
			$wpdb->terms,
			$wpdb->term_taxonomy,
			$wpdb->term_relationships,
			$wpdb->commentmeta,
			$wpdb->comments
		);
		
		$missing_tables = array();
		
		foreach ( $required_tables as $table ) {
			if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
				$missing_tables[] = $table;
			}
		}
		
		if ( ! empty( $missing_tables ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WPSPB: Missing database tables detected: ' . implode( ', ', $missing_tables ) );
			}
			return false;
		}
		
        // Check database connection (only if method exists on this WP version)
        if ( method_exists( $wpdb, 'check_connection' ) && ! $wpdb->check_connection() ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'WPSPB: Database connection check failed during activation' );
            }
            return false;
        }
		
		// Check for table corruption
		$corrupted_tables = array();
        foreach ( $required_tables as $table ) {
            // Suppress potential DB-level warnings from CHECK TABLE on constrained hosts
            $result = $wpdb->get_row( "CHECK TABLE `{$table}`" );
            if ( is_object( $result ) && isset( $result->Msg_text ) ) {
                if ( stripos( $result->Msg_text, 'OK' ) === false ) {
                    $corrupted_tables[] = $table;
                }
            }
        }
		
		if ( ! empty( $corrupted_tables ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WPSPB: Corrupted database tables detected: ' . implode( ', ', $corrupted_tables ) );
			}
			return false;
		}
		
		return true;
	}
	
	/**
	 * Cleanup legacy settings from older plugin versions
	 *
	 * @since 1.0.0
	 */
	private function cleanup_legacy_settings() {
		// Remove old option names from previous versions
		delete_option( 'wp_security_booster_settings' );
		delete_option( 'wpspb_old_settings' );
		delete_option( 'security_performance_booster_options' );
		delete_option( 'wpspb_first_install' );
		delete_option( 'wpspb_activation_time_old' );
		delete_option( 'wpspb_legacy_options' );
		
		// Clear legacy transients
		delete_transient( 'wp_security_booster_cache' );
		delete_transient( 'old_wpspb_data' );
		delete_transient( 'wpspb_legacy_cache' );
		
		// Clear site transients for multisite
		if ( is_multisite() ) {
			delete_site_option( 'wp_security_booster_settings' );
			delete_site_option( 'wpspb_old_settings' );
			delete_site_option( 'security_performance_booster_options' );
			delete_site_option( 'wpspb_first_install' );
			delete_site_transient( 'wp_security_booster_cache' );
			delete_site_transient( 'old_wpspb_data' );
		}
		
		// Clean up any orphaned user meta from previous versions
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'wpspb_%' AND meta_key NOT IN ('wpspb_user_settings')" );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Legacy settings cleanup completed' );
		}
	}

	/**
	 * Add admin menu
	 *
	 * @since 2.0.0
	 */
	public function add_admin_menu() {
		add_options_page(
			esc_html__( 'Security & Performance Booster', 'wp-security-performance-booster' ),
			esc_html__( 'Security Booster', 'wp-security-performance-booster' ),
			'manage_options',
			'wpspb-settings',
			array( $this, 'admin_settings_page' )
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @since 2.0.0
	 * @param string $hook The current admin page.
	 */
	public function enqueue_admin_assets( $hook ) {
        // Only load assets on our settings page for performance
        if ( 'settings_page_wpspb-settings' !== $hook ) {
            return;
        }
        
		// Add inline CSS for modern design
		wp_add_inline_style( 'wp-admin', $this->get_admin_css() );

		// Ensure a valid script handle and attach inline JS so it actually prints
		// Use jQuery as a guaranteed handle and enqueue it explicitly
		wp_enqueue_script( 'jquery' );
		wp_add_inline_script( 'jquery', $this->get_admin_js() );
		// Ensure dashicons are available for icons
		wp_enqueue_style( 'dashicons' );
	}

	/**
	 * Check and optimize database for performance
	 * Identifies and resolves common database issues that slow down admin
	 *
	 * @since 1.0.0
	 */
	private function check_and_optimize_database() {
		global $wpdb;
		
		// Only run this check occasionally to avoid performance impact
		$last_check = get_option( 'wpspb_last_db_check', 0 );
		$current_time = time();
		
		// Run check every 6 hours
		if ( ( $current_time - $last_check ) < 21600 ) {
			return;
		}
		
		// Update last check time
		update_option( 'wpspb_last_db_check', $current_time );
		
		// Check for options table bloat
		$options_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options}" );
		
		if ( $options_count > 5000 ) {
			// Clean up autoload options that are too large
			$this->cleanup_large_autoload_options();
		}
		
		// Check for orphaned transients
		$this->cleanup_orphaned_transients();
		
		// Optimize database tables if needed
		$this->optimize_database_tables();
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Database optimization check completed. Options count: ' . $options_count );
		}
	}
	
	/**
	 * Optimize database tables
	 * Performs basic optimization of WordPress database tables
	 *
	 * @since 1.0.0
	 */
	private function optimize_database_tables() {
		global $wpdb;
		
		// Only optimize tables once per day
		$last_optimization = get_option( 'wpspb_last_table_optimization', 0 );
		if ( ( time() - $last_optimization ) < 86400 ) {
			return;
		}
		
		// Update last optimization time
		update_option( 'wpspb_last_table_optimization', time() );
		
		// Get WordPress tables
		$tables = array(
			$wpdb->options,
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->comments,
			$wpdb->commentmeta,
			$wpdb->terms,
			$wpdb->term_taxonomy,
			$wpdb->term_relationships,
			$wpdb->users,
			$wpdb->usermeta
		);
		
		// Optimize each table
		foreach ( $tables as $table ) {
			$wpdb->query( "OPTIMIZE TABLE {$table}" );
		}
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Database tables optimized' );
		}
	}
	
	/**
	 * Optimize admin performance
	 * Reduces overhead on admin pages by conditionally loading features
	 *
	 * @since 1.0.0
	 */
	public function optimize_admin_performance() {
		// Only apply optimizations on admin pages
		if ( ! is_admin() ) {
			return;
		}
		
		// Defer non-critical admin scripts
		add_filter( 'script_loader_tag', array( $this, 'defer_non_critical_scripts' ), 10, 2 );
		
		// Reduce the number of queries on admin pages
		add_action( 'admin_init', array( $this, 'reduce_admin_queries' ), 1 );
		
		// Optimize admin dashboard loading
		add_action( 'admin_init', array( $this, 'optimize_admin_dashboard_loading' ), 5 );
	}
	
	/**
	 * Optimize admin dashboard loading
	 * Improves performance of admin dashboard by reducing overhead
	 *
	 * @since 1.0.0
	 */
	public function optimize_admin_dashboard_loading() {
		// Only apply on dashboard pages
		global $pagenow;
		if ( ! in_array( $pagenow, array( 'index.php', 'plugins.php', 'themes.php' ) ) ) {
			return;
		}
		
		// Remove unnecessary dashboard widgets to improve loading time
		add_action( 'wp_dashboard_setup', array( $this, 'optimize_dashboard_widgets' ), 999 );
		
		// Defer non-critical CSS and JavaScript
		add_filter( 'style_loader_tag', array( $this, 'defer_non_critical_styles' ), 10, 2 );
		
		// Limit the number of posts queried for dashboard
		add_filter( 'dashboard_recent_posts_query_args', array( $this, 'limit_dashboard_posts_query' ) );
	}
	
	/**
	 * Optimize dashboard widgets
	 * Removes unnecessary widgets to improve loading time
	 *
	 * @since 1.0.0
	 */
	public function optimize_dashboard_widgets() {
		// Only optimize on our plugin's settings page or dashboard
		global $current_screen;
		if ( isset( $current_screen->id ) && 'settings_page_wpspb-settings' === $current_screen->id ) {
			// Remove non-essential widgets on our settings page
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		}
	}
	
	/**
	 * Defer non-critical styles to improve page load times
	 *
	 * @since 1.0.0
	 * @param string $html Style tag.
	 * @param string $handle Style handle.
	 * @return string Modified style tag.
	 */
	public function defer_non_critical_styles( $html, $handle ) {
		// Only defer styles on our plugin page
		global $current_screen;
		if ( ! isset( $current_screen->id ) || 'settings_page_wpspb-settings' !== $current_screen->id ) {
			return $html;
		}
		
		// List of styles that can be deferred
		$defer_styles = array(
			// Add any non-critical styles here if needed in future
		);
		
		if ( in_array( $handle, $defer_styles, true ) ) {
			return str_replace( 'rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', $html );
		}
		
		return $html;
	}
	
	/**
	 * Limit dashboard posts query to improve performance
	 *
	 * @since 1.0.0
	 * @param array $query_args Query arguments.
	 * @return array Modified query arguments.
	 */
	public function limit_dashboard_posts_query( $query_args ) {
		// Limit to 5 posts instead of default 10 to reduce query load
		$query_args['posts_per_page'] = 5;
		return $query_args;
	}
	

	
	/**
	 * Get current status data for admin display
	 *
	 * @since 1.0.0
	 * @return array Status data.
	 */
	private function get_current_status_data() {
		$settings = get_option( 'wpspb_settings', $this->get_default_settings() );
		
		return array(
			array(
				'status' => isset( $settings['disable_updates'] ) ? $settings['disable_updates'] : false,
				'name' => __( 'WordPress Updates', 'wp-security-performance-booster' ),
				'description' => __( 'Core, plugins, themes update blocking', 'wp-security-performance-booster' )
			),
			array(
				'status' => isset( $settings['disable_comments'] ) ? $settings['disable_comments'] : false,
				'name' => __( 'Comments System', 'wp-security-performance-booster' ),
				'description' => __( 'Comment forms and functionality', 'wp-security-performance-booster' )
			),
			array(
				'status' => isset( $settings['disable_xmlrpc'] ) ? $settings['disable_xmlrpc'] : false,
				'name' => __( 'XML-RPC Service', 'wp-security-performance-booster' ),
				'description' => __( 'XML-RPC endpoint accessibility', 'wp-security-performance-booster' )
			),
			array(
				'status' => isset( $settings['hide_notifications'] ) ? $settings['hide_notifications'] : false,
				'name' => __( 'Admin Notifications', 'wp-security-performance-booster' ),
				'description' => __( 'Plugin/theme promotional messages', 'wp-security-performance-booster' )
			),
			array(
				'status' => isset( $settings['disable_pingbacks'] ) ? $settings['disable_pingbacks'] : false,
				'name' => __( 'Pingbacks & Trackbacks', 'wp-security-performance-booster' ),
				'description' => __( 'Pingback and trackback functionality', 'wp-security-performance-booster' )
			),
			array(
				'status' => isset( $settings['clean_dashboard'] ) ? $settings['clean_dashboard'] : false,
				'name' => __( 'Dashboard Cleanup', 'wp-security-performance-booster' ),
				'description' => __( 'Dashboard widget removal', 'wp-security-performance-booster' )
			)
		);
	}
	
	/**
	 * Reduce admin queries by caching where possible
	 *
	 * @since 1.0.0
	 */
	public function reduce_admin_queries() {
		// Only apply on our plugin page
		global $current_screen;
		if ( ! isset( $current_screen->id ) || 'settings_page_wpspb-settings' !== $current_screen->id ) {
			return;
		}
		
		// Cache plugin status data to reduce database queries
		$status_data = get_transient( 'wpspb_status_cache' );
		if ( false === $status_data ) {
			$status_data = $this->get_current_status_data();
			set_transient( 'wpspb_status_cache', $status_data, 300 ); // Cache for 5 minutes
		}
	}

	/**
	 * Load plugin textdomain for translations
	 *
	 * @since 2.0.0
	 */
	public function load_textdomain() {
		$domain = 'wp-security-performance-booster';
		
		// Load locale-specific language file
		$loaded = load_plugin_textdomain(
			$domain,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
		
		// Log loading status for debugging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			if ( $loaded ) {
				error_log( 'WordPress Security & Performance Booster: Language files loaded successfully.' );
			} else {
				error_log( 'WordPress Security & Performance Booster: No language files found or failed to load.' );
			}
		}
	}

	/**
	 * Get plugin locale (with user preference override)
	 *
	 * @since 2.0.0
	 * @return string The locale string
	 */
	private function get_plugin_locale() {
		// Check if user has set a language preference
		$user_locale = get_option( 'wpspb_language', '' );
		
		if ( ! empty( $user_locale ) ) {
			return $user_locale;
		}
		
		// Fall back to WordPress locale
		return get_locale();
	}

	/**
	 * Initialize anti-spam features
	 *
	 * @since 2.0.0
	 */
	private function init_anti_spam_features() {
		// Disable comments completely
		add_filter( 'comments_open', '__return_false', 20, 2 );
		add_filter( 'pings_open', '__return_false', 20, 2 );
		add_filter( 'comments_array', '__return_empty_array', 10, 2 );
		
		// Remove comment support from post types
		add_action( 'admin_init', array($this, 'disable_comments_post_types_support') );
		
		// Disable comment feeds
		add_filter( 'feed_links_show_comments_feed', '__return_false' );
		
		// Remove comment-related actions and filters
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		
		// Disable pingbacks and trackbacks
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'wp_headers', array($this, 'disable_pingback_header') );
		add_filter( 'bloginfo_url', array($this, 'disable_pingback_url'), 10, 2 );
		add_filter( 'bloginfo', array($this, 'disable_pingback_url'), 10, 2 );
		add_filter( 'xmlrpc_methods', array($this, 'disable_xmlrpc_pingback_methods') );
		
		// Remove pingback header
		add_action( 'init', array($this, 'disable_pingbacks_completely'), 1 );
		
		// Hide existing comments in admin
		add_action( 'admin_menu', array($this, 'disable_comments_admin_menu') );
		add_action( 'admin_init', array($this, 'disable_comments_admin_menu_redirect') );
		add_action( 'admin_init', array($this, 'disable_comments_dashboard') );
		add_action( 'init', array($this, 'disable_comments_admin_bar') );
	}

	/**
	 * Initialize notification cleaning features
	 *
	 * @since 2.0.0
	 */
	private function init_notification_cleaning() {
		// Remove all admin notices from plugins and themes
		add_action( 'admin_enqueue_scripts', array($this, 'hide_plugin_notifications') );
		add_action( 'admin_head', array($this, 'hide_admin_notices_css') );
	}

	/**
	 * Initialize update blocking features
	 *
	 * @since 2.0.0
	 */
	private function init_update_blocking_features() {
		/*
		 * Disable Theme Updates
		 * 2.8 to 3.0
		 */
		add_filter( 'pre_transient_update_themes', [$this, 'last_checked_atm'] );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_themes', [$this, 'last_checked_atm'] );


		/*
		 * Disable Plugin Updates
		 * 2.8 to 3.0
		 */
		add_action( 'pre_transient_update_plugins', [$this, 'last_checked_atm'] );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_plugins', [$this, 'last_checked_atm'] );


		/*
		 * Disable Core Updates
		 * 2.8 to 3.0
		 */
		add_filter( 'pre_transient_update_core', [$this, 'last_checked_atm'] );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_core', [$this, 'last_checked_atm'] );
		
		
		/*
		 * Filter schedule checks
		 *
		 * @link https://wordpress.org/support/topic/possible-performance-improvement/#post-8970451
		 */
        add_filter('schedule_event', [$this, 'filter_cron_events']);
		
		add_action( 'pre_set_site_transient_update_plugins', [$this, 'last_checked_atm'], 21, 1 );
		add_action( 'pre_set_site_transient_update_themes', [$this, 'last_checked_atm'], 21, 1 );

		/*
		 * Disable All Automatic Updates
		 * 3.7+
		 *
		 * @author	sLa NGjI's @ slangji.wordpress.com
		 */
		add_filter( 'auto_update_translation', '__return_false' );
		add_filter( 'automatic_updater_disabled', '__return_true' );
		add_filter( 'allow_minor_auto_core_updates', '__return_false' );
		add_filter( 'allow_major_auto_core_updates', '__return_false' );
		add_filter( 'allow_dev_auto_core_updates', '__return_false' );
		add_filter( 'auto_update_core', '__return_false' );
		add_filter( 'wp_auto_update_core', '__return_false' );
		add_filter( 'auto_core_update_send_email', '__return_false' );
		add_filter( 'send_core_update_notification_email', '__return_false' );
		add_filter( 'auto_update_plugin', '__return_false' );
		add_filter( 'auto_update_theme', '__return_false' );
		add_filter( 'automatic_updates_send_debug_email', '__return_false' );
		add_filter( 'automatic_updates_is_vcs_checkout', '__return_true' );

		remove_action( 'init', 'wp_schedule_update_checks' );
		remove_all_filters( 'plugins_api' );

		add_filter( 'automatic_updates_send_debug_email ', '__return_false', 1 );
		if( !defined( 'AUTOMATIC_UPDATER_DISABLED' ) ) define( 'AUTOMATIC_UPDATER_DISABLED', true );
		if( !defined( 'WP_AUTO_UPDATE_CORE') ) define( 'WP_AUTO_UPDATE_CORE', false );

		add_filter( 'pre_http_request', [$this, 'block_request'], 10, 3 );
		
		// Debug logging for initialized update blocking features
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Update blocking features initialized' );
		}
	}



	
	/**
	 * Optimize admin dashboard
	 * Reduces overhead and improves loading times for admin pages
	 *
	 * @since 1.0.0
	 */
	private function optimize_admin_dashboard() {
		// Only apply optimizations on admin pages
		if ( ! is_admin() ) {
			return;
		}
		
		// Defer non-critical scripts to improve page load times
		add_filter( 'script_loader_tag', array( $this, 'defer_non_critical_scripts' ), 10, 2 );
		
		// Reduce HTTP requests by combining CSS/JS where possible
		add_action( 'admin_enqueue_scripts', array( $this, 'consolidate_admin_assets' ), 999 );
		
		// Optimize database queries on admin pages
		add_action( 'admin_init', array( $this, 'optimize_admin_queries' ), 1 );
	}
	
	/**
	 * Consolidate admin assets to reduce HTTP requests
	 *
	 * @since 1.0.0
	 */
	public function consolidate_admin_assets() {
		// Only consolidate on our plugin page
		global $current_screen;
		if ( ! isset( $current_screen->id ) || 'settings_page_wpspb-settings' !== $current_screen->id ) {
			return;
		}
		
		// Remove unnecessary scripts and styles
		global $wp_scripts, $wp_styles;
		
		// List of scripts that are not needed on our settings page
		$unnecessary_scripts = array(
			// Add script handles that can be safely removed on our page
		);
		
		// List of styles that are not needed on our settings page
		$unnecessary_styles = array(
			// Add style handles that can be safely removed on our page
		);
		
		// Remove unnecessary scripts
		foreach ( $unnecessary_scripts as $script ) {
			if ( wp_script_is( $script, 'enqueued' ) ) {
				wp_dequeue_script( $script );
			}
		}
		
		// Remove unnecessary styles
		foreach ( $unnecessary_styles as $style ) {
			if ( wp_style_is( $style, 'enqueued' ) ) {
				wp_dequeue_style( $style );
			}
		}
	}
	
	/**
	 * Optimize database queries on admin pages
	 *
	 * @since 1.0.0
	 */
	public function optimize_admin_queries() {
		// Only apply on our plugin page
		global $current_screen;
		if ( ! isset( $current_screen->id ) || 'settings_page_wpspb-settings' !== $current_screen->id ) {
			return;
		}
		
		// Cache plugin status data to reduce database queries
		$status_data = get_transient( 'wpspb_status_cache' );
		if ( false === $status_data ) {
			$status_data = $this->get_current_status_data();
			set_transient( 'wpspb_status_cache', $status_data, 300 ); // Cache for 5 minutes
		}
		
		// Optimize options autoload
		$this->optimize_options_autoload();
	}
	
	/**
	 * Optimize options autoload to improve performance
	 *
	 * @since 1.0.0
	 */
	private function optimize_options_autoload() {
		global $wpdb;
		
		// Check if we've already optimized in the last 24 hours
		$last_optimization = get_option( 'wpspb_last_autoload_optimization', 0 );
		if ( ( time() - $last_optimization ) < 86400 ) {
			return;
		}
		
		// Update last optimization time
		update_option( 'wpspb_last_autoload_optimization', time() );
		
		// Optimize autoload options
		$this->cleanup_large_autoload_options();
	}
	

	
	/**
	 * Cleanup large autoload options that slow down admin
	 *
	 * @since 1.0.0
	 */
	private function cleanup_large_autoload_options() {
		global $wpdb;
		
		// Find large autoload options (> 100KB)
		$large_options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, LENGTH(option_value) as size 
				FROM {$wpdb->options} 
				WHERE autoload = %s 
				AND LENGTH(option_value) > %d 
				ORDER BY LENGTH(option_value) DESC 
				LIMIT 20",
				'yes',
				102400
			)
		);
		
		$critical_options = array(
			'siteurl',
			'home',
			'blogname',
			'blogdescription',
			'users_can_register',
			'admin_email',
			'start_of_week',
			'use_balanceTags',
			'use_smilies',
			'require_name_email',
			'comments_notify',
			'posts_per_rss',
			'rss_use_excerpt',
			'mailserver_url',
			'mailserver_login',
			'mailserver_pass',
			'mailserver_port',
			'default_category',
			'default_comment_status',
			'default_ping_status',
			'default_pingback_flag',
			'posts_per_page',
			'date_format',
			'time_format',
			'links_updated_date_format',
			'comment_moderation',
			'moderation_notify',
			'permalink_structure',
			'rewrite_rules',
			'hack_file',
			'blog_charset',
			'moderation_keys',
			'active_plugins',
			'category_base',
			'ping_sites',
			'comment_max_links',
			'gmt_offset',
			'default_email_category',
			'recently_edited',
			'template',
			'stylesheet',
			'comment_registration',
			'html_type',
			'use_trackback',
			'default_role',
			'db_version',
			'uploads_use_yearmonth_folders',
			'upload_path',
			'blog_public',
			'default_link_category',
			'show_on_front',
			'tag_base',
			'show_avatars',
			'avatar_rating',
			'upload_url_path',
			'thumbnail_size_w',
			'thumbnail_size_h',
			'thumbnail_crop',
			'medium_size_w',
			'medium_size_h',
			'avatar_default',
			'large_size_w',
			'large_size_h',
			'image_default_link_type',
			'image_default_size',
			'image_default_align',
			'close_comments_for_old_posts',
			'close_comments_days_old',
			'thread_comments',
			'thread_comments_depth',
			'page_comments',
			'comments_per_page',
			'default_comments_page',
			'comment_order',
			'sticky_posts',
			'widget_categories',
			'widget_text',
			'widget_rss',
			'uninstall_plugins',
			'timezone_string',
			'page_for_posts',
			'page_on_front',
			'default_post_format',
			'link_manager_enabled',
			'finished_splitting_shared_terms',
			'site_icon',
			'highlander_comment_form_prompt',
			'jetpack_comment_form_color_scheme',
			'wpspb_settings',
			'wpspb_version',
			'wpspb_language',
			'wpspb_last_db_check',
			'wpspb_last_autoload_optimization',
			'wpspb_last_table_optimization'
		);
		
		foreach ( $large_options as $option ) {
			// Only update non-critical options
			if ( ! in_array( $option->option_name, $critical_options ) ) {
				$wpdb->update(
					$wpdb->options,
					array( 'autoload' => 'no' ),
					array( 'option_name' => $option->option_name )
				);
				
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'WPSPB: Disabled autoload for large option: ' . $option->option_name . ' (' . $option->size . ' bytes)' );
				}
			}
		}
		
		// Additional optimization: Clean up duplicate or unnecessary options
		$this->cleanup_duplicate_options();
	}
	
	/**
	 * Cleanup duplicate or unnecessary options
	 *
	 * @since 1.0.0
	 */
	private function cleanup_duplicate_options() {
		global $wpdb;
		
		// Clean up duplicate options (rare but can happen)
		$wpdb->query(
			"DELETE o1 FROM {$wpdb->options} o1
			INNER JOIN {$wpdb->options} o2 
			WHERE o1.option_name = o2.option_name 
			AND o1.option_id > o2.option_id"
		);
		
		// Clean up orphaned post meta
		$wpdb->query(
			"DELETE pm FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE p.ID IS NULL"
		);
		
		// Clean up orphaned comment meta
		$wpdb->query(
			"DELETE cm FROM {$wpdb->commentmeta} cm
			LEFT JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id
			WHERE c.comment_ID IS NULL"
		);
		
		// Clean up orphaned user meta
		$wpdb->query(
			"DELETE um FROM {$wpdb->usermeta} um
			LEFT JOIN {$wpdb->users} u ON u.ID = um.user_id
			WHERE u.ID IS NULL"
		);
		
		// Clean up orphaned term relationships
		$wpdb->query(
			"DELETE tr FROM {$wpdb->term_relationships} tr
			LEFT JOIN {$wpdb->posts} p ON p.ID = tr.object_id
			WHERE p.ID IS NULL"
		);
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Duplicate and orphaned data cleanup completed' );
		}
	}
	
	/**
	 * Cleanup orphaned transients
	 *
	 * @since 1.0.0
	 */
	private function cleanup_orphaned_transients() {
		global $wpdb;
		
		// Clean up expired transients
		$time = time();
		
		// Clean up regular transients
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
				'_transient_timeout_%',
				$time
			)
		);
		
		// Clean up site transients
		if ( is_multisite() ) {
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s AND meta_value < %d",
					'_site_transient_timeout_%',
					$time
				)
			);
		}
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Orphaned transients cleanup completed' );
		}
	}
	
	/**
	 * Defer non-critical scripts to improve page load times
	 *
	 * @since 1.0.0
	 * @param string $tag Script tag.
	 * @param string $handle Script handle.
	 * @return string Modified script tag.
	 */
	public function defer_non_critical_scripts( $tag, $handle ) {
		// Only defer scripts on our plugin page
		global $current_screen;
		if ( ! isset( $current_screen->id ) || 'settings_page_wpspb-settings' !== $current_screen->id ) {
			return $tag;
		}
		
		// List of scripts that can be deferred
		$defer_scripts = array(
			// Add any non-critical scripts here if needed in future
		);
		
		if ( in_array( $handle, $defer_scripts, true ) ) {
			return str_replace( ' src', ' defer src', $tag );
		}
		
		return $tag;
	}
	
	/**
	 * Sanitize settings
	 *
	 * @since 1.0.0
	 * @param array $input Raw input data.
	 * @return array Sanitized data
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();
		
		// Validate input is array
		if ( ! is_array( $input ) ) {
			return $this->get_default_settings();
		}
		
		$defaults = $this->get_default_settings();
		foreach ( $defaults as $key => $default_value ) {
			$sanitized[ $key ] = isset( $input[ $key ] ) ? (bool) $input[ $key ] : false;
		}
		
		// Log settings change for debugging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $json_sanitized = function_exists( 'wp_json_encode' ) ? wp_json_encode( $sanitized ) : json_encode( $sanitized );
            error_log( 'WordPress Security & Performance Booster: Settings updated - ' . $json_sanitized );
			
			// Log raw input for debugging
            $json_input = function_exists( 'wp_json_encode' ) ? wp_json_encode( $input ) : json_encode( $input );
            error_log( 'WordPress Security & Performance Booster: Raw input - ' . $json_input );
		}
		
		return $sanitized;
	}



	/**
	 * Hide update checks in the Site Health screen
	 *
	 * @since 		1.6.8
	 */
	public function site_status_tests($tests) {
		unset( $tests['async']['background_updates'] );
		unset( $tests['direct']['plugin_theme_auto_updates'] );
		return $tests;
	}



	/**
	 * Add notice to admin bar when plugin is active
	 *
	 * @since 		2.0.0
	 */
    public function add_adminbar_items($admin_bar) {
        // Ensure get_plugin_data is available in non-admin requests
        if ( ! function_exists( 'get_plugin_data' ) ) {
            $plugin_file = ABSPATH . 'wp-admin/includes/plugin.php';
            if ( file_exists( $plugin_file ) ) {
                require_once $plugin_file;
            }
        }

        $plugin_data = function_exists( 'get_plugin_data' ) ? get_plugin_data( __FILE__ ) : array( 'Name' => 'WP Security & Performance Booster' );

		$admin_bar->add_menu(array(
			'id' => 'wpspb-notice',
			'title' => '<span class="dashicons dashicons-shield-alt" aria-hidden="true"></span>',
			'href' => network_admin_url('plugins.php'),
			'meta' => array(
				'class' => 'wp-admin-bar-wpspb-notice',
				'title' => sprintf(
					/* translators: %s: Name of the plugin */
					__('Security & Performance Booster is active: Updates disabled, spam blocked, notifications hidden!', 'wp-security-performance-booster'),
					$plugin_data['Name']
				)
			),
		));
	}



	/**
	 * Apply CSS styles to admin bar notice
	 *
	 * @since 		2.0.0
	 */
	public function admin_css_overrides() {
		wp_add_inline_style( 'admin-bar', '.wp-admin-bar-wpspb-notice { background-color: rgba(0, 150, 0, 0.6) !important; } .wp-admin-bar-wpspb-notice .dashicons { font-family: dashicons !important; }' );
	}


	/**
	 * Check the outgoing request
	 * Only blocks update-related requests if disable_updates is enabled
	 *
	 * @since 1.4.4
	 * @param false|array|WP_Error $pre A preemptive return value of an HTTP request.
	 * @param array                $args HTTP request arguments.
	 * @param string               $url The request URL.
	 * @return false|array|WP_Error
	 */
	public function block_request( $pre, $args, $url ) {
		// Check if update blocking is actually enabled
		$settings = get_option( 'wpspb_settings', $this->get_default_settings() );
		
		// If updates are not disabled, allow all requests
		if ( ! isset( $settings['disable_updates'] ) || ! $settings['disable_updates'] ) {
			// Debug logging for allowed requests
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WPSPB: Update blocking not enabled - allowing request to ' . esc_url( $url ) );
			}
			return $pre;
		}
		
		// Empty url
		if ( empty( $url ) || ! is_string( $url ) ) {
			return $pre;
		}

		// Invalid host
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $host ) {
			return $pre;
		}

		$url_data = wp_parse_url( $url );

		// Block WordPress.org API requests only if updates are disabled
		if ( false !== stripos( $host, 'api.wordpress.org' ) &&
		     isset( $url_data['path'] ) &&
		     ( false !== stripos( $url_data['path'], 'update-check' ) ||
		       false !== stripos( $url_data['path'], 'version-check' ) ||
		       false !== stripos( $url_data['path'], 'browse-happy' ) ||
		       false !== stripos( $url_data['path'], 'serve-happy' ) ) ) {
		       	
			// Log blocked request for debugging
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WordPress Security & Performance Booster: Blocked update request to ' . esc_url( $url ) );
			}
			
			return true;
		}

		return $pre;
	}


	/**
	 * Filter cron events
	 * Only blocks update-related cron events if disable_updates is enabled
	 *
	 * @since 1.5.0
	 * @param object $event Cron event object.
	 * @return object|false
	 */
	public function filter_cron_events( $event ) {
		// Check if update blocking is actually enabled
		$settings = get_option( 'wpspb_settings', $this->get_default_settings() );
		
		// If updates are not disabled, allow all cron events
		if ( ! isset( $settings['disable_updates'] ) || ! $settings['disable_updates'] ) {
			// Debug logging for allowed cron events
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_object( $event ) && isset( $event->hook ) ) {
				error_log( 'WPSPB: Update blocking not enabled - allowing cron event ' . $event->hook );
			}
			return $event;
		}
		
		if ( ! is_object( $event ) || ! isset( $event->hook ) ) {
			return $event;
		}
		
		switch ( $event->hook ) {
			case 'wp_version_check':
			case 'wp_update_plugins':
			case 'wp_update_themes':
			case 'wp_maybe_auto_update':
				// Log blocked cron event for debugging
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'WordPress Security & Performance Booster: Blocked cron event ' . $event->hook );
				}
				$event = false;
				break;
		}
		return $event;
	}
	
	
	/**
	 * Override version check info
	 * Only blocks updates if the disable_updates setting is enabled
	 *
	 * @since 1.6.0
	 * @param mixed $transient The transient value.
	 * @return object|mixed
	 */
	public function last_checked_atm( $transient ) {
		// Check if update blocking is actually enabled
		$settings = get_option( 'wpspb_settings', $this->get_default_settings() );
		
		// If updates are not disabled, let WordPress handle normally
		if ( ! isset( $settings['disable_updates'] ) || ! $settings['disable_updates'] ) {
			// Return the original transient to allow normal update checking
			return $transient;
		}
		
		// Only block if updates are explicitly disabled
		include ABSPATH . WPINC . '/version.php';
		
		$current = new stdClass();
		$current->updates = array();
		$current->version_checked = $wp_version; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		$current->last_checked = time();
		
		// Debug logging for update blocking
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Update check blocked - returning empty update object' );
		}
		
		return $current;
	}

	/**
	 * Disable comments for all post types
	 *
	 * @since 2.0.0
	 */
	public function disable_comments_post_types_support() {
		$post_types = get_post_types();
		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}

	/**
	 * Disable pingback header
	 *
	 * @since 2.0.0
	 */
	public function disable_pingback_header( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}

	/**
	 * Disable pingback URL
	 *
	 * @since 2.0.0
	 */
	public function disable_pingback_url( $output, $property ) {
		return ( $property == 'pingback_url' ) ? null : $output;
	}

	/**
	 * Disable XML-RPC pingback methods
	 *
	 * @since 2.0.0
	 */
	public function disable_xmlrpc_pingback_methods( $methods ) {
		unset( $methods['pingback.ping'] );
		unset( $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	}

	/**
	 * Completely disable pingbacks
	 *
	 * @since 2.0.0
	 */
	public function disable_pingbacks_completely() {
		// Disable pingback flag
		add_filter( 'xmlrpc_enabled', '__return_false' );
		
		// Remove pingback methods
		add_filter( 'xmlrpc_methods', function( $methods ) {
			unset( $methods['pingback.ping'] );
			unset( $methods['pingback.extensions.getPingbacks'] );
			return $methods;
		});
	}

	/**
	 * Remove comments from admin menu
	 *
	 * @since 2.0.0
	 */
	public function disable_comments_admin_menu() {
		remove_menu_page( 'edit-comments.php' );
	}

	/**
	 * Redirect comments admin page
	 *
	 * @since 2.0.0
	 */
	public function disable_comments_admin_menu_redirect() {
		global $pagenow;
		if ( $pagenow === 'edit-comments.php' ) {
			wp_redirect( admin_url() );
			exit;
		}
	}

	/**
	 * Remove comments from dashboard
	 *
	 * @since 2.0.0
	 */
	public function disable_comments_dashboard() {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}

	/**
	 * Remove comments from admin bar
	 *
	 * @since 2.0.0
	 */
	public function disable_comments_admin_bar() {
		if ( is_admin_bar_showing() ) {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		}
	}

	/**
	 * Hide plugin notifications
	 *
	 * @since 2.0.0
	 */
	public function hide_plugin_notifications() {
		// Remove all admin notices from other plugins
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'network_admin_notices' );
		remove_all_actions( 'all_admin_notices' );
	}

	/**
	 * Hide admin notices with CSS
	 *
	 * @since 2.0.0
	 */
	public function hide_admin_notices_css() {
		echo '<style>
			.notice, .update-nag, .updated, .error, .is-dismissible {
				display: none !important;
			}
		</style>';
	}

	/**
	 * Remove dashboard widgets
	 *
	 * @since 2.0.0
	 */
	public function remove_dashboard_widgets() {
		// Remove WordPress news and events
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		// Remove other WordPress news
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
		// Remove quick draft
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		// Remove recent drafts
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
		// Remove activity widget
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	}

	/**
	 * Admin settings page
	 *
	 * @since 2.0.0
	 */
	public function admin_settings_page() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-security-performance-booster' ) );
		}
		
		// Handle form submission
		if ( isset( $_POST['submit'] ) ) {
			// Verify nonce for security
			if ( ! wp_verify_nonce( $_POST['wpspb_settings_nonce'], 'wpspb_settings_action' ) ) {
				wp_die( esc_html__( 'Security check failed. Please try again.', 'wp-security-performance-booster' ) );
			}
			
			// Get current settings for comparison
			$old_settings = get_option( 'wpspb_settings', $this->get_default_settings() );
			
			// Sanitize and validate input
			// Process checkbox values properly (unchecked checkboxes are not submitted)
			$settings = array(
				'disable_updates'     => isset( $_POST['disable_updates'] ) && $_POST['disable_updates'] === '1',
				'disable_comments'    => isset( $_POST['disable_comments'] ) && $_POST['disable_comments'] === '1',
				'disable_xmlrpc'      => isset( $_POST['disable_xmlrpc'] ) && $_POST['disable_xmlrpc'] === '1',
				'hide_notifications'   => isset( $_POST['hide_notifications'] ) && $_POST['hide_notifications'] === '1',
				'disable_pingbacks'   => isset( $_POST['disable_pingbacks'] ) && $_POST['disable_pingbacks'] === '1',
				'clean_dashboard'     => isset( $_POST['clean_dashboard'] ) && $_POST['clean_dashboard'] === '1',
			);
			
			// Debug logging for form submission
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    $old_json = function_exists( 'wp_json_encode' ) ? wp_json_encode( $old_settings ) : json_encode( $old_settings );
                    $new_json = function_exists( 'wp_json_encode' ) ? wp_json_encode( $settings ) : json_encode( $settings );
                    error_log( 'WPSPB Settings Update - Old: ' . $old_json );
                    error_log( 'WPSPB Settings Update - New: ' . $new_json );
			}
			
			// Check if updates setting changed from enabled to disabled
			$updates_were_enabled = isset( $old_settings['disable_updates'] ) && $old_settings['disable_updates'];
			$updates_now_disabled = isset( $settings['disable_updates'] ) && ! $settings['disable_updates'];
			$updates_reactivated = $updates_were_enabled && $updates_now_disabled;
			
			// Debug logging for update reactivation check
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WPSPB Update Reactivation Check - Old: ' . ( $updates_were_enabled ? 'Enabled' : 'Disabled' ) . ', New: ' . ( $updates_now_disabled ? 'Disabled' : 'Enabled' ) . ', Reactivated: ' . ( $updates_reactivated ? 'Yes' : 'No' ) );
			}
			
			// Update settings
			update_option( 'wpspb_settings', $settings );
			
			// Save language preference
			if ( isset( $_POST['wpspb_language'] ) ) {
				$language = sanitize_text_field( wp_unslash( $_POST['wpspb_language'] ) );
				// Validate language code
				$allowed_languages = array( 'en_US', 'vi', 'de_DE', 'fr_FR' );
				if ( in_array( $language, $allowed_languages, true ) ) {
					update_option( 'wpspb_language', $language );
				}
			}
			
			// CRITICAL: Force complete cleanup before re-initializing
			$this->deactivate_all_features();
			
									
			// Clean up legacy settings if they exist
			$this->cleanup_legacy_settings();
									
			// Re-initialize features with new settings
			$this->init_features_conditionally();
			
			// If updates were just re-enabled, implement aggressive reactivation
			if ( $updates_reactivated ) {
				// Updates were just enabled - implement nuclear option for immediate visibility
				$this->nuclear_update_reactivation();
				
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'WPSPB: Updates were reactivated - implemented nuclear reactivation protocol' );
				}
			}
			
			// Force cache clearing for immediate effect
			wp_cache_flush();
			
			// Add success message
			$success_message = __( 'Settings saved successfully! All features have been updated.', 'wp-security-performance-booster' );
			if ( $updates_reactivated ) {
				$success_message .= ' ' . __( 'WordPress updates have been fully reactivated and should be visible immediately.', 'wp-security-performance-booster' );
			}
			echo '<div class="notice notice-success"><p>' . esc_html( $success_message ) . '</p></div>';
			
			// Always force a quick refresh so toggles OFF revert immediately (hooks run from page start)
			echo '<script type="text/javascript">setTimeout(function(){ window.location.reload(true); }, 800);</script>';
		}
		
		$settings = get_option('wpspb_settings', $this->get_default_settings());
		$current_language = get_option('wpspb_language', get_locale());
		
		?>
		<div class="wpspb-admin-wrap">
			<div class="wpspb-header">
				<div class="wpspb-logo">
					<h1><span class="wpspb-shield">🛡️</span> <?php echo __('Security & Performance Booster', 'wp-security-performance-booster'); ?></h1>
					<p class="wpspb-tagline"><?php echo __('by', 'wp-security-performance-booster'); ?> <strong>DPS.MEDIA</strong></p>
				</div>
				<div class="wpspb-version">
					<span class="version-badge">v1.0.0</span>
				</div>
			</div>
			
			<form method="post" action="" class="wpspb-form">
				<?php wp_nonce_field( 'wpspb_settings_action', 'wpspb_settings_nonce' ); ?>
			
			<!-- Language Switcher -->
				<div class="wpspb-language-section">
					<h3>🌍 <?php echo __('Language / Ngôn ngữ', 'wp-security-performance-booster'); ?></h3>
					<select name="wpspb_language" class="wpspb-language-select">
						<option value="en_US" <?php selected($current_language, 'en_US'); ?>>English</option>
						<option value="vi" <?php selected($current_language, 'vi'); ?>>Tiếng Việt</option>
						<option value="de_DE" <?php selected($current_language, 'de_DE'); ?>>Deutsch</option>
						<option value="fr_FR" <?php selected($current_language, 'fr_FR'); ?>>Français</option>
					</select>
				</div>
				
				<!-- Feature Status Verification -->
				<div class="wpspb-status-section">
					<h3>📊 <?php echo __('Current Feature Status', 'wp-security-performance-booster'); ?></h3>
					<div class="wpspb-status-grid">
						<?php
			$status_data = array(
				array(
					'name' => __('WordPress Updates', 'wp-security-performance-booster'),
					'enabled' => !empty($settings['disable_updates']),
					// Reflect current setting directly for clarity
					'status' => !empty($settings['disable_updates']),
					'description' => __('Core, plugins, themes update blocking', 'wp-security-performance-booster')
				),
				array(
					'name' => __('Comments System', 'wp-security-performance-booster'),
					'enabled' => !empty($settings['disable_comments']),
					'status' => !empty($settings['disable_comments']),
					'description' => __('Comment forms and functionality', 'wp-security-performance-booster')
				),
				array(
					'name' => __('XML-RPC Service', 'wp-security-performance-booster'),
					'enabled' => !empty($settings['disable_xmlrpc']),
					'status' => !empty($settings['disable_xmlrpc']),
					'description' => __('XML-RPC endpoint accessibility', 'wp-security-performance-booster')
				),
				array(
					'name' => __('Admin Notifications', 'wp-security-performance-booster'),
					'enabled' => !empty($settings['hide_notifications']),
					'status' => !empty($settings['hide_notifications']),
					'description' => __('Plugin/theme promotional messages', 'wp-security-performance-booster')
				)
			);
						
						foreach ($status_data as $item) {
							$icon = $item['status'] ? '✅' : '❌';
							$status_text = $item['status'] ? __('Blocked', 'wp-security-performance-booster') : __('Active', 'wp-security-performance-booster');
							$status_class = $item['status'] ? 'wpspb-status-blocked' : 'wpspb-status-active';
							echo '<div class="wpspb-status-item">';
							echo '<div class="wpspb-status-header">';
							echo '<span class="wpspb-status-icon">' . $icon . '</span>';
							echo '<span class="wpspb-status-name">' . $item['name'] . '</span>';
							echo '<span class="wpspb-status-badge ' . $status_class . '">' . $status_text . '</span>';
							echo '</div>';
							echo '<p class="wpspb-status-desc">' . $item['description'] . '</p>';
							echo '</div>';
						}
						?>
					</div>
				</div>
				<div class="wpspb-grid">
					<div class="wpspb-card">
						<h2><span class="card-icon">🔄</span> <?php echo __('Update Control', 'wp-security-performance-booster'); ?></h2>
						<p class="card-description"><?php echo __('Manage WordPress automatic updates and notifications', 'wp-security-performance-booster'); ?></p>
						
							<label class="wpspb-toggle">
								<input type="checkbox" name="disable_updates" value="1" <?php checked( $settings['disable_updates'] ); ?>>
								<span class="wpspb-slider"></span>
								<span class="wpspb-label"><?php echo __( 'Disable WordPress Updates', 'wp-security-performance-booster' ); ?></span>
							</label>
						<p class="wpspb-help"><?php echo __('Blocks all WordPress core, plugin, and theme update checks. Reduces server load and prevents automatic updates.', 'wp-security-performance-booster'); ?></p>
					</div>
					
					<div class="wpspb-card">
						<h2><span class="card-icon">💬</span> <?php echo __('Comment Protection', 'wp-security-performance-booster'); ?></h2>
						<p class="card-description"><?php echo __('Block spam comments and related features', 'wp-security-performance-booster'); ?></p>
						
							<label class="wpspb-toggle">
								<input type="checkbox" name="disable_comments" value="1" <?php checked( $settings['disable_comments'] ); ?>>
								<span class="wpspb-slider"></span>
								<span class="wpspb-label"><?php echo __( 'Disable Comments', 'wp-security-performance-booster' ); ?></span>
							</label>
						<p class="wpspb-help"><?php echo __('Completely disables comments across all post types. Removes comment forms, admin menus, and feeds.', 'wp-security-performance-booster'); ?></p>
						
							<label class="wpspb-toggle">
								<input type="checkbox" name="disable_pingbacks" value="1" <?php checked( $settings['disable_pingbacks'] ); ?>>
								<span class="wpspb-slider"></span>
								<span class="wpspb-label"><?php echo __( 'Block Pingbacks & Trackbacks', 'wp-security-performance-booster' ); ?></span>
							</label>
						<p class="wpspb-help"><?php echo __('Prevents pingback and trackback spam. Removes related headers and methods.', 'wp-security-performance-booster'); ?></p>
					</div>
					
					<div class="wpspb-card">
						<h2><span class="card-icon">🔒</span> <?php echo __('Security Enhancement', 'wp-security-performance-booster'); ?></h2>
						<p class="card-description"><?php echo __('Advanced security features and attack prevention', 'wp-security-performance-booster'); ?></p>
						
							<label class="wpspb-toggle">
								<input type="checkbox" name="disable_xmlrpc" value="1" <?php checked( $settings['disable_xmlrpc'] ); ?>>
								<span class="wpspb-slider"></span>
								<span class="wpspb-label"><?php echo __( 'Disable XML-RPC', 'wp-security-performance-booster' ); ?></span>
							</label>
						<p class="wpspb-help"><?php echo __('Blocks XML-RPC functionality to prevent brute force attacks and unauthorized access attempts.', 'wp-security-performance-booster'); ?></p>
					</div>
					
					<div class="wpspb-card">
						<h2><span class="card-icon">🧹</span> <?php echo __('Interface Cleanup', 'wp-security-performance-booster'); ?></h2>
						<p class="card-description"><?php echo __('Clean and optimize admin interface', 'wp-security-performance-booster'); ?></p>
						
							<label class="wpspb-toggle">
								<input type="checkbox" name="hide_notifications" value="1" <?php checked( $settings['hide_notifications'] ); ?>>
								<span class="wpspb-slider"></span>
								<span class="wpspb-label"><?php echo __( 'Hide Admin Notifications', 'wp-security-performance-booster' ); ?></span>
							</label>
						<p class="wpspb-help"><?php echo __('Removes plugin/theme promotional notifications and admin notices. Creates a cleaner interface.', 'wp-security-performance-booster'); ?></p>
						
							<label class="wpspb-toggle">
								<input type="checkbox" name="clean_dashboard" value="1" <?php checked( $settings['clean_dashboard'] ); ?>>
								<span class="wpspb-slider"></span>
								<span class="wpspb-label"><?php echo __( 'Clean Dashboard', 'wp-security-performance-booster' ); ?></span>
							</label>
						<p class="wpspb-help"><?php echo __('Removes unnecessary dashboard widgets like WordPress news, quick draft, and activity feeds.', 'wp-security-performance-booster'); ?></p>
					</div>
				</div>
				<div class="wpspb-submit-area">
					<button type="submit" name="submit" class="wpspb-btn-primary">
						<span class="btn-icon">💾</span> <?php echo __('Save Settings', 'wp-security-performance-booster'); ?>
					</button>
					
					<div class="wpspb-contact">
						<h3>📧 <?php echo __('Support & Contact', 'wp-security-performance-booster'); ?></h3>
						<p><strong><?php echo __('Developer:', 'wp-security-performance-booster'); ?></strong> HỒ QUANG HIỂN</p>
						<p><strong><?php echo __('Company:', 'wp-security-performance-booster'); ?></strong> DPS.MEDIA</p>
						<p><strong><?php echo __('Email:', 'wp-security-performance-booster'); ?></strong> <a href="mailto:hello@dps.media">hello@dps.media</a></p>
						<p><strong><?php echo __('Website:', 'wp-security-performance-booster'); ?></strong> <a href="https://dps.media" target="_blank">dps.media</a></p>
						<p><strong><?php echo __('Support:', 'wp-security-performance-booster'); ?></strong> <a href="https://dps.media/support" target="_blank">dps.media/support</a></p>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Check if WordPress updates are currently blocked
	 *
	 * @since 1.0.0
	 * @return bool True if updates are blocked, false otherwise
	 */
	private function check_updates_status() {
		// Check if update-related hooks are removed/blocked
		$blocked = false;
		
		// Check for common update blocking indicators
		if (!has_action('wp_version_check') || !has_action('wp_update_plugins') || !has_action('wp_update_themes')) {
			$blocked = true;
		}
		
		// Check if update transients are being blocked
		if (has_filter('pre_site_transient_update_core') || has_filter('pre_site_transient_update_plugins') || has_filter('pre_site_transient_update_themes')) {
			$blocked = true;
		}
		
		return $blocked;
	}

	/**
	 * Check if comments are currently disabled
	 *
	 * @since 1.0.0
	 * @return bool True if comments are disabled, false otherwise
	 */
	private function check_comments_status() {
		// Check if comments_open filter is set to false
		if (has_filter('comments_open', '__return_false')) {
			return true;
		}
		
		// Test with a sample post to see if comments are disabled
		$sample_post = get_posts(array('numberposts' => 1, 'post_status' => 'publish'));
		if (!empty($sample_post)) {
			return !comments_open($sample_post[0]->ID);
		}
		
		return false;
	}

	/**
	 * Check if XML-RPC is currently disabled
	 *
	 * @since 1.0.0
	 * @return bool True if XML-RPC is disabled, false otherwise
	 */
	private function check_xmlrpc_status() {
		// Check if xmlrpc_enabled filter is set to false
		return has_filter('xmlrpc_enabled', '__return_false') || !apply_filters('xmlrpc_enabled', true);
	}

	/**
	 * Get admin JavaScript for toggle functionality
	 *
	 * @since 1.0.0
	 * @return string JavaScript code
	 */
	private function get_admin_js() {
		return '
			document.addEventListener("DOMContentLoaded", function() {
				// Enhanced toggle functionality with improved state management
				const toggles = document.querySelectorAll(".wpspb-toggle");
				
				toggles.forEach(function(toggle) {
					const checkbox = toggle.querySelector("input[type=checkbox]");
					const slider = toggle.querySelector(".wpspb-slider");
					const label = toggle.querySelector(".wpspb-label");
					
					if (checkbox && slider) {
						// Enhanced state update function
						function updateToggleState(logChange = false) {
							const isChecked = checkbox.checked;
							
							// Ensure checkbox value is correctly set
							if (isChecked) {
								checkbox.value = "1";
							} else {
								checkbox.value = "0";
							}
							
							// Update CSS classes with proper state management
							toggle.classList.remove("wpspb-enabled", "wpspb-disabled");
							toggle.classList.add(isChecked ? "wpspb-enabled" : "wpspb-disabled");
							
							// Update slider state
							slider.classList.toggle("wpspb-active", isChecked);
							
							// Update accessibility attributes
							if (label) {
								label.setAttribute("aria-checked", isChecked.toString());
							}
							
							// Debug logging for state changes
							if (logChange) {
								console.log("WPSPB Toggle State Changed:", {
									name: checkbox.name,
									checked: isChecked,
									value: checkbox.value
								});
							}
						}
						
						// Initialize state
						updateToggleState(false);
						
						// Improved click handler with proper event management
						toggle.addEventListener("click", function(e) {
							// Prevent event bubbling and conflicts
							if (e.target !== checkbox) {
								e.preventDefault();
								// Toggle checkbox state
								checkbox.checked = !checkbox.checked;
								// Trigger change event for proper form handling
								var changeEvent = new Event("change", { bubbles: true });
								checkbox.dispatchEvent(changeEvent);
								// Update visual state with logging
								updateToggleState(true);
							}
						});
						
						// Handle direct checkbox changes (keyboard, etc.)
						checkbox.addEventListener("change", function(e) {
							updateToggleState(true);
						});
					}
				});
				
				// Replace broken icons and headings with Dashicons for a professional look
				(function(){
					// Header shield icon
					var shield = document.querySelector('.wpspb-logo .wpspb-shield');
					if (shield) {
						shield.outerHTML = '<span class="dashicons dashicons-shield-alt" aria-hidden="true"></span>';
					}
					// Language header
					var langH3 = document.querySelector('.wpspb-language-section h3');
					if (langH3) {
						langH3.innerHTML = '<span class="dashicons dashicons-translation" aria-hidden="true"></span> ' + 'Language';
					}
					// Status header
					var statusH3 = document.querySelector('.wpspb-status-section h3');
					if (statusH3) {
						statusH3.innerHTML = '<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span> ' + 'Current Feature Status';
					}
					// Status icons
					document.querySelectorAll('.wpspb-status-item').forEach(function(item){
						var badge = item.querySelector('.wpspb-status-badge');
						var iconSpan = item.querySelector('.wpspb-status-icon');
						if (badge && iconSpan) {
							if (badge.classList.contains('wpspb-status-blocked')) {
								iconSpan.innerHTML = '<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>';
							} else {
								iconSpan.innerHTML = '<span class="dashicons dashicons-yes" aria-hidden="true"></span>';
							}
						}
					});
					// Card icons by title
					document.querySelectorAll('.wpspb-card').forEach(function(card){
						var h2 = card.querySelector('h2');
						var iconEl = card.querySelector('.card-icon');
						if (!h2 || !iconEl) return;
						var t = (h2.textContent || '').toLowerCase();
						var iconClass = 'dashicons-admin-generic';
						if (t.indexOf('update') !== -1) iconClass = 'dashicons-update';
						else if (t.indexOf('comment') !== -1) iconClass = 'dashicons-admin-comments';
						else if (t.indexOf('security') !== -1) iconClass = 'dashicons-shield-alt';
						else if (t.indexOf('interface') !== -1 || t.indexOf('cleanup') !== -1) iconClass = 'dashicons-admin-appearance';
						iconEl.innerHTML = '<span class="dashicons ' + iconClass + '" aria-hidden="true"></span>';
					});
					// Save button icon
					var btnIcon = document.querySelector('.btn-icon');
					if (btnIcon) {
						btnIcon.innerHTML = '<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>';
					}
					// Support header icon
					var supportH3 = document.querySelector('.wpspb-contact h3');
					if (supportH3) {
						supportH3.innerHTML = '<span class="dashicons dashicons-sos" aria-hidden="true"></span> ' + supportH3.textContent.replace(/^[^A-Za-z]*/, '');
					}
				})();
				
				// Enhanced form validation with detailed logging
				const form = document.querySelector(".wpspb-form");
				if (form) {
					form.addEventListener("submit", function(e) {
						console.log("=== WPSPB Form Submission Debug ===");
						const formData = new FormData(form);
						
						// Log all form data
						for (let [key, value] of formData.entries()) {
							console.log(key + ": " + value);
						}
						
						// Log checkbox states specifically
						toggles.forEach(function(toggle) {
							const checkbox = toggle.querySelector("input[type=checkbox]");
							if (checkbox) {
								console.log("Checkbox " + checkbox.name + ": checked=" + checkbox.checked + ", value=" + checkbox.value);
							}
						});
						
						console.log("=== End Form Debug ===");
					});
				}
			});
		';
	}

	/**
	 * Get admin CSS styles
	 *
	 * @since 2.0.0
	 */
	private function get_admin_css() {
		return '
			.wpspb-admin-wrap {
				max-width: 1200px;
				margin: 20px 0;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			}
			
			.wpspb-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				background: linear-gradient(135deg, #151577, #32b561);
				padding: 30px;
				border-radius: 12px;
				color: white;
				margin-bottom: 30px;
				box-shadow: 0 8px 32px rgba(21, 21, 119, 0.3);
			}
			
			.wpspb-logo h1 {
				margin: 0;
				font-size: 28px;
				font-weight: 700;
				color: white;
			}
			.wpspb-logo .dashicons {
				font-size: 32px;
				width: 32px;
				height: 32px;
				line-height: 32px;
				margin-right: 10px;
				vertical-align: middle;
			}
			
			.wpspb-shield {
				font-size: 32px;
				margin-right: 10px;
			}
			
			.wpspb-tagline {
				margin: 5px 0 0 0;
				font-size: 14px;
				opacity: 0.9;
				font-weight: 400;
			}
			
			.version-badge {
				background: rgba(255, 255, 255, 0.2);
				padding: 8px 16px;
				border-radius: 20px;
				font-size: 12px;
				font-weight: 600;
				letter-spacing: 0.5px;
			}
			
			.wpspb-language-section {
				background: white;
				border: 1px solid #e1e5e9;
				border-radius: 12px;
				padding: 20px;
				margin-bottom: 24px;
				box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
			}
			
			.wpspb-language-section h3 {
				margin: 0 0 12px 0;
				font-size: 16px;
				color: #151577;
				font-weight: 600;
			}
			
			.wpspb-language-select {
				width: 200px;
				padding: 8px 12px;
				border: 2px solid #e1e5e9;
				border-radius: 6px;
				font-size: 14px;
				background: white;
				cursor: pointer;
				transition: border-color 0.3s ease;
			}
			
			.wpspb-language-select:focus {
				border-color: #32b561;
				outline: none;
				box-shadow: 0 0 0 3px rgba(50, 181, 97, 0.1);
			}
			
			.wpspb-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
				gap: 24px;
				margin-bottom: 30px;
			}
			
			.wpspb-card {
				background: white;
				border: 1px solid #e1e5e9;
				border-radius: 12px;
				padding: 24px;
				box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
				transition: all 0.3s ease;
			}
			
			.wpspb-card:hover {
				box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
				transform: translateY(-2px);
			}
			
			.wpspb-card h2 {
				margin: 0 0 8px 0;
				font-size: 18px;
				font-weight: 600;
				color: #151577;
				display: flex;
				align-items: center;
			}
			
			.card-icon {
				font-size: 20px;
				margin-right: 8px;
			}
			.card-icon .dashicons {
				font-size: 20px;
				width: 20px;
				height: 20px;
				line-height: 20px;
			}
			
			.card-description {
				color: #6b7280;
				font-size: 14px;
				margin: 0 0 20px 0;
				line-height: 1.5;
			}
			
			.wpspb-toggle {
				display: flex;
				align-items: center;
				margin: 16px 0;
				cursor: pointer;
				position: relative;
				user-select: none;
			}
			
			.wpspb-toggle input[type="checkbox"] {
				display: none;
			}
			
			.wpspb-slider {
				width: 50px;
				height: 24px;
				background: #cbd5e0;
				border-radius: 24px;
				position: relative;
				transition: all 0.3s ease;
				margin-right: 12px;
			}
			
			.wpspb-slider:before {
				content: "";
				position: absolute;
				width: 20px;
				height: 20px;
				border-radius: 50%;
				background: white;
				top: 2px;
				left: 2px;
				transition: all 0.3s ease;
				box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
			}
			
			/* Enhanced toggle state management with clear visual feedback */
			.wpspb-toggle.wpspb-disabled .wpspb-slider {
				background: #cbd5e0;
				border: 2px solid #e2e8f0;
			}
			
			.wpspb-toggle.wpspb-disabled .wpspb-slider:before {
				transform: translateX(0px);
				background: #f7fafc;
				border: 1px solid #cbd5e0;
			}
			
			.wpspb-toggle.wpspb-enabled .wpspb-slider {
				background: #32b561;
				border: 2px solid #28a745;
				box-shadow: 0 0 8px rgba(50, 181, 97, 0.3);
			}
			
			.wpspb-toggle.wpspb-enabled .wpspb-slider:before {
				transform: translateX(26px);
				background: white;
				border: 1px solid #28a745;
				box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
			}
			
			/* Hover effects for better UX */
			.wpspb-toggle:hover .wpspb-slider {
				transform: scale(1.05);
				transition: all 0.2s ease;
			}
			
			.wpspb-toggle:hover.wpspb-disabled .wpspb-slider {
				background: #a0aec0;
			}
			
			.wpspb-toggle:hover.wpspb-enabled .wpspb-slider {
				background: #2d9e4f;
				box-shadow: 0 0 12px rgba(50, 181, 97, 0.5);
			}
			
			/* Active/focus states for accessibility */
			.wpspb-toggle:active .wpspb-slider {
				transform: scale(0.95);
			}
			
			.wpspb-toggle:focus-within {
				outline: 2px solid #32b561;
				outline-offset: 2px;
			}
			
			/* Legacy support for existing CSS - removed for consistency */
			
			.wpspb-label {
				font-weight: 500;
				color: #374151;
				font-size: 14px;
			}
			
			.wpspb-help {
				font-size: 12px;
				color: #6b7280;
				margin: 8px 0 0 62px;
				line-height: 1.4;
			}
			
			.wpspb-submit-area {
				display: flex;
				justify-content: space-between;
				align-items: flex-start;
				gap: 30px;
				padding: 24px;
				background: #f9fafb;
				border-radius: 12px;
				border: 1px solid #e5e7eb;
			}
			
			.wpspb-btn-primary {
				background: linear-gradient(135deg, #151577, #32b561);
				color: white;
				border: none;
				padding: 16px 32px;
				border-radius: 8px;
				font-size: 16px;
				font-weight: 600;
				cursor: pointer;
				transition: all 0.3s ease;
				display: flex;
				align-items: center;
				box-shadow: 0 4px 12px rgba(21, 21, 119, 0.3);
			}
			
			.wpspb-btn-primary:hover {
				transform: translateY(-2px);
				box-shadow: 0 6px 20px rgba(21, 21, 119, 0.4);
			}
			
			.btn-icon {
				margin-right: 8px;
				font-size: 16px;
			}
			
			.wpspb-contact {
				flex: 1;
				max-width: 400px;
			}
			
			.wpspb-contact h3 {
				margin: 0 0 12px 0;
				font-size: 16px;
				color: #151577;
				font-weight: 600;
			}
			
			.wpspb-contact p {
				margin: 6px 0;
				font-size: 13px;
				color: #6b7280;
			}
			
			.wpspb-contact a {
				color: #32b561;
				text-decoration: none;
				font-weight: 500;
			}
			
			.wpspb-contact a:hover {
				text-decoration: underline;
			}
			
			.wpspb-status-section {
				background: white;
				border: 1px solid #e1e5e9;
				border-radius: 12px;
				padding: 24px;
				margin-bottom: 24px;
				box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
				border-left: 4px solid #32b561;
			}
			
			.wpspb-status-section h3 {
				margin: 0 0 16px 0;
				font-size: 18px;
				color: #32b561;
				font-weight: 600;
			}
			
			.wpspb-status-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
				gap: 16px;
			}
			
			.wpspb-status-item {
				padding: 16px;
				border: 1px solid #e1e5e9;
				border-radius: 8px;
				background: #f9fafb;
				transition: all 0.3s ease;
			}
			
			.wpspb-status-item:hover {
				box-shadow: 0 4px 12px rgba(0,0,0,0.1);
				transform: translateY(-1px);
			}
			
			.wpspb-status-header {
				display: flex;
				align-items: center;
				gap: 10px;
				margin-bottom: 8px;
			}
			
			.wpspb-status-icon {
				font-size: 16px;
				flex-shrink: 0;
			}
			.wpspb-status-icon .dashicons {
				font-size: 16px;
				width: 16px;
				height: 16px;
				line-height: 16px;
			}
			
			.wpspb-status-name {
				font-weight: 600;
				flex-grow: 1;
				color: #374151;
				font-size: 14px;
			}
			
			.wpspb-status-badge {
				padding: 4px 10px;
				border-radius: 12px;
				font-size: 11px;
				font-weight: 600;
				text-transform: uppercase;
				letter-spacing: 0.5px;
			}
			
			.wpspb-status-blocked {
				background: #d4edda;
				color: #155724;
				border: 1px solid #c3e6cb;
			}
			
			.wpspb-status-active {
				background: #f8d7da;
				color: #721c24;
				border: 1px solid #f5c6cb;
			}
			
			.wpspb-status-desc {
				margin: 0;
				font-size: 12px;
				color: #6b7280;
				line-height: 1.4;
			}
			
			@media (max-width: 768px) {
				.wpspb-header {
					flex-direction: column;
					text-align: center;
					gap: 15px;
				}
				
				.wpspb-grid {
					grid-template-columns: 1fr;
				}
				
				.wpspb-submit-area {
					flex-direction: column;
					align-items: center;
					text-align: center;
				}
			}
		';
	}
}

/**
 * Register activation/deactivation/uninstall hooks via lightweight wrappers
 * Ensures hooks are available during activation without relying on class instantiation order.
 */
if ( function_exists( 'register_activation_hook' ) ) {
    if ( ! function_exists( 'wpspb_do_activate' ) ) {
    function wpspb_do_activate() {
        // Respect minimum PHP requirement
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            return;
        }
        try {
            if ( class_exists( 'WP_Security_Performance_Booster' ) ) {
                WP_Security_Performance_Booster::get_instance()->activate();
            }
        } catch ( \Throwable $e ) {
            // Store error for admin notice and debugging
            $message = 'WPSPB activation error: ' . $e->getMessage();
            if ( function_exists( 'update_option' ) ) {
                update_option( 'wpspb_last_activation_error', $message );
            }
            if ( function_exists( 'error_log' ) ) {
                error_log( $message );
            }
        }
    }
    }
    register_activation_hook( __FILE__, 'wpspb_do_activate' );

    if ( ! function_exists( 'wpspb_do_deactivate' ) ) {
    function wpspb_do_deactivate() {
        if ( class_exists( 'WP_Security_Performance_Booster' ) ) {
            WP_Security_Performance_Booster::get_instance()->deactivate();
        }
    }
    }
    register_deactivation_hook( __FILE__, 'wpspb_do_deactivate' );

    if ( ! function_exists( 'wpspb_do_uninstall' ) ) {
    function wpspb_do_uninstall() {
        if ( is_callable( array( 'WP_Security_Performance_Booster', 'uninstall' ) ) ) {
            WP_Security_Performance_Booster::uninstall();
        }
    }
    }
    register_uninstall_hook( __FILE__, 'wpspb_do_uninstall' );
}

// Show a one-time admin notice if activation captured an error
if ( ! function_exists( 'wpspb_show_activation_error_notice' ) ) {
    function wpspb_show_activation_error_notice() {
        $msg = get_option( 'wpspb_last_activation_error' );
        if ( ! empty( $msg ) ) {
            echo '<div class="notice notice-error"><p>' . esc_html( $msg ) . '</p></div>';
            delete_option( 'wpspb_last_activation_error' );
        }
    }
    add_action( 'admin_notices', 'wpspb_show_activation_error_notice' );
}

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 * @return void
 */
function wpspb_init() {
	// Only initialize if we have the required PHP version
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		add_action( 'admin_notices', 'wpspb_php_version_notice' );
		return;
	}
	
	// Initialize the main plugin class
	WP_Security_Performance_Booster::get_instance();
}
add_action( 'plugins_loaded', 'wpspb_init' );

/**
 * Display PHP version notice if requirements not met
 *
 * @since 1.0.0
 * @return void
 */
function wpspb_php_version_notice() {
	$message = sprintf(
		/* translators: 1: Plugin name, 2: Required PHP version, 3: Current PHP version */
		esc_html__( '%1$s requires PHP version %2$s or higher. You are currently running PHP %3$s. Please upgrade PHP to use this plugin.', 'wp-security-performance-booster' ),
		'<strong>WordPress Security & Performance Booster</strong>',
		'7.4',
		PHP_VERSION
	);
	printf( '<div class="notice notice-error"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
