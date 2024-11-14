<?php

// Include WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Verify nonce
if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'leanwi_event_nonce')) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing nonce']);
    exit;
}

global $wpdb;

// Get parameters from the request
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$capacity = isset($_GET['capacity']) ? intval($_GET['capacity']) : 0;

if ($post_id > 0 && $capacity > 0) {
    // SQL to check if the event has any available occurrences with participants below capacity
    $sql = $wpdb->prepare("
        SELECT o.occurrence_id
        FROM {$wpdb->prefix}tec_occurrences o
        LEFT JOIN {$wpdb->prefix}leanwi_event_booking_occurrences bo ON o.occurrence_id = bo.occurrence_id
        LEFT JOIN {$wpdb->prefix}leanwi_event_booking b ON bo.booking_id = b.booking_id
        WHERE o.post_id = %d
        GROUP BY o.occurrence_id
        HAVING IFNULL(SUM(bo.number_of_participants), 0) < %d
    ", $post_id, $capacity);

    $results = $wpdb->get_results($sql);

    if (!empty($results)) {
        // If we have available occurrences
        echo json_encode(['isSoldOut' => false]);
    } else {
        // If no occurrences are available (sold out)
        echo json_encode(['isSoldOut' => true]);
    }
} else {
    echo json_encode(['isSoldOut' => true]); // Default to sold out if parameters are missing or invalid
}

?>
