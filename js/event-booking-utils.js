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

// Function to get selected occurrences count
function getSelectedOccurrencesCount() {
    return document.querySelectorAll('.occurrence-checkbox:checked').length;
}

function updateTotalCost() {
    totalCost = 0;
    let totalAttending = 0;
    let totalEvents = 0;

    // Loop through all cost items and calculate the total cost, total attending, and total events
    document.querySelectorAll('.cost-item').forEach(function (costItem) {
        const costAmount = parseFloat(costItem.querySelector('.cost-amount').textContent.replace('$', ''));
        const attending = parseInt(costItem.querySelector('.attending-input').value, 10) || 0;
        totalCost += costAmount * attending;
        totalAttending += attending;  // Sum the total participants
        // Retrieve and log the data-cost-id
        //const costId = costItem.querySelector('.cost-name').getAttribute('data-cost-id');
        //console.log('Cost ID:', costId, ' Amount:', costAmount, ' Attending:', attending);

    });

    // Get the number of selected occurrences
    const occurrencesCount = getSelectedOccurrencesCount();
    totalEvents = occurrencesCount;  // Set total events to the selected occurrences count
    let finalCost = totalCost * totalEvents;

    // Set correct wording for 'person' or 'people'
    const participantsText = totalAttending === 1 ? 'person' : 'people';
    const eventsText = totalEvents === 1 ? 'Event' : 'Events';

    // Update the total cost display
    totalCostDisplay.textContent = `Your total cost is $${finalCost.toFixed(2)} for ${totalEvents} ${eventsText} with ${totalAttending} ${participantsText} attending.`;
}


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