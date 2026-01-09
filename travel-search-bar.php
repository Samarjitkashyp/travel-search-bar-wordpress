<?php
/**
 * Plugin Name: Travel Search Bar with Advanced Filters
 * Plugin URI: https://yourwebsite.com/travel-search-bar
 * Description: A stylish search bar with advanced filters for travel websites, converted from Laravel project
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: travel-search-bar
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TSB_VERSION', '1.0.0');
define('TSB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TSB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TSB_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once TSB_PLUGIN_DIR . 'includes/class-travel-search-admin.php';
require_once TSB_PLUGIN_DIR . 'includes/class-travel-search-frontend.php';
require_once TSB_PLUGIN_DIR . 'includes/class-travel-search-ajax.php';
require_once TSB_PLUGIN_DIR . 'includes/class-travel-search-widget.php';

// Initialize the plugin
class TravelSearchBar {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Initialize admin
        if (is_admin()) {
            new TravelSearchBar_Admin();
        }
        
        // Initialize frontend
        new TravelSearchBar_Frontend();
        
        // Initialize AJAX handlers
        new TravelSearchBar_Ajax();
        
        // Initialize Widget
        add_action('widgets_init', [$this, 'register_widgets']);
        
        // Register activation hook
        register_activation_hook(__FILE__, [$this, 'activate']);
        
        // Register deactivation hook
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Load text domain for translations
        add_action('plugins_loaded', [$this, 'load_textdomain']);
    }
    
    /**
     * Register custom widgets
     */
    public function register_widgets() {
        register_widget('TravelSearchBar_Widget');
    }
    
    /**
     * ✅ FIXED: Add color brightness adjustment method
     * This can be called from templates
     */
    public static function adjust_color_brightness($hex, $steps) {
        // Steps should be between -255 and 255
        $steps = max(-255, min(255, $steps));
        
        // Normalize hex color
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . 
                   str_repeat(substr($hex, 1, 1), 2) . 
                   str_repeat(substr($hex, 2, 1), 2);
        }
        
        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Adjust brightness
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        
        // Convert back to hex
        $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
        $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
        $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
        
        return '#' . $r_hex . $g_hex . $b_hex;
    }
    
    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'travel-search-bar',
            false,
            dirname(TSB_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    public function activate() {
        // Create necessary database tables or options
        $default_settings = [
            'hero_background' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828',
            'search_placeholder' => 'Search destinations, hotels, attractions...',
            'enable_advanced_filters' => true,
            'filter_states' => ['assam', 'goa', 'kerala', 'rajasthan', 'himachal', 'uttarakhand', 'tamilnadu'],
            'filter_categories' => ['hill', 'beach', 'heritage', 'wildlife', 'adventure', 'religious', 'historical'],
            'enable_ajax_search' => true,
            'search_results_page' => '',
            'custom_css' => '',
            'primary_color' => '#3498db',
            'enable_widget' => true,
            'widget_title' => 'Search Destinations'
        ];
        
        add_option('travel_search_bar_settings', $default_settings);
        
        // Create default search results page if not exists
        $this->create_default_pages();
        
        flush_rewrite_rules();
    }
    
    /**
     * Create default pages for the plugin
     */
    private function create_default_pages() {
        // Check if search results page exists
        $results_page = get_page_by_path('search-results');
        
        if (!$results_page) {
            $page_data = [
                'post_title'    => __('Search Results', 'travel-search-bar'),
                'post_name'     => 'search-results',
                'post_content'  => '[travel_search_results]',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_author'   => 1,
                'meta_input'    => [
                    '_wp_page_template' => 'default'
                ]
            ];
            
            $page_id = wp_insert_post($page_data);
            
            // Update settings with new page ID
            $settings = get_option('travel_search_bar_settings', []);
            $settings['search_results_page'] = $page_id;
            update_option('travel_search_bar_settings', $settings);
        }
    }
    
    public function deactivate() {
        // Clear any scheduled tasks
        wp_clear_scheduled_hook('travel_search_cleanup_cache');
        
        flush_rewrite_rules();
    }
    
    /**
     * Get plugin settings
     */
    public static function get_settings() {
        return get_option('travel_search_bar_settings', []);
    }
    
    /**
     * Update plugin settings
     */
    public static function update_settings($settings) {
        return update_option('travel_search_bar_settings', $settings);
    }
    
    /**
     * Get a specific setting
     */
    public static function get_setting($key, $default = '') {
        $settings = self::get_settings();
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
}

// Initialize the plugin
TravelSearchBar::get_instance();

// ✅ Add helper functions for theme developers
if (!function_exists('display_travel_search_bar')) {
    /**
     * Display travel search bar anywhere in theme
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    function display_travel_search_bar($atts = []) {
        return do_shortcode('[travel_search_bar]');
    }
}

if (!function_exists('get_travel_search_bar')) {
    /**
     * Get travel search bar HTML without echoing
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    function get_travel_search_bar($atts = []) {
        ob_start();
        echo do_shortcode('[travel_search_bar]');
        return ob_get_clean();
    }
}

if (!function_exists('travel_search_bar_enabled')) {
    /**
     * Check if travel search bar is enabled
     * 
     * @return bool
     */
    function travel_search_bar_enabled() {
        $settings = TravelSearchBar::get_settings();
        return !empty($settings['enable_advanced_filters']);
    }
}

// ✅ Add color helper function that templates can use
if (!function_exists('tsb_adjust_color_brightness')) {
    /**
     * Adjust color brightness
     * 
     * @param string $hex Hex color code
     * @param int $steps Steps to adjust (-255 to 255)
     * @return string Adjusted hex color
     */
    function tsb_adjust_color_brightness($hex, $steps) {
        return TravelSearchBar::adjust_color_brightness($hex, $steps);
    }
}

// ✅ Add action hooks for other plugins
add_action('travel_search_bar_loaded', function() {
    do_action('travel_search_bar_init');
});

// ✅ Add filter for customizing output
add_filter('travel_search_bar_html', function($html, $atts) {
    // Allow other plugins/themes to modify the HTML
    return $html;
}, 10, 2);