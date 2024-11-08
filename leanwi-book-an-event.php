<?php
namespace LEANWI_Book_An_Event;

/*
Plugin Name:  LEANWI Book An Event
GitHub URI:   https://github.com/brendan-leanwi/LEANWI-Book-An-Event
Description:  Event Booking functionality compatible with LEANWI Divi WordPress websites
Version:      0.0.1
Author:       Brendan Tuckey
Author URI:   https://github.com/brendan-leanwi
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  leanwi-tutorial
Domain Path:  /languages
*/

// Require additional PHP files
require_once plugin_dir_path(__FILE__) . 'php/plugin/menu-functions.php';  // Menu Functions File
require_once plugin_dir_path(__FILE__) . 'php/plugin/schema.php'; //File containing table create and drop statements
require_once plugin_dir_path(__FILE__) . 'php/plugin/plugin-updates.php';
require_once plugin_dir_path(__FILE__) . 'php/frontend/display-event-details.php'; // Contains the page and shortcode for the event_details shortcode

// Hook to run when the plugin is activated
register_activation_hook(__FILE__, __NAMESPACE__ . '\\leanwi_event_create_tables');

// Hook to run when the plugin is uninstalled
register_uninstall_hook(__FILE__, __NAMESPACE__ . '\\leanwi_event_drop_tables');

// Register the JavaScript files
function leanwi_event_enqueue_scripts() {
    if (is_singular() && has_shortcode(get_post()->post_content, 'leanwi_event_details')) {
        wp_register_script(
            'event-booking-js',
            plugin_dir_url(__FILE__) . 'js/event-booking.js',
            array('jquery'),
            filemtime(plugin_dir_path(__FILE__) . 'js/event-booking.js'),
            true
        );
        wp_enqueue_script('event-booking-js');
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\leanwi_event_enqueue_scripts');

function enqueue_leanwi_event_custom_styles() {
    wp_enqueue_style('leanwi_event_custom-calendar-style', plugin_dir_url(__FILE__) . 'css/event-style.css');
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_leanwi_event_custom_styles');
