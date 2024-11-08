<?php

// Include WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

global $wpdb;

// Get parameters from the request
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$capacity = isset($_GET['capacity']) ? intval($_GET['capacity']) : 0;

if ($post_id > 0 && $capacity > 0) {
    // SQL to check if the event has any available occurrences with participants below capacity
    $sql = $wpdb->prepare("
        SELECT o.occurrence_id
        FROM wp_tec_occurrences o
        LEFT JOIN wp_leanwi_event_participant p ON o.occurrence_id = p.event_occurrence_id
        WHERE o.post_id = %d
        GROUP BY o.occurrence_id
        HAVING IFNULL(SUM(p.total_number_of_participants), 0) < %d
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
