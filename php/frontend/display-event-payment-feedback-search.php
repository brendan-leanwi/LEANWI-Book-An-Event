<?php
/**
 * Plugin Name: Display Event Payment and Feedback Search
 * Description: A shortcode to allow the user to display event bookings and mark whether paid or a feedback form has been sent.
 * Version: 1.1
 * Author: Brendan Tuckey
 */

// Register the shortcode for displaying the payment and feedback information for events
function display_event_payment_feedback_search() {
    global $wpdb;

    // Check if the user has staff privileges
    $current_user = wp_get_current_user();
    $is_event_staff = in_array('event_staff', (array) $current_user->roles);

    // If the user is not a staff member, display a restricted access message
    if (!$is_event_staff) {
        return 'You do not have the correct permissions to access this page.';
    }

    // Fetch used events so that latest events end up at the top
    $existing_events = $wpdb->get_results("
        SELECT e.event_data_id, p.post_title AS title, p.post_name as slug_event_name
        FROM {$wpdb->prefix}leanwi_event_data e
        JOIN {$wpdb->prefix}posts p ON p.ID = e.post_id
        WHERE p.post_type = 'tribe_events'
        and e.historic = 0
        ORDER BY e.event_data_id desc
    ");

    // Handle search request
    $search_results = [];
    $event_slug = '';

    if (!empty($_POST['event_data_id'])) {
        $event_id = intval($_POST['event_data_id']);
        $search_name = isset($_POST['search_name']) ? trim($_POST['search_name']) : '';
        $unpaid_only = isset($_POST['unpaid_only']) ? 1 : 0;

        // Find the matching event to retrieve my slug_name from
        foreach ($existing_events as $event) {
            if ($event->event_data_id == $event_id) {
                $slug_name = $event->slug_event_name;
                break;
            }
        }

        // Get site URL dynamically
        $site_url = get_site_url();

        // Construct event URL
        $event_slug = esc_url("{$site_url}/event/{$slug_name}");

        // Construct SQL query dynamically
        $query = "SELECT booking_id, booking_reference, name, email, phone, total_participants, has_paid, feedback_request_sent
                  FROM {$wpdb->prefix}leanwi_event_booking
                  WHERE event_data_id = %d";
        $query_params = [$event_id];

        if (!empty($search_name)) {
            $query .= " AND name LIKE %s";
            $query_params[] = "%{$search_name}%";
        }

        if ($unpaid_only) {
            $query .= " AND has_paid = 0";
        }

        $search_results = $wpdb->get_results($wpdb->prepare($query, ...$query_params));
    }

    // Start building the output
    ob_start();
    ?>
    <H2 style="margin-bottom: 20px;">Search by Event and Name</H2>
    <form id="event-payment-feedback-form" method="post">
        <label for="event-select">Select an Event:</label>
        <select id="event-select" name="event_data_id" style="padding: 8px;">
            <option value="">-- Select an Event --</option>
            <?php foreach ($existing_events as $event): ?>
                <option value="<?php echo esc_attr($event->event_data_id); ?>" 
                    <?php selected($_POST['event_data_id'] ?? '', $event->event_data_id); ?>>
                    <?php echo esc_html($event->title); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!--<label for="search-name">Search by Name:</label> -->
        <input type="text" id="search-name" name="search_name" placeholder="Search by attendee name or leave blank" style="padding: 8px; width: 40%; margin: 10px;" value="<?php echo esc_attr($_POST['search_name'] ?? ''); ?>">

        <label for="unpaid-only">
            <input type="checkbox" id="unpaid-only" name="unpaid_only" value="1" <?php checked(isset($_POST['unpaid_only'])); ?>>
            Unpaid Only
        </label>

        <button type="submit" style="padding: 8px; margin: 10px;">Search</button>
    </form>
    <hr>
    <?php if (!empty($search_results)): ?>
        <h3 style="margin-top: 20px;">Search Results</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Booking Ref</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Total Participants</th>
                    <th>Paid?</th>
                    <th>Feedback Sent?</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($search_results as $row): 
                    $booking_reference = $row->booking_reference;
                    $booking_url = esc_url($event_slug . '?booking_ref=' . urlencode($booking_reference));
            
                    // Determine action text and data for toggling payment status
                    $toggle_text = $row->has_paid == 0 ? 'Mark as Paid' : 'Mark as Unpaid';
                    $new_payment_status = $row->has_paid == 0 ? 1 : 0;
                
                    // Generate a nonce for the AJAX request
                    $mark_payment_nonce = wp_create_nonce('mark_payment_nonce');    
                ?>
                    <tr>
                    <td><a href="<?php echo $booking_url; ?>" target="_blank"><?php echo esc_html($booking_reference); ?></a></td>
                    <td><?php echo esc_html($row->name); ?></td>
                        <td><?php echo esc_html($row->email); ?></td>
                        <td><?php echo esc_html($row->phone); ?></td>
                        <td><?php echo esc_html($row->total_participants); ?></td>
                        <td><?php echo $row->has_paid ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $row->feedback_request_sent ? 'Yes' : 'No'; ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                            <a href="#" class="toggle-paid-link" 
                            data-booking-id="<?php echo esc_attr($row->booking_id); ?>" 
                            data-new-status="<?php echo esc_attr($new_payment_status); ?>" 
                            data-nonce="<?php echo esc_attr($mark_payment_nonce); ?>"
                            style="color: blue; text-decoration: underline;"><?php echo esc_html($toggle_text); ?></a>
                        </td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                            <a href="#" class="send-feedback-request-link" 
                            data-booking-id="<?php echo esc_attr($row->booking_id); ?>" 
                            data-nonce="<?php echo esc_attr($mark_payment_nonce); ?>"
                            style="color: blue; text-decoration: underline;">Send Feedback Request</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <p>No results found.</p>
    <?php endif; ?>

    <?php
    return ob_get_clean();
    
}
add_shortcode('event_payment_feedback_search', 'display_event_payment_feedback_search');
?>
