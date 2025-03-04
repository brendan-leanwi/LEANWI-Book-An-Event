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

$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
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
$capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
$capacity_override = isset($_POST['capacity_override']) ? intval($_POST['capacity_override']) : 0;

$new_booking_reference = sanitize_text_field($_POST['new_booking_reference']);
$existing_booking_reference = sanitize_text_field($_POST['existing_booking_reference']);
$fromWaitListBooking = strpos($existing_booking_reference, "WL-") === 0;
$existing_record = sanitize_text_field($_POST['existing_record']) === 'true';
$costs = json_decode(stripslashes($_POST['costs']), true);
$occurrences = json_decode(stripslashes($_POST['occurrences']), true);


if (empty($name) || empty($event_data_id) || empty($new_booking_reference) || ($existing_record && empty($existing_booking_reference))) {
    sendResponse(false, 'Missing required fields.');
}

//If capacity override is set then set capacity to 0 indicating that no capacity checks are needed
if ($capacity_override > 0){
    $capacity = 0;
}

// Start a database transaction for safety
$wpdb->query('START TRANSACTION');

//If we're saving a booking that was originally on a wait list we can delte the waitlist entry
if($fromWaitListBooking){
    $delete_result = $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}leanwi_event_waitlist_booking WHERE booking_reference = %s",
        $existing_booking_reference
    ));
    // Check if deletion was successful
    if ($delete_result === false) {
        $wpdb->query('ROLLBACK');
        sendResponse(false, 'Failed to delete the wait list booking.');
    } elseif ($delete_result === 0) {
        $wpdb->query('ROLLBACK');
        sendResponse(false, 'No matching wait list booking found.');
    }

    $existing_record = false; // It's actually a new Event Booking so treat it as such
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
        $special_notes,
        $physical_address,
        $zipcode, 
        $total_participants,
        $attending_virtually,
        $capacity,
        $existing_record
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
        $special_notes,
        $physical_address,
        $zipcode,
        $total_participants,
        $attending_virtually
    );

    if ($success) {
        if(sendEmails($wpdb, $event_data_id, $name, $email, $total_participants, $attending_virtually, $new_booking_reference, $existing_record)) {
            sendResponse(true, 'Booking successful! Your new reference is: ' . $new_booking_reference);
        }
        else {
            sendResponse(true, 'Booking successful! Your new reference is: ' . $new_booking_reference . ' but there was an issue sending an email notification.');
        }
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
function handleExistingBooking($wpdb, $event_data_id, $existing_booking_reference, $new_booking_reference, $costs, $occurrences, $name, $email, $phone, $special_notes, $physical_address, $zipcode, $total_participants, $attending_virtually, $capacity, $existing_record) {
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
            sendResponse(true, 'Booking successful! Your new reference is: ' . $new_booking_reference);
        }
        else {
            sendResponse(true, 'Booking successful! Your new reference is: ' . $new_booking_reference . ' but there was an issue sending an email notification.');
        }
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
    
        // Get the current total participants for this occurrence (We're excluding virtual attendees if applicable)
        $current_participants = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(
                CASE 
                    WHEN ed.virtual_event_rule = 'specify' 
                         AND ed.include_virtual_bookings_in_capacity_calc = 0 
                         AND b.attending_virtually = 1 
                    THEN 0 
                    ELSE bo.number_of_participants 
                END
            ), 0)
            FROM {$wpdb->prefix}leanwi_event_booking b
            INNER JOIN {$wpdb->prefix}leanwi_event_booking_occurrences bo ON b.booking_id = bo.booking_id
            INNER JOIN {$wpdb->prefix}leanwi_event_data ed ON ed.event_data_id = b.event_data_id
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
function addNewRecord($wpdb, $event_data_id, $booking_reference, $costs, $occurrences, $name, $email, $phone, $special_notes, $physical_address, $zipcode, $total_participants, $attending_virtually) {
    try {
        $result = $wpdb->insert("{$wpdb->prefix}leanwi_event_booking", [
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
                    $extra_info = sanitize_textarea_field($cost['extra_info']);
                    $extra_info = wp_unslash($extra_info);

                    if ($number_of_participants > 0) { // Only insert if number_of_participants is greater than 0
                        $wpdb->insert("{$wpdb->prefix}leanwi_event_booking_costs", [
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

    //This event's virtual event variables
    //$virtual_event_rule = isset($_POST['virtual_event_password']) ? sanitize_text_field($_POST['virtual_event_password']) : 'no';
    $virtual_event_url = isset($_POST['virtual_event_url']) ? esc_url($_POST['virtual_event_url']) : '';
    $virtual_event_password = isset($_POST['virtual_event_password']) ? sanitize_text_field($_POST['virtual_event_password']) : '';
    $virtual_event_password = wp_unslash($virtual_event_password);
    
    // No recipients? Skip sending
    if ($send_admin_email === 'no' && empty($event_admin_email) && empty($email)) {
        return true; // No one to send emails to, but function executed correctly
    }

    /* Build the email */
    if($existing_record) {
        $subject = ' Your event booking has been updated for ' . esc_html($event_name);
        $email_body = "<p>Hi <strong>" . esc_html($name) . "</strong>,</p>";
        $email_body .= "<p>Thank you. The details of your updated booking are below. Your booking ID is: <strong>" . esc_html($booking_reference) . "</strong>.</p>";
    }
    else {
        $subject = 'Your Event Booking Confirmation for ' . esc_html($event_name);
        $email_body = "<p>Hi <strong>" . esc_html($name) . "</strong>,</p>";
        $email_body .= "<p>Thank you for booking. Your booking ID is: <strong>" . esc_html($booking_reference) . "</strong>.</p>";
    }

    $email_body .= "<p>You can use this ID to find and modify your booking by going to this page: " .
        "<a href='" . esc_url($event_url) . "?booking_ref=" . esc_html($booking_reference) . "'>" . esc_url($event_url) . "</a> " .
        "and entering the above ID.</p>";

    $email_body .= "<h3>Details of Your Booking:</h3>";
    $email_body .= "<p><strong>Participants:</strong> " . esc_html($total_participants) . " attending.</p>";

    // Fetch existing booking ID
    $booking_id = $wpdb->get_var($wpdb->prepare(
        "SELECT booking_id FROM {$wpdb->prefix}leanwi_event_booking WHERE booking_reference = %s and event_data_id = %d",
        $booking_reference, $event_data_id
    ));
    if(!$booking_id){
        return false; //Couldn't get the booking_id for some reason so something must have gone wrong.
    }

    // Fetch event occurrences for this booking
    $events = $wpdb->get_results($wpdb->prepare("
        SELECT o.start_date
        FROM {$wpdb->prefix}leanwi_event_booking b
        JOIN {$wpdb->prefix}leanwi_event_booking_occurrences bo ON bo.booking_id = b.booking_id 
        JOIN {$wpdb->prefix}tec_occurrences o ON o.occurrence_id = bo.occurrence_id
        WHERE b.booking_id = %d
    ", $booking_id));

    // Query to fetch total cost for this booking
    $total_cost = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(c.cost_amount * bc.number_of_participants) AS total_cost
         FROM {$wpdb->prefix}leanwi_event_booking b
         JOIN {$wpdb->prefix}leanwi_event_booking_costs bc ON bc.booking_id = b.booking_id
         JOIN {$wpdb->prefix}leanwi_event_cost c ON c.cost_id = bc.cost_id
         JOIN {$wpdb->prefix}leanwi_event_booking_occurrences bo ON bo.booking_id = b.booking_id
         JOIN {$wpdb->prefix}tec_occurrences o ON o.occurrence_id = bo.occurrence_id
         WHERE b.booking_id = %d
         GROUP BY b.booking_id",
        $booking_id
    ));

    if (!empty($events)) {
        $email_body .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        $email_body .= "<tr><th>Event Name</th><th>Start Date</th><th>Attendance Type</th></tr>";

        foreach ($events as $event) {
            $start_date = date("F j, Y, g:i a", strtotime($event->start_date));
            $attendance_type = ($attending_virtually == 1) ? 
                "<a href='" . esc_url($virtual_event_url) . "'>Virtual</a>" : "In-person";

            $email_body .= "<tr>";
            $email_body .= "<td>" . esc_html($event_name) . "</td>";
            $email_body .= "<td>" . esc_html($start_date) . "</td>";
            $email_body .= "<td>" . $attendance_type . "</td>";
            $email_body .= "</tr>";
        }

        $email_body .= "</table>";
    }

    if ($attending_virtually == 1 && !empty($virtual_event_url)) {
        $email_body .= "<p><strong>Virtual Event Link:</strong> <a href='" . esc_url($virtual_event_url) . "'>" . esc_html($virtual_event_url) . "</a></p>";
        if (!empty($virtual_event_password)) {
            $email_body .= "<p><strong>Event Password:</strong> " . esc_html($virtual_event_password) . "</p>";
        }
    }

    // Add total cost line
    if ($total_cost !== null && $total_cost > 0) {
        $email_body .= "<p><strong>Total Cost:</strong> $" . number_format($total_cost, 2) . "</p>";
        $email_body .= "<p>You will receive communication for making payment at a future date.</p>";
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
