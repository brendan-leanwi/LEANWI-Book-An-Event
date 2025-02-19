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

if (empty($booking_ref)) {
    sendResponse(false, 'No Booking Reference provided.');
}

// Attempt to delete the record
$delete_result = $wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->prefix}leanwi_event_waitlist_booking WHERE booking_reference = %s",
    $booking_ref
));

// Check if deletion was successful
if ($delete_result === false) {
    sendResponse(false, 'Failed to delete the booking.');
} elseif ($delete_result === 0) {
    sendResponse(false, 'No matching booking found.');
} else {
    sendResponse(true, 'Wait List Deleted Successfully!');
}

// Utility function to handle response
function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}
?>
