<?php
// submit-event-booking.php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
global $wpdb;

// Ensure secure access with nonce verification
if (!isset($_POST['submit_event_nonce']) || !wp_verify_nonce($_POST['submit_event_nonce'], 'submit_event_action')) {
    sendResponse(false, 'Nonce verification failed.');
}

if(get_option('leanwi_event_enable_recaptcha') === 'yes')
{
    if (isset($_POST['g-recaptcha-response'])) {
        $recaptchaSecret = get_option('leanwi_event_recaptcha_secret_key', '');
        $response = $_POST['g-recaptcha-response'];
        
        // Make a request to the Google reCAPTCHA API to verify the token
        $remoteIp = $_SERVER['REMOTE_ADDR'];
        $recaptchaResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$response&remoteip=$remoteIp");
        $recaptchaData = json_decode($recaptchaResponse);

        // Check if the reCAPTCHA is valid
        if (!$recaptchaData->success || $recaptchaData->score < 0.5) { // reCaptcha score
            $success = false;
            $errorMessage = 'reCAPTCHA verification unsuccessful. Please try again.';
        }
    } else {
        $success = false;
        $errorMessage = 'reCAPTCHA response is missing.';
    }
}

// Input validation and sanitization
$name = sanitize_text_field($_POST['name']);
$name = wp_unslash($name); // Remove unnecessary escaping

$email = sanitize_email($_POST['email']);
$phone = sanitize_text_field($_POST['phone']);

$special_notes = sanitize_textarea_field($_POST['special_notes']);
$special_notes = wp_unslash($special_notes);

$physical_address = sanitize_text_field($_POST['physical_address']);
$physical_address = wp_unslash($physical_address);

$zipcode = sanitize_text_field($_POST['zipcode']);
$zipcode = wp_unslash($zipcode);

$total_participants = isset($_POST['total_participants']) ? intval($_POST['total_participants']) : 0;

$attending_virtually = isset($_POST['virtual_attendance']) ? 1 : 0;

$event_data_id = isset($_POST['event_data_id']) ? intval($_POST['event_data_id']) : 0;

$new_booking_reference = sanitize_text_field($_POST['new_booking_reference']);
$existing_booking_reference = sanitize_text_field($_POST['existing_booking_reference']);
$existing_record = sanitize_text_field($_POST['existing_record']) === 'true';
$costs = json_decode(stripslashes($_POST['costs']), true);
$occurrences = json_decode(stripslashes($_POST['occurrences']), true);

if (empty($name) || empty($event_data_id) || empty($new_booking_reference) || ($existing_record && empty($existing_booking_reference))) {
    sendResponse(false, 'Missing required fields.');
}

// Start a database transaction for safety
$wpdb->query('START TRANSACTION');

// Filter occurrences to exclude past ones i.e. include future only
$future_occurrences = filterPastOccurrences($wpdb, $occurrences, $event_data_id, current_time('mysql'));

// Handle existing or new booking
if ($existing_record) {
    handleExistingBooking(
        $wpdb, 
        $event_data_id, 
        $existing_booking_reference,
        $new_booking_reference, 
        $costs, 
        $future_occurrences, 
        $name, 
        $email, 
        $phone, 
        $special_notes,
        $physical_address,
        $zipcode, 
        $total_participants,
        $attending_virtually,
        $existing_record
    );
} else {
    $success = addNewRecord(
        $wpdb, 
        $event_data_id, 
        $new_booking_reference, 
        $costs, 
        $future_occurrences, 
        $name, 
        $email, 
        $phone, 
        $special_notes,
        $physical_address,
        $zipcode, 
        $total_participants,
        $attending_virtually
    );
    if ($success) {
        if(sendEmails($wpdb, $event_data_id, $name, $email, $total_participants, $attending_virtually, $new_booking_reference, $existing_record)) {
            sendResponse(true, 'Added to waitlist successfully! Your new reference is: ' . $new_booking_reference);
        }
        else {
            sendResponse(true, 'Added to waitlist successfully! Your new reference is: ' . $new_booking_reference . ' but there was an issue sending an email notification.');
        }
    } else {
        sendResponse(false, 'Failed to add to the waitlist.');
    }
}

// Utility function to handle response
function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
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

