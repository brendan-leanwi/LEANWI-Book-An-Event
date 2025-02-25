<?php

// Hook into WordPress to ensure functions like wp_get_current_user are available.
add_action('wp_enqueue_scripts', function () {
    // Check if the current user has the "event_staff" role.
    $current_user = wp_get_current_user();
    $is_event_staff = in_array('event_staff', (array) $current_user->roles);

    // Pass the result to JavaScript.
    echo '<script>';
    echo 'const isEventStaff = ' . json_encode($is_event_staff) . ';';
    echo '</script>';
});

// Register the shortcode for the event details
function display_event_details() {
    // Output the HTML and add the venue ID as a hidden field
    ob_start();
    $current_user = wp_get_current_user();
    $is_booking_staff = in_array('booking_staff', (array) $current_user->roles);
    ?>
    <div id="hidden_event_data">
        <!-- Data from leanwi_event_data will be placed in here as hidden input fields via JavaScript (event-booking.js) on page load -->
    </div>

    <div class="staff-search-container" style="display: <?php echo $is_booking_staff ? 'block' : 'none'; ?>;">
        <h2 style="text-align: center;">Search by Name</h2> 
        <form id="staffSearchForm" method="get" style="margin-bottom: 20px;">
            <input type="text" id="nameSearchInput" name="name_search" value="" placeholder="Search by name" style="padding: 8px; width: 70%;">
            <button type="submit" style="padding: 8px;">Search</button>
        </form>
        <div id="searchResults"></div> <!-- Results will be injected here -->
    </div>


    <div class="staff-button-container-1" style="display: <?php echo $is_booking_staff ? 'block' : 'none'; ?>;">
        <button type="button" id="show-occurrences" class="find-button">Show All Confirmed Bookings by Occurrence</button>
        <p>&nbsp;</p>
    </div>

    <div class="admin-occurrences-heading" id="admin-occurrences-heading" style="display: none">
    <p><h2 style="text-align: center;">Occurrences for this event</h2></p>
    </div>

    <div class="admin-occurrences-container" id="admin-occurrences-container" style="display: none">
    
    </div>

    <div class="staff-button-container-2" style="display: <?php echo $is_booking_staff ? 'block' : 'none'; ?>;">
        <button type="button" id="show-waitlist" class="find-button">Show All Wait List Bookings by Occurrence</button>
        <p>&nbsp;</p>
    </div>

    <div class="admin-waitlist-heading" id="admin-waitlist-heading" style="display: none">
    <p><h2 style="text-align: center;">Wait Lists for this event</h2></p>
    </div>

    <div class="admin-waitlist-container" id="admin-waitlist-container" style="display: none">
    
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
            <input type="hidden" id="booking_reference" name="booking_reference">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br />

            <label for="email">Email:</label>
            <input type="email" id="email" name="email"><br />

            <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                <input type="checkbox" name="virtual_attendance" id="virtual_attendance">
                <label for="virtual_attendance" id="virtual_attendance_label" style="display: none;">Yes, we will be attending this event virtually. (An email address is required)</label>
            </div>

            <div style="display: none; border: 1px solid black; padding: 15px; margin-bottom: 20px;" id="virtual_attendance_optional">
                <label id="virtual_attendance_optional_label" style="display: block; text-align: center; width: 100%; display: block;">** You have the option to attend this event either in-person or virtually.<br>Instructions for attending virtually will be emailed to you. **</label>
            </div>

            <div style="display: none; border: 1px solid black; padding: 15px; margin-bottom: 20px;" id="virtual_attendance_only">
                <label id="virtual_attendance_only_label" style="display: block; text-align: center; width: 100%; display: block;">** This is a virtual event only.<br>Instructions for attending virtually will be emailed to you. **</label>
            </div>

            <label for="phone">Phone:</label>
            <input type="phone" id="phone" name="phone"><br />

            <label for="physical_address" id="physical_address_label" style="display: none;">Physical Address:</label>
            <input type="text" id="physical_address" name="physical_address" style="width: 100%; padding: 8px; margin-bottom: 10px; display: none;">

            <label for="zipcode" id="zipcode_label" style="display: none;">
                Zipcode: 
                <span class="info-icon" title="We need this information to confirm that you are a local resident. Please contact us before making a booking if you are not a local resident."></span>
            </label>
            <input type="text" id="zipcode" name="zipcode" style="width: 100%; padding: 8px; margin-bottom: 10px; display: none;">

            <label for="special_notes" id="special_notes_label" style="display: none;">Please enter any special notes: </label>
            <textarea id="special_notes" name="special_notes" rows="3" style="width: 100%; padding: 8px; margin-bottom: 10px; display: none;"></textarea>


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

            <div class="staff-capacity-override-container" style="display: <?php echo $is_booking_staff ? 'block' : 'none'; ?>;">
                <input type="checkbox" name="capacity_override" id="capacity_override" class="capacity_override">
                <label for="capacity_override">Override capacity restrictions</label>
            </div>

            <!-- Submit and Wait List buttons -->
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="action" value="book">Book Event</button>         
                <button type="submit" name="action" value="waitlist" id="waitlist-booking" class="find-button">Add to Wait List</button>
            </div>
        </form>
        <p id="response-message"></p>
    </div>

    <div id="sold-out"  style="display: none">
        <h3 id="sold-out-text">Sorry, this event has been sold out</h3>
    </div>
    
    <?php
    return ob_get_clean();
}

add_shortcode('leanwi_event_details', 'display_event_details');
?>
