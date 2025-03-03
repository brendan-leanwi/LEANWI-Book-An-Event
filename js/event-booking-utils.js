function addListeners() {
    // Event listener for input changes to update the total cost
    document.querySelector('#costs-container').addEventListener('input', function (e) {
        if (e.target.matches('.attending-input')) {
            updateTotalCost();
        }
    });

    // Event listener for occurrence checkbox changes to update the total cost
    document.querySelector('#occurrences-container').addEventListener('change', function (e) {
        if (e.target.matches('.occurrence-checkbox')) {
            updateTotalCost();
        }
    });

    // Event Listener for main form 
    document.getElementById('attendance-form').addEventListener('submit', function(event) {
        event.preventDefault();

        // Identify which button was clicked
        let action = event.submitter.value; 

        // Collect form data
        let formData = new FormData(this);

        if (action === "book") {
            document.getElementById('response-message').textContent = 'Submitting Booking. Please wait...';
            submitBooking(formData);
        } else if (action === "waitlist") {
            document.getElementById('response-message').textContent = 'Adding to Wait List. Please wait...';
            submitWaitList(formData);
        }
    });
};

// Function to check event availability based on capacity
function checkEventAvailability(postId, capacity) {
    if(capacity <= 0) {
        return Promise.resolve(false); //A zero event capacity implies the event allows unlimited attendance so can't be sold out
    }

    return fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/check-event-availability.php?post_id=${postId}&capacity=${capacity}&_wpnonce=${leanwiVars.ajax_nonce}`)
        .then(response => response.json())
        .then(data => data.isSoldOut)
        .catch(error => {
            console.error('Error fetching availability status:', error);
            return true; // Assume sold out if there's an error
        });
}

// Function to apply participation rule logic
function applyParticipationRule(participationRule) {
    const checkboxes = document.querySelectorAll('.occurrence-checkbox');
    
    if (participationRule === 'one') {
        // Allow only one checkbox to be checked
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                if (checkbox.checked) {
                    checkboxes.forEach(otherCheckbox => {
                        if (otherCheckbox !== checkbox) {
                            otherCheckbox.checked = false;
                        }
                    });
                }
            });
        });
    } else if (participationRule === 'all') {
        // Check all checkboxes and prevent unchecking
        checkboxes.forEach(checkbox => {
            checkbox.checked = true; // Check all checkboxes initially
            checkbox.addEventListener('change', (event) => {
                if (!checkbox.checked) {
                    event.preventDefault(); // Prevent unchecking
                    alert("Attendance to all occurrences of this event is required.");
                    checkbox.checked = true; // Re-check the checkbox
                }
            });
        });
    } else if (participationRule === 'any') {
        // Allow normal behavior (no special logic needed)
        //checkboxes.forEach(checkbox => {
        //    checkbox.disabled = false;
        //});
    }
}

// Function to display the appropriate participation message
function displayParticipationMessage(participationRule) {
    // Hide all messages initially
    document.getElementById('participation-one').style.display = 'none';
    document.getElementById('participation-all').style.display = 'none';
    document.getElementById('participation-any').style.display = 'none';

    // Display the appropriate message based on participationRule
    if (participationRule === 'one') {
        document.getElementById('participation-one').style.display = 'block';
    } else if (participationRule === 'all') {
        document.getElementById('participation-all').style.display = 'block';
    } else if (participationRule === 'any') {
        document.getElementById('participation-any').style.display = 'block';
    }
}

function generateBookingReference() {
    return Math.random().toString(36).substring(2, 9);
}

function generateWaitListReference() {
    return "WL#" + Math.random().toString(36).substring(2, 9);
}

function setButtonColors() {
    
    // Get the user-defined colors from bookingSettings
    const button1BorderColor = eventSettings.button_1_border_color || '#000000';
    const button1BgColor = eventSettings.button_1_bg_color || '#007BFF';
    const button1TextColor = eventSettings.button_1_text_color || '#FFFFFF';

    const button2BorderColor = eventSettings.button_2_border_color || '#000000';
    const button2BgColor = eventSettings.button_2_bg_color || '#007BFF';
    const button2TextColor = eventSettings.button_2_text_color || '#FFFFFF';

    const button3BorderColor = eventSettings.button_3_border_color || '#000000';
    const button3BgColor = eventSettings.button_3_bg_color || '#007BFF';
    const button3TextColor = eventSettings.button_3_text_color || '#FFFFFF';

    // Update the CSS variables in the :root selector
    document.documentElement.style.setProperty('--button_1_border_color', button1BorderColor);
    document.documentElement.style.setProperty('--button_1_bg_color', button1BgColor);
    document.documentElement.style.setProperty('--button_1_text_color', button1TextColor);
    
    document.documentElement.style.setProperty('--button_2_border_color', button2BorderColor);
    document.documentElement.style.setProperty('--button_2_bg_color', button2BgColor);
    document.documentElement.style.setProperty('--button_2_text_color', button2TextColor);
    
    document.documentElement.style.setProperty('--button_3_border_color', button3BorderColor);
    document.documentElement.style.setProperty('--button_3_bg_color', button3BgColor);
    document.documentElement.style.setProperty('--button_3_text_color', button3TextColor);
}