<?php
/**
 * Plugin Name: WordPress Security & Performance Booster
 * Plugin URI:  https://github.com/hienhoceo-dpsmedia/WordPress-Security-Performance-Booster
 * Description: Comprehensive security and performance enhancement plugin that disables updates, prevents spam (comments, pingbacks, trackbacks, XML-RPC), reduces server load, and cleans notification spam. Perfect for expert users and development environments.
 * Version:     1.0.5
 * Author:      H? QUANG HI?N
 * Author URI:  https://dps.media/
 * Text Domain: wp-security-performance-booster
 * Domain Path: /languages
 * Requires at least: 4.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define version constant (simple string)
if ( ! defined( 'WPSPB_VERSION' ) ) {
    define( 'WPSPB_VERSION', '1.0.5' );
}

// Minimal, PHP-5.2-compatible loader. Do not use short arrays or modern syntax here.

// Show admin notice when PHP is below minimum and stop loading the plugin.
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
    if ( is_admin() ) {
        function wpspb_min_php_notice() {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'WordPress Security & Performance Booster requires PHP 7.4 or higher. Please upgrade PHP.', 'wp-security-performance-booster' ) . '</p></div>';
        }
        add_action( 'admin_notices', 'wpspb_min_php_notice' );
    }
    // Prevent activation from succeeding silently on unsupported PHP.
    if ( function_exists( 'register_activation_hook' ) ) {
        function wpspb_block_activate_due_to_php() {
            // Intentionally no-op; WordPress will show the generic fatal message if activation fails.
        }
        register_activation_hook( __FILE__, 'wpspb_block_activate_due_to_php' );
    }
    return;
}

// At this point, environment is compatible. Load the core.
define( 'WPSPB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSPB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Activation/deactivation/uninstall wrappers
if ( function_exists( 'register_activation_hook' ) ) {
    if ( ! function_exists( 'wpspb_activate_wrapper' ) ) {
        function wpspb_activate_wrapper() {
            // Load the core class file first
            require_once WPSPB_PLUGIN_PATH . 'includes/wpspb-core.php';
            if ( class_exists( 'WP_Security_Performance_Booster' ) ) {
                try {
                    WP_Security_Performance_Booster::get_instance()->activate();
                } catch ( Exception $e ) {
                    update_option( 'wpspb_last_activation_error', 'WPSPB activation error: ' . $e->getMessage() );
                }
            }
        }
    }
    register_activation_hook( __FILE__, 'wpspb_activate_wrapper' );

    if ( ! function_exists( 'wpspb_deactivate_wrapper' ) ) {
        function wpspb_deactivate_wrapper() {
            require_once WPSPB_PLUGIN_PATH . 'includes/wpspb-core.php';
            if ( class_exists( 'WP_Security_Performance_Booster' ) ) {
                WP_Security_Performance_Booster::get_instance()->deactivate();
            }
        }
    }
    register_deactivation_hook( __FILE__, 'wpspb_deactivate_wrapper' );

    if ( ! function_exists( 'wpspb_uninstall_wrapper' ) ) {
        function wpspb_uninstall_wrapper() {
            require_once WPSPB_PLUGIN_PATH . 'includes/wpspb-core.php';
            if ( is_callable( array( 'WP_Security_Performance_Booster', 'uninstall' ) ) ) {
                WP_Security_Performance_Booster::uninstall();
            }
        }
    }
    register_uninstall_hook( __FILE__, 'wpspb_uninstall_wrapper' );
}

// Bootstrap after plugins are loaded
function wpspb_bootstrap() {
    require_once WPSPB_PLUGIN_PATH . 'includes/wpspb-core.php';
    if ( class_exists( 'WP_Security_Performance_Booster' ) ) {
        WP_Security_Performance_Booster::get_instance();
    }
}
add_action( 'plugins_loaded', 'wpspb_bootstrap' );

// Display any captured activation error once in admin
function wpspb_show_last_activation_error_notice() {
    $msg = get_option( 'wpspb_last_activation_error' );
    if ( $msg ) {
        echo '<div class="notice notice-error"><p>' . esc_html( $msg ) . '</p></div>';
        delete_option( 'wpspb_last_activation_error' );
    }
}
add_action( 'admin_notices', 'wpspb_show_last_activation_error_notice' );
