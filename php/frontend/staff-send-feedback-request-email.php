<?php
// Load WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Check if it's an AJAX request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
    // If not a POST request, return an error
    echo json_encode(['success' => false, 'message' => 'Invalid request method or content type.']);
    exit;
}
// Get the JSON payload
$input = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json; charset=utf-8');

$booking_id = sanitize_text_field($input['booking_id']);
$nonce = isset($input['nonce']) ? sanitize_text_field($input['nonce']) : '';

// Verify the nonce
if (!wp_verify_nonce($nonce, 'mark_payment_nonce')) {
    echo json_encode(['success' => false, 'message' => 'Invalid nonce.']);
    exit;
}

$new_status = 1;
$success = true;

$email_from_name = get_option('leanwi_event_email_from_name', 'Library Events Team');

//Get the URL for the feedback form - likely a google form
$feedback_form_url = esc_url(get_option('leanwi_event_feedback_form_link', ''));
if (empty($feedback_form_url)) {
    echo json_encode(['success' => false, 'message' => 'Could not find a feedback form to link to']);
    exit;
}

global $wpdb;
$booking_table = $wpdb->prefix . 'leanwi_event_booking';
$posts_table = $wpdb->prefix . 'posts';
$event_data_table = $wpdb->prefix . 'leanwi_event_data';

//Get some values we need from the booking table
if (empty($booking_id)) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required.']);
    exit;
}

// Prepare SQL statement using $wpdb to get booking and user details
$sql = $wpdb->prepare("
    SELECT b.name, b.email, p.post_title AS event_name
    FROM $booking_table b
    JOIN $event_data_table ed ON ed.event_data_id = b.event_data_id
    JOIN $posts_table p ON p.ID = ed.post_id
    WHERE b.booking_id = %d
", $booking_id);

// Execute the query
$results = $wpdb->get_results($sql, ARRAY_A);
$result = [];

if (!empty($results)) {
    $result = $results[0]; // only expect one result
} else {
    $success = false;
}

//Construct the email
if ($success) {

    // Email details
    $email = sanitize_email($result['email']);
    $to = $email;
    $subject = "We'd appreciate your feedback";
    
    $message = "<p>Hi <strong>" . esc_html($result['name']) . "</strong>,</p>" .
    "<p>Thank you for attending an event recently. We hope everything met with your approval. " .
    "If you'd like to share feedback with us about the event, you can do so using this " .
    "<a href='" . esc_url($feedback_form_url) . "'>" . "online form</a>.</p>";

    $message .= "<p><strong>Here are the details of your booking:</strong></p>" .
            "<p><strong>Event:</strong> " . esc_html($result['event_name']) . "<br>";

    $message .= "<p>Thank you again!</p>" .
                "<p>" . $email_from_name . "</p>";

    // Set headers to send HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    //Send the email
    if (!is_email($email)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }
    
    // Send email using wp_mail
    $mail_sent = wp_mail($to, $subject, $message, $headers);

    if (!$mail_sent) {
        echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again later.']);
        exit;
    }

    // Update the feedback_request_sent field in the participant table
    $updated = $wpdb->update(
        $booking_table,
        ['feedback_request_sent' => $new_status], // Toggle payment status
        ['booking_id' => $booking_id], // Where condition
        ['%d'],
        ['%d']
    );

    if ($updated === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to send feedback request email.']);
    } else {
        $message = 'Feedback request email sent successfully.';
        echo json_encode(['success' => true, 'message' => $message]);
    } 
    exit;
}
else {
    echo json_encode(['success' => false, 'message' => 'Failed to find booking. No email was sent.']);
}

