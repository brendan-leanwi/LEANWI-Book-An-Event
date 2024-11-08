<?php

// Register the shortcode for the event details
function display_event_details() {
    // Output the HTML and add the venue ID as a hidden field
    ob_start();
    ?>
    <div id="hidden_event_data">
        <!-- Data from leanwi_event_data will be placed in here as hidden input fields via JavaScript (event-booking.js) on page load -->
    </div>

    <div id="event-attendance">
        <h3>RSVP for this Event</h3>
        <form id="attendance-form">
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
            <div id="event-disclaimers">
            </div>

            <p>&nbsp;</p><!-- Spacer -->
            <div id="occurrences-container" class="occurrences">
            <!-- This is where the dynamically added occurrences will appear -->
            </div>

            <!-- Submit button -->
            <button type="submit">Submit</button>
        </form>
        <p id="response-message"></p>
    </div>

    <style>
        #sold-out {
            display: none;
        }
    </style>

    <div id="sold-out">
        <h3>Sorry, this event has been sold out</h3>
    </div>
    
    <?php
    return ob_get_clean();
}

add_shortcode('leanwi_event_details', 'display_event_details');
?>
