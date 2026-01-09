<?php
class TravelSearchBar_Frontend {
    
    public function __construct() {
        // Add shortcode for search bar
        add_shortcode('travel_search_bar', [$this, 'render_search_bar']);
        
        // Add search results shortcode
        add_shortcode('travel_search_results', [$this, 'render_search_results']);
        
        // Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        
        // Register widget
        add_action('widgets_init', [$this, 'register_widget']);
    }
    
    public function render_search_bar($atts = []) {
        ob_start();
        
        // Get plugin settings
        $settings = TravelSearchBar::get_settings();
        
        // Extract shortcode attributes
        $atts = shortcode_atts([
            'background' => '',
            'placeholder' => $settings['search_placeholder'] ?? 'Search destinations, hotels, attractions...',
            'show_filters' => isset($settings['enable_advanced_filters']) ? $settings['enable_advanced_filters'] : true,
            'results_page' => $settings['search_results_page'] ?? '',
            'primary_color' => $settings['primary_color'] ?? '#3498db',
            'compact' => true,
            'show_hero' => false
        ], $atts);
        
        // Get filter options
        $states = is_array($settings['filter_states'] ?? '') ? $settings['filter_states'] : explode("\n", $settings['filter_states'] ?? '');
        $categories = is_array($settings['filter_categories'] ?? '') ? $settings['filter_categories'] : explode("\n", $settings['filter_categories'] ?? '');
        
        // Include the template
        include TSB_PLUGIN_DIR . 'templates/search-bar.php';
        
        return ob_get_clean();
    }
    
    public function render_search_results($atts = []) {
        ob_start();
        ?>
        <div class="travel-search-results">
            <h2>Search Results</h2>
            <p>Search functionality will be displayed here.</p>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function enqueue_frontend_scripts() {
        // Only enqueue on pages that need it
        if (!is_admin()) {
            // Enqueue CSS
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0');
            wp_enqueue_style('animate-css', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', [], '4.1.1');
            wp_enqueue_style('travel-search-bar-frontend', TSB_PLUGIN_URL . 'assets/css/frontend.css', [], TSB_VERSION);
            
            // Enqueue Bootstrap if not already loaded
            if (!wp_style_is('bootstrap', 'enqueued')) {
                wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', [], '5.3.0');
            }
            
            // Enqueue JS
            wp_enqueue_script('travel-search-bar-frontend', TSB_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], TSB_VERSION, true);
            
            // Localize script for AJAX
            wp_localize_script('travel-search-bar-frontend', 'travelSearchBar', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('travel_search_nonce'),
                'settings' => TravelSearchBar::get_settings()
            ]);
        }
    }
    
    public function register_widget() {
        require_once TSB_PLUGIN_DIR . 'includes/class-travel-search-widget.php';
        register_widget('TravelSearchBar_Widget');
    }
    
    /**
     * ✅ FIXED: Add color brightness adjustment method to frontend class
     * This ensures compatibility with template calls
     */
    public function adjust_color_brightness($hex, $steps) {
        // Use the main class method
        return TravelSearchBar::adjust_color_brightness($hex, $steps);
    }
}