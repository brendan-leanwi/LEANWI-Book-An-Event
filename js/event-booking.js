let totalCost = 0;
let totalCostDisplay;
let eventSlug;
let globalEventData;
let usedHistoricCosts = [];
let hasFutureOccurrences = true;
let occurrencesAdminPage = false;
let waitListAdminPage = false;
let waitListBooking = false;

document.addEventListener('DOMContentLoaded', function () {

    // Let's show in the cosole log whether the user is staff or not
    console.log("Is Event Staff:", isEventStaff);

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
                globalEventData = data.data[0];

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
        waitListBooking = bookingRef.startsWith("WL#");
        document.getElementById('booking_ref').value = bookingRef;
        document.getElementById('existing_booking_heading').textContent = "Your Booking Reference has been Entered:";
        document.getElementById('event_attendance_heading').textContent = "Your details for attending this event";
        document.getElementById('booking-choices-container').style.display = 'none';

        // Call the retrieve booking function to display the container
        showExistingBookingContainer();
    }

});

document.getElementById('show-occurrences').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent default button behavior if necessary

    occurrencesAdminPage = !occurrencesAdminPage;
    if(occurrencesAdminPage){
        document.getElementById('admin-occurrences-heading').style.display = 'block';
        document.getElementById('admin-occurrences-container').style.display = 'block';
        document.getElementById('show-occurrences').textContent = 'Do Not Show Confirmed Bookings'
        getAdminOccurrences();
    }
    else {
        document.getElementById('admin-occurrences-heading').style.display = 'none';
        document.getElementById('admin-occurrences-container').style.display = 'none';
        document.getElementById('show-occurrences').textContent = 'Show All Confirmed Bookings by Occurrence'
    }

});

document.getElementById('show-waitlist').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent default button behavior if necessary

    waitListAdminPage = !waitListAdminPage;
    if(waitListAdminPage){
        document.getElementById('admin-waitlist-heading').style.display = 'block';
        document.getElementById('admin-waitlist-container').style.display = 'block';
        document.getElementById('show-waitlist').textContent = 'Do Not Show Wait List Bookings'
        getWaitLists();
    }
    else {
        document.getElementById('admin-waitlist-heading').style.display = 'none';
        document.getElementById('admin-waitlist-container').style.display = 'none';
        document.getElementById('show-waitlist').textContent = 'Show All Wait List Bookings by Occurrence'
    }

});
/***************************************************************************************** */
//Name search click functionality to display event bookings and waitlists based on an entered name
/***************************************************************************************** */
document.getElementById("staffSearchForm").addEventListener("submit", function (event) {
    event.preventDefault(); // Prevent page reload

    const searchResults = document.getElementById("searchResults");
    const searchInput = document.getElementById("nameSearchInput").value.trim();
    document.body.style.cursor = 'wait'; // Set cursor before fetch starts

    // Construct URLs for both API requests using globalEventData
    const participantsUrl = `/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-participants-by-name.php?post_id=${globalEventData.post_id}&search_term=${encodeURIComponent(searchInput)}&_wpnonce=${leanwiVars.ajax_nonce}`;
    const waitlistUrl = `/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-waitlist-participants-by-name.php?post_id=${globalEventData.post_id}&search_term=${encodeURIComponent(searchInput)}&_wpnonce=${leanwiVars.ajax_nonce}`;

    // Fetch both participant lists in parallel
    Promise.all([
        fetch(participantsUrl).then(res => res.json()),
        fetch(waitlistUrl).then(res => res.json())
    ])
    .then(([participantsData, waitlistData]) => {
        let outputHtml = "";

        // Display event participants if available
        if (participantsData.bookings.length > 0) {
            outputHtml += `<h3>Confirmed Participants</h3>`;
            outputHtml += generateResultsTable(participantsData.bookings);
        } else {
            outputHtml += `<h3>Confirmed Participants</h3><p>No results found.</p>`;
        }

        // Display waitlist participants if available
        if (waitlistData.bookings.length > 0) {
            outputHtml += `<h3>Waitlist Participants</h3>`;
            outputHtml += generateResultsTable(waitlistData.bookings);
        } else {
            outputHtml += `<h3>Waitlist Participants</h3><p>No results found.</p>`;
        }

        searchResults.innerHTML = outputHtml;
    })
    .catch(error => {
        console.error("Error fetching data:", error);
        searchResults.innerHTML = "<p style='color: red;'>Error retrieving data.</p>";
    })
    .finally(() => {
        document.body.style.cursor = 'default'; // Reset cursor after fetch completes
    });
});

