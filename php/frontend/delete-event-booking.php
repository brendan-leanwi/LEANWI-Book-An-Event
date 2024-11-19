<?php
// delete-event-booking.php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
global $wpdb;

if (!isset($_POST['delete_existing_event_nonce']) || !wp_verify_nonce($_POST['delete_existing_event_nonce'], 'delete_existing_event_action')) {
    echo json_encode(['success' => false, 'error' => 'Nonce verification failed.']);
    exit;
}

// Sanitize and validate inputs
$booking_ref = isset($_POST['booking_ref']) ? sanitize_text_field($_POST['booking_ref']) : '';
$event_data_id = isset($_POST['event_data_id']) ? intval($_POST['event_data_id']) : 0;

//Testing code
//$booking_ref = isset($_GET['booking_ref']) ? sanitize_text_field($_GET['booking_ref']) : '';
//$event_data_id = isset($_GET['event_data_id']) ? intval($_GET['event_data_id']) : 0;


if (!empty($booking_ref)) {
    handleExistingBooking(
        $wpdb, 
        $event_data_id, 
        $booking_ref
    );
} else {
    sendResponse(false, 'No Booking Reference provided.');
}

// Utility function to handle response
function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Handle updates for existing bookings
function handleExistingBooking($wpdb, $event_data_id, $booking_reference) {
    $current_time = current_time('mysql');

    // Fetch booking ID and participation rule
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT b.booking_id, d.participation_rule
        FROM {$wpdb->prefix}leanwi_event_booking b
        INNER JOIN {$wpdb->prefix}leanwi_event_data d
        ON b.event_data_id = d.event_data_id
        WHERE b.booking_reference = %s AND b.event_data_id = %d",
        $booking_reference, $event_data_id
    ), ARRAY_A);

    if (!$result) {
        sendResponse(false, 'No existing booking found for the provided reference and Event.');
    }

    $booking_id = $result['booking_id'];
    $participation_rule = $result['participation_rule'];

    // Start a database transaction for safety
    $wpdb->query('START TRANSACTION');

    try {
        $no_future_occurrences = $wpdb->get_var($wpdb->prepare(
            "SELECT NOT EXISTS(
                SELECT 1
                FROM {$wpdb->prefix}leanwi_event_booking_occurrences bo
                INNER JOIN {$wpdb->prefix}tec_occurrences o ON bo.occurrence_id = o.occurrence_id
                WHERE bo.booking_id = %d
                AND o.start_date > %s
            ) AS no_future_occurrences",
            $booking_id,
            $current_time
        ));
        if ($no_future_occurrences) {
            // All occurrences are in the past so we shouldn't remove or change anything
            sendResponse(false, 'All events in this booking have already occurred - so no deletion is necessary.');
        } 
        
        $only_future_occurrences = $wpdb->get_var($wpdb->prepare(
            "SELECT NOT EXISTS(
                SELECT 1
                FROM {$wpdb->prefix}leanwi_event_booking_occurrences bo
                INNER JOIN {$wpdb->prefix}tec_occurrences o ON bo.occurrence_id = o.occurrence_id
                INNER JOIN {$wpdb->prefix}leanwi_event_data ed ON ed.event_data_id = %d
                WHERE bo.booking_id = %d
                AND o.start_date <= %s
                AND TIMESTAMPDIFF(HOUR, %s, o.start_date) < ed.cancellation_before_hours
            ) AS only_future_occurrences",
            $event_data_id,
            $booking_id,
            $current_time,
            $current_time
        ));

        if ($only_future_occurrences) {
            // Only Future occurrences exist
            // I can just delete the entire booking
            $delete_result = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}leanwi_event_booking
                WHERE booking_id = %d",
                $booking_id
            ));

            if ($delete_result === false) {
                throw new Exception('Failed to delete the booking.');
            }
        } else if ($participation_rule === 'all') {
            sendResponse(false, 'You can not delete this booking as you have already attended one of the events.');
        } else {
            // There are past occurrences in this booking so we need to save these
            // Remove future occurrences so all occurences in this booking are past
            $wpdb->query($wpdb->prepare(
                "DELETE bo 
                FROM {$wpdb->prefix}leanwi_event_booking_occurrences bo
                INNER JOIN {$wpdb->prefix}tec_occurrences o ON bo.occurrence_id = o.occurrence_id
                INNER JOIN {$wpdb->prefix}leanwi_event_data ed ON ed.event_data_id = %d
                WHERE bo.booking_id = %d
                AND o.start_date > %s
                AND TIMESTAMPDIFF(HOUR, %s, o.start_date) >= ed.cancellation_before_hours",
                $event_data_id,
                $booking_id,
                $current_time,
                $current_time
            ));

            //Mark the booking as historic so it can't be updated again to avoid confusion
            $update_result = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}leanwi_event_booking
                SET historic = 1
                WHERE booking_id = %d",
                $booking_id
            ));
            if ($update_result === false) {
                throw new Exception('Failed to mark the booking as historic.');
            }
        }

        // Commit the transaction if everything succeeded
        $wpdb->query('COMMIT');
    } catch (Exception $e) {
        // Roll back the transaction on any error
        $wpdb->query('ROLLBACK');
        sendResponse(false, $e->getMessage());
    }
}

?>
