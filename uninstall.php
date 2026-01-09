<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('travel_search_bar_settings');
delete_option('travel_search_bar_version');

// Delete user meta
global $wpdb;
$wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'travel_search_%'");

// Clear any cached data that might be related
wp_cache_flush();