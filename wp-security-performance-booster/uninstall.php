<?php
/**
 * Uninstall script for WordPress Security & Performance Booster
 *
 * This file is executed when the plugin is deleted from the WordPress admin.
 * It cleans up all plugin data, settings, and cached transients.
 *
 * @package WordPress_Security_Performance_Booster
 * @since   1.0.0
 * @author  HỒ QUANG HIỂN <hello@dps.media>
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Check user capabilities
if ( ! current_user_can( 'activate_plugins' ) ) {
	return;
}

// Check if the action was intended for this plugin
if ( __FILE__ != WP_UNINSTALL_PLUGIN ) {
	return;
}

/**
 * Delete plugin options
 *
 * Remove all stored plugin settings and options from the database
 * NOTE: For security plugins, settings should be preserved during upgrades
 */
function wpspb_cleanup_options() {
	// IMPORTANT: Preserve user settings during upgrades
	// Only delete version tracking and temporary data
	delete_option( 'wpspb_version' );
	delete_option( 'wpspb_last_db_check' );
	delete_option( 'wpspb_last_autoload_optimization' );
	
	// Delete only temporary/legacy options that shouldn't persist
	delete_option( 'wpspb_first_install' );
	delete_option( 'wpspb_activation_time' );
	delete_option( 'wpspb_activation_time_old' );
	delete_option( 'wpspb_old_settings' );
	delete_option( 'wpspb_legacy_options' );
	
	// NOTE: wpspb_settings and wpspb_language are preserved to maintain user preferences
	
	// Delete any cached transients
	delete_transient( 'wpspb_cache' );
	delete_transient( 'wpspb_status_cache' );
	delete_transient( 'wpspb_plugin_data' );
	delete_transient( 'wpspb_feature_check' );
	delete_transient( 'wp_security_booster_cache' );
	delete_transient( 'old_wpspb_data' );
	delete_transient( 'wpspb_legacy_cache' );
	
	// Delete site transients
	delete_site_transient( 'wpspb_cache' );
	delete_site_transient( 'wpspb_status_cache' );
	delete_site_transient( 'wpspb_plugin_data' );
	delete_site_transient( 'wp_security_booster_cache' );
	delete_site_transient( 'old_wpspb_data' );
	
	// For multisite installations
	if ( is_multisite() ) {
		delete_site_option( 'wpspb_settings' );
		delete_site_option( 'wpspb_version' );
		delete_site_option( 'wpspb_language' );
		delete_site_option( 'wpspb_last_db_check' );
		delete_site_option( 'wpspb_last_autoload_optimization' );
		delete_site_option( 'wp_security_booster_settings' );
		delete_site_option( 'wpspb_old_settings' );
		delete_site_option( 'wpspb_first_install' );
		delete_site_option( 'security_performance_booster_options' );
		delete_site_option( 'wpspb_legacy_options' );
	}
	
	// Clean up WordPress update-related transients that may have been modified
	delete_transient( 'update_core' );
	delete_transient( 'update_plugins' );
	delete_transient( 'update_themes' );
	delete_site_transient( 'update_core' );
	delete_site_transient( 'update_plugins' );
	delete_site_transient( 'update_themes' );
	
	// Clean up additional update-related transients
	delete_transient( 'update_plugins_last_checked' );
	delete_transient( 'update_themes_last_checked' );
	delete_site_transient( 'update_plugins_last_checked' );
	delete_site_transient( 'update_themes_last_checked' );
}

/**
 * Clean up user meta data
 *
 * Remove any user-specific plugin data
 */
function wpspb_cleanup_user_meta() {
	global $wpdb;
	
	// Remove user meta related to plugin
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'wpspb_%'" );
}

/**
 * Clean up scheduled events
 *
 * Remove any scheduled cron events created by the plugin
 */
function wpspb_cleanup_scheduled_events() {
	// Clear any scheduled hooks that might have been added
	wp_clear_scheduled_hook( 'wpspb_daily_cleanup' );
	wp_clear_scheduled_hook( 'wpspb_weekly_check' );
	
	// Re-enable WordPress native update schedules that may have been disabled
	if ( ! wp_next_scheduled( 'wp_version_check' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'wp_version_check' );
	}
	if ( ! wp_next_scheduled( 'wp_update_plugins' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'wp_update_plugins' );
	}
	if ( ! wp_next_scheduled( 'wp_update_themes' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'wp_update_themes' );
	}
	if ( ! wp_next_scheduled( 'wp_maybe_auto_update' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'wp_maybe_auto_update' );
	}
}

/**
 * Clean up custom database tables (if any)
 *
 * This plugin doesn't create custom tables, but this is here for future use
 */
function wpspb_cleanup_custom_tables() {
	global $wpdb;
	
	// No custom tables to remove in current version
	// This function is prepared for future versions
}

/**
 * Verify database tables before cleanup
 *
 * Ensures database integrity before performing cleanup operations
 */
function wpspb_verify_database_before_cleanup() {
	global $wpdb;
	
	// Check if we can connect to the database
	if ( ! $wpdb->check_connection() ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Database connection failed during uninstall verification' );
		}
		return false;
	}
	
	// Check core WordPress tables exist
	$required_tables = array(
		$wpdb->options,
		$wpdb->posts,
		$wpdb->users,
		$wpdb->usermeta
	);
	
	foreach ( $required_tables as $table ) {
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WPSPB: Required table missing during uninstall verification: ' . $table );
			}
			return false;
		}
	}
	
	return true;
}

/**
 * Clean up orphaned transients
 *
 * Remove expired and orphaned transients from the database
 */
function wpspb_cleanup_orphaned_transients() {
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
		error_log( 'WPSPB: Orphaned transients cleanup completed during uninstall' );
	}
}

/**
 * Main cleanup function
 *
 * Orchestrates the complete removal of plugin data
 */
function wpspb_uninstall_cleanup() {
	// Verify this is a legitimate uninstall
	if ( ! current_user_can( 'delete_plugins' ) ) {
		return;
	}
	
	// Verify database integrity before cleanup
	if ( ! wpspb_verify_database_before_cleanup() ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPSPB: Database verification failed, proceeding with cleanup anyway for safety' );
		}
	}
	
	// Clean up options
	wpspb_cleanup_options();
	
	// Clean up user meta
	wpspb_cleanup_user_meta();
	
	// Clean up scheduled events
	wpspb_cleanup_scheduled_events();
	
	// Clean up custom tables
	wpspb_cleanup_custom_tables();
	
	// Clean up orphaned transients
	wpspb_cleanup_orphaned_transients();
	
	// Flush rewrite rules
	flush_rewrite_rules();
	
	// Clear object cache
	wp_cache_flush();
	
	// Force immediate update checks to ensure updates work after uninstall
	if ( function_exists( 'wp_version_check' ) ) {
		wp_version_check();
	}
	if ( function_exists( 'wp_update_plugins' ) ) {
		wp_update_plugins();
	}
	if ( function_exists( 'wp_update_themes' ) ) {
		wp_update_themes();
	}
}

// Execute cleanup
wpspb_uninstall_cleanup();

// Log the uninstall for debugging (only if WP_DEBUG is enabled)
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( 'WordPress Security & Performance Booster: Plugin uninstalled and cleaned up successfully.' );
}