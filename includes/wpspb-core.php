<?php
/**
 * Internal core for WordPress Security & Performance Booster
 *
 * Note: Plugin headers live only in wp-security-performance-booster.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'WPSPB_VERSION' ) ) {
    define( 'WPSPB_VERSION', '1.0.7' );
}

if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
    return;
}

class WP_Security_Performance_Booster {
    private static $instance = null;

    private $option_key = 'wpspb_settings';

    private $defaults = array(
        // Updates (granular)
        'disable_core_updates'   => false,
        'disable_plugin_updates' => false,
        'disable_theme_updates'  => false,
        // Other features
        'disable_comments'       => false,
        'disable_xmlrpc'         => false,
        'hide_notifications'     => false,
        'disable_pingbacks'      => false,
        'clean_dashboard'        => false,
    );

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
        add_action( 'init', array( $this, 'apply_features' ), 0 );
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_indicator' ), 100 );
    }

    public function activate() {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        if ( false === get_option( $this->option_key, false ) ) {
            add_option( $this->option_key, $this->defaults );
        }
        update_option( 'wpspb_version', WPSPB_VERSION );

        // Clear update-related transients for a clean slate
        delete_site_transient( 'update_core' );
        delete_site_transient( 'update_plugins' );
        delete_site_transient( 'update_themes' );
        delete_transient( 'update_core' );
        delete_transient( 'update_plugins' );
        delete_transient( 'update_themes' );

        flush_rewrite_rules();
    }

    public function deactivate() {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        delete_transient( 'wpspb_cache' );
        delete_site_transient( 'wpspb_cache' );

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

        flush_rewrite_rules();
        wp_cache_flush();
    }

    public static function uninstall() {
        $uninstall = plugin_dir_path( dirname( __FILE__ ) ) . 'uninstall.php';
        if ( file_exists( $uninstall ) ) {
            include $uninstall;
        }
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'wp-security-performance-booster', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages' );
    }

    public function register_settings() {
        register_setting( $this->option_key, $this->option_key, array( $this, 'sanitize_settings' ) );
    }

    public function add_admin_menu() {
        add_options_page(
            __( 'Security & Performance Booster', 'wp-security-performance-booster' ),
            __( 'Security Booster', 'wp-security-performance-booster' ),
            'manage_options',
            'wpspb-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function admin_enqueue( $hook ) {
        if ( 'settings_page_wpspb-settings' !== $hook ) {
            return;
        }
        wp_enqueue_style( 'dashicons' );
        if ( ! wp_style_is( 'wpspb-admin', 'registered' ) ) {
            wp_register_style( 'wpspb-admin', false );
        }
        wp_enqueue_style( 'wpspb-admin' );
        $css = '.wpspb-wrap{max-width:820px;margin-top:20px}.wpspb-card{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px}.wpspb-card h2{margin-top:0}.wpspb-card h3{margin:18px 0 8px}.wpspb-card .description{color:#555;display:block;margin:6px 0 0}';
        wp_add_inline_style( 'wpspb-admin', $css );
    }

    public function apply_features() {
        $settings = $this->get_settings();

        // Updates (granular toggles)
        $this->hook_update_blockers( $settings );
        if ( ! empty( $settings['disable_comments'] ) ) {
            $this->hook_disable_comments();
        }
        if ( ! empty( $settings['disable_xmlrpc'] ) ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
            add_filter( 'wp_headers', array( $this, 'remove_pingback_header' ) );
            add_filter( 'xmlrpc_methods', array( $this, 'disable_xmlrpc_pingback_methods' ) );
        }
        if ( ! empty( $settings['hide_notifications'] ) ) {
            add_action( 'admin_head', array( $this, 'hide_admin_notices_css' ) );
        }
        if ( ! empty( $settings['disable_pingbacks'] ) ) {
            add_filter( 'wp_headers', array( $this, 'remove_pingback_header' ) );
            add_filter( 'xmlrpc_methods', array( $this, 'disable_xmlrpc_pingback_methods' ) );
        }
        if ( ! empty( $settings['clean_dashboard'] ) ) {
            add_action( 'wp_dashboard_setup', array( $this, 'clean_dashboard_widgets' ) );
        }
    }

    private function hook_update_blockers( array $settings ) {
        // Transient blockers
        if ( ! empty( $settings['disable_plugin_updates'] ) ) {
            add_filter( 'pre_site_transient_update_plugins', array( $this, 'block_update_transient' ) );
            add_filter( 'auto_update_plugin', '__return_false' );
        }
        if ( ! empty( $settings['disable_theme_updates'] ) ) {
            add_filter( 'pre_site_transient_update_themes', array( $this, 'block_update_transient' ) );
            add_filter( 'auto_update_theme', '__return_false' );
        }
        if ( ! empty( $settings['disable_core_updates'] ) ) {
            add_filter( 'pre_site_transient_update_core', array( $this, 'block_update_transient' ) );
            add_filter( 'automatic_updater_disabled', '__return_true' );
            add_filter( 'allow_minor_auto_core_updates', '__return_false' );
            add_filter( 'allow_major_auto_core_updates', '__return_false' );
            add_filter( 'allow_dev_auto_core_updates', '__return_false' );
            add_filter( 'auto_update_core', '__return_false' );
        }
        // HTTP request blocker (checks settings at runtime)
        add_filter( 'pre_http_request', array( $this, 'block_update_http' ), 10, 3 );
        // Cron schedule blocker (checks settings at runtime)
        add_filter( 'schedule_event', array( $this, 'filter_schedule_update_events' ) );
    }

    public function block_update_transient( $value ) {
        $obj = new stdClass();
        $obj->last_checked = time();
        return $obj;
    }

    public function block_update_http( $pre, $args, $url ) {
        $settings = $this->get_settings();
        if ( empty( $url ) || ! is_string( $url ) ) {
            return $pre;
        }
        $host = wp_parse_url( $url, PHP_URL_HOST );
        $path = wp_parse_url( $url, PHP_URL_PATH );
        if ( ! $host ) {
            return $pre;
        }
        if ( false !== stripos( $host, 'api.wordpress.org' ) && is_string( $path ) ) {
            // Core updates and related checks
            if ( ! empty( $settings['disable_core_updates'] ) && ( false !== stripos( $path, '/core/version-check' ) || false !== stripos( $path, '/core/browse-happy' ) || false !== stripos( $path, '/core/serve-happy' ) ) ) {
                return true;
            }
            // Plugin updates
            if ( ! empty( $settings['disable_plugin_updates'] ) && false !== stripos( $path, '/plugins/update-check' ) ) {
                return true;
            }
            // Theme updates
            if ( ! empty( $settings['disable_theme_updates'] ) && false !== stripos( $path, '/themes/update-check' ) ) {
                return true;
            }
        }
        return $pre;
    }

    public function filter_schedule_update_events( $event ) {
        $settings = $this->get_settings();
        if ( is_object( $event ) && isset( $event->hook ) ) {
            if ( 'wp_version_check' === $event->hook && ! empty( $settings['disable_core_updates'] ) ) { return false; }
            if ( 'wp_update_plugins' === $event->hook && ! empty( $settings['disable_plugin_updates'] ) ) { return false; }
            if ( 'wp_update_themes' === $event->hook && ! empty( $settings['disable_theme_updates'] ) ) { return false; }
            if ( 'wp_maybe_auto_update' === $event->hook && ( ! empty( $settings['disable_core_updates'] ) || ! empty( $settings['disable_plugin_updates'] ) || ! empty( $settings['disable_theme_updates'] ) ) ) { return false; }
        }
        return $event;
    }

    private function hook_disable_comments() {
        add_action( 'admin_menu', array( $this, 'remove_comments_menu' ) );
        add_action( 'wp_before_admin_bar_render', array( $this, 'remove_admin_bar_comments' ) );
        add_action( 'init', array( $this, 'disable_comments_post_types' ), 100 );
        add_filter( 'comments_open', '__return_false', 20, 2 );
        add_filter( 'pings_open', '__return_false', 20, 2 );
        add_filter( 'comments_array', '__return_empty_array', 10, 2 );
    }

    public function disable_comments_post_types() {
        $types = get_post_types();
        foreach ( $types as $type ) {
            if ( post_type_supports( $type, 'comments' ) ) {
                remove_post_type_support( $type, 'comments' );
                remove_post_type_support( $type, 'trackbacks' );
            }
        }
    }

    public function remove_comments_menu() {
        remove_menu_page( 'edit-comments.php' );
    }

    public function remove_admin_bar_comments() {
        if ( is_admin_bar_showing() ) {
            global $wp_admin_bar;
            if ( $wp_admin_bar ) {
                $wp_admin_bar->remove_menu( 'comments' );
            }
        }
    }

    public function remove_pingback_header( $headers ) {
        if ( isset( $headers['X-Pingback'] ) ) {
            unset( $headers['X-Pingback'] );
        }
        return $headers;
    }

    public function disable_xmlrpc_pingback_methods( $methods ) {
        if ( is_array( $methods ) ) {
            unset( $methods['pingback.ping'] );
            unset( $methods['pingback.extensions.getPingbacks'] );
        }
        return $methods;
    }

    public function hide_admin_notices_css() {
        echo '<style>.update-nag,.notice.notice-info,.notice.notice-warning{display:none!important}</style>';
    }

    public function clean_dashboard_widgets() {
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
    }

    public function admin_bar_indicator( $wp_admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $wp_admin_bar->add_menu( array(
            'id'    => 'wpspb-indicator',
            'title' => '<span class="ab-icon dashicons dashicons-shield-alt"></span><span class="ab-label">' . esc_html__( 'Security Booster', 'wp-security-performance-booster' ) . '</span>',
            'href'  => admin_url( 'options-general.php?page=wpspb-settings' ),
            'meta'  => array( 'title' => esc_html__( 'Security & Performance Booster', 'wp-security-performance-booster' ) ),
        ) );
    }

    public function sanitize_settings( $input ) {
        $clean = array();
        foreach ( $this->defaults as $key => $default ) {
            $clean[ $key ] = isset( $input[ $key ] ) ? (bool) $input[ $key ] : false;
        }
        return $clean;
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-security-performance-booster' ) );
        }
        $settings = $this->get_settings();
        ?>
        <div class="wrap wpspb-wrap">
            <h1><?php echo esc_html__( 'Security & Performance Booster', 'wp-security-performance-booster' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( $this->option_key ); ?>
                <div class="wpspb-card">
                    <h2><?php echo esc_html__( 'Features', 'wp-security-performance-booster' ); ?></h2>
                    <h3><?php echo esc_html__( 'Updates', 'wp-security-performance-booster' ); ?></h3>
                    <p><label><input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[disable_core_updates]" value="1" <?php checked( ! empty( $settings['disable_core_updates'] ) ); ?> /> <?php echo esc_html__( 'Disable Core Updates', 'wp-security-performance-booster' ); ?></label><br /><span class="description"><?php echo esc_html__( 'Blocks WordPress core version checks and automatic updates. Use on staging or when you pin WP version. Security updates must be applied manually.', 'wp-security-performance-booster' ); ?></span></p>
                    <p><label><input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[disable_plugin_updates]" value="1" <?php checked( ! empty( $settings['disable_plugin_updates'] ) ); ?> /> <?php echo esc_html__( 'Disable Plugin Updates', 'wp-security-performance-booster' ); ?></label><br /><span class="description"><?php echo esc_html__( 'Stops plugin update checks and auto-updates. Useful for controlled deploys or pinned plugin versions.', 'wp-security-performance-booster' ); ?></span></p>
                    <p><label><input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[disable_theme_updates]" value="1" <?php checked( ! empty( $settings['disable_theme_updates'] ) ); ?> /> <?php echo esc_html__( 'Disable Theme Updates', 'wp-security-performance-booster' ); ?></label><br /><span class="description"><?php echo esc_html__( 'Stops theme update checks and auto-updates. Use when themes are managed externally or pinned.', 'wp-security-performance-booster' ); ?></span></p>
                    <h3><?php echo esc_html__( 'Comments & Discussion', 'wp-security-performance-booster' ); ?></h3>
                    <p><label><input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[disable_comments]" value="1" <?php checked( ! empty( $settings['disable_comments'] ) ); ?> /> <?php echo esc_html__( 'Disable Comments and Trackbacks', 'wp-security-performance-booster' ); ?></label><br /><span class="description"><?php echo esc_html__( 'Removes comment functionality across all post types, disables trackbacks, and cleans related UI.', 'wp-security-performance-booster' ); ?></span></p>
                    <h3><?php echo esc_html__( 'Security', 'wp-security-performance-booster' ); ?></h3>
                    <p><label><input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[disable_xmlrpc]" value="1" <?php checked( ! empty( $settings['disable_xmlrpc'] ) ); ?> /> <?php echo esc_html__( 'Disable XML-RPC', 'wp-security-performance-booster' ); ?></label><br /><span class="description"><?php echo esc_html__( 'Blocks the XML-RPC endpoint to reduce brute-force and pingback abuse. Avoid if you rely on apps that need XML-RPC.', 'wp-security-performance-booster' ); ?></span></p>
                    <p><label><input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[disable_pingbacks]" value="1" <?php checked( ! empty( $settings['disable_pingbacks'] ) ); ?> /> <?php echo esc_html__( 'Disable Pingbacks', 'wp-security-performance-booster' ); ?></label><br /><span class="description"><?php echo esc_html__( 'Removes pingback headers and XML-RPC pingback methods to mitigate reflection attacks.', 'wp-security-performance-booster' ); ?></span></p>
                    <h3><?php echo esc_html__( 'Admin Experience', 'wp-security-performance-booster' ); ?></h3>
                    <p><label><input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[hide_notifications]" value="1" <?php checked( ! empty( $settings['hide_notifications'] ) ); ?> /> <?php echo esc_html__( 'Hide Admin Notifications', 'wp-security-performance-booster' ); ?></label><br /><span class="description"><?php echo esc_html__( 'Suppresses most plugin/theme nag notices for a cleaner admin. Use carefully to avoid missing important alerts.', 'wp-security-performance-booster' ); ?></span></p>
                    <p><label><input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[clean_dashboard]" value="1" <?php checked( ! empty( $settings['clean_dashboard'] ) ); ?> /> <?php echo esc_html__( 'Clean Dashboard Widgets', 'wp-security-performance-booster' ); ?></label><br /><span class="description"><?php echo esc_html__( 'Removes WordPress news/quick draft/activity widgets to reduce clutter and improve load time.', 'wp-security-performance-booster' ); ?></span></p>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    private function get_settings() {
        $settings = get_option( $this->option_key, $this->defaults );
        // Migrate legacy single toggle to granular, if present
        if ( isset( $settings['disable_updates'] ) ) {
            $flag = (bool) $settings['disable_updates'];
            unset( $settings['disable_updates'] );
            if ( ! isset( $settings['disable_core_updates'] ) ) {
                $settings['disable_core_updates'] = $flag;
            }
            if ( ! isset( $settings['disable_plugin_updates'] ) ) {
                $settings['disable_plugin_updates'] = $flag;
            }
            if ( ! isset( $settings['disable_theme_updates'] ) ) {
                $settings['disable_theme_updates'] = $flag;
            }
            // Persist migration
            update_option( $this->option_key, $settings );
        }
        // Merge with defaults to ensure all keys exist
        return array_merge( $this->defaults, is_array( $settings ) ? $settings : array() );
    }
}
