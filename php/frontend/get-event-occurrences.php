<?php

// Include WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Verify nonce
if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'leanwi_event_nonce')) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing nonce']);
    exit;
}

global $wpdb;

// Get the event_data_id from the request
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if ($post_id > 0) {
    // Get the prefixed table names
    $occurrences_table = $wpdb->prefix . 'tec_occurrences';
    $booking_occurrences_table = $wpdb->prefix . 'leanwi_event_booking_occurrences';
    $event_data_table = $wpdb->prefix . 'leanwi_event_data';

    // Get current local date and time
    $current_datetime = current_time('mysql'); // Uses WordPress's `current_time` to get local time in MySQL format

    // Fetch occurrences associated with the post_id that are in the future
    $sql = $wpdb->prepare("
        SELECT o.occurrence_id, o.start_date, o.end_date, IFNULL(SUM(bo.number_of_participants), 0) AS total_participants
        FROM $occurrences_table o
        LEFT JOIN $booking_occurrences_table bo ON o.occurrence_id = bo.occurrence_id
        LEFT JOIN $event_data_table ed ON o.post_id = ed.post_id
        WHERE o.post_id = %d
        AND o.start_date > %s
        AND TIMESTAMPDIFF(HOUR, %s, o.start_date) >= ed.booking_before_hours
        GROUP BY o.occurrence_id, o.start_date, o.end_date
    ", $post_id, $current_datetime, $current_datetime);

    $occurrences = $wpdb->get_results($sql, ARRAY_A);


    if (!empty($occurrences)) {
        
        // Sanitize output data with specific handling
        $safe_results = array_map(function($result) {
            return [
                'occurrence_id' => intval($result['occurrence_id']),
                'start_date' => esc_html($result['start_date']), 
                'end_date' => esc_html($result['end_date']),
                'total_participants' => intval($result['total_participants']),
            ];
        }, $occurrences);

        // Return disclaimers as JSON
        echo json_encode(['event_occurrences' => $safe_results]);
    } else {
        echo json_encode(['event_occurrences' => []]); // Return empty if no ocurrences
    }
} else {
    echo json_encode(['event_occurrences' => []]); // Return empty if no post_id
}

?>
