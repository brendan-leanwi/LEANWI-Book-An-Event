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
$name = wp_unslash($name); // Remove unnecessary escaping

$email = sanitize_email($_POST['email']);
$phone = sanitize_text_field($_POST['phone']);

$total_participants = isset($_POST['total_participants']) ? intval($_POST['total_participants']) : 0;
$event_data_id = isset($_POST['event_data_id']) ? intval($_POST['event_data_id']) : 0;
$capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
$capacity_override = isset($_POST['capacity_override']) ? intval($_POST['capacity_override']) : 0;

$new_booking_reference = sanitize_text_field($_POST['new_booking_reference']);
$existing_booking_reference = sanitize_text_field($_POST['existing_booking_reference']);
$existing_record = sanitize_text_field($_POST['existing_record']) === 'true';
$costs = json_decode(stripslashes($_POST['costs']), true);
$occurrences = json_decode(stripslashes($_POST['occurrences']), true);

if (empty($name) || empty($email) || empty($event_data_id) || empty($new_booking_reference) || ($existing_record && empty($existing_booking_reference))) {
    sendResponse(false, 'Missing required fields.');
}

//If capacity override is set then set capacity to 0 indicating that no capacity checks are needed
if ($capacity_override > 0){
    $capacity = 0;
}

// Start a database transaction for safety
$wpdb->query('START TRANSACTION');

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
        $total_participants,
        $capacity
    );
} else {
    // Before I try to insert lets check that all of our occurrences won't be put over capacity with this booking
    if(!CheckCapacities($wpdb, $event_data_id, $occurrences, $total_participants, $capacity)) {
        // Roll back the transaction on any error
        $wpdb->query('ROLLBACK');
        sendResponse(false, 'This booking would put atleast 1 of your selections over capacity. Please adjust your booking or add your entire booking to a wait list.');
    }

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
function handleExistingBooking($wpdb, $event_data_id, $existing_booking_reference, $new_booking_reference, $costs, $occurrences, $name, $email, $phone, $total_participants, $capacity) {
    $current_time = current_time('mysql');

    // Fetch existing booking ID
    $booking_id = $wpdb->get_var($wpdb->prepare(
        "SELECT booking_id FROM {$wpdb->prefix}leanwi_event_booking WHERE booking_reference = %s and event_data_id = %d",
        $existing_booking_reference, $event_data_id
    ));

    if (!$booking_id) {
        sendResponse(false, 'No existing booking found for the provided reference and Event.');
    }

    // Filter occurrences to exclude past ones i.e. include future only
    $future_occurrences = filterPastOccurrences($wpdb, $occurrences, $event_data_id, $current_time);

    try {
        if (count($future_occurrences) !== count($occurrences)) {
            // Remove future occurrences so all occurences in this booking are past
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
    } catch (Exception $e) {
        // Roll back the transaction on any error
        $wpdb->query('ROLLBACK');
        sendResponse(false, $e->getMessage());
    }

    // Before I try to insert lets check that all of our occurrences won't be put over capacity with this booking
    if(!CheckCapacities($wpdb, $event_data_id, $occurrences, $total_participants, $capacity)) {
        // Roll back the transaction on any error
        $wpdb->query('ROLLBACK');
        sendResponse(false, 'This booking would put atleast 1 of your selections over capacity. Please adjust your booking or add your entire booking to a wait list.');
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
        // Commit the transaction if everything succeeded
        $wpdb->query('COMMIT');
        sendResponse(true, 'Booking successful! Your new reference is: ' . $new_booking_reference);
    } else {
        // Roll back the transaction on any error
        $wpdb->query('ROLLBACK');
        sendResponse(false, 'Failed to create new booking.');
    }
}

function CheckCapacities($wpdb, $event_data_id, $occurrences, $total_participants, $capacity) {
    // A zero capacity indicates unlimited capacity so return true.
    if($capacity == 0){
        return true;
    }

    foreach ($occurrences as $occurrence) {
        $occurrence_id = intval($occurrence['occurrence_id']);

        // Get the current total participants for this occurrence
        $current_participants = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(bo.number_of_participants), 0)
             FROM {$wpdb->prefix}leanwi_event_booking b
             INNER JOIN {$wpdb->prefix}leanwi_event_booking_occurrences bo ON b.booking_id = bo.booking_id
             WHERE b.event_data_id = %d AND bo.occurrence_id = %d",
            $event_data_id,
            $occurrence_id
        ));

        // Check if adding the new participants exceeds capacity
        if (($current_participants + $total_participants) > $capacity) {
            return false; // Over capacity for at least one occurrence
        }
    }

    return true; // All occurrences are within capacity
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
            
            // Commit the transaction if everything succeeded
            $wpdb->query('COMMIT');
            return true; // Success!
        } else {
            // Roll back the transaction on any error
            $wpdb->query('ROLLBACK');
            return false;
        }
    } catch (Exception $e) {
        // Roll back the transaction on any error
        $wpdb->query('ROLLBACK');
        return false;
    }
}
?>
