/*****************************************************************************************
* FUNCTIONALITY FOR DISPLAYING AN EXISTING BOOKING
*****************************************************************************************/
const existingBookingForm = document.querySelector('#existing-booking');
const findButton = existingBookingForm.querySelector('.find-button[type="submit"]');
const deleteButton = existingBookingForm.querySelector('.find-button[type="button"]');

// Setting up the page for showing an existing booking
function showExistingBookingContainer() {
    const existingBookingContainer = document.getElementById('existing-booking-container');
    if (existingBookingContainer) {
        existingBookingContainer.style.display = 'block';
        document.getElementById('event-attendance').style.display = 'none';
        if(window.leanwiBookingData.waitListBooking) {
            findButton.textContent = 'Retrieve Wait List Booking';
            deleteButton.textContent = 'Delete Wait List Booking';
        }
    }

    createFormFields(window.leanwiBookingData.globalEventData);
}

document.getElementById('retrieve-booking').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent default button behavior if necessary

    showExistingBookingContainer();

});

// Retrieve Booking click to fetch the existing booking (or waitlist booking) and display
existingBookingForm.addEventListener('submit', function (event) {
    event.preventDefault();
    window.leanwiBookingData.existingRecord = true;

    // Prepare form data
    const formData = new FormData(this);
    const bookingRef = formData.get('booking_ref'); 
    window.leanwiBookingData.waitListBooking = bookingRef?.startsWith("WL-") || false;

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
    const url = window.leanwiBookingData.waitListBooking
        ? '/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/fetch-waitlist-booking.php'
        : '/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/fetch-event-booking.php';

    console.log(url, formData);
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
            displayDisclaimers(event_booking, window.leanwiBookingData.globalEventData);
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

function populateBookingFormFields(booking) {
    // Check if booking data exists
    if (booking) {
        // Populate the form fields with booking details
        document.getElementById('name').value = booking.name || '';
        document.getElementById('email').value = booking.email || '';
        document.getElementById('phone').value = booking.phone || '';
        document.getElementById('special_notes').value = booking.special_notes || '';
        document.getElementById('physical_address').value = booking.physical_address || '';
        document.getElementById('zipcode').value = booking.zipcode || '';
        document.getElementById('booking_reference').value = booking.booking_reference || '';
        document.getElementById('virtual_attendance').checked = booking.attending_virtually == 1;
    }
}

function displayCosts(booking, costs) { // `costs` contains previously booked costs with `cost_id` and `number_of_participants`
    let costsContainer = document.querySelector('#costs-container');
    costsContainer.innerHTML = ''; // Clear any previous content

    // Fetch event costs
    fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-costs.php?event_data_id=${window.leanwiBookingData.globalEventData.event_data_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
    .then(response => response.json())
    .then(data => {
        let costsContainer = document.querySelector('#costs-container');
        costsContainer.innerHTML = ''; // Clear any previous content

        const bookedCosts = costs.reduce((map, cost) => {
            map[cost.cost_id] = { 
                number_of_participants: cost.number_of_participants, 
                extra_info: cost.extra_info || "" 
            };
            return map;
        }, {});

        // Loop through each cost and create the display elements
        data.event_costs.forEach(cost => {
            if (cost.historic === 0 || (cost.historic === 1 && bookedCosts[cost.cost_id] > 0)) { // Costs are not historic or are historic but we have attendance in the booking for that cost
                // Create a container for each cost item
                let costElement = document.createElement('div');
                costElement.classList.add('cost-item'); // Add a class for styling

                // Determine the initial value for "Number Attending"
                let attendingValue = bookedCosts[cost.cost_id]?.number_of_participants || 0;
                let extraInfoValue = bookedCosts[cost.cost_id]?.extra_info || "";

                // Create the content of each cost item
                let costContent = `
                    <div class="cost-details">
                        <div class="cost-name" data-cost-id="${cost.cost_id}">${cost.cost_name}</div>
                        <div class="cost-amount">$${cost.cost_amount}</div>
                        <label for="attending-${cost.cost_id}">Number Attending:</label>
                        <input type="number" id="attending-${cost.cost_id}" name="attending-${cost.cost_id}" min="0" value="${attendingValue}" class="attending-input">
                    </div>
                `;

                // If include_extra_info is set to 1, add a new line for extra info
                if (cost.include_extra_info === 1) {
                    costContent += `
                        <div class="extra-info">
                            <label for="extra-info-${cost.cost_id}">${cost.extra_info_label || 'Additional Info:'}</label>
                            <textarea id="extra-info-${cost.cost_id}" name="extra-info-${cost.cost_id}" rows="3" class="extra-info-input">${extraInfoValue}</textarea>
                        </div>
                    `;
                }

                if(cost.historic === 1 && attendingValue > 0){
                    costContent += `<div class="user-message">
                                                (historic cost - please remove from attendance)
                                            </div>
                                            `;
                    //Save cost information to a global array to be checked when save main form.
                    window.leanwiBookingData.usedHistoricCosts.push(cost);

                }

                costElement.innerHTML = costContent;

                //Disable the input box if this is an historic booking
                const inputBox = costElement.querySelector(`#attending-${cost.cost_id}`);
                if (inputBox) {
                    inputBox.disabled = booking.historic === 1;
                }

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

// Function to get selected occurrences count
function getSelectedOccurrencesCount() {
    return document.querySelectorAll('.occurrence-checkbox:checked').length;
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
    if(booking.historic == 0 || window.leanwiBookingData.waitListBooking) { //Only display future occurrences if booking is not historic or is in a Wait List
        // Fetch event occurrences and display possible future occurrences
        console.log("Post Id: ", window.leanwiBookingData.globalEventData.post_id);
        fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-occurrences.php?post_id=${window.leanwiBookingData.globalEventData.post_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
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
                    let spotsAvailable = window.leanwiBookingData.globalEventData.capacity - occurrence.total_participants;

                    // Check if this occurrence is in futureOccurrences
                    let isChecked = futureOccurrences.some(futureOccurrence => futureOccurrence.occurrence_id === occurrence.occurrence_id) ? 'checked' : '';
                    let checkboxDisabled = window.leanwiBookingData.globalEventData.capacity > 0 && spotsAvailable === 0  && isChecked === '' ? 'disabled' : '';
                    
                    // Create a display element for each future occurrence
                    let occurrenceElement = document.createElement('div');
                    occurrenceElement.classList.add('occurrence-button');

                    occurrenceElement.innerHTML = `
                        <input type="checkbox" class="occurrence-checkbox" value="${occurrence.occurrence_id}" ${checkboxDisabled} ${isChecked}>
                        <div class="occurrence-info">
                            ${formattedStartDate} to ${formattedEndDate}
                        </div>
                    `;

                    if (window.leanwiBookingData.globalEventData.capacity > 0) {
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