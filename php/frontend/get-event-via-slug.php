<?php
// Load WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Verify nonce
if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'leanwi_event_nonce')) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing nonce']);
    exit;
}

global $wpdb; // Access the global $wpdb object

// verify the nonce before processing the rest of the form data
if (!isset($_GET['event_slug'])) {
    echo json_encode(['success' => false, 'error' => 'Need to pass a page slug']);
    exit;
}

// Sanitize and validate inputs
$event_slug = isset($_GET['event_slug']) ? sanitize_text_field($_GET['event_slug']) : '';

if (empty($event_slug) || !preg_match('/^[a-z0-9-]+$/', $event_slug)) {
    echo json_encode(['success' => false, 'error' => 'Invalid event slug']);
    exit;
}

if (!empty($event_slug)) {
    // Get the prefixed table names
    $data_table = $wpdb->prefix . 'leanwi_event_data';
    $posts_table = $wpdb->prefix . 'posts';

    // Prepare SQL statement using $wpdb to get booking and user details
    $sql = $wpdb->prepare("
        SELECT d.*, p.post_title AS event_name
        FROM $data_table d
        JOIN $posts_table p ON d.post_id = p.ID
        WHERE p.post_name = %s
        AND p.post_type = 'tribe_events' 
        AND p.post_content != ''
    ", $event_slug);


    // Execute the query
    $results = $wpdb->get_results($sql, ARRAY_A);

    if (!empty($results)) {
        
        // Sanitize output data with specific handling
        $safe_results = array_map(function($result) {
            return [
                'event_data_id' => intval($result['event_data_id']),
                'post_id' => sanitize_text_field($result['post_id']),
                'event_name' => esc_html($result['event_name']),
                'event_url' => esc_url($result['event_url']),
                'event_image' => esc_url($result['event_image']),
                'capacity' => intval($result['capacity']),
                'category_id' => intval($result['category_id']),
                'audience_id' => intval($result['audience_id']),
                'participation_rule' => esc_html($result['participation_rule']),
                'booking_before_hours' => intval($result['booking_before_hours']),
                'cancellation_before_hours' => intval($result['cancellation_before_hours']),
                'register_by_date' => esc_html($result['register_by_date']),
                'virtual_event_rule' => esc_html($result['virtual_event_rule']),
                'virtual_event_url' => esc_url($result['virtual_event_url']),
                'virtual_event_password' => sanitize_text_field($result['virtual_event_password']),
                'event_admin_email' => sanitize_email($result['event_admin_email']),
                'extra_email_text' => esc_html($result['extra_email_text']),
                'extra_event_url' => esc_url($result['extra_event_url']),
                'include_extra_event_url_in_email' => intval($result['include_extra_event_url_in_email']),
                'include_virtual_bookings_in_capacity_calc' => intval($result['include_virtual_bookings_in_capacity_calc']),
                'include_special_notes' => intval($result['include_special_notes']),
                'include_physical_address' => intval($result['include_physical_address']),
                'include_zipcode' => intval($result['include_zipcode']),
                'historic' => intval($result['historic']),
            ];
        }, $results);

        echo wp_json_encode(['success' => true, 'data' => $safe_results]); // Include success key
    } else {
        echo wp_json_encode(['error' => "LEANWI Event not found."]);
    }
} else {
    echo wp_json_encode(['error' => "No Event Slug provided."]);
}
?>