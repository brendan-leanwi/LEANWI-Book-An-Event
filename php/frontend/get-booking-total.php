<?php

// Include WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Verify nonce
if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'leanwi_event_nonce')) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing nonce']);
    exit;
}

global $wpdb;

// Get booking_id from the request
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($booking_id > 0) {
    // Prepare and execute the SQL query to calculate the total cost
    $sql = $wpdb->prepare("
        SELECT 
            SUM(c.cost_amount * bc.number_of_participants) AS total_cost
        FROM 
            {$wpdb->prefix}leanwi_event_booking_costs AS bc
        JOIN 
            {$wpdb->prefix}leanwi_event_cost AS c ON bc.cost_id = c.cost_id
        WHERE 
            bc.booking_id = %d
    ", $booking_id);

    // Retrieve the total cost
    $total_cost = $wpdb->get_var($sql);

    // Check if a result was found, otherwise set total_cost to 0
    $total_cost = ($total_cost !== null) ? $total_cost : 0.00;

    // Return the total cost in JSON format
    echo json_encode(['total_cost' => $total_cost]);
} else {
    // If booking_id is missing or invalid, return an error response
    echo json_encode(['error' => 'Invalid booking_id provided']);
}

?>
