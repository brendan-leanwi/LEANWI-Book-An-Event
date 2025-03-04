<?php
// delete-waitlist-booking.php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
global $wpdb;

// Verify nonce for security
if (!wp_verify_nonce($_POST['delete_existing_event_nonce'] ?? '', 'delete_existing_event_action')) {
    sendResponse(false, 'Nonce verification failed.');
}

// Sanitize and validate inputs
$booking_ref = sanitize_text_field($_POST['booking_ref'] ?? '');
$event_data_id = isset($_POST['event_data_id']) ? intval($_POST['event_data_id']) : 0;
$is_event_staff = isset($_POST['is_event_staff']) && filter_var($_POST['is_event_staff'], FILTER_VALIDATE_BOOLEAN);

if (empty($booking_ref)) {
    sendResponse(false, 'No Booking Reference provided.');
}

// Fetch booking ID and participation rule
$query_result = $wpdb->get_row($wpdb->prepare(
    "SELECT b.booking_id, b.name, b.email
    FROM {$wpdb->prefix}leanwi_event_waitlist_booking b
    INNER JOIN {$wpdb->prefix}leanwi_event_data d
    ON b.event_data_id = d.event_data_id
    WHERE b.booking_reference = %s AND b.event_data_id = %d",
    $booking_ref, $event_data_id
), ARRAY_A);

if (!$query_result) {
    sendResponse(false, 'No existing booking found for the provided reference and Event.');
}

$booking_id = $query_result['booking_id'];
$name = $query_result['name'];
$email = $query_result['email'];

// Attempt to delete the record
$delete_result = $wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->prefix}leanwi_event_waitlist_booking WHERE booking_id = %s",
    $booking_id
));

// Check if deletion was successful
if ($delete_result === false) {
    sendResponse(false, 'Failed to delete the booking.');
} elseif ($delete_result === 0) {
    sendResponse(false, 'No matching booking found.');
} else {
    if(sendEmails($name, $email, $booking_ref, $is_event_staff)) {
        sendResponse(true, 'Wait List Booking Deleted Successfully!');
    }
    else {
        sendResponse(true, 'Wait List Booking Deleted Successfully but there was an issue sending an email notification.');
    }
}

// Utility function to handle response
function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
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
        $subject = 'Your Waitlist Booking for Event ' . esc_html($event_name) . ' has been cancelled by library staff';
        $email_body = "<p>Hi <strong>" . esc_html($name) . "</strong>,</p>";
        $email_body .= "<p>Your waitlist booking for <strong>" . esc_html($event_name) . "</strong> with booking reference <strong> " . esc_html($booking_reference) . "</strong> has been cancelled by a member of our staff.</p>";
    }
    else {
        $subject = 'Your Waitlist Booking for Event ' . esc_html($event_name) . ' has been cancelled';
        $email_body = "<p>Hi <strong>" . esc_html($name) . "</strong>,</p>";
        $email_body .= "<p>Your waitlist booking for <strong>" . esc_html($event_name) . "</strong> with booking reference <strong> " . esc_html($booking_reference) . "</strong> has been cancelled.</p>";
    }

    if($cancellation_reason){
        $email_body .= "<p><strong>Reason given for cancellation:</strong> " . $cancellation_reason . "</p>";
    }

    $email_body .= "<p>If this was done in error, you will need to rebook again.</p>" .
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
