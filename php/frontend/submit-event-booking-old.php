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
$existing_record = sanitize_text_field($_POST['existing_record'])  === 'true';

if (empty($name) || empty($email) || empty($event_data_id) || empty($booking_reference)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Decode the JSON string into arrays
$costs = json_decode(stripslashes($_POST['costs']), true); 
$occurrences = json_decode(stripslashes($_POST['occurrences']), true); 

// Functionality for changing an existing booking
if($existing_record){
    // Fetch the existing booking_id for the provided booking_reference
    $booking_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT booking_id 
             FROM {$wpdb->prefix}leanwi_event_booking 
             WHERE booking_reference = %s",
            $booking_reference
        )
    );

    if ($booking_id) {
        // Remove future occurrences based on start_date and booking_before_hours
        $current_time = current_time('mysql');
        $wpdb->query(
            $wpdb->prepare(
                "DELETE bo 
                 FROM {$wpdb->prefix}leanwi_event_booking_occurrences bo
                 INNER JOIN {$wpdb->prefix}tec_occurrences o ON bo.occurrence_id = o.occurrence_id
                 INNER JOIN {$wpdb->prefix}leanwi_event_data ed ON ed.event_data_id = %d
                 WHERE bo.booking_id = %d
                   AND o.start_date > %s
                   AND TIMESTAMPDIFF(HOUR, %s, o.start_date) >= ed.booking_before_hours",
                $event_data_id,
                $booking_id,
                $current_time,
                $current_time
            )
        );

        // Filter out removed occurrences from the passed occurrences array
        $remaining_occurrences = [];
        if (is_array($occurrences)) {
            foreach ($occurrences as $occurrence) {
                $occurrence_id = intval($occurrence['occurrence_id']);
                $is_future_occurrence = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*)
                         FROM {$wpdb->prefix}tec_occurrences o
                         INNER JOIN {$wpdb->prefix}leanwi_event_data ed ON ed.event_data_id = %d
                         WHERE o.occurrence_id = %d
                           AND o.start_date > %s
                           AND TIMESTAMPDIFF(HOUR, %s, o.start_date) >= ed.booking_before_hours",
                        $event_data_id,
                        $occurrence_id,
                        $current_time,
                        $current_time
                    )
                );

                if (!$is_future_occurrence) {
                    $remaining_occurrences[] = $occurrence; // Add to the remaining list
                }
            }
        }

    
        $new_booking_reference = substr(md5(rand()), 0, 7); // Generate a booking reference for the old booking

        //Add the rest of the occurrences and the new cost data as a new booking with a new booking reference
        addNewRecord($costs, $$remaining_occurrences, $new_booking_reference);
    } else {
        echo json_encode(['success' => false, 'message' => 'No existing booking found for the provided reference.']);
        exit;
    }

} else {
    // If is a new booking just save the booking as it was passed
    addNewRecord($costs, $occurrences, $booking_reference);
}

function addNewRecord ($costs, $occurrences, $booking_reference){
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
} 
?>
