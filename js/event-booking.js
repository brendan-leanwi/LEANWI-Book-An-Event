let totalCost = 0;
let totalCostDisplay;
let eventSlug;

document.addEventListener('DOMContentLoaded', function () {

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

    // Function to extract query parameters from the URL
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    // Get booking_id from the URL and set it in the unique_id input field if it exists
    const bookingRef = getQueryParam('booking_ref');
    if (bookingRef) {
        document.getElementById('booking_ref').value = bookingRef;
        document.getElementById('existing_booking_heading').textContent = "Your Booking Reference has been Entered:";
        document.getElementById('event_attendance_heading').textContent = "Your details for attending this event";
        document.getElementById('booking-choices-container').style.display = 'none';

        // Call the retrieve booking function to display the container
        showExistingBookingContainer();
    }

    const page = getQueryParam('event_page');
    if (page) {
        if(page ==='admin'){
            alert("will go and do admin stuff");
        }
    }
});

document.getElementById('booking-choices').addEventListener('submit', function(event) {
    event.preventDefault();

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
                const event = data.data[0];
        
                // First, check if the event is sold out
                return checkEventAvailability(event.post_id, event.capacity)
                    .then(isSoldOut => {
                        if (isSoldOut) {
                            // If sold out, show the sold-out message and stop further processing
                            document.getElementById('sold-out').style.display = 'block';
                            document.getElementById('event-attendance').style.display = 'none';
                        } else {
                            // Show RSVP form and continue
                            document.getElementById('event-attendance').style.display = 'block';
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
                    })
                    .then(() => {
                        // Fetch event occurrences
                        return fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-occurrences.php?post_id=${event.post_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
                            .then(response => response.json())
                            .then(data => {
                                let occurrencesContainer = document.querySelector('#occurrences-container');
                                occurrencesContainer.innerHTML = ''; // Clear any previous content
        
                                data.event_occurrences.forEach(occurrence => {
                                    let startDate = new Date(occurrence.start_date);
                                    let endDate = new Date(occurrence.end_date);
        
                                    // Format the date and time
                                    let formattedStartDate = `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}, ${startDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
                                    let formattedEndDate = `${endDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
        
                                    // Calculate spots available
                                    let spotsAvailable = event.capacity - occurrence.total_participants;
        
                                    // Create a display element for each occurrence
                                    let occurrenceElement = document.createElement('div');
                                    occurrenceElement.classList.add('occurrence-button');
        
                                    // Disable checkbox if no spots are available
                                    let checkboxDisabled = event.capacity > 0 && spotsAvailable === 0 ? 'disabled' : '';
        
                                    occurrenceElement.innerHTML = `
                                            <input type="checkbox" class="occurrence-checkbox" value="${occurrence.occurrence_id}" ${checkboxDisabled}>
                                            <div class="occurrence-info">
                                                ${formattedStartDate} to ${formattedEndDate}
                                            </div> `;
                                    if (event.capacity > 0) {
                                        occurrenceElement.innerHTML += `
                                            <div class="participants">
                                                (${spotsAvailable} Spots Available)
                                            </div>
                                        `;
                                    } else {
                                        occurrenceElement.innerHTML += `
                                            <div class="participants">
                                                (Unlimited Spots Available)
                                            </div>
                                        `;
                                    }
        
                                    occurrencesContainer.appendChild(occurrenceElement);
                                });
        
                                // Apply participation rule logic
                                applyParticipationRule(event.participation_rule);
                            })
                            .catch(error => {
                                console.error('Error fetching occurrences:', error);
                            });
                    })
                    .then(() => {
                        // Fetch event costs
                        return fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-costs.php?event_data_id=${event.event_data_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
                            .then(response => response.json())
                            .then(data => {
                                let costsContainer = document.querySelector('#costs-container');
                                costsContainer.innerHTML = ''; // Clear any previous content
        
                                // Loop through each cost and create the display elements
                                data.event_costs.forEach(cost => {
                                    // Create a container for each cost item
                                    let costElement = document.createElement('div');
                                    costElement.classList.add('cost-item'); // Add a class for styling
        
                                    // Create the content of each cost item
                                    costElement.innerHTML = `
                                        <div class="cost-name" data-cost-id="${cost.cost_id}">${cost.cost_name}</div>
                                        <div class="cost-amount">$${cost.cost_amount}</div>
                                        <label for="attending-${cost.cost_id}">Number Attending:</label>
                                        <input type="number" id="attending-${cost.cost_id}" name="attending-${cost.cost_id}" min="0" value="0" class="attending-input">
                                    `;
        
                                    // Append the cost element to the container
                                    costsContainer.appendChild(costElement);
                                });
        
                                // Create a div to display the total cost
                                totalCostDisplay = document.createElement('div');
                                totalCostDisplay.id = 'total-cost-display';
                                totalCostDisplay.textContent = 'Your total cost is $0.00 for 0 Events';  // Initial message
                                costsContainer.appendChild(totalCostDisplay);
                            })
                            .catch(error => {
                                console.error('Error fetching costs:', error);
                            });
                    })
                    .then(() => {
                        // Fetch disclaimers last
                        const disclaimersDiv = document.getElementById('event-disclaimers');
                        return fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-disclaimers.php?event_data_id=${event.event_data_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
                            .then(response => response.json())
                            .then(disclaimerData => {
                                if (disclaimerData.disclaimers && disclaimerData.disclaimers.length > 0) {
                                    disclaimersDiv.innerHTML = ''; // Clear existing disclaimers
        
                                    // Create the table
                                    const table = document.createElement('table');
                                    table.classList.add('disclaimer-table'); // Add CSS class for styling
        
                                    disclaimerData.disclaimers.forEach((disclaimer, index) => {
                                        // Row for disclaimer
                                        const row = document.createElement('tr');
                                        row.classList.add('disclaimer-row');
        
                                        // Cell for checkbox
                                        const checkboxCell = document.createElement('td');
                                        const checkbox = document.createElement('input');
                                        checkbox.type = 'checkbox';
                                        checkbox.id = `disclaimer-${index}`;
                                        checkbox.name = `disclaimer-${index}`;
                                        checkbox.required = true; // Set the checkbox as required
                                        checkboxCell.appendChild(checkbox);
        
                                        // Cell for disclaimer text
                                        const textCell = document.createElement('td');
                                        const label = document.createElement('label');
                                        label.setAttribute('for', checkbox.id);
                                        label.textContent = disclaimer.disclaimer;
                                        textCell.appendChild(label);
        
                                        // Append cells to row
                                        row.appendChild(checkboxCell);
                                        row.appendChild(textCell);
        
                                        // Add row to table
                                        table.appendChild(row);
        
                                        // Insert a gap row for spacing (empty row)
                                        const gapRow = document.createElement('tr');
                                        const gapCell = document.createElement('td');
                                        gapCell.colSpan = 2; // Span across both columns
                                        gapRow.appendChild(gapCell);
                                        table.appendChild(gapRow);
                                    });
        
                                    disclaimersDiv.appendChild(table);
                                } else {
                                    disclaimersDiv.style.display = 'none';
                                    console.log('No disclaimers found for this event.');
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching disclaimers:', error);
                            });
                    });
            }
        })
        .catch(error => console.error('Error fetching event details:', error))
        .finally(() => {
            document.body.style.cursor = 'default'; // Reset cursor after fetch completes
        });
    
    document.getElementById('event-attendance').style.display = 'block';
    document.getElementById('existing-booking-container').style.display = 'none';

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

    document.getElementById('attendance-form').addEventListener('submit', function(event) {
        event.preventDefault();
    
        const eventCapacity = document.querySelector('#hidden_event_data input[name="capacity"]'); // <= 0 capacity implies unlimited attendance

        // Get form data and nonce
        const formData = new FormData(this);
        formData.append('booking_reference', generateBookingReference()); // Generate booking reference
        formData.append('submit_event_nonce', document.querySelector('#submit_event_nonce').value);
        
        // Retrieve the event_data_id from the hidden input field in hidden_event_data div
        const eventDataId = document.querySelector('#hidden_event_data input[name="event_data_id"]');
        if (eventDataId) {
            formData.append('event_data_id', eventDataId.value);
        }
        
        // Collect the cost-related data from the form
        let totalParticipants = 0
        const costs = [];
        document.querySelectorAll('.cost-item').forEach(function(item) {
            const costId = item.querySelector('.cost-name').dataset.costId; // Retrieve cost_id from data attribute
            //const numberOfParticipants = item.querySelector('.attending-input').value || 0;
            const numberOfParticipants = parseInt(item.querySelector('.attending-input').value, 10) || 0;
            totalParticipants += numberOfParticipants;
            costs.push({ cost_id: costId, number_of_participants: numberOfParticipants });
        });

        formData.append('costs', JSON.stringify(costs));  // Append the costs data as a JSON string

        // Collect occurrences data and check available spots
        const occurrences = [];
        let hasSpotsError = false;

        document.querySelectorAll('.occurrence-checkbox:checked').forEach(function(checkbox) {
            const occurrenceElement = checkbox.closest('.occurrence-button');
            const occurrenceId = checkbox.value;
            occurrences.push({ occurrence_id: occurrenceId, number_of_participants: totalParticipants });

            if(eventCapacity > 0) {
                // Retrieve the available spots for this occurrence
                const spotsAvailable = parseInt(occurrenceElement.querySelector('.participants').textContent.match(/\d+/)[0], 10) || 0;

                // Check if totalParticipants exceeds available spots for this occurrence
                if (totalParticipants > spotsAvailable) {
                    hasSpotsError = true;
                }
            }
        });

        if (hasSpotsError) {
            document.getElementById('response-message').textContent = 'Error: At least 1 of the selected events does not have enough spots available.';
            return;
        }
        formData.append('occurrences', JSON.stringify(occurrences));  // Append the occurrences data as a JSON string
        
        formData.append('total_participants', totalParticipants);

        //console.log('formData:', Object.fromEntries(formData));
        
        // Validation: Check if at least one occurrence is selected and at least one participant
        if (occurrences.length === 0) {
            document.getElementById('response-message').textContent = 'Please select at least one occurrence.';
            return;
        }
        if (totalParticipants === 0) {
            document.getElementById('response-message').textContent = 'Please enter at least one participant.';
            return;
        }

        // Send data via AJAX to PHP endpoint
        fetch('/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/submit-event-booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('response-message').textContent = 'Booking successful!';
            } else {
                document.getElementById('response-message').textContent = 'Booking failed: ' + data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});

/*****************************************************************************************
* FUNCTIONALITY FOR DISPLAYING AN EXISTING BOOKING
*****************************************************************************************/
let existingRecord = false;
const retrieveBookingForm = document.querySelector('#existing-booking');
const findButton = retrieveBookingForm.querySelector('.find-button[type="submit"]');

function showExistingBookingContainer() {
    const existingBookingContainer = document.getElementById('existing-booking-container');
    if (existingBookingContainer) {
        existingBookingContainer.style.display = 'block';
        document.getElementById('event-attendance').style.display = 'none';
    }

    document.body.style.cursor = 'wait'; // Set cursor before fetch starts

    // Need to fetch event data
    fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-via-slug.php?event_slug=${eventSlug}&_wpnonce=${leanwiVars.ajax_nonce}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data.length > 0) {
                const event = data.data[0];
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
        })
        .catch(error => console.error('Error fetching event details:', error))
        .finally(() => {
            document.body.style.cursor = 'default'; // Reset cursor after fetch completes
        });
}

document.getElementById('retrieve-booking').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent default button behavior if necessary

    showExistingBookingContainer();

});

retrieveBookingForm.addEventListener('submit', function (event) {
    event.preventDefault();
    existingRecord = true;
    //contactFormContainer = document.getElementById('event-attendance');

    // Prepare form data
    const formData = new FormData(this);
    const eventDataId = document.querySelector('#hidden_event_data input[name="event_data_id"]');
    console.log('event_data_id', eventDataId);
    if (eventDataId) {
        formData.append('event_data_id', eventDataId.value);
    }
    
    // Add the nonce
    formData.append('fetch_existing_event_nonce', document.querySelector('#fetch_existing_event_nonce').value);

    // Change cursor and disable submit button to prevent multiple clicks
    document.body.style.cursor = 'wait';
    findButton.disabled = true;
    findButton.style.cursor = 'wait';
    fetch('/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/fetch-event-booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const booking = data.data[0]; // Assuming single booking

            console.log('Booking data:', data);
            // Populate form fields and display booking information
            //populateBookingFormFields(booking);
            
            //handleOccurrences(booking); // ????
        } else {
            alert(data.error || 'Booking retrieval unsuccessful.');
        }
    })
    .catch(error => {
        console.error('Error fetching booking:', error);
        alert('An error occurred while fetching the booking. Please try again.');
    })
    .finally(() => {
        document.body.style.cursor = 'default'; // Reset cursor after fetch completes
        findButton.disabled = false;
        findButton.style.cursor = 'default';
    });
});


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
        totalAttending += attending;  // Sum the total attendees
    });

    // Get the number of selected occurrences
    const occurrencesCount = getSelectedOccurrencesCount();
    totalEvents = occurrencesCount;  // Set total events to the selected occurrences count
    let finalCost = totalCost * totalEvents;

    // Set correct wording for 'person' or 'people'
    const attendeesText = totalAttending === 1 ? 'person' : 'people';
    const eventsText = totalEvents === 1 ? 'Event' : 'Events';

    // Update the total cost display
    totalCostDisplay.textContent = `Your total cost is $${finalCost.toFixed(2)} for ${totalEvents} ${eventsText} with ${totalAttending} ${attendeesText} attending.`;
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
        checkboxes.forEach(checkbox => {
            checkbox.disabled = false;
        });
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