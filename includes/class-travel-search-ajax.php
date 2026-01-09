<?php
class TravelSearchBar_Ajax {
    
    public function __construct() {
        // Search AJAX handler
        add_action('wp_ajax_travel_search', [$this, 'handle_search']);
        add_action('wp_ajax_nopriv_travel_search', [$this, 'handle_search']);
        
        // Get filter options AJAX handler
        add_action('wp_ajax_get_filter_options', [$this, 'get_filter_options']);
        add_action('wp_ajax_nopriv_get_filter_options', [$this, 'get_filter_options']);
        
        // Save search preferences
        add_action('wp_ajax_save_search_preferences', [$this, 'save_search_preferences']);
        add_action('wp_ajax_nopriv_save_search_preferences', [$this, 'save_search_preferences']);
    }
    
    public function handle_search() {
        // Verify nonce
        check_ajax_referer('travel_search_nonce', 'nonce');
        
        // Get search parameters
        $search_query = sanitize_text_field($_POST['search_query'] ?? '');
        $state = sanitize_text_field($_POST['state'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');
        $min_price = floatval($_POST['min_price'] ?? 0);
        $max_price = floatval($_POST['max_price'] ?? 100000);
        $rating = floatval($_POST['rating'] ?? 0);
        
        // This is where you would query your database
        // For now, we'll return demo data
        $results = $this->get_demo_results($search_query, $state, $category, $min_price, $max_price, $rating);
        
        wp_send_json_success([
            'results' => $results,
            'count' => count($results),
            'query' => $search_query
        ]);
    }
    
    private function get_demo_results($query, $state, $category, $min_price, $max_price, $rating) {
        // Demo data matching your Laravel structure
        $destinations = [
            [
                'id' => 1,
                'name' => 'Guwahati, Assam',
                'state' => 'assam',
                'category' => 'heritage',
                'price' => 8500,
                'image' => 'https://images.unsplash.com/photo-1552733407-5d5c46c3bb3b',
                'description' => 'Gateway to North-East with Kamakhya Temple, Brahmaputra river, and rich cultural heritage.',
                'rating' => 4.7,
                'hotels_count' => 42
            ],
            [
                'id' => 2,
                'name' => 'Goa Beaches',
                'state' => 'goa',
                'category' => 'beach',
                'price' => 12000,
                'image' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4',
                'description' => 'Famous for pristine beaches, Portuguese architecture, vibrant nightlife and seafood.',
                'rating' => 4.8,
                'hotels_count' => 68
            ],
            [
                'id' => 3,
                'name' => 'Munnar, Kerala',
                'state' => 'kerala',
                'category' => 'hill',
                'price' => 9500,
                'image' => 'https://images.unsplash.com/photo-1519681393784-d120267933ba',
                'description' => 'Beautiful hill station with tea plantations, waterfalls, and pleasant climate.',
                'rating' => 4.6,
                'hotels_count' => 35
            ]
        ];
        
        // Filter results based on search criteria
        $filtered = array_filter($destinations, function($dest) use ($query, $state, $category, $min_price, $max_price, $rating) {
            // Filter by search query
            if ($query && stripos($dest['name'], $query) === false && stripos($dest['description'], $query) === false) {
                return false;
            }
            
            // Filter by state
            if ($state && $dest['state'] !== $state) {
                return false;
            }
            
            // Filter by category
            if ($category && $dest['category'] !== $category) {
                return false;
            }
            
            // Filter by price range
            if ($dest['price'] < $min_price || $dest['price'] > $max_price) {
                return false;
            }
            
            // Filter by rating
            if ($dest['rating'] < $rating) {
                return false;
            }
            
            return true;
        });
        
        return array_values($filtered);
    }
    
    public function get_filter_options() {
        $settings = get_option('travel_search_bar_settings');
        
        $states = is_array($settings['filter_states'] ?? '') ? 
            $settings['filter_states'] : 
            explode("\n", $settings['filter_states'] ?? '');
        
        $categories = is_array($settings['filter_categories'] ?? '') ? 
            $settings['filter_categories'] : 
            explode("\n", $settings['filter_categories'] ?? '');
        
        wp_send_json_success([
            'states' => array_map('trim', $states),
            'categories' => array_map('trim', $categories)
        ]);
    }
    
    public function save_search_preferences() {
        // Verify nonce
        check_ajax_referer('travel_search_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }
        
        $preferences = [
            'search_filters' => $_POST['filters'] ?? [],
            'saved_date' => current_time('mysql')
        ];
        
        update_user_meta($user_id, 'travel_search_preferences', $preferences);
        
        wp_send_json_success('Preferences saved');
    }
}