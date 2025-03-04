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
$is_event_staff = isset($data['is_event_staff']) && filter_var($data['is_event_staff'], FILTER_VALIDATE_BOOLEAN);


if (!empty($booking_ref)) {
    handleExistingBooking(
        $wpdb, 
        $event_data_id, 
        $booking_ref,
        $is_event_staff
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
function handleExistingBooking($wpdb, $event_data_id, $booking_reference, $is_event_staff) {
    $current_time = current_time('mysql');

    // Fetch booking ID and participation rule
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT b.booking_id, b.name, b.email, d.participation_rule
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
    $name = $result['name'];
    $email = $result['email'];
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
        if(sendEmails($name, $email, $booking_reference, $is_event_staff)) {
            sendResponse(true, 'Event Booking Deleted Successfully!');
        }
        else {
            sendResponse(true, 'Event Booking Deleted Successfully but there was an issue sending an email notification.');
        }

        sendResponse(true, 'Event Booking Deleted Successfully!');
    } catch (Exception $e) {
        // Roll back the transaction on any error
        $wpdb->query('ROLLBACK');
        sendResponse(false, $e->getMessage());
    }
}

function sendEmails($name, $email, $booking_reference, $is_event_staff) {
    
    //Global Event Settings Email Variables
    $send_admin_email = get_option('leanwi_event_send_admin_booking_email');
    $admin_email_address = get_option('leanwi_event_admin_email_address');
    $email_from_name = get_option('leanwi_event_email_from_name', 'Library Events Team');
    $email_from_name = wp_unslash($email_from_name);

    //This event's email variables
    $event_name = isset($_POST['event_name']) ? sanitize_text_field($_POST['event_name']) : '';
    $event_name = wp_unslash($event_name);

    $event_admin_email = isset($_POST['event_admin_email']) ? sanitize_email($_POST['event_admin_email']) : '';

    $cancellation_reason = isset($_POST['cancellation_reason']) ? sanitize_text_field($_POST['cancellation_reason']) : '';
    $cancellation_reason = wp_unslash($cancellation_reason);

    // No recipients? Skip sending
    if ($send_admin_email === 'no' && empty($event_admin_email) && empty($email)) {
        return true; // No one to send emails to, but function executed correctly
    }

    /* Build the email */
    if($is_event_staff) {
        $subject = 'Your Event Booking for ' . esc_html($event_name) . ' has been cancelled by library staff';
        $email_body = "<p>Hi <strong>" . esc_html($name) . "</strong>,</p>";
        $email_body .= "<p>Your booking for <strong>" . esc_html($event_name) . "</strong> with booking reference <strong> " . esc_html($booking_reference) . "</strong> has been cancelled by a member of our staff.</p>";
    }
    else {
        $subject = 'Your Event Booking for ' . esc_html($event_name) . ' has been cancelled';
        $email_body = "<p>Hi <strong>" . esc_html($name) . "</strong>,</p>";
        $email_body .= "<p>Your booking for <strong>" . esc_html($event_name) . "</strong> with booking reference <strong> " . esc_html($booking_reference) . "</strong> has been cancelled.</p>";
    }

    if($cancellation_reason){
        $email_body .= "<p><strong>Reason given for cancellation:</strong> " . $cancellation_reason . "</p>";
    }

    $email_body .= "<p>If this was done in error, please rebook.</p>" .
                "<p>If you are unsure as to the reason please contact library staff before making another booking.</p>" .
                "<p>Regards,</p>" .
                "<p>" . $email_from_name . "</p>";

    
    $email_headers = ['Content-Type: text/html; charset=UTF-8'];

    // Send to the user
    $user_email_sent = false;
    if (!empty($email)) {
        $user_email_sent = wp_mail($email, $subject, $email_body, $email_headers);
    }

    // Send to admin if enabled
    $admin_email_sent = false;
    if ($send_admin_email === 'yes' && !empty($admin_email_address)) {
        $admin_email_sent = wp_mail($admin_email_address, "Admin Notification: $subject", $email_body, $email_headers);
    }

    // Send to event admin if provided
    $event_admin_email_sent = false;
    if (!empty($event_admin_email)) {
        $event_admin_email_sent = wp_mail($event_admin_email, "Event Admin: $subject", $email_body, $email_headers);
    }

    // Return true if at least one email was successfully sent
    return $user_email_sent || $admin_email_sent || $event_admin_email_sent;
}

?>
