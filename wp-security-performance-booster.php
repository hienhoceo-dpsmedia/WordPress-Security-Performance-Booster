<?php
/**
 * WordPress Security & Performance Booster
 * 
 * A comprehensive WordPress plugin that enhances security and performance by:
 * - Disabling WordPress updates (core, plugins, themes)
 * - Blocking spam (comments, pingbacks, trackbacks, XML-RPC)
 * - Reducing server load and cleaning notification spam
 *
 * @package WordPress_Plugins
 * @subpackage WP_Security_Performance_Booster
 */

/*
Plugin Name: WordPress Security & Performance Booster
Description: Comprehensive security and performance enhancement plugin that disables updates, prevents spam (comments, pingbacks, trackbacks, XML-RPC), reduces server load, and cleans notification spam.
Plugin URI:  https://dps.media/
Version:     2.0.0
Author:      HỒ QUANG HIỂN
Author URI:  https://dps.media/
Text Domain: wp-security-performance-booster
Domain Path: /languages
License:	 GPL2

Developer: HỒ QUANG HIỂN
Company: DPS.MEDIA
Email: hello@dps.media
Website: dps.media
Support: dps.media/support

Copyright 2024 HỒ QUANG HIỂN (email: hello@dps.media)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



/**
 * Define the plugin version
 */
const WPSPB_VERSION = "2.0.0";


/**
 * The WP_Security_Performance_Booster class
 *
 * @package 	WordPress_Plugins
 * @subpackage 	WP_Security_Performance_Booster
 * @since 		1.0
 * @author 		hello@dps.media
 */
class WP_Security_Performance_Booster {
	/**
	 * The WP_Security_Performance_Booster class constructor
	 * initializing required stuff for the plugin
	 *
	 * PHP 5 Constructor
	 *
	 * @since 		1.0
	 * @author 		hello@dps.media
	 */
	function __construct() {
		add_action( 'admin_init', array(&$this, 'admin_init') );
		add_action( 'admin_menu', array(&$this, 'add_admin_menu') );
		add_action( 'admin_enqueue_scripts', array(&$this, 'enqueue_admin_assets') );
		add_action( 'plugins_loaded', array(&$this, 'load_textdomain') );

		// Initialize features based on settings
		$this->init_features_conditionally();
	}

	/**
	 * Initialize features based on user settings
	 *
	 * @since 2.0.0
	 */
	private function init_features_conditionally() {
		$settings = get_option('wpspb_settings', $this->get_default_settings());
		
		if ($settings['disable_updates']) {
			$this->init_update_blocking_features();
		}
		
		if ($settings['disable_comments']) {
			$this->init_anti_spam_features();
		}
		
		if ($settings['disable_xmlrpc']) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}
		
		if ($settings['hide_notifications']) {
			$this->init_notification_cleaning();
		}
		
