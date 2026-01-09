<?php
class TravelSearchBar_Admin {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Travel Search Bar Settings',
            'Travel Search',
            'manage_options',
            'travel-search-bar',
            [$this, 'render_settings_page'],
            'dashicons-search',
            30
        );
        
        add_submenu_page(
            'travel-search-bar',
            'General Settings',
            'General',
            'manage_options',
            'travel-search-bar',
            [$this, 'render_settings_page']
        );
        
        add_submenu_page(
            'travel-search-bar',
            'Filter Settings',
            'Filters',
            'manage_options',
            'travel-search-bar-filters',
            [$this, 'render_filters_page']
        );
        
        add_submenu_page(
            'travel-search-bar',
            'Appearance',
            'Appearance',
            'manage_options',
            'travel-search-bar-appearance',
            [$this, 'render_appearance_page']
        );
    }
    
    public function register_settings() {
        register_setting('travel_search_bar_general', 'travel_search_bar_settings');
        
        // General settings section
        add_settings_section(
            'general_settings',
            'General Settings',
            [$this, 'general_settings_section_callback'],
            'travel-search-bar'
        );
        
        add_settings_field(
            'search_placeholder',
            'Search Placeholder Text',
            [$this, 'text_field_callback'],
            'travel-search-bar',
            'general_settings',
            [
                'label_for' => 'search_placeholder',
                'description' => 'Text to display in search input field'
            ]
        );
        
        add_settings_field(
            'enable_advanced_filters',
            'Enable Advanced Filters',
            [$this, 'checkbox_field_callback'],
            'travel-search-bar',
            'general_settings',
            [
                'label_for' => 'enable_advanced_filters',
                'description' => 'Show/hide advanced search filters'
            ]
        );
        
        add_settings_field(
            'search_results_page',
            'Search Results Page',
            [$this, 'page_select_callback'],
            'travel-search-bar',
            'general_settings',
            [
                'label_for' => 'search_results_page',
                'description' => 'Select page to display search results'
            ]
        );
        
        // Filter settings section
        add_settings_section(
            'filter_settings',
            'Filter Options',
            [$this, 'filter_settings_section_callback'],
            'travel-search-bar-filters'
        );
        
        add_settings_field(
            'filter_states',
            'States/Regions',
            [$this, 'multi_text_field_callback'],
            'travel-search-bar-filters',
            'filter_settings',
            [
                'label_for' => 'filter_states',
                'description' => 'Add states for filtering (one per line)'
            ]
        );
        
        add_settings_field(
            'filter_categories',
            'Categories',
            [$this, 'multi_text_field_callback'],
            'travel-search-bar-filters',
            'filter_settings',
            [
                'label_for' => 'filter_categories',
                'description' => 'Add categories for filtering (one per line)'
            ]
        );
        
        // Appearance settings section
        add_settings_section(
            'appearance_settings',
            'Appearance Settings',
            [$this, 'appearance_settings_section_callback'],
            'travel-search-bar-appearance'
        );
        
        add_settings_field(
            'hero_background',
            'Hero Background Image URL',
            [$this, 'text_field_callback'],
            'travel-search-bar-appearance',
            'appearance_settings',
            [
                'label_for' => 'hero_background',
                'description' => 'URL for hero section background image'
            ]
        );
        
        add_settings_field(
            'primary_color',
            'Primary Color',
            [$this, 'color_field_callback'],
            'travel-search-bar-appearance',
            'appearance_settings',
            [
                'label_for' => 'primary_color',
                'default' => '#3498db'
            ]
        );
        
        add_settings_field(
            'custom_css',
            'Custom CSS',
            [$this, 'textarea_field_callback'],
            'travel-search-bar-appearance',
            'appearance_settings',
            [
                'label_for' => 'custom_css',
                'description' => 'Add custom CSS styles'
            ]
        );
    }
    
    public function general_settings_section_callback() {
        echo '<p>Configure general search bar settings</p>';
    }
    
    public function filter_settings_section_callback() {
        echo '<p>Configure filter options for destinations</p>';
    }
    
    public function appearance_settings_section_callback() {
        echo '<p>Customize the appearance of the search bar</p>';
    }
    
    public function text_field_callback($args) {
        $options = get_option('travel_search_bar_settings');
        $value = $options[$args['label_for']] ?? '';
        ?>
        <input type="text" 
               id="<?php echo esc_attr($args['label_for']); ?>" 
               name="travel_search_bar_settings[<?php echo esc_attr($args['label_for']); ?>]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text">
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif;
    }
    
    public function checkbox_field_callback($args) {
        $options = get_option('travel_search_bar_settings');
        $checked = isset($options[$args['label_for']]) && $options[$args['label_for']];
        ?>
        <input type="checkbox" 
               id="<?php echo esc_attr($args['label_for']); ?>" 
               name="travel_search_bar_settings[<?php echo esc_attr($args['label_for']); ?>]" 
               value="1" 
               <?php checked($checked); ?>>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif;
    }
    
    public function page_select_callback($args) {
        $options = get_option('travel_search_bar_settings');
        $value = $options[$args['label_for']] ?? '';
        wp_dropdown_pages([
            'name' => 'travel_search_bar_settings[' . $args['label_for'] . ']',
            'id' => $args['label_for'],
            'selected' => $value,
            'show_option_none' => 'Select a page',
            'option_none_value' => ''
        ]);
        if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif;
    }
    
    public function multi_text_field_callback($args) {
        $options = get_option('travel_search_bar_settings');
        $values = $options[$args['label_for']] ?? [];
        if (is_string($values)) {
            $values = explode("\n", $values);
        }
        ?>
        <textarea id="<?php echo esc_attr($args['label_for']); ?>" 
                  name="travel_search_bar_settings[<?php echo esc_attr($args['label_for']); ?>]" 
                  rows="5" 
                  class="large-text"><?php echo esc_textarea(implode("\n", $values)); ?></textarea>
        <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php
    }
    
    public function color_field_callback($args) {
        $options = get_option('travel_search_bar_settings');
        $value = $options[$args['label_for']] ?? ($args['default'] ?? '#3498db');
        ?>
        <input type="color" 
               id="<?php echo esc_attr($args['label_for']); ?>" 
               name="travel_search_bar_settings[<?php echo esc_attr($args['label_for']); ?>]" 
               value="<?php echo esc_attr($value); ?>">
        <?php
    }
    
    public function textarea_field_callback($args) {
        $options = get_option('travel_search_bar_settings');
        $value = $options[$args['label_for']] ?? '';
        ?>
        <textarea id="<?php echo esc_attr($args['label_for']); ?>" 
                  name="travel_search_bar_settings[<?php echo esc_attr($args['label_for']); ?>]" 
                  rows="10" 
                  class="large-text"><?php echo esc_textarea($value); ?></textarea>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif;
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('travel_search_bar_general');
                do_settings_sections('travel-search-bar');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }
    
    public function render_filters_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Filter Settings</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('travel_search_bar_general');
                do_settings_sections('travel-search-bar-filters');
                submit_button('Save Filter Settings');
                ?>
            </form>
        </div>
        <?php
    }
    
    public function render_appearance_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Appearance Settings</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('travel_search_bar_general');
                do_settings_sections('travel-search-bar-appearance');
                submit_button('Save Appearance Settings');
                ?>
            </form>
        </div>
        <?php
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'travel-search-bar') === false) {
            return;
        }
        
        wp_enqueue_style('travel-search-bar-admin', TSB_PLUGIN_URL . 'assets/css/admin.css', [], TSB_VERSION);
        wp_enqueue_script('travel-search-bar-admin', TSB_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], TSB_VERSION, true);
    }
}