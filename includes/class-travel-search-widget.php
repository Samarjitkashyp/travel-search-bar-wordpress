<?php
/**
 * Travel Search Bar Widget
 * 
 * @package TravelSearchBar
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class TravelSearchBar_Widget extends WP_Widget {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'travel_search_bar_widget',
            __('Travel Search Bar', 'travel-search-bar'),
            [
                'description' => __('Display travel search bar with advanced filters', 'travel-search-bar'),
                'customize_selective_refresh' => true,
            ]
        );
        
        // Add widget-specific hooks
        $this->init_hooks();
    }
    
    /**
     * Initialize widget hooks
     */
    private function init_hooks() {
        // Admin scripts for widget form
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        
        // Frontend scripts for widget
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'widgets.php') {
            wp_enqueue_style(
                'travel-search-bar-widget-admin',
                TSB_PLUGIN_URL . 'assets/css/widget-admin.css',
                [],
                TSB_VERSION
            );
            
            wp_enqueue_script(
                'travel-search-bar-widget-admin',
                TSB_PLUGIN_URL . 'assets/js/widget-admin.js',
                ['jquery', 'jquery-ui-sortable', 'wp-color-picker'],
                TSB_VERSION,
                true
            );
            
            // Localize script
            wp_localize_script('travel-search-bar-widget-admin', 'travelSearchWidget', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('travel_search_widget_nonce'),
                'i18n' => [
                    'select_image' => __('Select Image', 'travel-search-bar'),
                    'change_image' => __('Change Image', 'travel-search-bar'),
                    'remove_image' => __('Remove Image', 'travel-search-bar'),
                ]
            ]);
        }
    }
    
    /**
     * Enqueue frontend scripts for widget
     */
    public function enqueue_frontend_scripts() {
        // Widget will use main plugin scripts
    }
    
    /**
     * Output widget content
     * 
     * @param array $args Widget arguments
     * @param array $instance Widget instance
     */
    public function widget($args, $instance) {
        // Get plugin settings
        $plugin_settings = TravelSearchBar::get_settings();
        
        // Check if widget is enabled in settings
        if (isset($plugin_settings['enable_widget']) && !$plugin_settings['enable_widget']) {
            return;
        }
        
        // Merge instance with defaults
        $instance = wp_parse_args($instance, $this->get_defaults());
        
        // Start output
        echo $args['before_widget'];
        
        // Widget title
        if (!empty($instance['title'])) {
            $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        // Widget description
        if (!empty($instance['description'])) {
            echo '<div class="travel-search-widget-description">';
            echo wpautop(esc_textarea($instance['description']));
            echo '</div>';
        }
        
        // Display search bar using shortcode with widget-specific attributes
        $shortcode_atts = $this->get_shortcode_attributes($instance);
        echo do_shortcode('[travel_search_bar ' . $shortcode_atts . ']');
        
        // Display widget footer if set
        if (!empty($instance['footer_text'])) {
            echo '<div class="travel-search-widget-footer">';
            echo wp_kses_post($instance['footer_text']);
            echo '</div>';
        }
        
        echo $args['after_widget'];
        
        // Add widget-specific styles if custom colors are set
        $this->output_custom_styles($instance);
    }
    
    /**
     * Output widget form in admin
     * 
     * @param array $instance Widget instance
     */
    public function form($instance) {
        // Merge with defaults
        $instance = wp_parse_args($instance, $this->get_defaults());
        ?>
        
        <div class="travel-search-widget-form">
            <!-- Title -->
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                    <?php esc_html_e('Title:', 'travel-search-bar'); ?>
                </label>
                <input type="text"
                       class="widefat"
                       id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                       name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                       value="<?php echo esc_attr($instance['title']); ?>"
                       placeholder="<?php esc_attr_e('Search Destinations', 'travel-search-bar'); ?>">
            </p>
            
            <!-- Description -->
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('description')); ?>">
                    <?php esc_html_e('Description:', 'travel-search-bar'); ?>
                </label>
                <textarea class="widefat"
                          id="<?php echo esc_attr($this->get_field_id('description')); ?>"
                          name="<?php echo esc_attr($this->get_field_name('description')); ?>"
                          rows="3"
                          placeholder="<?php esc_attr_e('Enter a short description...', 'travel-search-bar'); ?>"><?php echo esc_textarea($instance['description']); ?></textarea>
            </p>
            
            <!-- Display Options -->
            <div class="widget-section">
                <h4><?php esc_html_e('Display Options', 'travel-search-bar'); ?></h4>
                
                <!-- Show Advanced Filters -->
                <p>
                    <input type="checkbox"
                           id="<?php echo esc_attr($this->get_field_id('show_advanced')); ?>"
                           name="<?php echo esc_attr($this->get_field_name('show_advanced')); ?>"
                           value="1"
                           <?php checked($instance['show_advanced'], '1'); ?>>
                    <label for="<?php echo esc_attr($this->get_field_id('show_advanced')); ?>">
                        <?php esc_html_e('Show Advanced Filters Button', 'travel-search-bar'); ?>
                    </label>
                </p>
                
                <!-- Show Hero Section -->
                <p>
                    <input type="checkbox"
                           id="<?php echo esc_attr($this->get_field_id('show_hero')); ?>"
                           name="<?php echo esc_attr($this->get_field_name('show_hero')); ?>"
                           value="1"
                           <?php checked($instance['show_hero'], '1'); ?>>
                    <label for="<?php echo esc_attr($this->get_field_id('show_hero')); ?>">
                        <?php esc_html_e('Show Hero Section', 'travel-search-bar'); ?>
                    </label>
                </p>
                
                <!-- Compact Mode -->
                <p>
                    <input type="checkbox"
                           id="<?php echo esc_attr($this->get_field_id('compact_mode')); ?>"
                           name="<?php echo esc_attr($this->get_field_name('compact_mode')); ?>"
                           value="1"
                           <?php checked($instance['compact_mode'], '1'); ?>>
                    <label for="<?php echo esc_attr($this->get_field_id('compact_mode')); ?>">
                        <?php esc_html_e('Compact Mode', 'travel-search-bar'); ?>
                    </label>
                    <small class="description">
                        <?php esc_html_e('Show only search input without hero background', 'travel-search-bar'); ?>
                    </small>
                </p>
            </div>
            
            <!-- Customization Options -->
            <div class="widget-section">
                <h4><?php esc_html_e('Customization', 'travel-search-bar'); ?></h4>
                
                <!-- Background Color -->
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('bg_color')); ?>">
                        <?php esc_html_e('Background Color:', 'travel-search-bar'); ?>
                    </label>
                    <input type="color"
                           class="color-picker"
                           id="<?php echo esc_attr($this->get_field_id('bg_color')); ?>"
                           name="<?php echo esc_attr($this->get_field_name('bg_color')); ?>"
                           value="<?php echo esc_attr($instance['bg_color']); ?>">
                </p>
                
                <!-- Text Color -->
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('text_color')); ?>">
                        <?php esc_html_e('Text Color:', 'travel-search-bar'); ?>
                    </label>
                    <input type="color"
                           class="color-picker"
                           id="<?php echo esc_attr($this->get_field_id('text_color')); ?>"
                           name="<?php echo esc_attr($this->get_field_name('text_color')); ?>"
                           value="<?php echo esc_attr($instance['text_color']); ?>">
                </p>
                
                <!-- Custom Background Image -->
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('bg_image')); ?>">
                        <?php esc_html_e('Background Image:', 'travel-search-bar'); ?>
                    </label>
                    <div class="image-upload-container">
                        <input type="hidden"
                               id="<?php echo esc_attr($this->get_field_id('bg_image')); ?>"
                               name="<?php echo esc_attr($this->get_field_name('bg_image')); ?>"
                               value="<?php echo esc_attr($instance['bg_image']); ?>"
                               class="image-url">
                        <div class="image-preview" style="margin: 10px 0;">
                            <?php if (!empty($instance['bg_image'])): ?>
                                <img src="<?php echo esc_url($instance['bg_image']); ?>" 
                                     style="max-width: 100%; height: auto; display: block;">
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button upload-image-button">
                            <?php esc_html_e('Select Image', 'travel-search-bar'); ?>
                        </button>
                        <button type="button" class="button remove-image-button" style="<?php echo empty($instance['bg_image']) ? 'display: none;' : ''; ?>">
                            <?php esc_html_e('Remove Image', 'travel-search-bar'); ?>
                        </button>
                    </div>
                </p>
            </div>
            
            <!-- Search Options -->
            <div class="widget-section">
                <h4><?php esc_html_e('Search Options', 'travel-search-bar'); ?></h4>
                
                <!-- Placeholder Text -->
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('placeholder')); ?>">
                        <?php esc_html_e('Placeholder Text:', 'travel-search-bar'); ?>
                    </label>
                    <input type="text"
                           class="widefat"
                           id="<?php echo esc_attr($this->get_field_id('placeholder')); ?>"
                           name="<?php echo esc_attr($this->get_field_name('placeholder')); ?>"
                           value="<?php echo esc_attr($instance['placeholder']); ?>"
                           placeholder="<?php esc_attr_e('Search destinations...', 'travel-search-bar'); ?>">
                </p>
                
                <!-- Default Category -->
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('default_category')); ?>">
                        <?php esc_html_e('Default Category:', 'travel-search-bar'); ?>
                    </label>
                    <select class="widefat"
                            id="<?php echo esc_attr($this->get_field_id('default_category')); ?>"
                            name="<?php echo esc_attr($this->get_field_name('default_category')); ?>">
                        <option value=""><?php esc_html_e('All Categories', 'travel-search-bar'); ?></option>
                        <?php
                        $categories = TravelSearchBar::get_setting('filter_categories', []);
                        if (is_string($categories)) {
                            $categories = explode("\n", $categories);
                        }
                        foreach ($categories as $category):
                            $category = trim($category);
                            if (!empty($category)):
                        ?>
                            <option value="<?php echo esc_attr($category); ?>" 
                                <?php selected($instance['default_category'], $category); ?>>
                                <?php echo esc_html(ucwords(str_replace('_', ' ', $category))); ?>
                            </option>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </select>
                </p>
                
                <!-- Default State -->
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('default_state')); ?>">
                        <?php esc_html_e('Default State:', 'travel-search-bar'); ?>
                    </label>
                    <select class="widefat"
                            id="<?php echo esc_attr($this->get_field_id('default_state')); ?>"
                            name="<?php echo esc_attr($this->get_field_name('default_state')); ?>">
                        <option value=""><?php esc_html_e('All States', 'travel-search-bar'); ?></option>
                        <?php
                        $states = TravelSearchBar::get_setting('filter_states', []);
                        if (is_string($states)) {
                            $states = explode("\n", $states);
                        }
                        foreach ($states as $state):
                            $state = trim($state);
                            if (!empty($state)):
                        ?>
                            <option value="<?php echo esc_attr($state); ?>" 
                                <?php selected($instance['default_state'], $state); ?>>
                                <?php echo esc_html(ucwords(str_replace('_', ' ', $state))); ?>
                            </option>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </select>
                </p>
            </div>
            
            <!-- Advanced Options -->
            <div class="widget-section">
                <h4><?php esc_html_e('Advanced Options', 'travel-search-bar'); ?></h4>
                
                <!-- Custom CSS Class -->
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('css_class')); ?>">
                        <?php esc_html_e('Custom CSS Class:', 'travel-search-bar'); ?>
                    </label>
                    <input type="text"
                           class="widefat"
                           id="<?php echo esc_attr($this->get_field_id('css_class')); ?>"
                           name="<?php echo esc_attr($this->get_field_name('css_class')); ?>"
                           value="<?php echo esc_attr($instance['css_class']); ?>"
                           placeholder="<?php esc_attr_e('my-custom-class', 'travel-search-bar'); ?>">
                </p>
                
                <!-- Footer Text -->
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('footer_text')); ?>">
                        <?php esc_html_e('Footer Text:', 'travel-search-bar'); ?>
                    </label>
                    <textarea class="widefat"
                              id="<?php echo esc_attr($this->get_field_id('footer_text')); ?>"
                              name="<?php echo esc_attr($this->get_field_name('footer_text')); ?>"
                              rows="2"
                              placeholder="<?php esc_attr_e('Add footer text...', 'travel-search-bar'); ?>"><?php echo esc_textarea($instance['footer_text']); ?></textarea>
                    <small class="description">
                        <?php esc_html_e('HTML allowed', 'travel-search-bar'); ?>
                    </small>
                </p>
            </div>
        </div>
        
        <?php
    }
    
    /**
     * Update widget instance
     * 
     * @param array $new_instance New settings
     * @param array $old_instance Old settings
     * @return array Updated instance
     */
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        
        // Sanitize text fields
        $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
        $instance['description'] = sanitize_textarea_field($new_instance['description'] ?? '');
        $instance['placeholder'] = sanitize_text_field($new_instance['placeholder'] ?? '');
        $instance['footer_text'] = wp_kses_post($new_instance['footer_text'] ?? '');
        $instance['css_class'] = sanitize_html_class($new_instance['css_class'] ?? '');
        
        // Sanitize colors
        $instance['bg_color'] = sanitize_hex_color($new_instance['bg_color'] ?? '');
        $instance['text_color'] = sanitize_hex_color($new_instance['text_color'] ?? '');
        
        // Sanitize URLs
        $instance['bg_image'] = esc_url_raw($new_instance['bg_image'] ?? '');
        
        // Sanitize select fields
        $instance['default_category'] = sanitize_text_field($new_instance['default_category'] ?? '');
        $instance['default_state'] = sanitize_text_field($new_instance['default_state'] ?? '');
        
        // Sanitize checkboxes
        $instance['show_advanced'] = isset($new_instance['show_advanced']) ? '1' : '0';
        $instance['show_hero'] = isset($new_instance['show_hero']) ? '1' : '0';
        $instance['compact_mode'] = isset($new_instance['compact_mode']) ? '1' : '0';
        
        return $instance;
    }
    
    /**
     * Get default widget settings
     * 
     * @return array Default settings
     */
    private function get_defaults() {
        return [
            'title' => __('Search Destinations', 'travel-search-bar'),
            'description' => '',
            'show_advanced' => '1',
            'show_hero' => '1',
            'compact_mode' => '0',
            'bg_color' => '',
            'text_color' => '',
            'bg_image' => '',
            'placeholder' => '',
            'default_category' => '',
            'default_state' => '',
            'css_class' => '',
            'footer_text' => ''
        ];
    }
    
    /**
     * Convert widget instance to shortcode attributes
     * 
     * @param array $instance Widget instance
     * @return string Shortcode attributes
     */
    private function get_shortcode_attributes($instance) {
        $atts = [];
        
        // Add widget-specific attributes
        if (!empty($instance['placeholder'])) {
            $atts[] = 'placeholder="' . esc_attr($instance['placeholder']) . '"';
        }
        
        if ($instance['show_advanced'] === '0') {
            $atts[] = 'show_filters="false"';
        }
        
        if ($instance['show_hero'] === '0') {
            $atts[] = 'show_hero="false"';
        }
        
        if ($instance['compact_mode'] === '1') {
            $atts[] = 'compact="true"';
        }
        
        if (!empty($instance['bg_color'])) {
            $atts[] = 'primary_color="' . esc_attr($instance['bg_color']) . '"';
        }
        
        if (!empty($instance['bg_image'])) {
            $atts[] = 'background="' . esc_attr($instance['bg_image']) . '"';
        }
        
        if (!empty($instance['default_category'])) {
            $atts[] = 'default_category="' . esc_attr($instance['default_category']) . '"';
        }
        
        if (!empty($instance['default_state'])) {
            $atts[] = 'default_state="' . esc_attr($instance['default_state']) . '"';
        }
        
        if (!empty($instance['css_class'])) {
            $atts[] = 'class="' . esc_attr($instance['css_class']) . '"';
        }
        
        return implode(' ', $atts);
    }
    
    /**
     * Output custom styles for widget
     * 
     * @param array $instance Widget instance
     */
    private function output_custom_styles($instance) {
        $styles = [];
        
        if (!empty($instance['bg_color'])) {
            $styles[] = '.widget_' . $this->id_base . ' .travel-hero-section { background-color: ' . $instance['bg_color'] . ' !important; }';
        }
        
        if (!empty($instance['text_color'])) {
            $styles[] = '.widget_' . $this->id_base . ' .travel-hero-section, .widget_' . $this->id_base . ' .travel-hero-section h1, .widget_' . $this->id_base . ' .travel-hero-section p { color: ' . $instance['text_color'] . ' !important; }';
        }
        
        if (!empty($instance['bg_image'])) {
            $styles[] = '.widget_' . $this->id_base . ' .travel-hero-section { background-image: url("' . $instance['bg_image'] . '") !important; }';
        }
        
        if (!empty($styles)) {
            echo '<style>' . implode(' ', $styles) . '</style>';
        }
    }
}

// ✅ Register the widget
add_action('widgets_init', function() {
    register_widget('TravelSearchBar_Widget');
});