// Handle updates for existing bookings
function handleExistingBooking($wpdb, $event_data_id, $existing_booking_reference, $new_booking_reference, $costs, $occurrences, $name, $email, $phone, $special_notes, $physical_address, $zipcode, $total_participants, $attending_virtually, $existing_record) {
    try {
        // Remove all of the previous bookings (past and future) as we don't need to keep these anymore
        $delete_result = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}leanwi_event_waitlist_booking
            WHERE booking_reference = %s and event_data_id = %d",
            $existing_booking_reference, $event_data_id
        ));

        if ($delete_result === false) {
            throw new Exception('Failed to delete the previous waitlist.');
        }

        // Reuse the booking reference
        $new_booking_reference = $existing_booking_reference;

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
        $occurrences, 
        $name, 
        $email, 
        $phone, 
        $special_notes,
        $physical_address,
        $zipcode, 
        $total_participants,
        $attending_virtually
    );
    if ($success) {
        // Commit the transaction if everything succeeded
        $wpdb->query('COMMIT');
        if(sendEmails($wpdb, $event_data_id, $name, $email, $total_participants, $attending_virtually, $new_booking_reference, $existing_record)) {
            sendResponse(true, 'Added to waitlist successfully! Your new reference is: ' . $new_booking_reference);
        }
        else {
            sendResponse(true, 'Added to waitlist successfully! Your new reference is: ' . $new_booking_reference . ' but there was an issue sending an email notification.');
        }
    } else {
        // Roll back the transaction on any error
        $wpdb->query('ROLLBACK');
        sendResponse(false, 'Failed to add to the waitlist.');
    }
}

