/*****************************************************************************************/
// Event Listener for WaitList form save - 'Add to Wait List' Click
/*****************************************************************************************/
function submitWaitList(formData) {

    document.getElementById('response-message').textContent = 'Adding to Wait List. Please wait...';

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
    formData.append('new_booking_reference', generateWaitListReference()); // Pass a generated booking reference
    
    // Append the required fields from the Event Hidden Fields DIV
    const hiddenDiv = document.getElementById('hidden_event_data');
    if (hiddenDiv) {
        const fieldNames = [
            'event_data_id',
            'event_name',
            'event_url',
            'event_admin_email',
            'extra_email_text',
            'extra_event_url',
            'include_extra_event_url_in_email'
        ];

        fieldNames.forEach(field => {
            const input = hiddenDiv.querySelector(`input[name="${field}"]`);
            if (input) {
                formData.append(field, input.value);
            }
        });
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