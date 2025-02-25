/***************************************************************************************************
 * On click of 'Show all confirmed bookings by occurrence' Button
 ****************************************************************************************************/
document.getElementById('show-occurrences').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent default button behavior if necessary

    window.leanwiBookingData.occurrencesAdminPage = !window.leanwiBookingData.occurrencesAdminPage;
    if(window.leanwiBookingData.occurrencesAdminPage){
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

/***************************************************************************************************
 * On click of 'Show all Wait List bookings by occurrence' Button
 ****************************************************************************************************/
document.getElementById('show-waitlist').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent default button behavior if necessary

    window.leanwiBookingData.waitListAdminPage = !window.leanwiBookingData.waitListAdminPage;
    if(window.leanwiBookingData.waitListAdminPage){
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

    // Construct URLs for both API requests using window.leanwiBookingData.globalEventData
    const participantsUrl = `/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-participants-by-name.php?post_id=${window.leanwiBookingData.globalEventData.post_id}&search_term=${encodeURIComponent(searchInput)}&_wpnonce=${leanwiVars.ajax_nonce}`;
    const waitlistUrl = `/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-waitlist-participants-by-name.php?post_id=${window.leanwiBookingData.globalEventData.post_id}&search_term=${encodeURIComponent(searchInput)}&_wpnonce=${leanwiVars.ajax_nonce}`;

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
    
    let url = new URL(window.location.href);
    let baseUrl = url.origin + url.pathname;

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

    fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-occurrences.php?post_id=${window.leanwiBookingData.globalEventData.post_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
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
                let spotsAvailable = window.leanwiBookingData.globalEventData.capacity - occurrence.total_participants;

                // Create a display element for each occurrence
                let occurrenceElement = document.createElement('div');
                occurrenceElement.classList.add('occurrence-button');
                occurrenceElement.dataset.occurrenceId = occurrence.occurrence_id;

                occurrenceElement.innerHTML = `
                    <div class="occurrence-info">
                        ${formattedStartDate} to ${formattedEndDate}
                    </div>
                    <div class="participants">
                        (${window.leanwiBookingData.globalEventData.capacity > 0 ? `${spotsAvailable} Spots Available of ${window.leanwiBookingData.globalEventData.capacity}` : 'Unlimited Spots Available'})
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
    fetch(`/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/get-event-waitlists.php?post_id=${window.leanwiBookingData.globalEventData.post_id}&_wpnonce=${leanwiVars.ajax_nonce}`)
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