<?php

// Register the shortcode for the event details
function display_event_details() {
    // Output the HTML and add the venue ID as a hidden field
    ob_start();
    ?>
    <div id="hidden_event_data">
        <!-- Data from leanwi_event_data will be placed in here as hidden input fields via JavaScript (event-booking.js) on page load -->
    </div>

    <div class="booking-choices-container" id="booking-choices-container" style="display: block">
        <p><h2 id="booking_choices_heading" style="text-align: center;">Signup for this event or review a booking</h2></p>
        <p> </p>
        <form id="booking-choices" method="POST">
            <div class="button-container">
                <button type="submit" class="find-button">Signup for this Event</button>
                <button type="button" id="retrieve-booking" class="find-button">Find my Booking</button>
            </div>
        </form>
    </div>

    <div class="existing-booking-container" id="existing-booking-container" style="display: none">
        <p><h2 id="existing_booking_heading">Please enter your booking reference?</h2></p>
        <p> </p>
        <form id="existing-booking" method="POST">
            <!-- Set up nonce verification for the fetch and delete actions -->
            <?php wp_nonce_field('fetch_existing_event_action', 'fetch_existing_event_nonce'); ?>
            <?php wp_nonce_field('delete_existing_event_action', 'delete_existing_event_nonce'); ?>

            <label for="booking_ref" class="find-label">Booking #:</label>
            <input type="text" id="booking_ref" name="booking_ref" class="find-input" required>
            
            <div class="button-container">
                <button type="submit" class="find-button">Retrieve Booking</button>
                <button type="button" id="delete-booking" class="find-button">Delete Booking</button>
            </div>
        </form>
    </div>
    
    <div class="event-attendance" id="event-attendance" style="display: none">
        <h3 id="event_attendance_heading">Signup to attend this event</h3>
        <form id="attendance-form">
            <?php wp_nonce_field('submit_event_action', 'submit_event_nonce'); ?>
            <!-- Hidden field with the dynamic event slug -->
            <input type="hidden" id="event-slug" name="event_slug">
            <input type="hidden" id="capacity" name="capacity">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br />

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br />

            <label for="phone">Phone:</label>
            <input type="phone" id="phone" name="phone"><br />

            <p>&nbsp;</p><!-- Spacer -->
            <p id="participation-one" style="display: none"><strong>Please select an event occurrence.</strong></p>
            <p id="participation-all" style="display: none"><strong>Please sign up for all of the following events.</strong></p>
            <p id="participation-any" style="display: none"><strong>Please choose all events you wish to attend.</strong></p>

            <div id="occurrences-container">
            <!-- This is where the dynamically added occurrences will appear -->
            </div>

            <div id="costs-container">
            <!-- This is where the dynamically added attendance and costs will appear -->
            </div>
          
            <p>&nbsp;</p><!-- Spacer -->
            <div id="event-disclaimers">
            </div>

            <!-- Submit button -->
            <button type="submit">Book Event</button>
        </form>
        <p id="response-message"></p>
    </div>

    <div id="sold-out"  style="display: none">
        <h3>Sorry, this event has been sold out</h3>
    </div>
    
    <?php
    return ob_get_clean();
}

add_shortcode('leanwi_event_details', 'display_event_details');
?>
