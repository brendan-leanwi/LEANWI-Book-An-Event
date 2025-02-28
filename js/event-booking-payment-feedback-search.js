document.addEventListener("DOMContentLoaded", function () {
    console.log("event-booking-payment-feedback-search.js loaded successfully!");

    // Add click event listener for "Mark Paid" and "Mark as Unpaid" links
    document.addEventListener("click", function(event) {
        if (event.target.closest(".toggle-paid-link")) { 
            event.preventDefault();
            
            const link = event.target.closest(".toggle-paid-link");
            const bookingId = link.getAttribute("data-booking-id");
            const newStatus = link.getAttribute("data-new-status");
            const nonce = link.getAttribute("data-nonce");
    
            document.body.style.cursor = 'wait';
    
            fetch('/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/staff-mark-payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ booking_id: bookingId, new_status: newStatus, nonce: String(nonce) }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Updating payment status was successful!');
                    location.reload();
                } else {
                    alert('Error updating payment status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                document.body.style.cursor = 'default';
            });
        }
    });
    
    // Add click event listener for sending the feedback request links
    document.querySelectorAll('.send-feedback-request-link').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent navigation
            const bookingId = this.getAttribute('data-booking-id');
            const nonce = this.getAttribute('data-nonce'); // Get the nonce

            console.log("staff-send-feedback-request-email bookingId:", bookingId, "nonce:", nonce)
            document.body.style.cursor = 'wait';

            // Make an AJAX request to the PHP file
            fetch('/wp-content/plugins/LEANWI-Book-An-Event/php/frontend/staff-send-feedback-request-email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    booking_id: bookingId,
                    nonce: String(nonce) // Include the nonce
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the page
                    alert('Sending the feedback request email was successful!');
                    location.reload();
                } else {
                    alert('Error sending feedback request email: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                document.body.style.cursor = 'default';
            });
        });
    });
});