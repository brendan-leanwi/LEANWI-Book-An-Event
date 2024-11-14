<?php
// Load WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

global $wpdb; // Access the global $wpdb object

// verify the nonce before processing the rest of the form data
if (!isset($_POST['fetch_existing_event_nonce']) || !wp_verify_nonce($_POST['fetch_existing_event_nonce'], 'fetch_existing_event_action')) {
    echo json_encode(['success' => false, 'error' => 'Nonce verification failed.']);
    exit;
}

// Sanitize and validate inputs
$booking_ref = isset($_POST['booking_ref']) ? sanitize_text_field($_POST['booking_ref']) : '';
$event_data_id = isset($_POST['event_data_id']) ? intval($_POST['event_data_id']) : 0;

if (!empty($booking_ref)) {
    // Get the prefixed table names
    $booking_table = $wpdb->prefix . 'leanwi_event_booking';
    $cost_table = $wpdb->prefix . 'leanwi_event_cost';
    $occurrences_table = $wpdb->prefix . 'tec_occurrences';
    $booking_costs_table = $wpdb->prefix . 'leanwi_event_booking_costs';
    $booking_occurrences_table = $wpdb->prefix . 'leanwi_event_booking_occurrences';

    $booking_results = [];
    $cost_results = [];
    $occurrence_results = [];

    // Prepare SQL statement using $wpdb to get booking and user details
    $sql = $wpdb->prepare("
        SELECT * FROM $booking_table
        WHERE booking_reference = %s
    ", $booking_ref);

    // Execute the query
    $results = $wpdb->get_results($sql, ARRAY_A);

    if (!empty($results)) {
        // Validate venue_id
        if ($results[0]['event_data_id'] != $event_data_id) {
            echo json_encode(['error' => "The Booking Reference does not belong to this event."]);
        } else {
            $booking_id = intval($results[0]['booking_id']);

            // Sanitize output data with specific handling
            $booking_results = array_map(function($result) use ($booking_id) {
                return [
                    'booking_id' => $booking_id,
                    'booking_reference' => esc_html($result['booking_reference']),
                    'event_data_id' => intval($result['event_data_id']),
                    'name' => esc_html($result['name']),
                    'email' => sanitize_email($result['email']),
                    'phone' => esc_html($result['phone']),
                    'total_participants' => intval($result['total_participants']),
                ];
            }, $results);

            // Get the Occurrence data for the Booking
            $sql = $wpdb->prepare("
                SELECT bct.*, c.cost_name, c.cost_amount
                FROM $booking_costs_table bct
                JOIN $cost_table c ON bct.cost_id = c.cost_id
                WHERE bct.booking_id = %d
            ", $booking_id);

            // Execute the query
            $costs = $wpdb->get_results($sql, ARRAY_A);

            if (!empty($costs)) {
                // Sanitize output data with specific handling
                $cost_results = array_map(function($result) use ($booking_id) {
                    return [
                        'booking_id' => $booking_id,
                        'cost_id' => intval($result['cost_id']),
                        'number_of_participants' => intval($result['number_of_participants']),
                        'cost_name' => esc_html($result['cost_name']), 
                        'cost_amount' => is_numeric($result['cost_amount']) ? number_format((float)$result['cost_amount'], 2, '.', '') : '0.00',
                    ];
                }, $costs);
            }

            // Get the Occurrence data for the Booking
            $sql = $wpdb->prepare("
                SELECT bot.*, o.start_date, o.end_date
                FROM $booking_occurrences_table bot
                JOIN $occurrences_table o ON bot.occurrence_id = o.occurrence_id
                WHERE bot.booking_id = %d
            ", $booking_id);

            // Execute the query
            $occurrences = $wpdb->get_results($sql, ARRAY_A);

            if (!empty($occurrences)) {
                // Sanitize output data with specific handling
                $occurrence_results = array_map(function($result) use ($booking_id) {
                    return [
                        'booking_id' => $booking_id,
                        'occurrence_id' => intval($result['occurrence_id']),
                        'number_of_participants' => intval($result['number_of_participants']),
                        'start_date' => esc_html($result['start_date']), 
                        'end_date' => esc_html($result['end_date']), 
                    ];
                }, $occurrences);
            }

            echo json_encode([
                'success' => true,
                'data' => $booking_results, 
                'costs' => $cost_results, 
                'occurrences' => $occurrence_results
            ]);
        }
    } else {
        echo json_encode(['error' => "Booking not found."]);
    }
} else {
    echo json_encode(['error' => "No Booking Reference provided."]);
}
?>