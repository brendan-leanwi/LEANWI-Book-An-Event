<?php
// Load WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

global $wpdb; // Access the global $wpdb object

// Verify nonce
if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'leanwi_event_nonce')) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing nonce']);
    exit;
}

// Get the post_id from the request
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$search_term = isset($_GET['search_term']) ? sanitize_text_field($_GET['search_term']) : '';

if ($post_id > 0) {
    $booking_occurrences_table = $wpdb->prefix . 'leanwi_event_booking_occurrences';
    $booking_table = $wpdb->prefix . 'leanwi_event_booking';
    $event_data_table = $wpdb->prefix . 'leanwi_event_data';

    $sql = $wpdb->prepare("
       SELECT eb.name, eb.email, eb.phone, eb.total_participants, count(bo.occurrence_id) AS occurrence_count, eb.booking_reference
        FROM $booking_table eb
        INNER JOIN $booking_occurrences_table bo ON bo.booking_id = eb.booking_id
        INNER JOIN $event_data_table ed ON ed.event_data_id = eb.event_data_id
        WHERE ed.post_id = %d
        AND eb.name LIKE %s
        GROUP BY bo.booking_id
    ", $post_id, '%' . $search_term . '%');

    $bookings = $wpdb->get_results($sql, ARRAY_A);

    if ($bookings && count($bookings) > 0) {

        
        // Sanitize output data with specific handling
        $safe_results = array_map(function($result) {
            return [
                'name' => isset($result['name']) && $result['name'] !== null ? html_entity_decode($result['name'], ENT_QUOTES) : '',
                'email' => sanitize_email($result['email']), 
                'phone' => esc_html($result['phone']),
                'total_participants' => intval($result['total_participants']),
                'occurrence_count' => intval($result['occurrence_count']),
                'booking_reference' => esc_html($result['booking_reference']),
            ];
        }, $bookings);

        // Return bookings as JSON
        echo json_encode(['bookings' => $safe_results]);
    } else {
        echo json_encode(['bookings' => []]); // Return empty if no bookings
    }
} else {
    echo json_encode(['bookings' => []]); // Return empty if no event_data_id
}
?>