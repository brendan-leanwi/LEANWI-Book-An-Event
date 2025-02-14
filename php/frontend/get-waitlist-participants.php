<?php
// Load WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

global $wpdb; // Access the global $wpdb object

// Verify nonce
if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'leanwi_event_nonce')) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing nonce']);
    exit;
}

// Get the event_data_id from the request
$occurrence_id = isset($_GET['occurrence_id']) ? intval($_GET['occurrence_id']) : 0;

if ($occurrence_id > 0) {
    $booking_occurrences_table = $wpdb->prefix . 'leanwi_event_waitlist_occurrences';
    $booking_table = $wpdb->prefix . 'leanwi_event_waitlist_booking';

    $sql = $wpdb->prepare("
        SELECT b.booking_reference, b.name, b.email, b.phone, b.total_participants
        FROM $booking_occurrences_table o
        LEFT JOIN $booking_table b ON o.booking_id = b.booking_id
        WHERE o.occurrence_id = %d
    ", $occurrence_id);

    $bookings = $wpdb->get_results($sql, ARRAY_A);

    if ($bookings && count($bookings) > 0) {

        
        // Sanitize output data with specific handling
        $safe_results = array_map(function($result) {
            return [
                'name' => isset($result['name']) && $result['name'] !== null ? html_entity_decode($result['name'], ENT_QUOTES) : '',
                'email' => sanitize_email($result['email']), 
                'phone' => esc_html($result['phone']),
                'total_participants' => intval($result['total_participants']),
                'booking_reference' => esc_html($result['booking_reference']),
            ];
        }, $bookings);

        // Return disclaimers as JSON
        echo json_encode(['participants' => $safe_results]);
    } else {
        echo json_encode(['participants' => []]); // Return empty if no bookings
    }
} else {
    echo json_encode(['participants' => []]); // Return empty if no occurrence_id
}
?>