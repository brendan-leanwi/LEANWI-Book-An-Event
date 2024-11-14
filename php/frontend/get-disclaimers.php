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
$event_data_id = isset($_GET['event_data_id']) ? intval($_GET['event_data_id']) : 0;

if ($event_data_id > 0) {
    // Fetch disclaimers associated with the event_data_id
    $sql = $wpdb->prepare("
        SELECT disclaimer
        FROM {$wpdb->prefix}leanwi_event_disclaimer
        WHERE event_data_id = %d
    ", $event_data_id);

    $disclaimers = $wpdb->get_results($sql);
    
    // Sanitize disclaimer text to prevent XSS (escape HTML tags)
    $sanitized_disclaimers = [];
    foreach ($disclaimers as $disclaimer) {
        $sanitized_disclaimers[] = [
            'disclaimer' => esc_html($disclaimer->disclaimer) // Prevent XSS
        ];
    }

    // Return disclaimers as JSON
    echo json_encode(['disclaimers' => $sanitized_disclaimers]);
} else {
    echo json_encode(['disclaimers' => []]); // Return empty if no event_data_id
}

?>
