<?php
// submit-event-booking.php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
global $wpdb;

// Ensure secure access with nonce verification
if (!isset($_POST['submit_event_nonce']) || !wp_verify_nonce($_POST['submit_event_nonce'], 'submit_event_action')) {
    sendResponse(false, 'Nonce verification failed.');
}

// Input validation and sanitization
$name = sanitize_text_field($_POST['name']);
$email = sanitize_email($_POST['email']);
$phone = sanitize_text_field($_POST['phone']);
$total_participants = intval($_POST['total_participants']);
$event_data_id = intval($_POST['event_data_id']);
$new_booking_reference = sanitize_text_field($_POST['new_booking_reference']);
$existing_booking_reference = sanitize_text_field($_POST['existing_booking_reference']);
$existing_record = sanitize_text_field($_POST['existing_record']) === 'true';
$costs = json_decode(stripslashes($_POST['costs']), true);
$occurrences = json_decode(stripslashes($_POST['occurrences']), true);

if (empty($name) || empty($email) || empty($event_data_id) || empty($new_booking_reference) || ($existing_record && empty($existing_booking_reference))) {
    sendResponse(false, 'Missing required fields.');
}

// Handle existing or new booking
if ($existing_record) {
    handleExistingBooking(
        $wpdb, 
        $event_data_id, 
        $existing_booking_reference,
        $new_booking_reference, 
        $costs, 
        $occurrences, 
        $name, 
        $email, 
        $phone, 
        $total_participants
    );
} else {
    $success = addNewRecord(
        $wpdb, 
        $event_data_id, 
        $new_booking_reference, 
        $costs, 
        $occurrences, 
        $name, 
        $email, 
        $phone, 
        $total_participants
    );
    if ($success) {
        sendResponse(true, 'Booking successful! Your new reference is: ' . $new_booking_reference);
    } else {
        sendResponse(false, 'Failed to create new booking.');
    }
}

// Utility function to handle response
function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Handle updates for existing bookings
function handleExistingBooking($wpdb, $event_data_id, $existing_booking_reference, $new_booking_reference, $costs, $occurrences, $name, $email, $phone, $total_participants) {
    $current_time = current_time('mysql');

    // Fetch existing booking ID
    $booking_id = $wpdb->get_var($wpdb->prepare(
        "SELECT booking_id FROM {$wpdb->prefix}leanwi_event_booking WHERE booking_reference = %s",
        $existing_booking_reference
    ));

    if (!$booking_id) {
        sendResponse(false, 'No existing booking found for the provided reference.');
    }

    // Filter occurrences to exclude past ones i.e. include future only
    $future_occurrences = filterPastOccurrences($wpdb, $occurrences, $event_data_id, $current_time);

    // Start a database transaction for safety
    $wpdb->query('START TRANSACTION');

    try {
        if (count($future_occurrences) !== count($occurrences)) {
            // Handle bookings with past occurrences
            $wpdb->query($wpdb->prepare(
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
            ));

            $update_result = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}leanwi_event_booking
                SET historic = 1
                WHERE booking_id = %d",
                $booking_id
            ));

            if ($update_result === false) {
                throw new Exception('Failed to mark the booking as historic.');
            }
        } else {
            // Handle bookings with only future occurrences
            $delete_result = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}leanwi_event_booking
                WHERE booking_id = %d",
                $booking_id
            ));

            if ($delete_result === false) {
                throw new Exception('Failed to delete the booking.');
            }

            // Reuse the booking reference
            $new_booking_reference = $existing_booking_reference;
        }

        // Commit the transaction if everything succeeded
        $wpdb->query('COMMIT');
    } catch (Exception $e) {
        // Roll back the transaction on any error
        $wpdb->query('ROLLBACK');
        sendResponse(false, $e->getMessage());
    }

    
    $success = addNewRecord(
        $wpdb, 
        $event_data_id, 
        $new_booking_reference, 
        $costs, 
        $future_occurrences, 
        $name, 
        $email, 
        $phone, 
        $total_participants
    );
    if ($success) {
        sendResponse(true, 'Booking successful! Your new reference is: ' . $new_booking_reference);
    } else {
        sendResponse(false, 'Failed to create new booking.');
    }
}

// Filter out past occurrences
function filterPastOccurrences($wpdb, $occurrences, $event_data_id, $current_time) {
    $remaining_occurrences = [];
    if (is_array($occurrences)) {
        foreach ($occurrences as $occurrence) {
            $occurrence_id = intval($occurrence['occurrence_id']);
            $is_future_occurrence = $wpdb->get_var($wpdb->prepare(
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
            ));

            if ($is_future_occurrence) {
                $remaining_occurrences[] = $occurrence;
            }
        }
    }
    return $remaining_occurrences;
}

// Insert new booking data
function addNewRecord($wpdb, $event_data_id, $booking_reference, $costs, $occurrences, $name, $email, $phone, $total_participants) {
    try {
        $result = $wpdb->insert("{$wpdb->prefix}leanwi_event_booking", [
            'booking_reference' => $booking_reference,
            'event_data_id' => $event_data_id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'total_participants' => $total_participants
        ]);

        if ($result) {
            $booking_id = $wpdb->insert_id;

            if (is_array($costs)) {
                foreach ($costs as $cost) {
                    $number_of_participants = intval($cost['number_of_participants']);
                    if ($number_of_participants > 0) { // Only insert if number_of_participants is greater than 0
                        $wpdb->insert("{$wpdb->prefix}leanwi_event_booking_costs", [
                            'booking_id' => $booking_id,
                            'cost_id' => intval($cost['cost_id']),
                            'number_of_participants' => $number_of_participants
                        ]);
                    }
                }
            }

            if (is_array($occurrences)) {
                foreach ($occurrences as $occurrence) {
                    $wpdb->insert("{$wpdb->prefix}leanwi_event_booking_occurrences", [
                        'booking_id' => $booking_id,
                        'occurrence_id' => intval($occurrence['occurrence_id']),
                        'number_of_participants' => intval($occurrence['number_of_participants'])
                    ]);
                }
            }

            return true; // Success!
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}
?>
