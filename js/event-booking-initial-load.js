window.leanwiBookingData = {
    totalCost: 0,
    totalCostDisplay: null,
    eventSlug: '',
    globalEventData: null,
    usedHistoricCosts: [],
    hasFutureOccurrences: true,
    occurrencesAdminPage: false,
    waitListAdminPage: false,
    waitListBooking: false,
    existingRecord: false
};

document.addEventListener('DOMContentLoaded', function () {

    // Let's show in the cosole log whether the user is staff or not
    console.log("Is Event Staff:", isEventStaff);

    setButtonColors();

    // Get event slug from URL
    try {
        let pathArray = window.location.pathname.split('/').filter(segment => segment);
        eventSlug = pathArray[pathArray.length - 1];
        // Check if the last segment is a date (YYYY-MM-DD format)
        if (/^\d{4}-\d{2}-\d{2}$/.test(eventSlug)) {
            eventSlug = pathArray[pathArray.length - 2];
        }
    } catch (error) {
        console.error('Error getting event slug:', error);
    }

    const searchResults = document.getElementById("searchResults");
    document.body.style.cursor = 'wait'; // Set cursor before fetch starts
    // Fetch event data
    fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-via-slug.php?event_slug=${eventSlug}&_wpnonce=${leanwiVars.ajax_nonce}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data.length > 0) {
                window.leanwiBookingData.globalEventData = data.data[0];
                console.log("Retrieved globalEventdata: ", window.leanwiBookingData.globalEventData);
                
                displayOptionalFields();

                if (bookingRef) {
                    showExistingBookingContainer();
                }
            } else {
                throw new Error("Event data not found or invalid.");
            }
        })
        .catch(error => {
            console.error("Error fetching data:", error);
            searchResults.innerHTML = "<p style='color: red;'>Error retrieving Event data.</p>";
        })
        .finally(() => {
            document.body.style.cursor = 'default'; // Reset cursor after fetch completes
        });

    // Function to extract query parameters from the URL
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    // Get booking_id from the URL and set it in the unique_id input field if it exists
    const bookingRef = getQueryParam('booking_ref');
    if (bookingRef) {
        window.leanwiBookingData.waitListBooking = bookingRef.startsWith("WL#");
        document.getElementById('booking_ref').value = bookingRef;
        document.getElementById('existing_booking_heading').textContent = "Your Booking Reference has been Entered:";
        document.getElementById('event_attendance_heading').textContent = "Your details for attending this event";
        document.getElementById('booking-choices-container').style.display = 'none';
    }

});

