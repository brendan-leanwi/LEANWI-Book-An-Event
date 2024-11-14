<?php
// submit-event-booking.php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
global $wpdb;

// verify the nonce before processing the rest of the form data
if (!isset($_POST['submit_event_nonce']) || !wp_verify_nonce($_POST['submit_event_nonce'], 'submit_event_action')) {
    echo json_encode(['success' => false, 'message' => 'Nonce verification failed.']);
    exit;
}

// Validate and sanitize input data
$name = sanitize_text_field($_POST['name']);
$email = sanitize_email($_POST['email']);
$phone = sanitize_text_field($_POST['phone']);
$total_participants = intval($_POST['total_participants']);
$event_data_id = intval($_POST['event_data_id']);
$booking_reference = sanitize_text_field($_POST['booking_reference']);

if (empty($name) || empty($email) || empty($event_data_id) || empty($booking_reference)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

try {
    // Insert booking data into the table
    $result = $wpdb->insert("{$wpdb->prefix}leanwi_event_booking", [
        'booking_reference' => $booking_reference,
        'event_data_id' => $event_data_id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'total_participants' => $total_participants
    ]);

    if ($result) {
        $booking_id = $wpdb->insert_id;  // Get the generated booking_id

        // Decode the JSON string into an array for costs
        $costs = json_decode(stripslashes($_POST['costs']), true);  // Convert JSON to PHP array
        if (is_array($costs)) {
            foreach ($costs as $cost) {
                $cost_id = intval($cost['cost_id']);
                $number_of_participants = intval($cost['number_of_participants']);
                $wpdb->insert("{$wpdb->prefix}leanwi_event_booking_costs", [
                    'booking_id' => $booking_id,
                    'cost_id' => $cost_id,
                    'number_of_participants' => $number_of_participants
                ]);
            }
        }

        // Decode the JSON string into an array for occurrences
        $occurrences = json_decode(stripslashes($_POST['occurrences']), true);  // Convert JSON to PHP array
        if (is_array($occurrences)) {
            foreach ($occurrences as $occurrence) {
                $occurrence_id = intval($occurrence['occurrence_id']);
                $number_of_participants = intval($occurrence['number_of_participants']);
                $wpdb->insert("{$wpdb->prefix}leanwi_event_booking_occurrences", [
                    'booking_id' => $booking_id,
                    'occurrence_id' => $occurrence_id,
                    'number_of_participants' => $number_of_participants
                ]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Booking successful.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database insert error: ' . $wpdb->last_error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
