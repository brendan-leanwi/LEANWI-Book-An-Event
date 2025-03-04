// Delete a booking functionality
deleteButton.addEventListener('click', function (event) {
    event.preventDefault();
    window.leanwiBookingData.existingRecord = true;
    // Prepare form data
    const formData = new FormData(existingBookingForm);
    const eventDataId = document.querySelector('#hidden_event_data input[name="event_data_id"]');
    if (eventDataId) {
        formData.append('event_data_id', eventDataId.value);
    }
    
    // Add the nonce
    formData.append('delete_existing_event_nonce', document.querySelector('#delete_existing_event_nonce').value);

    if (confirm(`Are you sure you want to delete the booking?`)) {
        // Prompt for cancellation reason
        const cancellationReason = prompt("Please provide a reason for the cancellation (optional):");
        formData.append('cancellation_reason', cancellationReason);

        // Change cursor and disable submit button to prevent multiple clicks
        document.body.style.cursor = 'wait';
        deleteButton.disabled = true;
        deleteButton.style.cursor = 'wait';

        // Append the required fields from the Event Hidden Fields DIV
        const hiddenDiv = document.getElementById('hidden_event_data');
        if (hiddenDiv) {
            const fieldNames = [
                'event_data_id',
                'event_name',
                'event_admin_email'
            ];

            fieldNames.forEach(field => {
                const input = hiddenDiv.querySelector(`input[name="${field}"]`);
                if (input) {
                    formData.append(field, input.value);
                }
            });
        }

        formData.append('is_event_staff', Boolean(isEventStaff));

        // Determine the correct URL based on whether it's a waitlist booking
        const url = window.leanwiBookingData.waitListBooking
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
                alert(window.leanwiBookingData.waitListBooking ? 'Wait List booking deleted successfully!' : 'Booking deleted successfully!');
                // Optionally hide the form or refresh the page
                existingBookingForm.reset();
                document.querySelector('#existing-booking-container').style.display = 'none';

                // Refresh the page without parameters
                location.href = location.pathname;
                
            } else {
                alert(data.message || (window.leanwiBookingData.waitListBooking ? 'Wait List booking delete unsuccessful' : 'Booking delete unsuccessful.'));
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