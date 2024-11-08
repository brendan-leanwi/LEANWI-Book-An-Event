document.addEventListener('DOMContentLoaded', function () {
    let eventSlug;

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

    document.body.style.cursor = 'wait'; // Set cursor before fetch starts

    // Fetch event data
    fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-via-slug.php?event_slug=${eventSlug}`)
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
                            // Show RSVP form, fetch disclaimers, and continue
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

                            const disclaimersDiv = document.getElementById('event-disclaimers');
                            // Fetch disclaimers for this event
                            return fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-disclaimers.php?event_data_id=${event.event_data_id}`)
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
                        }
                    })
                    .then(() => {
                        // Fetch event occurrences
                        return fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-occurrences.php?post_id=${event.post_id}`)
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
                                    let checkboxDisabled = spotsAvailable === 0 ? 'disabled' : '';

                                    occurrenceElement.innerHTML = `
                                        <input type="checkbox" class="occurrence-checkbox" value="${occurrence.occurrence_id}" ${checkboxDisabled}>
                                        <div class="occurrence-info">
                                            ${formattedStartDate} to ${formattedEndDate}
                                        </div>
                                        <div class="participants">
                                            (${spotsAvailable} Spots Available)
                                        </div>
                                    `;

                                    occurrencesContainer.appendChild(occurrenceElement);
                                });
                            })
                            .catch(error => {
                                console.error('Error fetching occurrences:', error);
                            });
                    });
            }
        })
        .catch(error => console.error('Error fetching event details:', error))
        .finally(() => {
            document.body.style.cursor = 'default'; // Reset cursor after fetch completes
        });
});

// Function to check event availability based on capacity
function checkEventAvailability(postId, capacity) {
    return fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/check-event-availability.php?post_id=${postId}&capacity=${capacity}`)
        .then(response => response.json())
        .then(data => data.isSoldOut)
        .catch(error => {
            console.error('Error fetching availability status:', error);
            return true; // Assume sold out if there's an error
        });
}
