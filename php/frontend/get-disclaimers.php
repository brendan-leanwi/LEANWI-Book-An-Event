<?php

// Include WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

global $wpdb;

// Get the event_data_id from the request
$event_data_id = isset($_GET['event_data_id']) ? intval($_GET['event_data_id']) : 0;

if ($event_data_id > 0) {
    // Fetch disclaimers associated with the event_data_id
    $sql = $wpdb->prepare("
        SELECT disclaimer
        FROM {$wpdb->prefix}leanwi_event_disclaimer
        WHERE event_data_id = %d
    ", $event_data_id);

    $disclaimers = $wpdb->get_results($sql);

    // Return disclaimers as JSON
    echo json_encode(['disclaimers' => $disclaimers]);
} else {
    echo json_encode(['disclaimers' => []]); // Return empty if no event_data_id
}

?>
