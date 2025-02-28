/**************************************************************************************************
 * NEW SIGNUP FOR THIS EVENT
 * *************************************************************************************************/
document.getElementById('booking-choices').addEventListener('submit', function(event) {
    event.preventDefault();

    window.leanwiBookingData.existingRecord = false;

    //First, check if the register_by_date has been passed
    if(new Date(window.leanwiBookingData.globalEventData.register_by_date) < new Date() && !isEventStaff) {
        // If sold out, show the sold-out message and stop further processing
        document.getElementById('sold-out').style.display = 'block';
        document.getElementById('sold-out-text').textContent = 'Sorry, Registration for this event has passed.'
        document.getElementById('event-attendance').style.display = 'none';
    }
    else {
        // Second, check if the event is sold out
        checkEventAvailability(window.leanwiBookingData.globalEventData.post_id, window.leanwiBookingData.globalEventData.capacity)
            .then(isSoldOut => {
                if (isSoldOut && !isEventStaff) {
                    // If sold out, show the sold-out message and stop further processing
                    document.getElementById('sold-out').style.display = 'block';
                    document.getElementById('event-attendance').style.display = 'none';
                    return Promise.reject('Event is sold out'); // Stop execution
                } else {
                    createFormFields(window.leanwiBookingData.globalEventData);
                    document.getElementById('event-attendance').style.display = 'block';
                }
            })
            .then(() => {
                document.body.style.cursor = 'wait'; // Set cursor before fetch starts
                // Fetch event occurrences
                return fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-occurrences.php?post_id=${window.leanwiBookingData.globalEventData.post_id}&_wpnonce=${leanwiVars.ajax_nonce}`) 
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
                            if (window.leanwiBookingData.globalEventData.capacity > 0 && spotsAvailable < 1) {
                                occurrenceElement.classList.add('no-spots');
                            }

                            occurrenceElement.innerHTML = `
                                <input type="checkbox" class="occurrence-checkbox" value="${occurrence.occurrence_id}">
                                <div class="occurrence-info">
                                    ${formattedStartDate} to ${formattedEndDate}
                                </div> 
                                <div class="participants">
                                    (${window.leanwiBookingData.globalEventData.capacity > 0 ? spotsAvailable + ' Spots Available' : 'Unlimited Spots Available'})
                                </div>
                            `;

                            occurrencesContainer.appendChild(occurrenceElement);
                        });

                        // Apply participation rule logic
                        applyParticipationRule(window.leanwiBookingData.globalEventData.participation_rule);
                    })
                    .catch(error => {
                        console.error('Error fetching occurrences:', error);
                    });
            })
            .then(() => {
                // Fetch event costs
                return fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-costs.php?event_data_id=${window.leanwiBookingData.globalEventData.event_data_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
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

                                // Create the main content of each cost item
                                let costContent = `
                                    <div class="cost-details">
                                        <div class="cost-name" data-cost-id="${cost.cost_id}">${cost.cost_name}</div>
                                        <div class="cost-amount">$${cost.cost_amount}</div>
                                        <label for="attending-${cost.cost_id}">Number Attending:</label>
                                        <input type="number" id="attending-${cost.cost_id}" name="attending-${cost.cost_id}" min="0" value="0" class="attending-input">
                                    </div>
                                `;

                                // If include_extra_info is set to 1, add a new line for extra info
                                if (cost.include_extra_info === 1) {
                                    costContent += `
                                        <div class="extra-info">
                                            <label for="extra-info-${cost.cost_id}">${cost.extra_info_label || 'Additional Info:'}</label>
                                            <textarea id="extra-info-${cost.cost_id}" name="extra-info-${cost.cost_id}" rows="3" class="extra-info-input"></textarea>
                                        </div>
                                    `;
                                }

                                costElement.innerHTML = costContent;
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
                displayDisclaimers(window.leanwiBookingData.globalEventData, window.leanwiBookingData.globalEventData);
            })
            .catch(error => console.error('Error in booking process:', error))
            .finally(() => {
                document.body.style.cursor = 'default'; // Reset cursor after fetch completes
            });

        document.getElementById('event-attendance').style.display = 'block';
        document.getElementById('existing-booking-container').style.display = 'none';

        addListeners();
    }
});

/*****************************************************************************************/
// Event Listener for Booking form save - 'Book Event' Click
/*****************************************************************************************/
function submitBooking(formData) {
    document.getElementById('response-message').textContent = 'Submitting Booking. Please wait...';

    // Check if any historic costs in `usedHistoricCosts` have attendingValue > 0
    const problematicCost = window.leanwiBookingData.usedHistoricCosts.find(cost => {
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

    if(window.leanwiBookingData.existingRecord){
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

        // Find the extra-info textarea if it exists
        const extraInfoInput = item.querySelector('.extra-info-input');
        const extraInfo = extraInfoInput ? extraInfoInput.value.trim() : '';

        costs.push({ 
            cost_id: costId, 
            number_of_participants: numberOfParticipants, 
            extra_info: extraInfo 
        });
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

    // Execute reCAPTCHA if enabled
    if (eventSettings.enableRecaptcha) {
        grecaptcha.execute(eventSettings.recaptchaSiteKey, { action: 'submit' })
        .then(function(token) {
            // Append the reCAPTCHA token to the form
            formData.append('g-recaptcha-response', token);
        })
        .catch(function (error) {
            console.error('reCAPTCHA error:', error);
        });
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