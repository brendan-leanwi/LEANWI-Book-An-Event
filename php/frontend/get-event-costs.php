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
    // Get the prefixed table names
    $costs_table = $wpdb->prefix . 'leanwi_event_cost';

    // Fetch costs associated with the event_data_id
    $sql = $wpdb->prepare("
        SELECT *
        FROM $costs_table
        WHERE event_data_id = %d
        ORDER BY cost_name
    ", $event_data_id);

    $costs = $wpdb->get_results($sql, ARRAY_A);

    if (!empty($costs)) {
        
        // Sanitize output data with specific handling
        $safe_results = array_map(function($result) {
            return [
                'cost_id' => intval($result['cost_id']),
                'cost_name' => esc_html($result['cost_name']), 
                'cost_amount' => is_numeric($result['cost_amount']) ? number_format((float)$result['cost_amount'], 2, '.', '') : '0.00',
                'include_extra_info' => intval($result['include_extra_info']),
                'extra_info_label' => esc_html($result['extra_info_label']), 
                'historic' => intval($result['historic']),
            ];
        }, $costs);


        // Return costs as JSON
        echo json_encode(['event_costs' => $safe_results]);
    } else {
        echo json_encode(['event_costs' => []]); // Return empty if no costs
    }
} else {
    echo json_encode(['event_costs' => []]); // Return empty if no event_data_id
}

?>
