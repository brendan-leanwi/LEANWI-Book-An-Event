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
require_once plugin_dir_path(__FILE__) . 'php/frontend/display-event-payment-feedback-search.php';

// Hook to run when the plugin is activated
register_activation_hook(__FILE__, __NAMESPACE__ . '\\leanwi_event_create_tables');

// Hook to run when the plugin is uninstalled
register_uninstall_hook(__FILE__, __NAMESPACE__ . '\\leanwi_event_drop_tables');

// Register the JavaScript files
function leanwi_event_enqueue_scripts() {
    if (is_singular() && has_shortcode(get_post()->post_content, 'leanwi_event_details')) {

        wp_register_script(
            'event-booking-initial-load-js',
            plugin_dir_url(__FILE__) . 'js/event-booking-initial-load.js',
            array('jquery'),
            filemtime(plugin_dir_path(__FILE__) . 'js/event-booking-initial-load.js'),
            true
        );

        wp_register_script(
            'event-booking-admin-functionality-js',
            plugin_dir_url(__FILE__) . 'js/event-booking-admin-functionality.js',
            array('jquery', 'event-booking-initial-load-js'), // Ensure correct dependencies
            filemtime(plugin_dir_path(__FILE__) . 'js/event-booking-admin-functionality.js'),
            true
        );

        wp_register_script(
            'event-booking-new-functionality-js',
            plugin_dir_url(__FILE__) . 'js/event-booking-new-functionality.js',
            array('jquery', 'event-booking-initial-load-js'), // Ensure correct dependencies
            filemtime(plugin_dir_path(__FILE__) . 'js/event-booking-new-functionality.js'),
            true
        );

        wp_register_script(
            'event-booking-waitlist-functionality-js',
            plugin_dir_url(__FILE__) . 'js/event-booking-waitlist-functionality.js',
            array('jquery', 'event-booking-initial-load-js'), // Ensure correct dependencies
            filemtime(plugin_dir_path(__FILE__) . 'js/event-booking-waitlist-functionality.js'),
            true
        );

        wp_register_script(
            'event-booking-existing-functionality-js',
            plugin_dir_url(__FILE__) . 'js/event-booking-existing-functionality.js',
            array('jquery', 'event-booking-initial-load-js'), // Ensure correct dependencies
            filemtime(plugin_dir_path(__FILE__) . 'js/event-booking-existing-functionality.js'),
            true
        );

        wp_register_script(
            'event-booking-delete-functionality-js',
            plugin_dir_url(__FILE__) . 'js/event-booking-delete-functionality.js',
            array('jquery', 'event-booking-initial-load-js'), // Ensure correct dependencies
            filemtime(plugin_dir_path(__FILE__) . 'js/event-booking-delete-functionality.js'),
            true
        );

        wp_register_script(
            'event-booking-utils-js',
            plugin_dir_url(__FILE__) . 'js/event-booking-utils.js',
            array('jquery', 'event-booking-initial-load-js'), // Ensure correct dependencies
            filemtime(plugin_dir_path(__FILE__) . 'js/event-booking-utils.js'),
            true
        );

        // Localize the maximum booking slots setting and maxMonths
        wp_localize_script('event-booking-new-functionality-js', 'eventSettings', array(
            'enableRecaptcha' => get_option('leanwi_event_enable_recaptcha', 'no'),
            'recaptchaSiteKey' => get_option('leanwi_event_recaptcha_site_key', '')
        ));
        
        wp_enqueue_script('event-booking-initial-load-js');
        wp_enqueue_script('event-booking-admin-functionality-js');
        wp_enqueue_script('event-booking-new-functionality-js');
        wp_enqueue_script('event-booking-waitlist-functionality-js');
        wp_enqueue_script('event-booking-existing-functionality-js');
        wp_enqueue_script('event-booking-delete-functionality-js');
        wp_enqueue_script('event-booking-utils-js');

        wp_localize_script('event-booking-initial-load-js', 'leanwiVars', [
            'ajax_nonce' => wp_create_nonce('leanwi_event_nonce')
        ]);
    }

    if (is_singular() && has_shortcode(get_post()->post_content, 'event_payment_feedback_search')) {
        
        wp_register_script(
            'event-booking-payment-feedback-search-js',
            plugin_dir_url(__FILE__) . 'js/event-booking-payment-feedback-search.js',
            array('jquery'), // Ensure correct dependencies
            filemtime(plugin_dir_path(__FILE__) . 'js/event-booking-payment-feedback-search.js'),
            true
        );

        wp_enqueue_script('event-booking-payment-feedback-search-js');
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\leanwi_event_enqueue_scripts');

function enqueue_leanwi_event_custom_styles() {
    if (is_singular() && has_shortcode(get_post()->post_content, 'leanwi_event_details')) {
        
        wp_enqueue_style(
            'leanwi_event_custom-calendar-style',
            plugin_dir_url(__FILE__) . 'css/event-style.css',
            array(), // No dependencies
            filemtime(plugin_dir_path(__FILE__) . 'css/event-style.css') // Version control for cache-busting
        );
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_leanwi_event_custom_styles');