		if ($settings['clean_dashboard']) {
			add_action( 'wp_dashboard_setup', array($this, 'remove_dashboard_widgets') );
		}
	}

	/**
	 * Get default settings
	 *
	 * @since 2.0.0
	 */
	private function get_default_settings() {
		return array(
			'disable_updates' => true,
			'disable_comments' => true,
			'disable_xmlrpc' => true,
			'hide_notifications' => true,
			'disable_pingbacks' => true,
			'clean_dashboard' => true
		);
	}

	/**
	 * Add admin menu
	 *
	 * @since 2.0.0
	 */
	public function add_admin_menu() {
		add_options_page(
			'Security & Performance Booster',
			'Security Booster',
			'manage_options',
			'wpspb-settings',
			array($this, 'admin_settings_page')
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @since 2.0.0
	 */
	public function enqueue_admin_assets($hook) {
		if ($hook !== 'settings_page_wpspb-settings') {
			return;
		}
		
		// Add inline CSS for modern design
		wp_add_inline_style('wp-admin', $this->get_admin_css());
	}

	/**
	 * Load plugin textdomain for translations
	 *
	 * @since 2.0.0
	 */
	public function load_textdomain() {
		$domain = 'wp-security-performance-booster';
		$locale = $this->get_plugin_locale();
		
		// Load locale-specific language file
		load_plugin_textdomain(
			$domain,
			false,
			dirname(plugin_basename(__FILE__)) . '/languages'
		);
	}

	/**
	 * Get plugin locale (with user preference override)
	 *
	 * @since 2.0.0
	 */
	private function get_plugin_locale() {
		// Check if user has set a language preference
		$user_locale = get_option('wpspb_language', '');
		
		if (!empty($user_locale)) {
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
		add_action('schedule_event', [$this, 'filter_cron_events']);
		
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
	}


	function admin_init() {
		if ( !function_exists("remove_action") ) return;

		// Register settings
		register_setting('wpspb_settings', 'wpspb_settings');

		if ( current_user_can( 'update_core' ) ) {
			add_action( 'admin_bar_menu', [$this, 'add_adminbar_items'], 100 );
			add_action( 'admin_enqueue_scripts', [$this, 'admin_css_overrides'] );
		}
		
		// Get current settings
		$settings = get_option('wpspb_settings', $this->get_default_settings());
		
		// Apply settings-based functionality
		if ($settings['disable_updates']) {
			/*
			 * Remove 'update plugins' option from bulk operations select list
			 */
			global $current_user;
			$current_user->allcaps['update_plugins'] = 0;
			
			/*
			 * Hide maintenance and update nag
			 */
			add_filter( 'site_status_tests', [$this, 'site_status_tests'] );
			remove_action( 'admin_notices', 'update_nag', 3 );
			remove_action( 'network_admin_notices', 'update_nag', 3 );
			remove_action( 'admin_notices', 'maintenance_nag' );
			remove_action( 'network_admin_notices', 'maintenance_nag' );
			
			/*
			 * Disable Theme Updates
			 * 2.8 to 3.0
			 */
			remove_action( 'load-themes.php', 'wp_update_themes' );
			remove_action( 'load-update.php', 'wp_update_themes' );
			remove_action( 'admin_init', '_maybe_update_themes' );
			remove_action( 'wp_update_themes', 'wp_update_themes' );
			wp_clear_scheduled_hook( 'wp_update_themes' );
			
			/*
			 * 3.0
			 */
			remove_action( 'load-update-core.php', 'wp_update_themes' );
			wp_clear_scheduled_hook( 'wp_update_themes' );
			
			/*
			 * Disable Plugin Updates
			 * 2.8 to 3.0
			 */
			remove_action( 'load-plugins.php', 'wp_update_plugins' );
			remove_action( 'load-update.php', 'wp_update_plugins' );
			remove_action( 'admin_init', '_maybe_update_plugins' );
			remove_action( 'wp_update_plugins', 'wp_update_plugins' );
			wp_clear_scheduled_hook( 'wp_update_plugins' );
			
			/*
			 * 3.0
			 */
			remove_action( 'load-update-core.php', 'wp_update_plugins' );
			wp_clear_scheduled_hook( 'wp_update_plugins' );
			
			/*
			 * Disable Core Updates
			 * 2.8 to 3.0
			 */
			add_filter( 'pre_option_update_core', '__return_null' );
			
			remove_action( 'wp_version_check', 'wp_version_check' );
			remove_action( 'admin_init', '_maybe_update_core' );
			wp_clear_scheduled_hook( 'wp_version_check' );
			
			/*
			 * 3.0
			 */
			wp_clear_scheduled_hook( 'wp_version_check' );
			
			/*
			 * 3.7+
			 */
			remove_action( 'wp_maybe_auto_update', 'wp_maybe_auto_update' );
			remove_action( 'admin_init', 'wp_maybe_auto_update' );
			remove_action( 'admin_init', 'wp_auto_update_core' );
			wp_clear_scheduled_hook( 'wp_maybe_auto_update' );
			
			remove_all_filters( 'plugins_api' );
		}
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
		$plugin_data = get_plugin_data( __FILE__ );

		$admin_bar->add_menu([
			'id' => 'wpspb-notice',
			'title' => '<span class="dashicons dashicons-shield-alt" aria-hidden="true"></span>',
			'href' => network_admin_url('plugins.php'),
			'meta' => [
				'class' => 'wp-admin-bar-wpspb-notice',
				'title' => sprintf(
					/* translators: %s: Name of the plugin */
					__('Security & Performance Booster is active: Updates disabled, spam blocked, notifications hidden!', 'wp-security-performance-booster'),
					$plugin_data['Name']
				)
			],
		]);
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
	 *
	 * @since 		1.4.4
	 */
	public function block_request($pre, $args, $url) {
		/* Empty url */
		if( empty( $url ) ) {
			return $pre;
		}

		/* Invalid host */
		if( !$host = parse_url($url, PHP_URL_HOST) ) {
			return $pre;
		}

		$url_data = parse_url( $url );

		/* block request */
		if( false !== stripos( $host, 'api.wordpress.org' ) &&
		    isset( $url_data['path'] ) &&
		    (false !== stripos( $url_data['path'], 'update-check' ) ||
		     false !== stripos( $url_data['path'], 'version-check' ) ||
		     false !== stripos( $url_data['path'], 'browse-happy' ) ||
		     false !== stripos( $url_data['path'], 'serve-happy' )) ) {
			return true;
		}

		return $pre;
	}


	/**
	 * Filter cron events
	 *
	 * @since 		1.5.0
	 */
	public function filter_cron_events($event) {
		switch( $event->hook ) {
			case 'wp_version_check':
			case 'wp_update_plugins':
			case 'wp_update_themes':
			case 'wp_maybe_auto_update':
				$event = false;
				break;
		}
		return $event;
	}
	
	
	/**
	 * Override version check info
	 *
	 * @since 		1.6.0
	 */
	public function last_checked_atm( $t ) {
		include ABSPATH . WPINC . '/version.php';
		
		$current = new stdClass;
		$current->updates = [];
		$current->version_checked = $wp_version;
		$current->last_checked = time();
		
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
			.notice, .error, .updated, .update-nag, .plugin-update-tr { display: none !important; }
			.wp-core-ui .notice, .wp-core-ui .error, .wp-core-ui .updated { display: none !important; }
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
		// Handle form submission
		if (isset($_POST['submit'])) {
			check_admin_referer('wpspb_settings_nonce');
			
			$settings = array(
				'disable_updates' => isset($_POST['disable_updates']),
				'disable_comments' => isset($_POST['disable_comments']),
				'disable_xmlrpc' => isset($_POST['disable_xmlrpc']),
				'hide_notifications' => isset($_POST['hide_notifications']),
				'disable_pingbacks' => isset($_POST['disable_pingbacks']),
				'clean_dashboard' => isset($_POST['clean_dashboard'])
			);
			
			update_option('wpspb_settings', $settings);
			
			// Save language preference
			if (isset($_POST['wpspb_language'])) {
				update_option('wpspb_language', sanitize_text_field($_POST['wpspb_language']));
			}
			
			echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'wp-security-performance-booster') . '</p></div>';
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
					<span class="version-badge">v2.0.0</span>
				</div>
			</div>
			
			<form method="post" action="" class="wpspb-form">
				<?php wp_nonce_field('wpspb_settings_nonce'); ?>
				
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
				<div class="wpspb-grid">
					<div class="wpspb-card">
						<h2><span class="card-icon">🔄</span> <?php echo __('Update Control', 'wp-security-performance-booster'); ?></h2>
						<p class="card-description"><?php echo __('Manage WordPress automatic updates and notifications', 'wp-security-performance-booster'); ?></p>
						
						<label class="wpspb-toggle">
							<input type="checkbox" name="disable_updates" <?php checked($settings['disable_updates']); ?>>
							<span class="wpspb-slider"></span>
							<span class="wpspb-label"><?php echo __('Disable WordPress Updates', 'wp-security-performance-booster'); ?></span>
						</label>
						<p class="wpspb-help"><?php echo __('Blocks all WordPress core, plugin, and theme update checks. Reduces server load and prevents automatic updates.', 'wp-security-performance-booster'); ?></p>
					</div>
					
					<div class="wpspb-card">
						<h2><span class="card-icon">💬</span> <?php echo __('Comment Protection', 'wp-security-performance-booster'); ?></h2>
						<p class="card-description"><?php echo __('Block spam comments and related features', 'wp-security-performance-booster'); ?></p>
						
						<label class="wpspb-toggle">
							<input type="checkbox" name="disable_comments" <?php checked($settings['disable_comments']); ?>>
							<span class="wpspb-slider"></span>
							<span class="wpspb-label"><?php echo __('Disable Comments', 'wp-security-performance-booster'); ?></span>
						</label>
						<p class="wpspb-help"><?php echo __('Completely disables comments across all post types. Removes comment forms, admin menus, and feeds.', 'wp-security-performance-booster'); ?></p>
						
						<label class="wpspb-toggle">
							<input type="checkbox" name="disable_pingbacks" <?php checked($settings['disable_pingbacks']); ?>>
							<span class="wpspb-slider"></span>
							<span class="wpspb-label"><?php echo __('Block Pingbacks & Trackbacks', 'wp-security-performance-booster'); ?></span>
						</label>
						<p class="wpspb-help"><?php echo __('Prevents pingback and trackback spam. Removes related headers and methods.', 'wp-security-performance-booster'); ?></p>
					</div>
					
					<div class="wpspb-card">
						<h2><span class="card-icon">🔒</span> <?php echo __('Security Enhancement', 'wp-security-performance-booster'); ?></h2>
						<p class="card-description"><?php echo __('Advanced security features and attack prevention', 'wp-security-performance-booster'); ?></p>
						
						<label class="wpspb-toggle">
							<input type="checkbox" name="disable_xmlrpc" <?php checked($settings['disable_xmlrpc']); ?>>
							<span class="wpspb-slider"></span>
							<span class="wpspb-label"><?php echo __('Disable XML-RPC', 'wp-security-performance-booster'); ?></span>
						</label>
						<p class="wpspb-help"><?php echo __('Blocks XML-RPC functionality to prevent brute force attacks and unauthorized access attempts.', 'wp-security-performance-booster'); ?></p>
					</div>
					
					<div class="wpspb-card">
						<h2><span class="card-icon">🧹</span> <?php echo __('Interface Cleanup', 'wp-security-performance-booster'); ?></h2>
						<p class="card-description"><?php echo __('Clean and optimize admin interface', 'wp-security-performance-booster'); ?></p>
						
						<label class="wpspb-toggle">
							<input type="checkbox" name="hide_notifications" <?php checked($settings['hide_notifications']); ?>>
							<span class="wpspb-slider"></span>
							<span class="wpspb-label"><?php echo __('Hide Admin Notifications', 'wp-security-performance-booster'); ?></span>
						</label>
						<p class="wpspb-help"><?php echo __('Removes plugin/theme promotional notifications and admin notices. Creates a cleaner interface.', 'wp-security-performance-booster'); ?></p>
						
						<label class="wpspb-toggle">
							<input type="checkbox" name="clean_dashboard" <?php checked($settings['clean_dashboard']); ?>>
							<span class="wpspb-slider"></span>
							<span class="wpspb-label"><?php echo __('Clean Dashboard', 'wp-security-performance-booster'); ?></span>
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
			
			.wpspb-toggle input:checked + .wpspb-slider {
				background: #32b561;
			}
			
			.wpspb-toggle input:checked + .wpspb-slider:before {
				transform: translateX(26px);
			}
			
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

if ( class_exists('WP_Security_Performance_Booster') ) {
	$WP_Security_Performance_Booster = new WP_Security_Performance_Booster();
}