// Insert new booking data
function addNewRecord($wpdb, $event_data_id, $booking_reference, $costs, $occurrences, $name, $email, $phone, $special_notes, $physical_address, $zipcode, $total_participants, $attending_virtually) {
    try {
        $result = $wpdb->insert("{$wpdb->prefix}leanwi_event_waitlist_booking", [
            'booking_reference' => $booking_reference,
            'event_data_id' => $event_data_id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'special_notes' => $special_notes,
            'physical_address' => $physical_address,
            'zipcode' => $zipcode,
            'total_participants' => $total_participants,
            'attending_virtually' => $attending_virtually
        ]);

        if ($result) {
            $booking_id = $wpdb->insert_id;

            if (is_array($costs)) {
                foreach ($costs as $cost) {
                    $number_of_participants = intval($cost['number_of_participants']);
                    $extra_info = isset($cost['extra_info']) ? wp_unslash($cost['extra_info']) : '';
                    $extra_info = sanitize_textarea_field($extra_info);

                    if ($number_of_participants > 0) { // Only insert if number_of_participants is greater than 0
                        $wpdb->insert("{$wpdb->prefix}leanwi_event_waitlist_costs", [
                            'booking_id' => $booking_id,
                            'cost_id' => intval($cost['cost_id']),
                            'number_of_participants' => $number_of_participants,
                            'extra_info' => $extra_info
                        ]);
                    }
                }
            }

            if (is_array($occurrences)) {
                foreach ($occurrences as $occurrence) {
                    $wpdb->insert("{$wpdb->prefix}leanwi_event_waitlist_occurrences", [
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


function sendEmails($wpdb, $event_data_id, $name, $email, $total_participants, $attending_virtually, $booking_reference, $existing_record) {
    
    //Global Event Settings Email Variables
    $send_admin_email = get_option('leanwi_event_send_admin_booking_email');
    $admin_email_address = get_option('leanwi_event_admin_email_address');
    $email_from_name = get_option('leanwi_event_email_from_name', 'Library Events Team');
    $email_from_name = wp_unslash($email_from_name);

    //This event's email variables
    $event_name = isset($_POST['event_name']) ? sanitize_text_field($_POST['event_name']) : '';
    $event_name = wp_unslash($event_name);

    $event_url = isset($_POST['event_url']) ? esc_url($_POST['event_url']) : '';
    $event_admin_email = isset($_POST['event_admin_email']) ? sanitize_email($_POST['event_admin_email']) : '';

    $extra_email_text = isset($_POST['extra_email_text']) ? sanitize_text_field($_POST['extra_email_text']) : '';
    $extra_email_text = wp_unslash($extra_email_text);

    $extra_event_url = isset($_POST['extra_event_url']) ? esc_url($_POST['extra_event_url']) : '';

    $include_extra_event_url_in_email = isset($_POST['include_extra_event_url_in_email']) && $_POST['include_extra_event_url_in_email'] == 1;

    // No recipients? Skip sending
    if ($send_admin_email === 'no' && empty($event_admin_email) && empty($email)) {
        return true; // No one to send emails to, but function executed correctly
    }

    /* Build the email */
    if($existing_record) {
        $subject = ' Your waitlist booking has been updated for ' . esc_html($event_name);
        $email_body = "<p>Hi <strong>" . esc_html($name) . "</strong>,</p>";
        $email_body .= "<p>Thank you. The details of your updated waitlist booking are below. Your booking ID is: <strong>" . esc_html($booking_reference) . "</strong>.</p>";
    }
    else {
        $subject = 'Details of your Waitlist Booking for ' . esc_html($event_name);
        $email_body = "<p>Hi <strong>" . esc_html($name) . "</strong>,</p>";
        $email_body .= "<p>Thank you for registering your interest in " . esc_html($event_name) . ". Your waitlist booking ID is: <strong>" . esc_html($booking_reference) . "</strong>.</p>";
    }

    $email_body .= "<p>You can use this ID to find and modify your waitlist booking by going to this page: " .
        "<a href='" . esc_url($event_url) . "?booking_ref=" . esc_html($booking_reference) . "'>" . esc_url($event_url) . "</a> " .
        "and entering the above ID.</p>";

    $email_body .= "<p>We will endeaver to keep you informed of your booking status and whether your waitlist booking may be upgraded to a confirmed booking 
        should a spot become available.</p>";

    $email_body .= "<h3>Details of Your Waitlist Booking:</h3>";
    $email_body .= "<p><strong>Participants:</strong> " . esc_html($total_participants) . " attending.</p>";

    // Fetch existing booking ID
    $booking_id = $wpdb->get_var($wpdb->prepare(
        "SELECT booking_id FROM {$wpdb->prefix}leanwi_event_waitlist_booking WHERE booking_reference = %s and event_data_id = %d",
        $booking_reference, $event_data_id
    ));
    if(!$booking_id){
        return false; //Couldn't get the booking_id for some reason so something must have gone wrong.
    }

    // Fetch event occurrences for this booking
    $events = $wpdb->get_results($wpdb->prepare("
        SELECT o.start_date
        FROM {$wpdb->prefix}leanwi_event_waitlist_booking b
        JOIN {$wpdb->prefix}leanwi_event_waitlist_occurrences wo ON wo.booking_id = b.booking_id 
        JOIN {$wpdb->prefix}tec_occurrences o ON o.occurrence_id = wo.occurrence_id
        WHERE b.booking_id = %d
    ", $booking_id));

    // Query to fetch total cost for this booking
    $total_cost = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(c.cost_amount * wc.number_of_participants) AS total_cost
         FROM {$wpdb->prefix}leanwi_event_waitlist_booking b
         JOIN {$wpdb->prefix}leanwi_event_waitlist_costs wc ON wc.booking_id = b.booking_id
         JOIN {$wpdb->prefix}leanwi_event_cost c ON c.cost_id = wc.cost_id
         JOIN {$wpdb->prefix}leanwi_event_waitlist_occurrences wo ON wo.booking_id = b.booking_id
         JOIN {$wpdb->prefix}tec_occurrences o ON o.occurrence_id = wo.occurrence_id
         WHERE b.booking_id = %d
         GROUP BY b.booking_id",
        $booking_id
    ));

    if (!empty($events)) {
        $email_body .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        $email_body .= "<tr><th>Event Name</th><th>Start Date</th><th>Attendance Type</th></tr>";

        foreach ($events as $event) {
            $start_date = date("F j, Y, g:i a", strtotime($event->start_date));
            $attendance_type = ($attending_virtually == 1) ? "Virtual" : "In-person";

            $email_body .= "<tr>";
            $email_body .= "<td>" . esc_html($event_name) . "</td>";
            $email_body .= "<td>" . esc_html($start_date) . "</td>";
            $email_body .= "<td>" . $attendance_type . "</td>";
            $email_body .= "</tr>";
        }

        $email_body .= "</table>";
    }

    // Add total cost line
    if ($total_cost !== null && $total_cost > 0) {
        $email_body .= "<p><strong>Total Cost:</strong> $" . number_format($total_cost, 2) . " (For your information only - no payment is required)</p>";
    }

    if (!empty($extra_email_text)) {
        $email_body .= "<p>" . esc_html($extra_email_text) . "</p>";
    }

    if ($include_extra_event_url_in_email && !empty($extra_event_url)) {
        $email_body .= "<p><strong>Additional information about this event can be found at:</strong> <a href='" . esc_url($extra_event_url) . "'>" . esc_html($extra_event_url) . "</a></p>";
    }

    $email_body .= "<p>Regards,</p>" .
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