function displayOptionalFields(){
    
    let emailInput = document.getElementById("email"); //Need this for making required later

    // Get the elements
    let addressLabel = document.getElementById("physical_address_label");
    let addressInput = document.getElementById("physical_address");

    if (window.leanwiBookingData.globalEventData.include_physical_address === 1) {
        // Show the fields and mark as required
        addressLabel.style.display = "block";
        addressInput.style.display = "block";
        addressInput.required = true;
    } else {
        // Hide the fields and remove required attribute
        addressLabel.style.display = "none";
        addressInput.style.display = "none";
        addressInput.required = false;
    }

    // Get the elements
    let zipcodeLabel = document.getElementById("zipcode_label");
    let zipcodeInput = document.getElementById("zipcode");

    if (window.leanwiBookingData.globalEventData.include_zipcode === 1) {
        // Show the fields and mark as required
        zipcodeLabel.style.display = "block";
        zipcodeInput.style.display = "block";
        zipcode.required = true;
    } else {
        // Hide the fields and remove required attribute
        zipcodeLabel.style.display = "none";
        zipcodeInput.style.display = "none";
        zipcodeInput.required = false;
    }

    // Get the elements
    let specialNotesLabel = document.getElementById("special_notes_label");
    let specialNotesInput = document.getElementById("special_notes");

    if (window.leanwiBookingData.globalEventData.include_special_notes === 1) {
        // Show the fields and mark as required
        specialNotesLabel.style.display = "block";
        specialNotesInput.style.display = "block";
        specialNotesInput.required = false;
    } else {
        // Hide the fields and remove required attribute
        specialNotesLabel.style.display = "none";
        specialNotesInput.style.display = "none";
        specialNotesInput.required = false;
    }

    // Get the elements
    let indicateVirtualAttendanceLabel = document.getElementById("virtual_attendance_label");
    let indicateVirtualAttendanceInput = document.getElementById("virtual_attendance");

    if (['specify'].includes(window.leanwiBookingData.globalEventData.virtual_event_rule)) {
        // Show the fields and mark as required
        indicateVirtualAttendanceLabel.style.display = "block";
        indicateVirtualAttendanceInput.style.display = "block";
        indicateVirtualAttendanceInput.required = false;
    } else {
        // Hide the fields and remove required attribute
        indicateVirtualAttendanceLabel.style.display = "none";
        indicateVirtualAttendanceInput.style.display = "none";
        indicateVirtualAttendanceInput.required = false;
    }

    //Add a listener to make the email required if checkbox is checked
    indicateVirtualAttendanceInput.addEventListener("change", function () {
        if (this.checked) {
            emailInput.setAttribute("required", "required");
        } else {
            emailInput.removeAttribute("required");
        }
    });

    // Get the elements
    let indicateVirtualAttendanceOptional = document.getElementById("virtual_attendance_optional");

    if (['optional'].includes(window.leanwiBookingData.globalEventData.virtual_event_rule)) {
        // Show the fields and mark as required
        indicateVirtualAttendanceOptional.style.display = "block";
    } else {
        // Hide the fields and remove required attribute
        indicateVirtualAttendanceOptional.style.display = "none";
    }

    // Get the elements
    let indicateVirtualAttendanceOnly = document.getElementById("virtual_attendance_only");

    if (['only'].includes(window.leanwiBookingData.globalEventData.virtual_event_rule)) {
        // Show the fields and mark as required
        indicateVirtualAttendanceOnly.style.display = "block";
        emailInput.required = true;
    } else {
        // Hide the fields and remove required attribute
        indicateVirtualAttendanceOnly.style.display = "none";
    }
}

// Function to adjust the button label and state
function adjustBookingButtons() {
    const submitButton = document.querySelector('#attendance-form button[type="submit"][value="book"]');
    const waitlistButton = document.querySelector('#attendance-form button[type="submit"][value="waitlist"]');

    if (waitlistButton) {
        // Do not display the 'Add to Wait List' button if it is an existing record that is not already a waitlist booking
        if (window.leanwiBookingData.existingRecord && !window.leanwiBookingData.waitListBooking) {
            waitlistButton.style.display = 'none';
        } else {
            waitlistButton.style.display = 'inline-block'; // Ensure it's visible if conditions allow
            waitlistButton.textContent = window.leanwiBookingData.existingRecord ? 'Update Wait List' : 'Add to Wait List';
        }
    }

    if (submitButton) {
        if (!hasFutureOccurrences) {
            // Disable the button and set the message for past bookings
            submitButton.disabled = true;
            submitButton.textContent = 'Past Booking - No updates to booking possible';
        } else {
            // Enable the button and adjust text based on `existingRecord`
            submitButton.disabled = false;
            if(window.leanwiBookingData.existingRecord && window.leanwiBookingData.waitListBooking && !isEventStaff){
                submitButton.style.display = 'none';
            }
            else if(window.leanwiBookingData.existingRecord && window.leanwiBookingData.waitListBooking && isEventStaff){
                submitButton.style.display = 'inline-block';
                submitButton.textContent = 'Transfer Wait List to a Booking';
            }
            else {
                submitButton.textContent = window.leanwiBookingData.existingRecord ? 'Update Event Booking' : 'Book Event';
            }
        }
    }
}

function createFormFields(event) {
    globalEventData = event;
    const hiddenDiv = document.getElementById('hidden_event_data');

    // Create hidden inputs for each event property
    const fields = {
        'event_data_id': event.event_data_id,
        'post_id': event.post_id,
        'event_url': event.event_url,
        'event_image': event.event_image,
        'capacity': event.capacity,
        'category_id': event.category_id,
        'audience_id': event.audience_id,
        'historic': event.historic,
        'participation_rule': event.participation_rule
    };

    for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        hiddenDiv.appendChild(input);
    }

    displayParticipationMessage(event.participation_rule);
}