// Function to generate HTML table from data
function generateResultsTable(bookings) {
    let tableHtml = `
        <table style="width:100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px;">Name</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Email</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Phone</th>
                    <th style="border: 1px solid #ddd; padding: 8px;"># Participants</th>
                    <th style="border: 1px solid #ddd; padding: 8px;"># Occurrences</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Action</th>
                </tr>
            </thead>
            <tbody>`;

    bookings.forEach(booking => {
        const bookingUrl = `${baseUrl}?booking_ref=${encodeURIComponent(booking.booking_reference)}`;

        tableHtml += `
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;">${booking.name}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">${booking.email}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">${booking.phone}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${booking.total_participants}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${booking.occurrence_count}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                    <a href="${bookingUrl}" style="color: blue; text-decoration: underline;" target="_blank">View</a>
                </td>
            </tr>`;
    });

    tableHtml += `</tbody></table>`;
    return tableHtml;
}

/***************************************************************************************** */
//Display the occurrences so that the admin is able to view them
/***************************************************************************************** */
function getAdminOccurrences() {
    document.body.style.cursor = 'wait'; // Set cursor before fetch starts

    fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-occurrences.php?post_id=${globalEventData.post_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
        .then(response => response.json())
        .then(data => {
            let occurrencesContainer = document.querySelector('#admin-occurrences-container');
            occurrencesContainer.innerHTML = ''; // Clear any previous content

            data.event_occurrences.forEach(occurrence => {
                let startDate = new Date(occurrence.start_date);
                let endDate = new Date(occurrence.end_date);

                // Format the date and time
                let formattedStartDate = `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}, ${startDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
                let formattedEndDate = `${endDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;

                // Calculate spots available
                let spotsAvailable = globalEventData.capacity - occurrence.total_participants;

                // Create a display element for each occurrence
                let occurrenceElement = document.createElement('div');
                occurrenceElement.classList.add('occurrence-button');
                occurrenceElement.dataset.occurrenceId = occurrence.occurrence_id;

                occurrenceElement.innerHTML = `
                    <div class="occurrence-info">
                        ${formattedStartDate} to ${formattedEndDate}
                    </div>
                    <div class="participants">
                        (${globalEventData.capacity > 0 ? `${spotsAvailable} Spots Available of ${globalEventData.capacity}` : 'Unlimited Spots Available'})
                    </div>
                `;

                // Create a container for the participant list (hidden initially)
                let participantListContainer = document.createElement('div');
                participantListContainer.classList.add('participant-list');
                participantListContainer.style.display = 'none'; // Hide by default

                // Add click event to toggle participant list
                occurrenceElement.addEventListener('click', function () {
                    let occurrenceId = this.dataset.occurrenceId;

                    // Hide all other participant list containers
                    document.querySelectorAll('.participant-list').forEach(container => {
                        container.style.display = 'none';
                    });

                    // Show only the clicked occurrence's participant list
                    if (participantListContainer.style.display === 'none') {
                        fetchParticipants(occurrenceId, participantListContainer);
                        participantListContainer.style.display = 'block';
                    } else {
                        participantListContainer.style.display = 'none';
                    }
                });

                // Append the button to the container
                occurrencesContainer.appendChild(occurrenceElement);
                // Append the participant list separately below the button
                occurrencesContainer.appendChild(participantListContainer);
            });
        })
        .catch(error => {
            console.error('Error fetching occurrences:', error);
        })
        .finally(() => {
            document.body.style.cursor = 'default'; // Reset cursor after fetch completes
        });
}

/***************************************************************************************** */
//Display the Wait Lists so that the admin is able to view them
/***************************************************************************************** */
function getWaitLists(){
    document.body.style.cursor = 'wait'; // Set cursor before fetch starts

    // Fetch event data
    fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-waitlists.php?post_id=${globalEventData.post_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
        .then(response => response.json())
        .then(data => {
            let occurrencesContainer = document.querySelector('#admin-waitlist-container');
            occurrencesContainer.innerHTML = ''; // Clear any previous content

            data.event_occurrences.forEach(occurrence => {
                let startDate = new Date(occurrence.start_date);
                let endDate = new Date(occurrence.end_date);

                // Format the date and time
                let formattedStartDate = `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}, ${startDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
                let formattedEndDate = `${endDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;

                // Create a display element for each occurrence
                let occurrenceElement = document.createElement('div');
                occurrenceElement.classList.add('occurrence-button');
                occurrenceElement.dataset.occurrenceId = occurrence.occurrence_id;

                occurrenceElement.innerHTML = `
                    <div class="occurrence-info">
                        ${formattedStartDate} to ${formattedEndDate}
                    </div>
                    <div class="participants">
                        ${occurrence.total_participants} are waiting
                    </div>
                `;

                // Create a container for the participant list (hidden initially)
                let participantListContainer = document.createElement('div');
                participantListContainer.classList.add('participant-list');
                participantListContainer.style.display = 'none'; // Hide by default

                // Add click event to toggle participant list
                occurrenceElement.addEventListener('click', function () {
                    let occurrenceId = this.dataset.occurrenceId;

                    // Hide all other participant list containers
                    document.querySelectorAll('.participant-list').forEach(container => {
                        container.style.display = 'none';
                    });

                    // Show only the clicked occurrence's participant list
                    if (participantListContainer.style.display === 'none') {
                        fetchWaitListParticipants(occurrenceId, participantListContainer);
                        participantListContainer.style.display = 'block';
                    } else {
                        participantListContainer.style.display = 'none';
                    }
                });

                // Append the button to the container
                occurrencesContainer.appendChild(occurrenceElement);
                // Append the participant list separately below the button
                occurrencesContainer.appendChild(participantListContainer);
            });
        })
        .catch(error => {
            console.error('Error fetching occurrences:', error);
        })
        .finally(() => {
            document.body.style.cursor = 'default'; // Reset cursor after fetch completes
        });
}

// Function to fetch and display participants for a specific occurrence
function fetchParticipants(occurrenceId, container) {
    container.innerHTML = '<p>Loading participants...</p>'; // Show loading message

    fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-occurrence-participants.php?occurrence_id=${occurrenceId}&_wpnonce=${leanwiVars.ajax_nonce}`)
        .then(response => response.json())
        .then(data => {
            container.innerHTML = ''; // Clear loading message

            if (data.participants.length > 0) {
                let participantTable = document.createElement('table');
                participantTable.classList.add('participant-table'); // Add a class for styling
            
                // Create the table header
                let tableHeader = document.createElement('thead');
                tableHeader.innerHTML = `
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Participants</th>
                        <th>Action</th>
                    </tr>
                `;
                participantTable.appendChild(tableHeader);
            
                // Create the table body
                let tableBody = document.createElement('tbody');
            
                data.participants.forEach(participant => {
                    let row = document.createElement('tr');
            
                    // Construct the base URL for the booking reference link
                    let url = new URL(window.location.href);
                    let baseUrl = url.origin + url.pathname;
                    let bookingLink = `${baseUrl}?booking_ref=${encodeURIComponent(participant.booking_reference)}`;
            
                    row.innerHTML = `
                        <td>${participant.name}</td>
                        <td><a href="mailto:${participant.email}">${participant.email}</a></td>
                        <td>${participant.phone}</td>
                        <td>${participant.total_participants}</td>
                        <td><a href="${bookingLink}" target="_blank">Go to Booking</a></td>
                    `;
            
                    tableBody.appendChild(row);
                });
            
                participantTable.appendChild(tableBody);
                container.appendChild(participantTable);
            }
             else {
                container.innerHTML = '<p>No participants are registered for this occurrence of the event.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching participants:', error);
            container.innerHTML = '<p>Error loading participants.</p>';
        });
}

// Function to fetch and display participants for a specific occurrence
function fetchWaitListParticipants(occurrenceId, container) {
    container.innerHTML = '<p>Loading participants...</p>'; // Show loading message

    fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-waitlist-participants.php?occurrence_id=${occurrenceId}&_wpnonce=${leanwiVars.ajax_nonce}`)
        .then(response => response.json())
        .then(data => {
            container.innerHTML = ''; // Clear loading message

            if (data.participants.length > 0) {
                let participantTable = document.createElement('table');
                participantTable.classList.add('participant-table'); // Add a class for styling
            
                // Create the table header
                let tableHeader = document.createElement('thead');
                tableHeader.innerHTML = `
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Participants</th>
                        <th>Action</th>
                    </tr>
                `;
                participantTable.appendChild(tableHeader);
            
                // Create the table body
                let tableBody = document.createElement('tbody');
            
                data.participants.forEach(participant => {
                    let row = document.createElement('tr');
            
                    // Construct the base URL for the booking reference link
                    let url = new URL(window.location.href);
                    let baseUrl = url.origin + url.pathname;
                    let bookingLink = `${baseUrl}?booking_ref=${encodeURIComponent(participant.booking_reference)}`;
            
                    row.innerHTML = `
                        <td>${participant.name}</td>
                        <td><a href="mailto:${participant.email}">${participant.email}</a></td>
                        <td>${participant.phone}</td>
                        <td>${participant.total_participants}</td>
                        <td><a href="${bookingLink}" target="_blank">Go to Listing</a></td>
                    `;
            
                    tableBody.appendChild(row);
                });
            
                participantTable.appendChild(tableBody);
                container.appendChild(participantTable);
            }
             else {
                container.innerHTML = '<p>No participants are registered for this occurrence of the event.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching participants:', error);
            container.innerHTML = '<p>Error loading participants.</p>';
        });
}
/**************************************************************************************************
 * NEW SIGNUP FOR THIS EVENT
 * *************************************************************************************************/
document.getElementById('booking-choices').addEventListener('submit', function(event) {
    event.preventDefault();

    existingRecord = false;
    document.body.style.cursor = 'wait'; // Set cursor before fetch starts

    // First, check if the event is sold out
    checkEventAvailability(globalEventData.post_id, globalEventData.capacity)
        .then(isSoldOut => {
            if (isSoldOut) {
                // If sold out, show the sold-out message and stop further processing
                document.getElementById('sold-out').style.display = 'block';
                document.getElementById('event-attendance').style.display = 'none';
            } else {
                createFormFields(globalEventData);
                document.getElementById('event-attendance').style.display = 'block';
            }
        })
        .then(() => {
            // Fetch event occurrences
            return fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-occurrences.php?post_id=${globalEventData.post_id}&_wpnonce=${leanwiVars.ajax_nonce}`) 
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

                        // Calculate spots available. If it's overbooked (negative), show 0 spots available to general users
                        let spotsAvailable = globalEventData.capacity - occurrence.total_participants;
                        if (!isEventStaff && spotsAvailable < 0) {
                            spotsAvailable = 0;
                        }

                        // Create a display element for each occurrence
                        let occurrenceElement = document.createElement('div');
                        occurrenceElement.classList.add('occurrence-button');

                        // Add a class if no spots are available
                        if (globalEventData.capacity > 0 && spotsAvailable < 1) {
                            occurrenceElement.classList.add('no-spots');
                        }

                        occurrenceElement.innerHTML = `
                            <input type="checkbox" class="occurrence-checkbox" value="${occurrence.occurrence_id}">
                            <div class="occurrence-info">
                                ${formattedStartDate} to ${formattedEndDate}
                            </div> 
                            <div class="participants">
                                (${globalEventData.capacity > 0 ? spotsAvailable + ' Spots Available' : 'Unlimited Spots Available'})
                            </div>
                        `;

                        occurrencesContainer.appendChild(occurrenceElement);
                    });

                    // Apply participation rule logic
                    applyParticipationRule(globalEventData.participation_rule);
                })
                .catch(error => {
                    console.error('Error fetching occurrences:', error);
                });
        })
        .then(() => {
            // Fetch event costs
            return fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-costs.php?event_data_id=${globalEventData.event_data_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
                .then(response => response.json())
                .then(data => {
                    let costsContainer = document.querySelector('#costs-container');
                    costsContainer.innerHTML = ''; // Clear any previous content

                    // Loop through each cost and create the display elements
                    data.event_costs.forEach(cost => {
                        if (cost.historic == 0) { // Only show costs that are not historic
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
                        }
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
            displayDisclaimers(globalEventData, globalEventData);
        })
        .catch(error => console.error('Error in booking process:', error))
        .finally(() => {
            document.body.style.cursor = 'default'; // Reset cursor after fetch completes
        });

    document.getElementById('event-attendance').style.display = 'block';
    document.getElementById('existing-booking-container').style.display = 'none';

    addListeners();
});

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

        // Get the form element
        let form = event.target;

        // Identify which button was clicked
        let action = event.submitter.value; 

        // Collect form data
        let formData = new FormData(this); //(form)??

        if (action === "book") {
            document.getElementById('response-message').textContent = 'Submitting Booking. Please wait...';
            submitBooking(formData);
        } else if (action === "waitlist") {
            document.getElementById('response-message').textContent = 'Adding to Wait List. Please wait...';
            submitWaitList(formData);
        }
    });
};

/*****************************************************************************************/
// Event Listener for Booking form save - 'Book Event' Click
/*****************************************************************************************/
function submitBooking(formData) {
    document.getElementById('response-message').textContent = 'Submitting Booking. Please wait...';

    // Check if any historic costs in `usedHistoricCosts` have attendingValue > 0
    const problematicCost = usedHistoricCosts.find(cost => {
        // Get the current attending input value from the DOM for this cost
        const inputElement = document.querySelector(`.attending-input[name="attending-${cost.cost_id}"]`);
        const currentAttendingValue = parseInt(inputElement?.value, 10) || 0;
        return currentAttendingValue > 0;
    });

    if (problematicCost) {
        const costName = problematicCost.cost_name;
        alert(`You cannot save because the historic cost "${costName}" is still being used.`);
        e.preventDefault(); // Stop the form submission
        return false; // Prevent further processing
    }
    
    formData.append('submit_event_nonce', document.querySelector('#submit_event_nonce').value);

    if(existingRecord){
        formData.append('existing_record', 'true');
        formData.append('existing_booking_reference', document.getElementById('booking_reference').value); // Pass the booking ref from the hidden element
    } else {
        formData.append('existing_record', 'false');
        formData.append('existing_booking_reference', '');
    }
    formData.append('new_booking_reference', generateBookingReference()); // Pass a generated booking reference
    
    // Retrieve the event_data_id from the hidden input field in hidden_event_data div
    const eventDataId = document.querySelector('#hidden_event_data input[name="event_data_id"]');
    if (eventDataId) {
        formData.append('event_data_id', eventDataId.value);
    }

    // Retrieve the event_data_id from the hidden input field in hidden_event_data div
    const eventCapacity = document.querySelector('#hidden_event_data input[name="capacity"]');
    if (eventDataId) {
        formData.append('capacity', eventCapacity.value);
    }
    
    // Collect the cost-related data from the form
    let totalParticipants = 0
    const costs = [];
    document.querySelectorAll('.cost-item').forEach(function(item) {
        const costId = item.querySelector('.cost-name').dataset.costId; // Retrieve cost_id from data attribute
        const numberOfParticipants = parseInt(item.querySelector('.attending-input').value, 10) || 0;
        totalParticipants += numberOfParticipants;
        costs.push({ cost_id: costId, number_of_participants: numberOfParticipants });
    });

    formData.append('costs', JSON.stringify(costs));  // Append the costs data as a JSON string

    // Collect occurrences data and check available spots
    const occurrences = [];

    document.querySelectorAll('.occurrence-checkbox:checked').forEach(function(checkbox) {
        const occurrenceElement = checkbox.closest('.occurrence-button');
        const occurrenceId = checkbox.value;
        occurrences.push({ occurrence_id: occurrenceId, number_of_participants: totalParticipants });
    });

    formData.append('occurrences', JSON.stringify(occurrences));  // Append the occurrences data as a JSON string
    
    formData.append('total_participants', totalParticipants);

    let capacityOverride = document.querySelector('#capacity_override').checked ? '1' : '0';
    formData.append('capacity_override', capacityOverride);

    console.log('formData:', Object.fromEntries(formData));
    
    // Validation: Check if at least one occurrence is selected and at least one participant
    if (occurrences.length === 0) {
        document.getElementById('response-message').textContent = 'Please select at least one occurrence.';
        return;
    }
    if (totalParticipants === 0) {
        document.getElementById('response-message').textContent = 'Please enter at least one participant.';
        return;
    }

    document.body.style.cursor = 'wait'; // Set cursor before fetch starts
    fetch('/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/submit-event-booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('response-message').textContent = data.message;
        } else {
            document.getElementById('response-message').textContent = 'Booking failed: ' + data.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    })
    .finally(() => {
        document.body.style.cursor = 'default'; // Reset cursor after fetch completes
    });
}
    

/*****************************************************************************************/
// Event Listener for WaitList form save - 'Add to Wait List' Click
/*****************************************************************************************/
function submitWaitList(formData) {

    document.getElementById('response-message').textContent = 'Adding to Wait List. Please wait...';

    // Check if any historic costs in `usedHistoricCosts` have attendingValue > 0
    const problematicCost = usedHistoricCosts.find(cost => {
        // Get the current attending input value from the DOM for this cost
        const inputElement = document.querySelector(`.attending-input[name="attending-${cost.cost_id}"]`);
        const currentAttendingValue = parseInt(inputElement?.value, 10) || 0;
        return currentAttendingValue > 0;
    });

    if (problematicCost) {
        const costName = problematicCost.cost_name;
        alert(`You cannot save because the historic cost "${costName}" is still being used.`);
        e.preventDefault(); // Stop the form submission
        return false; // Prevent further processing
    }
    
    formData.append('submit_event_nonce', document.querySelector('#submit_event_nonce').value);

    if(existingRecord){
        formData.append('existing_record', 'true');
        formData.append('existing_booking_reference', document.getElementById('booking_reference').value); // Pass the booking ref from the hidden element
    } else {
        formData.append('existing_record', 'false');
        formData.append('existing_booking_reference', '');
    }
    formData.append('new_booking_reference', generateWaitListReference()); // Pass a generated booking reference
    
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
        const numberOfParticipants = parseInt(item.querySelector('.attending-input').value, 10) || 0;
        totalParticipants += numberOfParticipants;
        costs.push({ cost_id: costId, number_of_participants: numberOfParticipants });
    });

    formData.append('costs', JSON.stringify(costs));  // Append the costs data as a JSON string

    // Collect occurrences data and check available spots
    const occurrences = [];

    document.querySelectorAll('.occurrence-checkbox:checked').forEach(function(checkbox) {
        const occurrenceElement = checkbox.closest('.occurrence-button');
        const occurrenceId = checkbox.value;
        occurrences.push({ occurrence_id: occurrenceId, number_of_participants: totalParticipants });
    });

    formData.append('occurrences', JSON.stringify(occurrences));  // Append the occurrences data as a JSON string
    
    formData.append('total_participants', totalParticipants);

    console.log('formData:', Object.fromEntries(formData));
    
    // Validation: Check if at least one occurrence is selected and at least one participant
    if (occurrences.length === 0) {
        document.getElementById('response-message').textContent = 'Please select at least one occurrence.';
        return;
    }
    if (totalParticipants === 0) {
        document.getElementById('response-message').textContent = 'Please enter at least one participant.';
        return;
    }

    document.body.style.cursor = 'wait'; // Set cursor before fetch starts
    fetch('/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/submit-waitlist-booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('response-message').textContent = data.message;
        } else {
            document.getElementById('response-message').textContent = 'Attempt to add to Wait List failed: ' + data.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    })
    .finally(() => {
        document.body.style.cursor = 'default'; // Reset cursor after fetch completes
    });
}

/*****************************************************************************************
* FUNCTIONALITY FOR DISPLAYING AN EXISTING BOOKING
*****************************************************************************************/
let existingRecord = false;
const existingBookingForm = document.querySelector('#existing-booking');
const findButton = existingBookingForm.querySelector('.find-button[type="submit"]');
const deleteButton = existingBookingForm.querySelector('.find-button[type="button"]');

// Setting up the page for showing an existing booking
function showExistingBookingContainer() {
    const existingBookingContainer = document.getElementById('existing-booking-container');
    if (existingBookingContainer) {
        existingBookingContainer.style.display = 'block';
        document.getElementById('event-attendance').style.display = 'none';
        if(waitListBooking) {
            findButton.textContent = 'Retrieve Wait List Booking';
            deleteButton.textContent = 'Delete Wait List Booking';
        }
    }

    document.body.style.cursor = 'wait'; // Set cursor before fetch starts

    createFormFields(globalEventData);
}

document.getElementById('retrieve-booking').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent default button behavior if necessary

    showExistingBookingContainer();

});

// Retrieve Booking click to fetch the existing booking (or waitlist booking) and display
existingBookingForm.addEventListener('submit', function (event) {
    event.preventDefault();
    existingRecord = true;

    // Prepare form data
    const formData = new FormData(this);
    const eventDataId = document.querySelector('#hidden_event_data input[name="event_data_id"]');
    if (eventDataId) {
        formData.append('event_data_id', eventDataId.value);
    }
    
    // Add the nonce
    formData.append('fetch_existing_event_nonce', document.querySelector('#fetch_existing_event_nonce').value);

    // Change cursor and disable submit button to prevent multiple clicks
    document.body.style.cursor = 'wait';
    findButton.disabled = true;
    findButton.style.cursor = 'wait';

    // Determine the correct URL based on whether it's a waitlist booking
    const url = waitListBooking
        ? '/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/fetch-waitlist-booking.php'
        : '/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/fetch-event-booking.php';

    fetchBooking(url, formData);
});

// Consolidated fetch function
function fetchBooking(url, formData) {
    fetch(url, {
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
            console.log('Booking data:', data);
            const event_booking = data.data[0];

            // Populate form fields and display booking information
            document.getElementById('event-attendance').style.display = 'block';
            populateBookingFormFields(event_booking);
            displayOccurrences(event_booking, data.future_occurrences, data.past_occurrences);
            displayCosts(event_booking, data.costs);
            displayDisclaimers(event_booking, globalEventData);
            addListeners();
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
}


// Delete a booking functionality
deleteButton.addEventListener('click', function (event) {
    event.preventDefault();
    existingRecord = true;
    // Prepare form data
    const formData = new FormData(existingBookingForm);
    const eventDataId = document.querySelector('#hidden_event_data input[name="event_data_id"]');
    if (eventDataId) {
        formData.append('event_data_id', eventDataId.value);
    }
    
    // Add the nonce
    formData.append('delete_existing_event_nonce', document.querySelector('#delete_existing_event_nonce').value);

    if (confirm(`Are you sure you want to delete the booking?`)) {

        // Change cursor and disable submit button to prevent multiple clicks
        document.body.style.cursor = 'wait';
        deleteButton.disabled = true;
        deleteButton.style.cursor = 'wait';

        // Determine the correct URL based on whether it's a waitlist booking
        const url = waitListBooking
            ? '/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/delete-waitlist-booking.php'
            : '/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/delete-event-booking.php';

        console.log("delete URL: ", url);
        console.log('formData:', Object.fromEntries(formData));

        fetch(url, {
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
                // Display success message
                alert(waitListBooking ? 'Wait List deleted successfully!' : 'Booking deleted successfully!');
                // Optionally hide the form or refresh the page
                existingBookingForm.reset();
                document.querySelector('#existing-booking-container').style.display = 'none';

                // Refresh the page without parameters
                location.href = location.pathname;
                
            } else {
                alert(data.message || (waitListBooking ? 'Wait List delete unsuccessful' : 'Booking delete unsuccessful.'));
            }
        })
        .catch(error => {
            console.error('Error deleting booking:', error);
            alert('An error occurred while deleting the booking. Please try again.');
        })
        .finally(() => {
            document.body.style.cursor = 'default'; // Reset cursor after delete completes
            deleteButton.disabled = false;
            deleteButton.style.cursor = 'default';
        });
    }
});

function populateBookingFormFields(booking) {
    // Check if booking data exists
    if (booking) {
        // Populate the form fields with booking details
        document.getElementById('name').value = booking.name || '';
        document.getElementById('email').value = booking.email || '';
        document.getElementById('phone').value = booking.phone || '';
        document.getElementById('booking_reference').value = booking.booking_reference || '';
    }
}

function displayCosts(booking, costs) { // `costs` contains previously booked costs with `cost_id` and `number_of_participants`
    let costsContainer = document.querySelector('#costs-container');
    costsContainer.innerHTML = ''; // Clear any previous content

    // Fetch event costs
    fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-costs.php?event_data_id=${globalEventData.event_data_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
    .then(response => response.json())
    .then(data => {
        let costsContainer = document.querySelector('#costs-container');
        costsContainer.innerHTML = ''; // Clear any previous content

        const bookedCosts = costs.reduce((map, cost) => {
            map[cost.cost_id] = cost.number_of_participants;
            return map;
        }, {});

        // Loop through each cost and create the display elements
        data.event_costs.forEach(cost => {
            if (cost.historic === 0 || (cost.historic === 1 && bookedCosts[cost.cost_id] > 0)) { // Costs are not historic or are historic but we have attendance in the booking for that cost
                // Create a container for each cost item
                let costElement = document.createElement('div');
                costElement.classList.add('cost-item'); // Add a class for styling

                // Determine the initial value for "Number Attending"
                let attendingValue = bookedCosts[cost.cost_id] || 0; // Use booked number or default to 0

                // Create the content of each cost item
                costElement.innerHTML = `
                    <div class="cost-name" data-cost-id="${cost.cost_id}">${cost.cost_name}</div>
                    <div class="cost-amount">$${cost.cost_amount}</div>
                    <label for="attending-${cost.cost_id}">Number Attending:</label>
                    <input type="number" id="attending-${cost.cost_id}" name="attending-${cost.cost_id}" min="0" value="${attendingValue}" class="attending-input">
                `;

                if(cost.historic === 1 && attendingValue > 0){
                    costElement.innerHTML += `<div class="user-message">
                                                (historic cost - please remove from attendance)
                                            </div>
                                            `;
                    //Save cost information to a global array to be checked when save main form.
                    usedHistoricCosts.push(cost);

                }
                //Disable the input box of this is an historic booking
                const inputBox = costElement.querySelector(`#attending-${cost.cost_id}`);
                inputBox.disabled = booking.historic == 1;

                // Append the cost element to the container
                costsContainer.appendChild(costElement);
            } 
        });

        // Create a div to display the total cost
        totalCostDisplay = document.createElement('div');
        totalCostDisplay.id = 'total-cost-display';
        costsContainer.appendChild(totalCostDisplay);
        
        updateTotalCost();
    })
    .catch(error => {
        console.error('Error fetching costs:', error);
    });

}

function displayOccurrences(booking, futureOccurrences, pastOccurrences) {
    let occurrencesContainer = document.querySelector('#occurrences-container');
    occurrencesContainer.innerHTML = ''; // Clear any previous content

    // Display past occurrences as checked and disabled
    pastOccurrences.forEach(occurrence => {
        let startDate = new Date(occurrence.start_date);
        let endDate = new Date(occurrence.end_date);

        // Format the date and time
        let formattedStartDate = `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}, ${startDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
        let formattedEndDate = `${endDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;

        // Create a display element for each past occurrence
        let occurrenceElement = document.createElement('div');
        occurrenceElement.classList.add('occurrence-button');

        occurrenceElement.innerHTML = `
            <input type="checkbox" class="occurrence-checkbox" value="${occurrence.occurrence_id}" checked disabled>
            <div class="occurrence-info">
                ${formattedStartDate} to ${formattedEndDate}
            </div>
            <div class="user-message">
                (No updates possible - Event passed or too close)
            </div>
        `;

        occurrencesContainer.appendChild(occurrenceElement);
    });

    hasFutureOccurrences = false;
    if(booking.historic == 0 || waitListBooking) { //Only display future occurrences if booking is not historic or is in a Wait List
        // Fetch event occurrences and display possible future occurrences
        console.log("Post Id: ", globalEventData.post_id);
        fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-occurrences.php?post_id=${globalEventData.post_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
            .then(response => response.json())
            .then(data => {
                data.event_occurrences.forEach(occurrence => {
                    hasFutureOccurrences = true;
                    console.log('hasFutureOccurrences = true');
                    let startDate = new Date(occurrence.start_date);
                    let endDate = new Date(occurrence.end_date);

                    // Format the date and time
                    let formattedStartDate = `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}, ${startDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
                    let formattedEndDate = `${endDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;

                    // Calculate spots available
                    let spotsAvailable = globalEventData.capacity - occurrence.total_participants;

                    // Check if this occurrence is in futureOccurrences
                    let isChecked = futureOccurrences.some(futureOccurrence => futureOccurrence.occurrence_id === occurrence.occurrence_id) ? 'checked' : '';
                    let checkboxDisabled = globalEventData.capacity > 0 && spotsAvailable === 0  && isChecked === '' ? 'disabled' : '';
                    
                    // Create a display element for each future occurrence
                    let occurrenceElement = document.createElement('div');
                    occurrenceElement.classList.add('occurrence-button');

                    occurrenceElement.innerHTML = `
                        <input type="checkbox" class="occurrence-checkbox" value="${occurrence.occurrence_id}" ${checkboxDisabled} ${isChecked}>
                        <div class="occurrence-info">
                            ${formattedStartDate} to ${formattedEndDate}
                        </div>
                    `;

                    if (globalEventData.capacity > 0) {
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

                adjustBookingButtons();

            })
            .catch(error => {
                console.error('Error fetching occurrences:', error);
            });
    } else {
        adjustBookingButtons();
    }

}

function displayDisclaimers(booking, event) {
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

                    // Check the checkbox if booking.historic is 1
                    if (booking.historic === 1) {
                        checkbox.checked = true;
                    }
                    
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
}

// Function to adjust the button label and state
function adjustBookingButtons() {
    const submitButton = document.querySelector('#attendance-form button[type="submit"][value="book"]');
    const waitlistButton = document.querySelector('#attendance-form button[type="submit"][value="waitlist"]');

    if (waitlistButton) {
        // Do not display the 'Add to Wait List' button if it is an existing record that is not already a waitlist booking
        if (existingRecord && !waitListBooking) {
            waitlistButton.style.display = 'none';
        } else {
            waitlistButton.style.display = 'inline-block'; // Ensure it's visible if conditions allow
            waitlistButton.textContent = existingRecord ? 'Update Wait List' : 'Add to Wait List';
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
            if(existingRecord && waitListBooking && !isEventStaff){
                submitButton.style.display = 'none';
            }
            else if(existingRecord && waitListBooking && isEventStaff){
                submitButton.style.display = 'inline-block';
                submitButton.textContent = 'Transfer Wait List to a Booking';
            }
            else {
                submitButton.textContent = existingRecord ? 'Update Event Booking' : 'Book Event';
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