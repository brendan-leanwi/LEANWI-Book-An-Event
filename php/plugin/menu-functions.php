<?php
namespace LEANWI_Book_An_Event;

/**************************************************************************************************
 * Main Menu and Main Page
 **************************************************************************************************/

function leanwi_event_add_admin_menu() {
    // Parent menu: "LEANWI Book-An-Event"
    add_menu_page(
        'LEANWI-Book-An-Event',   // Page title (for the parent menu)
        'LEANWI-Book-An-Event',     // Menu title (for the plugin name in the dashboard)
        'manage_options',         // Capability
        'leanwi-book-an-event-main', // Menu slug
        __NAMESPACE__ . '\\leanwi_event_main_page',       // Callback function
        'dashicons-tickets',     // Menu icon (optional)
        7                         // Position
    );

    // Sub-menu: "Documentation"
    add_submenu_page(
        'leanwi-book-an-event-main',    // Parent slug
        'Documentation and Support',  // Page title (for the actual documentation page)
        'Documentation',              // Menu title (this will be the first submenu item)
        'manage_options',             // Capability
        'leanwi-book-an-event-main',    // Menu slug (reuse 'leanwi-book-an-event-main' to link it to the parent page)
        __NAMESPACE__ . '\\leanwi_event_main_page'            // Callback function (this will now display the Documentation page)
    );

    // Sub-menu: "Events"
    add_submenu_page(
        'leanwi-book-an-event-main',    // Parent slug
        'Events',                     // Page title
        'Events',                     // Menu title
        'manage_options',             // Capability
        'leanwi-book-an-event-events',  // Menu slug
        __NAMESPACE__ . '\\leanwi_events_page'          // Callback function to display events
    );

    // Sub-menu: "Add Event"
    add_submenu_page(
        'leanwi-book-an-event-main',
        'Add Event',
        'Add Event',
        'manage_options',
        'leanwi-add-event',
        __NAMESPACE__ . '\\leanwi_add_event_page'
    );

    // Sub-menu: "Delete Event"
    add_submenu_page(
        'leanwi-book-an-event-main',
        'Delete Event',
        'Delete Event',
        'manage_options',
        'leanwi-delete-event',
        __NAMESPACE__ . '\\leanwi_delete_event_page'
    );

    // Sub-menu: "Edit Event"
    add_submenu_page(
        'leanwi-book-an-event-main', // Parent slug (linked to Events submenu)
        'Edit Event',                 // Page title
        'Edit Event',                 // Menu title
        'manage_options',             // Capability
        'leanwi-edit-event',          // Menu slug
        __NAMESPACE__ . '\\leanwi_edit_event_page'      // Callback function to display the edit event form
    );

    // Sub-menu: "Categories"
    add_submenu_page(
        'leanwi-book-an-event-main',    // Parent slug
        'Categories',                   // Page title
        'Categories',                   // Menu title
        'manage_options',             // Capability
        'leanwi-book-an-event-categories',// Menu slug
        __NAMESPACE__ . '\\leanwi_event_categories_page'        // Callback function to display settings
    );

    // Sub-menu: "Add Category"
    add_submenu_page(
        'leanwi-book-an-event-main',
        'Add Category',
        'Add Category',
        'manage_options',
        'leanwi-event-add-category',
        __NAMESPACE__ . '\\leanwi_event_add_category_page'
    );

    // Sub-menu: "Edit Category"
    add_submenu_page(
        'leanwi-book-an-event-main', // Parent slug (linked to Categories submenu)
        'Edit Category',                 // Page title
        'Edit Category',                 // Menu title
        'manage_options',             // Capability
        'leanwi-event-edit-category',          // Menu slug
        __NAMESPACE__ . '\\leanwi_event_edit_category_page'      // Callback function to display the edit category form
    );

    // Sub-menu: "Audiences"
    add_submenu_page(
        'leanwi-book-an-event-main',    // Parent slug
        'Audiences',                   // Page title
        'Audiences',                   // Menu title
        'manage_options',             // Capability
        'leanwi-book-an-event-audiences',// Menu slug
        __NAMESPACE__ . '\\leanwi_event_audiences_page'        // Callback function to display settings
    );

    // Sub-menu: "Add Audience"
    add_submenu_page(
        'leanwi-book-an-event-main',
        'Add Audience',
        'Add Audience',
        'manage_options',
        'leanwi-event-add-audience',
        __NAMESPACE__ . '\\leanwi_event_add_audience_page'
    );

    // Sub-menu: "Edit Audience"
    add_submenu_page(
        'leanwi-book-an-event-main', // Parent slug (linked to Audiences submenu)
        'Edit Audience',                 // Page title
        'Edit Audience',                 // Menu title
        'manage_options',             // Capability
        'leanwi-event-edit-audience',          // Menu slug
        __NAMESPACE__ . '\\leanwi_event_edit_audience_page'      // Callback function to display the edit audience form
    );

    // Sub-menu: "disclaimers"
    add_submenu_page(
        'leanwi-book-an-event-main',    // Parent slug
        'Disclaimers',                   // Page title
        'Disclaimers',                   // Menu title
        'manage_options',             // Capability
        'leanwi-book-an-event-disclaimers',// Menu slug
        __NAMESPACE__ . '\\leanwi_event_disclaimers_page'        // Callback function to display settings
    );

    // Sub-menu: "Add Disclaimer"
    add_submenu_page(
        'leanwi-book-an-event-main',
        'Add Disclaimer',
        'Add Disclaimer',
        'manage_options',
        'leanwi-event-add-disclaimer',
        __NAMESPACE__ . '\\leanwi_event_add_disclaimer_page'
    );

    // Sub-menu: "Delete Disclaimer"
    add_submenu_page(
        'leanwi-book-an-event-main',
        'Delete Disclaimer',
        'Delete Disclaimer',
        'manage_options',
        'leanwi-event-delete-disclaimer',
        __NAMESPACE__ . '\\leanwi_event_delete_disclaimer_page'
    );

    // Sub-menu: "Edit Disclaimer"
    add_submenu_page(
        'leanwi-book-an-event-main', // Parent slug (linked to Audiences submenu)
        'Edit Disclaimer',                 // Page title
        'Edit Disclaimer',                 // Menu title
        'manage_options',             // Capability
        'leanwi-event-edit-disclaimer',          // Menu slug
        __NAMESPACE__ . '\\leanwi_event_edit_disclaimer_page'      // Callback function to display the edit Disclaimer form
    );

    // Sub-menu: "Reports"
    add_submenu_page(
        'leanwi-book-an-event-main',    // Parent slug
        'Reports',                   // Page title
        'Reporting',                   // Menu title
        'manage_options',             // Capability
        'leanwi-book-an-event-reports',// Menu slug
        __NAMESPACE__ . '\\leanwi_event_reports_page'        // Callback function to display the reports page
    );

    // Sub-menu: "Settings"
    add_submenu_page(
        'leanwi-book-an-event-main',    // Parent slug
        'Settings',                   // Page title
        'Settings',                   // Menu title
        'manage_options',             // Capability
        'leanwi-book-an-event-settings',// Menu slug
        __NAMESPACE__ . '\\leanwi_event_settings_page'        // Callback function to display settings
    );

    // Sub-menu: "Staff"
    add_submenu_page(
        'leanwi-book-an-event-main',    // Parent slug
        'Event Staff',                   // Page title
        'Staff',                   // Menu title
        'manage_options',             // Capability
        'leanwi-book-an-event-staff',// Menu slug
        __NAMESPACE__ . '\\leanwi_event_staff_page'        // Callback function to display settings
    );
}

// Hook to create the admin menu
add_action('admin_menu', __NAMESPACE__ . '\\leanwi_event_add_admin_menu');

// Hide the Add and Edit pages submenus from the left-hand navigation menu using CSS
function leanwi_event_hide_add_edit_submenus_css() {
    echo '<style>
        #toplevel_page_leanwi-book-an-event-main .wp-submenu a[href="admin.php?page=leanwi-add-event"] {
            display: none !important;
        }
        #toplevel_page_leanwi-book-an-event-main .wp-submenu a[href="admin.php?page=leanwi-delete-event"] {
            display: none !important;
        }
        #toplevel_page_leanwi-book-an-event-main .wp-submenu a[href="admin.php?page=leanwi-edit-event"] {
            display: none !important;
        }
        #toplevel_page_leanwi-book-an-event-main .wp-submenu a[href="admin.php?page=leanwi-event-add-category"] {
            display: none !important;
        }
        #toplevel_page_leanwi-book-an-event-main .wp-submenu a[href="admin.php?page=leanwi-event-edit-category"] {
            display: none !important;
        }
        #toplevel_page_leanwi-book-an-event-main .wp-submenu a[href="admin.php?page=leanwi-event-add-audience"] {
            display: none !important;
        }
        #toplevel_page_leanwi-book-an-event-main .wp-submenu a[href="admin.php?page=leanwi-event-edit-audience"] {
            display: none !important;
        }
        #toplevel_page_leanwi-book-an-event-main .wp-submenu a[href="admin.php?page=leanwi-event-add-disclaimer"] {
            display: none !important;
        }
        #toplevel_page_leanwi-book-an-event-main .wp-submenu a[href="admin.php?page=leanwi-event-delete-disclaimer"] {
            display: none !important;
        }
        #toplevel_page_leanwi-book-an-event-main .wp-submenu a[href="admin.php?page=leanwi-event-edit-disclaimer"] {
            display: none !important;
        }
    </style>';
}
add_action('admin_head', __NAMESPACE__ . '\\leanwi_event_hide_add_edit_submenus_css');

// Function to display the main page
function leanwi_event_main_page() {
    ?>
    <div class="wrap">
        <h1>Documentation and Support</h1>
        <p>Welcome to the LEANWI Book-An-Event plugin!</p>
    </div>
    <?php
}

/**************************************************************************************************
 * Events
 **************************************************************************************************/

// Function to display the list of events
function leanwi_events_page() {
    
    // Display event list
    echo '<div class="wrap">';
    echo '<h1>Events</h1>';

    echo '<a href="' . admin_url('admin.php?page=leanwi-add-event') . '" class="button button-primary">Add Event</a>';
    echo '<p>&nbsp;</p>'; 
    echo '<strong>NOTE:</strong> You need to add the following shortcode to your event pages - <strong>[leanwi_event_details]</strong>';
    echo '<p> </p>'; 
    echo '<table class="wp-list-table widefat striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th scope="col">Title</th>';
    echo '<th scope="col">Capacity</th>';
    echo '<th scope="col">Next Date</th>';
    echo '<th scope="col">Audience</th>';  
    echo '<th scope="col">Category</th>';
    echo '<th scope="col">Historic</th>';
    echo '<th scope="col">Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Fetch events
    $events = fetch_events();
    if (isset($events['error'])) {
        echo '<tr><td colspan="6">' . esc_html($events['error']) . '</td></tr>';
    } elseif (empty($events['events'])) {
        echo '<tr><td colspan="7">No Booking Events have been created yet.</td></tr>';
    } else {
        // Display each event in a row
        foreach ($events['events'] as $event) {
            echo '<tr>';
           echo '<td>' . esc_html($event['title']) . '</td>';
            echo '<td>' . esc_html($event['capacity']) . '</td>';
            echo '<td>' . esc_html($event['next_start_date']) . '</td>';
            echo '<td>' . esc_html($event['audience']) . '</td>';          
            echo '<td>' . esc_html($event['category']) . '</td>';
            echo '<td>' . ($event['historic'] == 0 ? 'False' : 'True') . '</td>';
            echo '<td>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=leanwi-edit-event&event_data_id=' . esc_attr($event['event_data_id']))) . '" class="button">Edit</a> ';
            echo '<a href="' . esc_url(admin_url('admin.php?page=leanwi-delete-event&event_data_id=' . esc_attr($event['event_data_id']))) . '" class="button" onclick="return confirm(\'Are you sure you want to delete this event?\');">Delete</a>';
            echo '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
// Function to get events
function fetch_events() {
    // Construct the URL for get-all-event-data.php
    $url = plugins_url('LEANWI-Book-An-Event/php/plugin/get-all-event-data.php');
    // Log the URL to the debug.log file
    error_log('Fetching events from URL: ' . $url); // Log the URL

    // Use wp_remote_get to fetch the data with SSL verification disabled
    $response = wp_remote_get($url, [
        'sslverify' => false, // Disable SSL verification
        'timeout' => 15, // Increase the timeout to 15 seconds
    ]);

    // Check for errors
    if (is_wp_error($response)) {
        // Handle error
        error_log('Error fetching events: ' . $response->get_error_message());
        return ['error' => 'Unable to fetch events.'];
    }

    // Get the body of the response
    $body = wp_remote_retrieve_body($response);

    // Decode the JSON response
    $events = json_decode($body, true);

    return $events; // Return the events array
}

// Function to handle deletion
function leanwi_delete_event_page() {
    global $wpdb;

    // Check if `event_data_id` is set in the URL
    if (!isset($_GET['event_data_id'])) {
        echo '<div class="error"><p>No event specified.</p></div>';
        return;
    }

    // Sanitize and fetch the event_data_id from the URL
    $event_data_id = intval($_GET['event_data_id']);
    $data_table = $wpdb->prefix . 'leanwi_event_data';
    $booking_table =  $wpdb->prefix . 'leanwi_event_booking';

    $booking = $wpdb->get_row($wpdb->prepare("SELECT 1 FROM $booking_table WHERE event_data_id = %d", $event_data_id));

    if($booking){
        echo '<div class="error"><p>This event could not be deleted as there are bookings for this event.</p></div>';
    } else {
        /**************************************************************************************************
         * 
         * Also deletes on cascade from the following tables:
         *      leanwi_event_cost
         *      leanwi_event_disclaimer
         *  (would do booking too but we're checking for that above)
         **************************************************************************************************/
        $wpdb->delete(
            $data_table,
            ['event_data_id' => $event_data_id],
            ['%d']
        );
        echo '<div class="deleted"><p>Event deleted successfully.</p></div>';
    }
}

function leanwi_add_event_page() {
    global $wpdb;
    $data_table = $wpdb->prefix . 'leanwi_event_data';
    $cost_table = $wpdb->prefix . 'leanwi_event_cost';
    $disclaimer_table = $wpdb->prefix . 'leanwi_event_disclaimer';

    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify nonce before processing the form
        if (isset($_POST['event_nonce']) && wp_verify_nonce($_POST['event_nonce'], 'add_event_action')) {
            // The nonce is valid; proceed with form processing.
            $post_id = sanitize_text_field($_POST['post_id']);
            $event_url = esc_url($_POST['event_url']);
            $event_image = esc_url($_POST['event_image']);
            $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
            $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 1;
            $audience_id = isset($_POST['audience_id']) ? intval($_POST['audience_id']) : 1;
            $participation_rule = esc_html($_POST['participation_rule']);
            $booking_before_hours = isset($_POST['booking_before_hours']) ? intval($_POST['booking_before_hours']) : 0;
            $cancellation_before_hours = isset($_POST['cancellation_before_hours']) ? intval($_POST['cancellation_before_hours']) : 0;

            $register_by_date = !empty($_POST['register_by_date']) ? sanitize_text_field($_POST['register_by_date']) : NULL;
            $virtual_event_rule = wp_kses_post($_POST['virtual_event_rule']);
            $virtual_event_rule = wp_unslash($virtual_event_rule);
            $virtual_event_url = esc_url($_POST['virtual_event_url']);
            $virtual_event_password = wp_kses_post($_POST['virtual_event_password']);
            $virtual_event_password = wp_unslash($virtual_event_password);
            $event_admin_email = isset($_POST['event_admin_email']) ? sanitize_email($_POST['event_admin_email']) : '';

            $extra_email_text = wp_kses_post($_POST['extra_email_text']);
            $extra_email_text = wp_unslash($extra_email_text);
            $extra_event_url = esc_url($_POST['extra_event_url']);
            $include_extra_event_url_in_email = isset($_POST['include_extra_event_url_in_email']) ? 1 : 0;

            $include_virtual_bookings_in_capacity_calc = isset($_POST['include_virtual_bookings_in_capacity_calc']) ? 1 : 0;
            $include_special_notes = isset($_POST['include_special_notes']) ? 1 : 0;
            $include_physical_address = isset($_POST['include_physical_address']) ? 1 : 0;
            $include_zipcode = isset($_POST['include_zipcode']) ? 1 : 0;

            // Insert the new event into the database
            $inserted = $wpdb->insert(
                $data_table,
                array(
                    'post_id' => $post_id,
                    'event_url' => $event_url,
                    'event_image' => $event_image,
                    'participation_rule' => $participation_rule,
                    'capacity' => $capacity,
                    'category_id' => $category_id,
                    'audience_id' => $audience_id,
                    'booking_before_hours' => $booking_before_hours,
                    'cancellation_before_hours' => $cancellation_before_hours,
                    'register_by_date' => $register_by_date,
                    'virtual_event_rule' => $virtual_event_rule,
                    'virtual_event_url' => $virtual_event_url,
                    'virtual_event_password' => $virtual_event_password,
                    'event_admin_email' => $event_admin_email,
                    'extra_email_text' => $extra_email_text,
                    'extra_event_url' => $extra_event_url,
                    'include_extra_event_url_in_email' => $include_extra_event_url_in_email,
                    'include_virtual_bookings_in_capacity_calc' => $include_virtual_bookings_in_capacity_calc,
                    'include_special_notes' => $include_special_notes,
                    'include_physical_address' => $include_physical_address,
                    'include_zipcode' => $include_zipcode,
                )
            );

            if ($inserted) {
                // Get the newly inserted event_data_id
                $event_data_id = $wpdb->insert_id;
                
                // Insert costs into the database
                if (!empty($_POST['cost_name']) && !empty($_POST['cost_amount'])) {
                    foreach ($_POST['cost_name'] as $key => $cost_name) {
                        $cost_name = wp_kses_post($cost_name);
                        $cost_name = wp_unslash($cost_name);
                        $cost_amount = floatval($_POST['cost_amount'][$key]);
                        $include_extra_info = isset($_POST['include_extra_info'][$key]) ? 1 : 0;
                        $extra_info_label = wp_kses_post($_POST['extra_info_label'][$key]);
                        $extra_info_label = wp_unslash($extra_info_label);

                        $wpdb->insert(
                            $cost_table,
                            array(
                                'event_data_id' => $event_data_id,
                                'cost_name' => $cost_name,
                                'cost_amount' => $cost_amount,
                                'include_extra_info' => $include_extra_info,
                                'extra_info_label' => $extra_info_label,
                            )
                        );
                    }
                }

                // Save disclaimers that are checked
                if (!empty($_POST['disclaimer']) && is_array($_POST['disclaimer'])) {
                    foreach ($_POST['disclaimer'] as $disclaimer_id) {
                        // Fetch the disclaimer text
                        $disclaimer_text = wp_kses_post($_POST['disclaimer_text_' . $disclaimer_id]);
                        $disclaimer_text = wp_unslash($disclaimer_text);

                        // Insert the disclaimer into the event_disclaimer table
                        $wpdb->insert(
                            $disclaimer_table,
                            array(
                                'event_data_id' => $event_data_id,
                                'disclaimer' => $disclaimer_text,
                            )
                        );
                    }
                }

                echo '<div class="updated"><p>Event added successfully.</p></div>';
            } else {
                echo '<div class="error"><p>Error adding Event. Please try again.</p></div>';
            }
        } else {
            // Nonce is invalid; handle the error accordingly.
            wp_die('Nonce verification failed.');
        }
    }    

    // Initialize blank values for the form
    $event = (object) [
        'event_data_id' => '',
        'post_id' => '',
        'event_url' => '',
        'event_image' => '',
        'title' => '',
        'capacity' => '',
        'category' => '',
        'audience' => '',
        'participation_rule' => 'any',
        'cancellation_before_hours' => '0',
        'booking_before_hours' => '0',
        'register_by_date' => '',
        'virtual_event_rule' => 'no',
        'virtual_event_url' => '',
        'virtual_event_password' => '',
        'event_admin_email' => '',
        'extra_email_text' => '',
        'extra_event_url' => '',
        'include_extra_event_url_in_email' => 0,
        'include_virtual_bookings_in_capacity_calc' => 0,
        'include_special_notes' => 0,
        'include_physical_address' => 0,
        'include_zipcode' => 0
    ];

    // Fetch unused events so that latest events end up at the top
    $unused_events = $wpdb->get_results("
    SELECT p.ID AS post_id, p.post_title AS title
    FROM {$wpdb->prefix}posts p
    WHERE p.post_type = 'tribe_events'
    AND NOT EXISTS (
        SELECT 1
        FROM {$wpdb->prefix}leanwi_event_data ed
        WHERE ed.post_id = p.ID
    )
    Order by p.post_date desc
    LIMIT 20    
    ");
    
    /* 
     * Can't decide which code to use but think I can just list all of my Events that I haven't used yet
     * or perhaps I'll end up making a list of categories in the menu that can be used in this query.
     * It depends how big this list might get I suppose but I've now made it so latest Events appear at the top
     * and I have limited the size of the list
     * 
     * I can do this because I am not reliant on using divi templates any more and so do not require a set category
     * to make my functionality work from the template
     * 
    // Fetch unused events that are in the 'LEANWI Event' Category
    $category_name = 'LEANWI Event';
    $query = $wpdb->prepare("
        SELECT p.ID AS post_id, p.post_title AS title
        FROM {$wpdb->prefix}posts p
        INNER JOIN {$wpdb->prefix}term_relationships tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->prefix}terms t ON tt.term_id = t.term_id
        WHERE p.post_type = 'tribe_events'
        AND tt.taxonomy = 'tribe_events_cat'
        AND t.name = %s
        AND NOT EXISTS (
            SELECT 1
            FROM {$wpdb->prefix}leanwi_event_data ed
            WHERE ed.post_id = p.ID
        )
        Order by p.post_date desc
        LIMIT 20
    ", $category_name);
    $unused_events = $wpdb->get_results($query);
    */

    // Fetch categories (excluding historic)
    $categories = $wpdb->get_results("
    SELECT category_id, category_name
    FROM {$wpdb->prefix}leanwi_event_category
    WHERE historic = 0 
    ORDER BY display_order ASC
    ");

    // Fetch audience options (excluding historic)
    $audiences = $wpdb->get_results("
    SELECT audience_id, audience_name
    FROM {$wpdb->prefix}leanwi_event_audience
    WHERE historic = 0 
    ORDER BY display_order ASC
    ");

    // Fetch saved disclaimers for the event
    $saved_disclaimers = $wpdb->get_results("
        SELECT id, disclaimer
        FROM {$wpdb->prefix}leanwi_event_saved_disclaimer
    ");


?>
    <div class="wrap">
        <h1>Add Event</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="event_data_id">Event ID</label></th>
                    <td><input type="text" id="event_data_id" value="<?php echo esc_attr($event->event_data_id); ?>" disabled /></td>
                </tr>
                <tr>
                    <th><label for="title">Event Title</label></th>
                    <td>
                        <select id="title" name="post_id" required>
                            <option value="">Select an event</option>
                            <?php foreach ($unused_events as $unused_event): ?>
                                <option value="<?php echo esc_attr($unused_event->post_id); ?>">
                                    <?php echo esc_html($unused_event->title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="event_url">Event URL</label></th>
                    <td><input type="text" id="event_url" name="event_url" value="<?php echo esc_attr($event->event_url); ?>" required style="width: 90%;"/></td>
                </tr>
                <tr>
                    <th><label for="event_image">Image URL</label></th>
                    <td><input type="text" id="event_image" name="event_image" value="<?php echo esc_attr($event->event_image); ?>" style="width: 90%;"/></td>
                </tr>
                <tr>
                    <th><label for="participation_rule">Participation Rule</label></th>
                    <td>
                        <select id="participation_rule" name="participation_rule" required>
                            <option value="any" <?php selected($event->participation_rule, 'any'); ?>>Any</option>
                            <option value="all" <?php selected($event->participation_rule, 'all'); ?>>All</option>
                            <option value="one" <?php selected($event->participation_rule, 'one'); ?>>One</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="capacity">Capacity</label></th>
                    <td><input type="number" id="capacity" name="capacity" value="<?php echo esc_attr($event->capacity); ?>" required /> Enter 0 (zero) to indicate unlimited capacity.</td>
                </tr>               
                <tr>
                    <th><label for="register_by_date">Any/All occurrences of this event must be registered by:</label></th>
                    <td><input type="date" id="register_by_date" name="register_by_date" value="<?php echo esc_attr($event->register_by_date); ?>"/> Leaving blank means no register by date is needed.</td>
                </tr>
                <tr>
                    <th><label for="booking_before_hours">Hours away from event that a booking is allowed</label></th>
                    <td><input type="number" id="booking_before_hours" name="booking_before_hours" value="<?php echo esc_attr($event->booking_before_hours); ?>" required /> Enter 0 (zero) to indicate a booking may be placed up to the time of the event occurrence.</td>
                </tr>
                <tr>
                    <th><label for="cancellation_before_hours">Hours away from event that a cancellation is allowed</label></th>
                    <td><input type="number" id="cancellation_before_hours" name="cancellation_before_hours" value="<?php echo esc_attr($event->cancellation_before_hours); ?>" required /> Enter 0 (zero) to indicate a booking may be cancelled up to the time of the event occurrence.</td>
                </tr>
                <tr>
                    <th><label for="virtual_event_rule">Select the virtual event rule</label></th>
                    <td>
                        <select id="virtual_event_rule" name="virtual_event_rule" required>
                            <option value="no" <?php selected($event->virtual_event_rule, 'no'); ?>>No virtual event option</option>
                            <option value="only" <?php selected($event->virtual_event_rule, 'only'); ?>>Is only a virtual event</option>
                            <option value="optional" <?php selected($event->virtual_event_rule, 'optional'); ?>>Optionally attend in-person or virtual</option>
                            <option value="specify" <?php selected($event->virtual_event_rule, 'specify'); ?>>Attendee must specify in-person or virtual</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="include_virtual_bookings_in_capacity_calc">Include virtual bookings when calculating capacity?</label></th>
                    <td>
                        <input type="checkbox" id="include_virtual_bookings_in_capacity_calc" name="include_virtual_bookings_in_capacity_calc" <?php echo ($event->include_virtual_bookings_in_capacity_calc == 1) ? 'checked' : ''; ?>/>
                        This option is only used if the 'Attendee must specify in-person or virtual' option has been selected above.
                    </td>
                </tr>
                <tr>
                    <th><label for="virtual_event_url">URL link to access the virtual event</label></th>
                    <td><input type="text" id="virtual_event_url" name="virtual_event_url" value="<?php echo esc_attr($event->virtual_event_url); ?>" required style="width: 90%;"/></td>
                </tr>
                <tr>
                    <th><label for="virtual_event_password">Password to access the virtual event</label></th>
                    <td><input type="text" id="virtual_event_password" name="virtual_event_password" value="<?php echo esc_attr($event->virtual_event_password); ?>" style="width: 90%;" /></td>
                </tr>
                <tr>
                    <th><label for="category">Category</label></th>
                    <td>
                        <select id="category" name="category_id" required> <!-- Correct name for category_id -->
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category->category_id); ?>">
                                    <?php echo esc_html($category->category_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="audience">Audience</label></th>
                    <td>
                        <select id="audience" name="audience_id" required> <!-- Correct name for audience_id -->
                            <option value="">Select an audience</option>
                            <?php foreach ($audiences as $audience): ?>
                                <option value="<?php echo esc_attr($audience->audience_id); ?>">
                                    <?php echo esc_html($audience->audience_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="event_admin_email">Event Admin Email</label></th>
                    <td><input type="email" id="event_admin_email" name="event_admin_email" style="width: 90%;" value="<?php echo esc_attr($event->event_admin_email); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="extra_email_text">Extra text to be added to confirmation emails</label></th>
                    <td>
                        <textarea name="extra_email_text" id="extra_email_text" rows="3" style="width: 80%;"><?php echo esc_textarea($event->extra_email_text ?? ''); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th><label for="extra_event_url">Additional website or facebook link for the event</label></th>
                    <td><input type="text" id="extra_event_url" name="extra_event_url" value="<?php echo esc_attr($event->extra_event_url); ?>" required style="width: 90%;"/></td>
                </tr>
                <tr>
                    <th><label for="include_extra_event_url_in_email">Include the above additional link in emails?</label></th>
                    <td>
                        <input type="checkbox" id="include_extra_event_url_in_email" name="include_extra_event_url_in_email" <?php echo ($event->include_extra_event_url_in_email == 1) ? 'checked' : ''; ?>/>
                    </td>
                </tr>
                <tr>
                    <th><label for="include_special_notes">Allow users to add notes to their booking?</label></th>
                    <td>
                        <input type="checkbox" id="include_special_notes" name="include_special_notes" <?php echo ($event->include_special_notes == 1) ? 'checked' : ''; ?>/>
                    </td>
                </tr>
                <tr>
                    <th><label for="include_physical_address">Users must enter a physical address when booking?</label></th>
                    <td>
                        <input type="checkbox" id="include_physical_address" name="include_physical_address" <?php echo ($event->include_physical_address == 1) ? 'checked' : ''; ?>/>
                    </td>
                </tr>
                <tr>
                    <th><label for="include_zipcode">Users must enter a zipcode when booking?</label></th>
                    <td>
                        <input type="checkbox" id="include_zipcode" name="include_zipcode" <?php echo ($event->include_zipcode == 1) ? 'checked' : ''; ?>/>
                    </td>
                </tr>


                <!-- Dynamic costs section -->
                <tr>
                    <th><label for="costs">Add Costs</label></th>
                    <td>
                        <button type="button" id="add-costs-button" class="button">Add Costs</button>
                        <div id="costs-container"></div>
                    </td>
                </tr>

                <!-- Display disclaimers -->
                <tr>
                <th><label for="disclaimers">Disclaimers</label></th>
                <td>
                    <?php foreach ($saved_disclaimers as $disclaimer): ?>
                        <div class="disclaimer-item">
                            <input type="checkbox" name="disclaimer[]" value="<?php echo esc_attr($disclaimer->id); ?>" id="disclaimer_<?php echo esc_attr($disclaimer->id); ?>" />
                            <label for="disclaimer_<?php echo esc_attr($disclaimer->id); ?>">Include this disclaimer</label><br/>
                            <!-- Change input to textarea and set rows="3" for 3 lines -->
                            <textarea name="disclaimer_text_<?php echo esc_attr($disclaimer->id); ?>" rows="3" style="width: 80%;"><?php echo esc_textarea($disclaimer->disclaimer ?? ''); ?></textarea>
                        </div>
                    <?php endforeach; ?>

                    <!-- Button to add new disclaimers -->
                    <button type="button" id="add-disclaimer-button" class="button">Add Disclaimer</button>
                    <div id="disclaimers-container"></div>
                </td>
            </tr>
            </table>

            <?php wp_nonce_field('add_event_action', 'event_nonce'); ?>
            <p class="submit">
                <input type="submit" class="button button-primary" value="Add Event" />
            </p>
        </form>
    </div>

    <script>
        let costCount = 0;
        document.getElementById('add-costs-button').addEventListener('click', function() {
            costCount++;
            let costHtml = `
                <div id="cost-group-${costCount}" class="cost-group">
                    <p><strong>Cost ${costCount}</strong></p>
                    <label for="cost_name_${costCount}">Cost Name</label>
                    <input type="text" name="cost_name[]" id="cost_name_${costCount}" required />

                    <label for="cost_amount_${costCount}">Cost Amount $</label>
                    <input type="number" name="cost_amount[]" id="cost_amount_${costCount}" required step="0.01" min="0" />
                    
                    <label for="include_extra_info_${costCount}">Include extra info?</label>
                    <input type="hidden" name="include_extra_info[${costCount}]" value="0"> 
                    <input type="checkbox" id="include_extra_info_${costCount}" name="include_extra_info[${costCount}]" value="1" />

                    <label for="extra_info_label_${costCount}">Extra Info Label</label>
                    <input type="text" name="extra_info_label[]" id="extra_info_label_${costCount}" />

                    <button type="button" onclick="removeCost(${costCount})" class="button">Remove Cost</button>
                </div>
            `;
            document.getElementById('costs-container').insertAdjacentHTML('beforeend', costHtml);
            this.textContent = 'Add More Costs';
        });

        function removeCost(costId) {
            const costGroup = document.getElementById('cost-group-' + costId);
            costGroup.remove();
        }

        // For dynamically adding disclaimers
        let disclaimerCount = 0;
        document.getElementById('add-disclaimer-button').addEventListener('click', function() {
            disclaimerCount++;
            let disclaimerHtml = `
                <div id="disclaimer-group-${disclaimerCount}" class="disclaimer-group">
                    <input type="checkbox" name="disclaimer[]" value="new_${disclaimerCount}" id="disclaimer_new_${disclaimerCount}" checked />
                    <label for="disclaimer_new_${disclaimerCount}">Include this disclaimer</label><br/>
                    <textarea name="disclaimer_text_new_${disclaimerCount}" rows="3" style="width: 80%;" placeholder="Enter disclaimer text here"></textarea>
                    
                    <button type="button" onclick="removeDisclaimer(${disclaimerCount})" class="button">Remove Disclaimer</button>
                </div>
            `;
            document.getElementById('disclaimers-container').insertAdjacentHTML('beforeend', disclaimerHtml);
        });

        function removeDisclaimer(disclaimerId) {
            const disclaimerGroup = document.getElementById('disclaimer-group-' + disclaimerId);
            disclaimerGroup.remove();
        }
    </script>
<?php
}

function leanwi_edit_event_page() {
    global $wpdb;

    // Check if `event_data_id` is set in the URL
    if (!isset($_GET['event_data_id'])) {
        echo '<div class="error"><p>No event specified.</p></div>';
        return;
    }

    // Sanitize and fetch the event_data_id from the URL
    $event_data_id = intval($_GET['event_data_id']);
    $data_table = $wpdb->prefix . 'leanwi_event_data';
    $category_table = $wpdb->prefix . 'leanwi_event_category';
    $audience_table = $wpdb->prefix . 'leanwi_event_audience';
    $cost_table = $wpdb->prefix . 'leanwi_event_cost';
    $disclaimer_table = $wpdb->prefix . 'leanwi_event_disclaimer';

    // Fetch the event data based on event_data_id
    $event = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM $data_table WHERE event_data_id = %d", $event_data_id
    ));

    if (!$event) {
        echo '<div class="error"><p>Event not found.</p></div>';
        return;
    }

    // Fetch the existing costs for this event
    $costs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $cost_table WHERE event_data_id = %d", $event_data_id));

    // Fetch disclaimers for this event
    $disclaimers = $wpdb->get_results($wpdb->prepare("SELECT * FROM $disclaimer_table WHERE event_data_id = %d", $event_data_id));

    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify nonce before processing the form
        if (isset($_POST['event_nonce']) && wp_verify_nonce($_POST['event_nonce'], 'edit_event_action')) {
            // Sanitize and assign values
            $post_id = sanitize_text_field($_POST['post_id']);
            $event_url = esc_url($_POST['event_url']);
            $event_image = esc_url($_POST['event_image']);
            $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
            $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 1;
            $audience_id = isset($_POST['audience_id']) ? intval($_POST['audience_id']) : 1;
            $participation_rule = esc_html($_POST['participation_rule']);
            $booking_before_hours = isset($_POST['booking_before_hours']) ? intval($_POST['booking_before_hours']) : 0;
            $cancellation_before_hours = isset($_POST['cancellation_before_hours']) ? intval($_POST['cancellation_before_hours']) : 0;

            $register_by_date = !empty($_POST['register_by_date']) ? sanitize_text_field($_POST['register_by_date']) : NULL;
            $virtual_event_rule = wp_kses_post($_POST['virtual_event_rule']);
            $virtual_event_rule = wp_unslash($virtual_event_rule);
            $virtual_event_url = esc_url($_POST['virtual_event_url']);
            $virtual_event_password = wp_kses_post($_POST['virtual_event_password']);
            $virtual_event_password = wp_unslash($virtual_event_password);
            $event_admin_email = isset($_POST['event_admin_email']) ? sanitize_email($_POST['event_admin_email']) : '';

            $extra_email_text = wp_kses_post($_POST['extra_email_text']);
            $extra_email_text = wp_unslash($extra_email_text);
            $extra_event_url = esc_url($_POST['extra_event_url']);
            $include_extra_event_url_in_email = isset($_POST['include_extra_event_url_in_email']) ? 1 : 0;

            $include_virtual_bookings_in_capacity_calc = isset($_POST['include_virtual_bookings_in_capacity_calc']) ? 1 : 0;
            $include_special_notes = isset($_POST['include_special_notes']) ? 1 : 0;
            $include_physical_address = isset($_POST['include_physical_address']) ? 1 : 0;
            $include_zipcode = isset($_POST['include_zipcode']) ? 1 : 0;
            $historic = isset($_POST['historic']) ? 1 : 0;


            // Update the event in the database
            $updated = $wpdb->update(
                $data_table,
                array(
                    'post_id' => $post_id,
                    'event_url' => $event_url,
                    'event_image' => $event_image,
                    'participation_rule' => $participation_rule,
                    'capacity' => $capacity,
                    'category_id' => $category_id,
                    'audience_id' => $audience_id,
                    'booking_before_hours' => $booking_before_hours,
                    'cancellation_before_hours' => $cancellation_before_hours,
                    'register_by_date' => $register_by_date,
                    'virtual_event_rule' => $virtual_event_rule,
                    'virtual_event_url' => $virtual_event_url,
                    'virtual_event_password' => $virtual_event_password,
                    'event_admin_email' => $event_admin_email,
                    'extra_email_text' => $extra_email_text,
                    'extra_event_url' => $extra_event_url,
                    'include_extra_event_url_in_email' => $include_extra_event_url_in_email,
                    'include_virtual_bookings_in_capacity_calc' => $include_virtual_bookings_in_capacity_calc,
                    'include_special_notes' => $include_special_notes,
                    'include_physical_address' => $include_physical_address,
                    'include_zipcode' => $include_zipcode,
                    'historic' => $historic,
                ),
                array('event_data_id' => $event_data_id)
            );

            // Handle updating or inserting costs
            if (isset($_POST['cost_name']) && isset($_POST['cost_amount'])) {
                // Loop through cost names, amounts, and historic checkboxes
                foreach ($_POST['cost_name'] as $key => $cost_name) {
                    $cost_name = wp_kses_post($cost_name);
                    $cost_name = wp_unslash($cost_name);
                    $cost_amount = floatval($_POST['cost_amount'][$key]);
                    $include_extra_info = isset($_POST['include_extra_info'][$key]) ? intval($_POST['include_extra_info'][$key]) : 0;

                    $extra_info_label = wp_kses_post($_POST['extra_info_label'][$key]);
                    $extra_info_label = wp_unslash($extra_info_label);
                    $cost_historic = isset($_POST['cost_historic'][$key]) ? 1 : 0;
                    
                    // Format the cost amount to two decimal places
                    $cost_amount = number_format($cost_amount, 2, '.', '');

                    // Check if a cost ID is provided for this entry (if updating existing cost)
                    if (isset($_POST['cost_id'][$key]) && !empty($_POST['cost_id'][$key])) {
                        $cost_id = intval($_POST['cost_id'][$key]);

                        // Update the existing cost
                        $wpdb->update(
                            $cost_table,
                            array(
                                'cost_name' => $cost_name,
                                'cost_amount' => $cost_amount,
                                'include_extra_info' => $include_extra_info,
                                'extra_info_label' => $extra_info_label,
                                'historic' => $cost_historic,
                            ),
                            array('cost_id' => $cost_id)
                        );
                    } else {
                        // Insert a new cost if no cost ID exists
                        $wpdb->insert(
                            $cost_table,
                            array(
                                'event_data_id' => $event_data_id,
                                'cost_name' => $cost_name,
                                'cost_amount' => $cost_amount,
                                'include_extra_info' => $include_extra_info,
                                'extra_info_label' => $extra_info_label,
                                'historic' => $cost_historic,
                            )
                        );
                    }
                }
            }

            // Save disclaimers that are checked
            if (!empty($_POST['disclaimer']) && is_array($_POST['disclaimer'])) {
                // First, delete the existing disclaimers for the event
                $wpdb->delete($disclaimer_table, array('event_data_id' => $event_data_id));

                // Loop through the submitted disclaimers and insert them back
                foreach ($_POST['disclaimer'] as $disclaimer_id) {
                    // Fetch the disclaimer text if it exists
                    $disclaimer_text_key = 'disclaimer_text_' . $disclaimer_id;
                    $disclaimer_text = isset($_POST[$disclaimer_text_key]) ? sanitize_textarea_field($_POST[$disclaimer_text_key]) : '';

                    // Insert each disclaimer as a new entry in the database
                    $wpdb->insert(
                        $disclaimer_table,
                        array(
                            'event_data_id' => $event_data_id,
                            'disclaimer' => $disclaimer_text,
                        ),
                        array('%d', '%s') // Specify data types
                    );
                }
            } else {
                // If no disclaimers were submitted, delete existing disclaimers
                $wpdb->delete($disclaimer_table, array('event_data_id' => $event_data_id));
            }

            if ($updated !== false) {
                echo '<div class="updated"><p>Event updated successfully.</p></div>';
                // Refresh the event data to display updated values
                $event = $wpdb->get_row($wpdb->prepare("SELECT * FROM $data_table WHERE event_data_id = %d", $event_data_id));
                $costs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $cost_table WHERE event_data_id = %d", $event_data_id)); // Re-fetch costs
                $disclaimers = $wpdb->get_results($wpdb->prepare("SELECT * FROM $disclaimer_table WHERE event_data_id = %d", $event_data_id)); // Re-fetch disclaimers
            } else {
                echo '<div class="error"><p>Error updating Event. Please try again.</p></div>';
            }
        } else {
            // Nonce is invalid; handle the error accordingly.
            wp_die('Nonce verification failed.');
        }
    }

    // Fetch unused events for the dropdown
    $unused_events = $wpdb->get_results("
        SELECT p.ID AS post_id, p.post_title AS title
        FROM {$wpdb->prefix}posts p
        WHERE p.post_type = 'tribe_events'
        AND (p.ID = {$event->post_id} OR NOT EXISTS (
            SELECT 1
            FROM {$data_table} ed
            WHERE ed.post_id = p.ID
        ))
    ");

    // Fetch categories (excluding historic)
    $categories = $wpdb->get_results("
        SELECT category_id, category_name
        FROM {$category_table}
        WHERE historic = 0 
        ORDER BY display_order ASC
    ");

    // Fetch audience options (excluding historic)
    $audiences = $wpdb->get_results("
        SELECT audience_id, audience_name
        FROM {$audience_table}
        WHERE historic = 0
        ORDER BY display_order ASC
    ");

?>
    <div class="wrap">
        <h1>Edit Event</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="event_data_id">Event ID</label></th>
                    <td><input type="text" id="event_data_id" value="<?php echo esc_attr($event->event_data_id); ?>" disabled /></td>
                </tr>
                <tr>
                    <th><label for="title">Event Title</label></th>
                    <td>
                        <select id="title" name="post_id" required>
                            <option value="">Select an event</option>
                            <?php foreach ($unused_events as $unused_event): ?>
                                <option value="<?php echo esc_attr($unused_event->post_id); ?>" <?php selected($unused_event->post_id, $event->post_id); ?>>
                                    <?php echo esc_html($unused_event->title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="historic">Make Historic</label></th>
                    <td>
                        <input type="checkbox" id="historic" name="historic" <?php echo esc_attr($event->historic) == 1 ? 'checked' : ''; ?> />
                    </td>
                </tr>

                <tr>
                    <th><label for="event_url">Event URL</label></th>
                    <td><input type="text" id="event_url" name="event_url" value="<?php echo esc_attr($event->event_url); ?>" required style="width: 90%;"/></td>
                </tr>
                <tr>
                    <th><label for="event_image">Image URL</label></th>
                    <td><input type="text" id="event_image" name="event_image" value="<?php echo esc_attr($event->event_image); ?>" style="width: 90%;"/></td>
                </tr>
                <tr>
                    <th><label for="participation_rule">Participation Rule</label></th>
                    <td>
                        <select id="participation_rule" name="participation_rule" required>
                            <option value="any" <?php selected($event->participation_rule, 'any'); ?>>Any</option>
                            <option value="all" <?php selected($event->participation_rule, 'all'); ?>>All</option>
                            <option value="one" <?php selected($event->participation_rule, 'one'); ?>>One</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="capacity">Capacity</label></th>
                    <td><input type="number" id="capacity" name="capacity" value="<?php echo esc_attr($event->capacity); ?>" required /> Enter 0 (zero) to indicate unlimited capacity.</td>
                </tr>
                <tr>
                    <th><label for="register_by_date">Any/All occurrences of this event must be registered by:</label></th>
                    <td><input type="date" id="register_by_date" name="register_by_date" value="<?php echo esc_attr($event->register_by_date); ?>"/> Leaving blank means no register by date is needed.</td>
                </tr>
                <tr>
                    <th><label for="booking_before_hours">Hours away from event that a booking is allowed</label></th>
                    <td><input type="number" id="booking_before_hours" name="booking_before_hours" value="<?php echo esc_attr($event->booking_before_hours); ?>" required /> Enter 0 (zero) to indicate a booking may be placed up to the time of the event occurrence.</td>
                </tr>
                <tr>
                    <th><label for="cancellation_before_hours">Hours away from event that a cancellation is allowed</label></th>
                    <td><input type="number" id="cancellation_before_hours" name="cancellation_before_hours" value="<?php echo esc_attr($event->cancellation_before_hours); ?>" required /> Enter 0 (zero) to indicate a booking may be cancelled up to the time of the event occurrence.</td>
                </tr>
                <tr>
                    <th><label for="virtual_event_rule">Select the virtual event rule</label></th>
                    <td>
                        <select id="virtual_event_rule" name="virtual_event_rule" required>
                            <option value="no" <?php selected($event->virtual_event_rule, 'no'); ?>>No virtual event option</option>
                            <option value="only" <?php selected($event->virtual_event_rule, 'only'); ?>>Is only a virtual event</option>
                            <option value="optional" <?php selected($event->virtual_event_rule, 'optional'); ?>>Optionally attend in-person or virtual</option>
                            <option value="specify" <?php selected($event->virtual_event_rule, 'specify'); ?>>Attendee must specify in-person or virtual</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="include_virtual_bookings_in_capacity_calc">Include virtual bookings when calculating capacity?</label></th>
                    <td>
                        <input type="checkbox" id="include_virtual_bookings_in_capacity_calc" name="include_virtual_bookings_in_capacity_calc" <?php echo ($event->include_virtual_bookings_in_capacity_calc == 1) ? 'checked' : ''; ?>/>
                        This option is only used if the 'Attendee must specify in-person or virtual' option has been selected above.
                    </td>
                </tr>
                <tr>
                    <th><label for="virtual_event_url">URL link to access the virtual event</label></th>
                    <td><input type="text" id="virtual_event_url" name="virtual_event_url" value="<?php echo esc_attr($event->virtual_event_url); ?>" required style="width: 90%;"/></td>
                </tr>
                <tr>
                    <th><label for="virtual_event_password">Password to access the virtual event</label></th>
                    <td><input type="text" id="virtual_event_password" name="virtual_event_password" value="<?php echo esc_attr($event->virtual_event_password); ?>" style="width: 90%;" /></td>
                </tr>
                <tr>
                    <th><label for="category">Category</label></th>
                    <td>
                        <select id="category" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category->category_id); ?>" 
                                    <?php echo ($category->category_id == $event->category_id) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($category->category_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th><label for="audience">Audience</label></th>
                    <td>
                        <select id="audience" name="audience_id" required>
                            <option value="">Select an audience</option>
                            <?php foreach ($audiences as $audience): ?>
                                <option value="<?php echo esc_attr($audience->audience_id); ?>" 
                                    <?php echo ($audience->audience_id == $event->audience_id) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($audience->audience_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th><label for="event_admin_email">Event Admin Email</label></th>
                    <td><input type="email" id="event_admin_email" name="event_admin_email" style="width: 90%;" value="<?php echo esc_attr($event->event_admin_email); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="extra_email_text">Extra text to be added to confirmation emails</label></th>
                    <td>
                        <textarea name="extra_email_text" id="extra_email_text" rows="3" style="width: 80%;"><?php echo esc_textarea($event->extra_email_text ?? ''); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th><label for="extra_event_url">Additional website or facebook link for the event</label></th>
                    <td><input type="text" id="extra_event_url" name="extra_event_url" value="<?php echo esc_attr($event->extra_event_url); ?>" required style="width: 90%;"/></td>
                </tr>
                <tr>
                    <th><label for="include_extra_event_url_in_email">Include the above additional link in emails?</label></th>
                    <td>
                        <input type="checkbox" id="include_extra_event_url_in_email" name="include_extra_event_url_in_email" <?php echo ($event->include_extra_event_url_in_email == 1) ? 'checked' : ''; ?>/>
                    </td>
                </tr>
                <tr>
                    <th><label for="include_special_notes">Allow users to add notes to their booking?</label></th>
                    <td>
                        <input type="checkbox" id="include_special_notes" name="include_special_notes" <?php echo ($event->include_special_notes == 1) ? 'checked' : ''; ?>/>
                    </td>
                </tr>
                <tr>
                    <th><label for="include_physical_address">Users must enter a physical address when booking?</label></th>
                    <td>
                        <input type="checkbox" id="include_physical_address" name="include_physical_address" <?php echo ($event->include_physical_address == 1) ? 'checked' : ''; ?>/>
                    </td>
                </tr>
                <tr>
                    <th><label for="include_zipcode">Users must enter a zipcode when booking?</label></th>
                    <td>
                        <input type="checkbox" id="include_zipcode" name="include_zipcode" <?php echo ($event->include_zipcode == 1) ? 'checked' : ''; ?>/>
                    </td>
                </tr>
                <tr>
                    <th><label for="costs">Costs</label></th>
                    <td id="costs-container">
                        <?php if ($costs): ?>
                            <?php foreach ($costs as $index => $cost): ?>
                                <div id="cost-group-<?php echo $index; ?>" class="cost-group">
                                    <p><strong>Cost <?php echo $index + 1; ?></strong></p>
                                    <input type="hidden" name="cost_id[]" value="<?php echo esc_attr($cost->cost_id); ?>" />
                                    <label for="cost_name_<?php echo $index; ?>">Cost Name</label>
                                    <input type="text" name="cost_name[]" id="cost_name_<?php echo $index; ?>" value="<?php echo esc_attr($cost->cost_name); ?>" required />

                                    <label for="cost_amount_<?php echo $index; ?>">Cost Amount</label>
                                    <input type="number" name="cost_amount[]" id="cost_amount_<?php echo $index; ?>" value="<?php echo esc_attr($cost->cost_amount); ?>" required step="0.01" min="0" />

                                    <label for="include_extra_info_<?php echo $index; ?>">Include extra info?</label>
                                    <input type="hidden" name="include_extra_info[<?php echo $index; ?>]" value="0">
                                    <input type="checkbox" id="include_extra_info_<?php echo $index; ?>" name="include_extra_info[<?php echo $index; ?>]" 
                                        value="1" <?php echo $cost->include_extra_info ? 'checked' : ''; ?> />

                                    <label for="extra_info_label_<?php echo $index; ?>">Extra Info Label</label>
                                    <input type="text" name="extra_info_label[]" id="extra_info_label_<?php echo $index; ?>" 
                                        value="<?php echo esc_attr($cost->extra_info_label); ?>" />


                                    <label for="cost_historic_<?php echo $index; ?>">Removed from Use</label>
                                    <input type="checkbox" name="cost_historic[]" id="cost_historic_<?php echo $index; ?>" <?php echo $cost->historic ? 'checked' : ''; ?> />
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No costs available for this event.</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <button type="button" id="add-cost" class="button">Add More Costs</button>
                    </td>
                </tr>

                <tr>
                    <th><label for="disclaimers">Disclaimers</label></th>
                    <td>
                        <?php foreach ($disclaimers as $disclaimer): ?>
                            <div class="disclaimer-item">
                                <input type="checkbox" name="disclaimer[]" value="<?php echo esc_attr($disclaimer->id); ?>" id="disclaimer_<?php echo esc_attr($disclaimer->id); ?>" checked />
                                <label for="disclaimer_<?php echo esc_attr($disclaimer->id); ?>">Include this disclaimer</label><br/>
                                <!-- Textarea for disclaimer text -->
                                <textarea name="disclaimer_text_<?php echo esc_attr($disclaimer->id); ?>" rows="3" style="width: 80%;"><?php echo esc_textarea($disclaimer->disclaimer ?? ''); ?></textarea>
                            </div>
                        <?php endforeach; ?>

                        <!-- Button to add new disclaimers -->
                        <button type="button" id="add-disclaimer-button" class="button">Add Disclaimer</button>
                        <div id="disclaimers-container"></div>
                    </td>
                </tr>

            </table>

            <?php wp_nonce_field('edit_event_action', 'event_nonce'); ?>
            <p class="submit">
                <input type="submit" class="button button-primary" value="Update Event" />
            </p>
        </form>
    </div>

    <script>
        // Add a new cost input group dynamically
        let costIndex = <?php echo count($costs); ?>;

        document.getElementById('add-cost').addEventListener('click', function() {
            const costContainer = document.getElementById('costs-container');
            const costGroup = document.createElement('div');
            costGroup.classList.add('cost-group');
            costGroup.id = 'cost-group-' + costIndex;

            costGroup.innerHTML = `
                <p><strong>Cost ${costIndex + 1}</strong></p>
                <label for="cost_name_${costIndex}">Cost Name</label>
                <input type="text" name="cost_name[]" id="cost_name_${costIndex}" required />

                <label for="cost_amount_${costIndex}">Cost Amount</label>
                <input type="number" name="cost_amount[]" id="cost_amount_${costIndex}" required step="0.01" min="0" />

                
                <label for="include_extra_info_${costCount}">Include extra info?</label>
                <input type="hidden" name="include_extra_info[${costCount}]" value="0"> 
                <input type="checkbox" id="include_extra_info_${costCount}" name="include_extra_info[${costCount}]" value="1" />


                <label for="extra_info_label_${costCount}">Extra Info Label</label>
                <input type="text" name="extra_info_label[]" id="extra_info_label_${costCount}" />

                <label for="cost_historic_${costIndex}">Removed from Use</label>
                <input type="checkbox" name="cost_historic[]" id="cost_historic_${costIndex}" />
            `;
            costContainer.appendChild(costGroup);
            costIndex++;
        });

        // For dynamically adding disclaimers
        disclaimerCount = 0;
        document.getElementById('add-disclaimer-button').addEventListener('click', function() {
            disclaimerCount++;
            let disclaimerHtml = `
                <div id="disclaimer-group-${disclaimerCount}" class="disclaimer-group">
                    <input type="checkbox" name="disclaimer[]" value="new_${disclaimerCount}" id="disclaimer_new_${disclaimerCount}" checked />
                    <label for="disclaimer_new_${disclaimerCount}">Include this disclaimer</label><br/>
                    <textarea name="disclaimer_text_new_${disclaimerCount}" rows="3" style="width: 80%;" placeholder="Enter disclaimer text here"></textarea>
                    
                    <button type="button" onclick="removeDisclaimer(${disclaimerCount})" class="button">Remove Disclaimer</button>
                </div>
            `;
            document.getElementById('disclaimers-container').insertAdjacentHTML('beforeend', disclaimerHtml);
        });

        function removeDisclaimer(disclaimerId) {
            const disclaimerGroup = document.getElementById('disclaimer-group-' + disclaimerId);
            disclaimerGroup.remove();
        }
    </script>
<?php
}

add_action('wp_ajax_check_cost_associations', 'check_cost_associations');
function check_cost_associations() {
    global $wpdb;
    
    // Verify the nonce for security
    check_ajax_referer('event_nonce', 'security');

    if (!isset($_POST['cost_id'])) {
        wp_send_json_error('Cost ID missing.');
    }
    
    $cost_id = intval($_POST['cost_id']);
    $table_name = "{$wpdb->prefix}leanwi_event_booking_costs";

    // Check for associated records in leanwi_event_booking_costs
    $associated_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE cost_id = %d", $cost_id));
    
    if ($associated_count > 0) {
        wp_send_json_success(['associated' => true]);
    } else {
        wp_send_json_success(['associated' => false]);
    }
}

/**************************************************************************************************
 * Categories
 **************************************************************************************************/

// Function to display the list of categories
function leanwi_event_categories_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_category';

    // Process display order update if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_display_order'])) {
        if (isset($_POST['display_order']) && is_array($_POST['display_order'])) {
            foreach ($_POST['display_order'] as $category_id => $display_order) {
                $category_id = intval($category_id);
                $display_order = intval($display_order);

                $wpdb->update(
                    $table_name,
                    ['display_order' => $display_order],
                    ['category_id' => $category_id],
                    ['%d'],
                    ['%d']
                );
            }
            echo '<div class="updated notice"><p>Display order updated successfully.</p></div>';
        }
    }

    // Display category list
    echo '<div class="wrap">';
    echo '<h1>Categories</h1>';

    echo '<a href="' . admin_url('admin.php?page=leanwi-event-add-category') . '" class="button button-primary">Add Category</a>';
    echo '<p> </p>'; // Space below the button before the category table

    echo '<form method="POST">';
    echo '<table class="wp-list-table widefat striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th scope="col">Category ID</th>';
    echo '<th scope="col">Category Name</th>';
    echo '<th scope="col">Display Order</th>';
    echo '<th scope="col">Historic</th>';
    echo '<th scope="col">Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Fetch categories
    $categories = fetch_categories();
    if (isset($categories['error'])) {
        echo '<tr><td colspan="5">' . esc_html($categories['error']) . '</td></tr>';
    } else {
        // Display each category in a row
        foreach ($categories['categories'] as $category) {
            echo '<tr>';
            echo '<td>' . esc_html($category['category_id']) . '</td>';
            echo '<td>' . esc_html($category['category_name']) . '</td>';
            echo '<td><input type="number" name="display_order[' . esc_attr($category['category_id']) . ']" value="' . esc_attr($category['display_order']) . '" style="width: 60px;"></td>';
            echo '<td>' . ($category['historic'] ? 'Yes' : 'No') . '</td>';
            echo '<td>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=leanwi-event-edit-category&category_id=' . esc_attr($category['category_id']))) . '" class="button">Edit</a> ';
            echo '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody>';
    echo '</table>';

    echo '<p><input type="submit" name="save_display_order" value="Save Display Order" class="button button-primary"></p>';
    echo '</form>';
    echo '</div>';
}

// Function to get categories
function fetch_categories() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_category';

    // Fetch categories
    $categories = $wpdb->get_results("SELECT category_id, category_name, display_order, historic FROM $table_name", ARRAY_A);

    if (empty($categories)) {
        return ['error' => 'No categories found.'];
    } else {
        return ['categories' => $categories];
    }
}

function leanwi_event_add_category_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_category';

    // Handle form submission
    if (isset($_POST['add_event_category'])) {
        // Find the max display_order and increment
        $max_order = $wpdb->get_var("SELECT MAX(display_order) FROM $table_name");
        $new_order = ($max_order !== null) ? $max_order + 1 : 1;

        $category_name = wp_kses_post($_POST['category_name']);
        $category_name = wp_unslash($category_name);
        
        $wpdb->insert(
            $table_name,
            ['category_name' => $category_name, 
             'display_order' => $new_order,
             'historic' => isset($_POST['historic']) ? 1 : 0],
            ['%s', '%d', '%d']
        );
        echo '<div class="updated"><p>Category added successfully.</p></div>';
    }

    // Display the add category form
    echo '<div class="wrap">';
    echo '<h1>Add Event Category</h1>';
    echo '<form method="POST">';
    echo '<p>Category Name: <input type="text" name="category_name" required></p>';
    echo '<p>Historic: <input type="checkbox" name="historic"></p>';
    echo '<p><input type="submit" name="add_event_category" value="Add Category" class="button button-primary"></p>';
    echo '</form>';
    echo '</div>';
}

// Function to handle editing of a category
function leanwi_event_edit_category_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_category';

    // Handle the form submission to update the category
    if (isset($_POST['update_event_category'])) {
        $category_id = intval($_POST['category_id']);
        $category_name = wp_kses_post($_POST['category_name']);
        $category_name = wp_unslash($category_name);
        $historic = isset($_POST['historic']) ? 1 : 0; // Check if the "Historic" checkbox is checked

        // Update the category in the database
        $wpdb->update(
            $table_name,
            [
                'category_name' => $category_name,
                'historic' => $historic,
            ],
            ['category_id' => $category_id],
            ['%s', '%d'],
            ['%d']
        );

        echo '<div class="updated"><p>Category updated successfully.</p></div>';
    }

    // Check if a category ID is provided for editing
    if (isset($_GET['category_id'])) {
        $category_id = intval($_GET['category_id']);
        $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE category_id = %d", $category_id));

        if ($category) {
            // Display form to edit the category
            echo '<div class="wrap">';
            echo '<h1>Edit Category</h1>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="category_id" value="' . esc_attr($category->category_id) . '">';

            // Display the category name input
            echo '<p>Category Name: <input type="text" name="category_name" value="' . esc_attr($category->category_name) . '" class="regular-text"></p>';

            // Display the checkbox for marking a category as historic
            echo '<p>';
            echo '<label><input type="checkbox" name="historic" ' . checked($category->historic, 1, false) . '> Historic</label>';
            echo '</p>';

            // Submit button to update the category
            echo '<p><input type="submit" name="update_event_category" value="Save Changes" class="button button-primary"></p>';
            echo '</form>';
            echo '</div>';
        } else {
            // Display a message if the category is not found
            echo '<div class="error"><p>Category not found.</p></div>';
        }
    } else {
        // Redirect back if no category ID is provided
        echo '<div class="error"><p>No category ID provided.</p></div>';
    }
}

/**************************************************************************************************
 * Audiences
 **************************************************************************************************/

// Function to display the list of audiences
function leanwi_event_audiences_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_audience';

    // Process display order update if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_display_order'])) {
        if (isset($_POST['display_order']) && is_array($_POST['display_order'])) {
            foreach ($_POST['display_order'] as $audience_id => $display_order) {
                $audience_id = intval($audience_id);
                $display_order = intval($display_order);

                $wpdb->update(
                    $table_name,
                    ['display_order' => $display_order],
                    ['audience_id' => $audience_id],
                    ['%d'],
                    ['%d']
                );
            }
            echo '<div class="updated notice"><p>Display order updated successfully.</p></div>';
        }
    }

    // Display audience list
    echo '<div class="wrap">';
    echo '<h1>Audiences</h1>';

    echo '<a href="' . admin_url('admin.php?page=leanwi-event-add-audience') . '" class="button button-primary">Add Audience</a>';
    echo '<p> </p>'; // Space below the button before the audience table

    echo '<form method="POST">';
    echo '<table class="wp-list-table widefat striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th scope="col">Audience ID</th>';
    echo '<th scope="col">Audience Name</th>';
    echo '<th scope="col">Display Order</th>';
    echo '<th scope="col">Historic</th>';
    echo '<th scope="col">Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Fetch audiences
    $audiences = fetch_audiences();
    if (isset($audiences['error'])) {
        echo '<tr><td colspan="5">' . esc_html($audiences['error']) . '</td></tr>';
    } else {
        // Display each audience in a row
        foreach ($audiences['audiences'] as $audience) {
            echo '<tr>';
            echo '<td>' . esc_html($audience['audience_id']) . '</td>';
            echo '<td>' . esc_html($audience['audience_name']) . '</td>';
            echo '<td><input type="number" name="display_order[' . esc_attr($audience['audience_id']) . ']" value="' . esc_attr($audience['display_order']) . '" style="width: 60px;"></td>';
            echo '<td>' . ($audience['historic'] ? 'Yes' : 'No') . '</td>';
            echo '<td>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=leanwi-event-edit-audience&audience_id=' . esc_attr($audience['audience_id']))) . '" class="button">Edit</a> ';
            echo '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody>';
    echo '</table>';

    echo '<p><input type="submit" name="save_display_order" value="Save Display Order" class="button button-primary"></p>';
    echo '</form>';
    echo '</div>';
}

// Function to get audiences
function fetch_audiences() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_audience';

    // Fetch audiences
    $audiences = $wpdb->get_results("SELECT audience_id, audience_name, display_order, historic FROM $table_name", ARRAY_A);

    if (empty($audiences)) {
        return ['error' => 'No audiences found.'];
    } else {
        return ['audiences' => $audiences];
    }
}

function leanwi_event_add_audience_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_audience';

   // Handle form submission
   if (isset($_POST['add_audience'])) {
        // Find the max display_order and increment
        $max_order = $wpdb->get_var("SELECT MAX(display_order) FROM $table_name");
        $new_order = ($max_order !== null) ? $max_order + 1 : 1;
        $audience_name = wp_kses_post($_POST['audience_name']);
        $audience_name = wp_unslash($audience_name);

        $wpdb->insert(
            $table_name,
            ['audience_name' => $audience_name,
            'display_order' => $new_order,
            'historic' => isset($_POST['historic']) ? 1 : 0],
            ['%s', '%d', '%d']
        );
        echo '<div class="updated"><p>Audience added successfully.</p></div>';
    }

    // Display the add audience form
    echo '<div class="wrap">';
    echo '<h1>Add Audience</h1>';
    echo '<form method="POST">';
    echo '<p>Audience Name: <input type="text" name="audience_name" required></p>';
    echo '<p>Historic: <input type="checkbox" name="historic"></p>';
    echo '<p><input type="submit" name="add_audience" value="Add Audience" class="button button-primary"></p>';
    echo '</form>';
    echo '</div>';
}

// Function to handle editing of an audience
function leanwi_event_edit_audience_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_audience';

    // Handle the form submission to update the audience
    if (isset($_POST['update_audience'])) {
        $audience_id = intval($_POST['audience_id']);
        $audience_name = wp_kses_post($_POST['audience_name']);
        $audience_name = wp_unslash($audience_name);
        $historic = isset($_POST['historic']) ? 1 : 0; // Check if the "Historic" checkbox is checked

        // Update the audience in the database
        $wpdb->update(
            $table_name,
            [
                'audience_name' => $audience_name,
                'historic' => $historic,
            ],
            ['audience_id' => $audience_id],
            ['%s', '%d'],
            ['%d']
        );

        echo '<div class="updated"><p>Audience updated successfully.</p></div>';
    }

    // Check if an audience ID is provided for editing
    if (isset($_GET['audience_id'])) {
        $audience_id = intval($_GET['audience_id']);
        $audience = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE audience_id = %d", $audience_id));

        if ($audience) {
            // Display form to edit the audience
            echo '<div class="wrap">';
            echo '<h1>Edit Audience</h1>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="audience_id" value="' . esc_attr($audience->audience_id) . '">';

            // Display the audience name input
            echo '<p>Audience Name: <input type="text" name="audience_name" value="' . esc_attr($audience->audience_name) . '" class="regular-text"></p>';

            // Display the checkbox for marking an audience as historic
            echo '<p>';
            echo '<label><input type="checkbox" name="historic" ' . checked($audience->historic, 1, false) . '> Historic</label>';
            echo '</p>';

            // Submit button to update the audience
            echo '<p><input type="submit" name="update_audience" value="Save Changes" class="button button-primary"></p>';
            echo '</form>';
            echo '</div>';
        } else {
            // Display a message if the audience is not found
            echo '<div class="error"><p>Audience not found.</p></div>';
        }
    } else {
        // Redirect back if no audience ID is provided
        echo '<div class="error"><p>No audience ID provided.</p></div>';
    }
}

/**************************************************************************************************
 * disclaimers
 **************************************************************************************************/

// Function to display the list of disclaimers
function leanwi_event_disclaimers_page() {
    
    // Display disclaimers list
    echo '<div class="wrap">';
    echo '<h1>Disclaimers</h1>';

    echo '<a href="' . admin_url('admin.php?page=leanwi-event-add-disclaimer') . '" class="button button-primary">Add Disclaimer</a>';
    echo '<p> </p>'; // Space below the button before the disclaimer table

    echo '<table class="wp-list-table widefat striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th scope="col">ID</th>';
    echo '<th scope="col" width="75%">Disclaimer</th>';
    echo '<th scope="col" style="text-align: right; padding-right: 40px;">Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Fetch disclaimers
    $disclaimers = fetch_disclaimers();
    if (empty($disclaimers)) {
        echo '<tr><td colspan="3">No disclaimers found.</td></tr>';
    } else {
        // Display each disclaimer in a row
        foreach ($disclaimers as $disclaimer) {
            echo '<tr>';
            echo '<td>' . esc_html($disclaimer['id']) . '</td>';
            echo '<td>' . esc_html($disclaimer['disclaimer']) . '</td>';
            echo '<td style="text-align: right;">';
            echo '<a href="' . esc_url(admin_url('admin.php?page=leanwi-event-edit-disclaimer&id=' . esc_attr($disclaimer['id']))) . '" class="button">Edit</a> ';
            echo '<a href="' . esc_url(admin_url('admin.php?page=leanwi-event-delete-disclaimer&id=' . esc_attr($disclaimer['id']))) . '" class="button" onclick="return confirm(\'Are you sure you want to delete this disclaimer?\');">Delete</a>';
            echo '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

// Function to get disclaimers
function fetch_disclaimers() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_saved_disclaimer';

    // Fetch disclaimers and check for database errors
    $disclaimers = $wpdb->get_results("SELECT id, disclaimer FROM $table_name", ARRAY_A);
    if ($wpdb->last_error) {
        return ['error' => $wpdb->last_error];
    }

    return $disclaimers ?: []; // Return an empty array if no results are found
}

// Function to handle deletion
function leanwi_event_delete_disclaimer_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_saved_disclaimer';

    // Check if an ID is provided for deleting
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $wpdb->delete(
            $table_name,
            ['id' => $id],
            ['%d']
        );
        echo '<div class="deleted"><p>Disclaimer deleted successfully.</p></div>';
    } else {
        // Handle the case where no ID is provided
        echo '<div class="error"><p>No Disclaimer ID provided for deletion.</p></div>';
    }
}

function leanwi_event_add_disclaimer_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_saved_disclaimer';

    // Handle form submission
    if (isset($_POST['add_disclaimer'])) {
        $wpdb->insert(
            $table_name,
            ['disclaimer' => sanitize_text_field($_POST['disclaimer'])],
            ['%s']
        );
        echo '<div class="updated"><p>Disclaimer added successfully.</p></div>';
    }

    // Display the add Disclaimer form
    echo '<div class="wrap">';
    echo '<h1>Add Disclaimer</h1>';
    echo '<form method="POST">';
    echo '<p>Disclaimer:<br><textarea name="disclaimer" rows="5" cols="50" required></textarea></p>';
    echo '<p><input type="submit" name="add_disclaimer" value="Add Disclaimer" class="button button-primary"></p>';
    echo '</form>';
    echo '</div>';
}

// Function to handle editing of an disclaimer
function leanwi_event_edit_disclaimer_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'leanwi_event_saved_disclaimer';

    // Handle the form submission to update the disclaimer
    if (isset($_POST['update_disclaimer'])) {
        $id = intval($_POST['id']);
        $disclaimer = sanitize_text_field($_POST['disclaimer']);

        // Update the disclaimer in the database
        $wpdb->update(
            $table_name,
            ['disclaimer' => $disclaimer],
            ['id' => $id],
            ['%s'],
            ['%d']
        );

        echo '<div class="notice notice-success"><p>Disclaimer updated successfully.</p></div>';
    }

    // Check if an ID is provided for editing
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $disclaimer = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

        if ($disclaimer) {
            // Display form to edit the disclaimer
            echo '<div class="wrap">';
            echo '<h1>Edit Disclaimer</h1>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="id" value="' . esc_attr($disclaimer->id) . '">';

            // Display the disclaimer input
            echo '<p>Disclaimer:<br><textarea name="disclaimer" rows="5" cols="50" required>' . esc_textarea($disclaimer->disclaimer ?? '') . '</textarea></p>';
            
            // Submit button to update the disclaimer
            echo '<p><input type="submit" name="update_disclaimer" value="Save Changes" class="button button-primary"></p>';
            echo '</form>';
            echo '</div>';
        } else {
            // Display a message if the disclaimer is not found
            echo '<div class="notice notice-error"><p>Disclaimer not found.</p></div>';
        }
    } else {
        // Display a message if no ID is provided
        echo '<div class="notice notice-error"><p>No ID provided.</p></div>';
    }
}


/**************************************************************************************************
 * Reporting
 **************************************************************************************************/

// Function to display the reporting functionality
function leanwi_event_reports_page() {
    //Fetch Events
    $events_response = fetch_events();
    if (isset($events_response['error'])) {
        echo '<tr><td colspan="6">' . esc_html($events_response['error']) . '</td></tr>';
        return; // Exit early if there's an error
    }

    // Ensure events is set and is an array
    $events = isset($events_response['events']) ? $events_response['events'] : [];

    // Define the directory path for reports
    $upload_dir = wp_upload_dir();
    $reports_dir = $upload_dir['basedir'] . '/leanwi_event_reports/';
    
    // Get report files
    $report_files = glob($reports_dir . '*.csv');
    $report_count = count($report_files);

    ?>
    <div class="wrap">
        <h1>Reports</h1>
        <form id="leanwi-event-attendance-report-form" method="post" action="<?php echo plugins_url('LEANWI-Book-An-Event/php/plugin/generate-attendance-report.php'); ?>">
            <h2>Event Attendance Reporting</h2>
            <?php wp_nonce_field('leanwi_event_generate_report', 'leanwi_event_generate_report_nonce'); ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                <label for="event_info">Select Event:</label>
                    <select id="event_info" name="event_info">
                        <option value="">-- All Events --</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo esc_attr($event['event_data_id']) . '|' . esc_attr($event['title']); ?>">
                                <?php echo esc_html($event['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="include_category">Include Category:</label>
                    <input type="checkbox" id="include_category" name="include_category" value="yes">
                </div>
                <div class="form-group">
                    <label for="include_audience">Include Audience:</label>
                    <input type="checkbox" id="include_audience" name="include_audience" value="yes">
                </div>
            </div>
            <div class="form-row">
                <input type="submit" value="Generate Attendance Report" class="button button-primary">
            </div>
        </form>
        <hr>

        <form id="leanwi-event-payment-report-form" method="post" action="<?php echo plugins_url('LEANWI-Book-An-Event/php/plugin/generate-payment-report.php'); ?>">
            <h2>Payment Reporting</h2>
            <?php wp_nonce_field('leanwi_event_generate_report', 'leanwi_event_generate_report_nonce'); ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                <label for="event_info">Select Event:</label>
                    <select id="event_info" name="event_info">
                        <option value="">-- All Events --</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo esc_attr($event['event_data_id']) . '|' . esc_attr($event['title']); ?>">
                                <?php echo esc_html($event['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="include_paid">Include Paid:</label>
                    <input type="checkbox" id="include_paid" name="include_paid" value="yes">
                </div>
                <div class="form-group">
                    <label for="include_unpaid">Include Unpaid:</label>
                    <input type="checkbox" id="include_unpaid" name="include_unpaid" value="yes">
                </div>
            </div>
            <div class="form-row">
                <input type="submit" value="Generate Payment Report" class="button button-primary">
            </div>
        </form>
        <hr>

        <!-- Handle form submission so we can update the number of reports we have on the server after the report has been created -->
        <script type="text/javascript">
            function handleFormSubmit(event) {
                
                const form = this;
                const formData = new FormData(form);
                formData.append('_ajax_nonce', '<?php echo wp_create_nonce('leanwi_event_generate_report_nonce'); ?>');

                // Disable the submit button to prevent double submission
                const submitButton = form.querySelector("input[type='submit']");
                submitButton.disabled = true;

                // Perform AJAX request to generate the report
                fetch(form.action, {
                    method: "POST",
                    body: formData,
                })
                .then(response => {
                    if (!response.ok) throw new Error("Failed to generate report.");
                    return response.text();
                })
                .then(() => {
                    return fetch("<?php echo admin_url('admin-ajax.php'); ?>?action=leanwi_event_get_report_count", {
                        method: "GET",
                        credentials: "same-origin",
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(".purge-reports-section p").innerText = 
                            `You currently have ${data.data.report_count} reports sitting on the server.`;
                    }
                })
                .catch(error => alert(error.message))
                .finally(() => {
                    submitButton.disabled = false; // Re-enable the button after completion
                });
            };

            // Attach the event listener to both forms
            document.getElementById("leanwi-event-attendance-report-form").addEventListener("submit", handleFormSubmit);
            document.getElementById("leanwi-event-payment-report-form").addEventListener("submit", handleFormSubmit);

        </script>

        <div class="purge-reports-section">
            <p>You currently have <?php echo esc_html($report_count); ?> reports sitting on the server.</p>
            <form method="post" action="" onsubmit="return confirmPurge();">
                <?php wp_nonce_field('leanwi_event_purge_reports', 'leanwi_event_purge_reports_nonce'); ?>
                <input type="hidden" name="purge_event_reports" value="1">
                <input type="submit" value="Purge Old Reports" class="button button-secondary">
            </form>
        </div>

        <script type="text/javascript">
            function confirmPurge() {
                return confirm("Are you sure you want to purge all old reports? This action cannot be undone.");
            }
        </script>
    </div>

    <style>
        .form-row {
            display: flex;
            align-items: center; /* Center items vertically */
            margin-bottom: 15px; /* Space between rows */
        }
        .form-group {
            margin-right: 20px; /* Space between form elements */
        }
        .form-group label {
            display: block; /* Make label take full width */
            margin-bottom: 5px; /* Space between label and input */
        }
        #leanwi-event-attendance-report-form input[type="date"] {
            padding: 5px; /* Padding inside the date input */
            width: 150px; /* Set a fixed width for the date inputs */
        }
        /* Adjust the checkbox label alignment */
        .form-group input[type="checkbox"] {
            margin-left: 5px; /* Space between checkbox and label */
        }
        /* Style the dropdown */
        #event_info {
            padding: 5px; /* Padding inside the dropdown */
            width: 150px; /* Set a fixed width for the dropdown */
        }
        .purge-reports-section {
            margin-top: 20px;
        }
    </style>
    <?php
}

// Handle report purge action
function leanwi_event_purge_reports() {
    if (!isset($_POST['purge_event_reports']) || $_POST['purge_event_reports'] != '1') {
        return;
    }
    // Verify the nonce
    if (!isset($_POST['leanwi_event_purge_reports_nonce']) || !wp_verify_nonce($_POST['leanwi_event_purge_reports_nonce'], 'leanwi_event_purge_reports')) {
        wp_die('Nonce verification failed. Please reload the page and try again.');
    }
    // Define the directory path for reports
    $upload_dir = wp_upload_dir();
    $reports_dir = $upload_dir['basedir'] . '/leanwi_event_reports/';
    
    // Get all report files in the directory
    $report_files = glob($reports_dir . '*.csv');
    
    // Delete each file
    foreach ($report_files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    
    // Redirect back to the reports page to refresh the count
    wp_safe_redirect(admin_url('admin.php?page=leanwi-book-an-event-reports'));
    exit;
}   
add_action('admin_init', __NAMESPACE__ . '\\leanwi_event_purge_reports');
//add_action('admin_post_leanwi_purge_reports', 'leanwi_event_purge_reports');

// AJAX handler for fetching updated report count
function leanwi_event_get_report_count() {
    // Check if the user has the required permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
    }

    // Get the directory path for reports
    $upload_dir = wp_upload_dir();
    $reports_dir = $upload_dir['basedir'] . '/leanwi_event_reports/';
    
    // Count the reports
    $report_files = glob($reports_dir . '*.csv');
    $report_count = count($report_files);

    // Return the count
    wp_send_json_success(['report_count' => $report_count]);
}
add_action('wp_ajax_leanwi_event_get_report_count', __NAMESPACE__ . '\\leanwi_event_get_report_count');


/**************************************************************************************************
 * Settings
 **************************************************************************************************/

// Function to display settings page
function leanwi_event_settings_page() {
    ?>
    <div class="wrap">
        <h1>Book-An-Event Settings</h1>
        <form method="post" action="options.php">
            <?php
                // Output security fields for the registered setting
                settings_fields('leanwi_event_plugin_settings_group');
                
                // Output setting sections and their fields
                do_settings_sections('leanwi-book-an-event-settings');
                
                // Submit button
                submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Function to register settings
function leanwi_event_register_settings() {
    
    register_setting('leanwi_event_plugin_settings_group', 'leanwi_event_send_admin_booking_email');
    register_setting('leanwi_event_plugin_settings_group', 'leanwi_event_admin_email_address');
    register_setting('leanwi_event_plugin_settings_group', 'leanwi_event_email_from_name');
    register_setting('leanwi_event_plugin_settings_group', 'leanwi_event_feedback_form_link');

    // Register settings for the three button types
    $button_types = ['button_type_1', 'button_type_2', 'button_type_3'];
    $color_settings = ['border_color', 'bg_color', 'text_color'];

    foreach ($button_types as $button_type) {
        foreach ($color_settings as $color) {
            register_setting('leanwi_event_plugin_settings_group', "leanwi_event_{$button_type}_{$color}");
        }
    }

    // Register settings for reCAPTCHA enable, site key, and secret key
    register_setting('leanwi_event_plugin_settings_group', 'leanwi_event_enable_recaptcha');
    register_setting('leanwi_event_plugin_settings_group', 'leanwi_event_recaptcha_site_key');
    register_setting('leanwi_event_plugin_settings_group', 'leanwi_event_recaptcha_secret_key');

    // Add a section to the settings page
    add_settings_section(
        'leanwi_event_main_section',          // Section ID
        'Book-An-Event Settings',         // Section title
        null,                           // Callback function (optional)
        'leanwi-book-an-event-settings'   // Page slug where the section will be displayed
    );

    // Add whether admin should be sent a copy of the booking email field
    add_settings_field(
        'leanwi_event_send_admin_booking_email',       // Field ID
        'Send Admin a Copy of the Booking Email?',     // Label for the field
        __NAMESPACE__ . '\\leanwi_event_send_admin_booking_email_field', // Function to display the dropdown
        'leanwi-book-an-event-settings',  // Page slug
        'leanwi_event_main_section'           // Section ID
    );

    // Add Admin email address field
    add_settings_field(
        'leanwi_event_admin_email_address',  // Field ID
        'Email Address for Booking Emails',         // Label for the field
        __NAMESPACE__ . '\\leanwi_event_admin_email_address_field', // Function to display the input
        'leanwi-book-an-event-settings',  // Page slug
        'leanwi_event_main_section'           // Section ID
    );

    // Add email from name field
    add_settings_field(
        'leanwi_event_email_from_name',  // Field ID
        'Email From Name',         // Label for the field
        __NAMESPACE__ . '\\leanwi_event_email_from_name_field', // Function to display the input
        'leanwi-book-an-event-settings',  // Page slug
        'leanwi_event_main_section'           // Section ID
    );

    // Add Admin email address field
    add_settings_field(
        'leanwi_event_feedback_form_link',  // Field ID
        'URL Link to Feedback Form',         // Label for the field
        __NAMESPACE__ . '\\leanwi_event_feedback_form_link_field', // Function to display the input
        'leanwi-book-an-event-settings',  // Page slug
        'leanwi_event_main_section'           // Section ID
    );

    // Add a settings section for the button colors
    add_settings_section(
        'leanwi_event_button_colors_section',
        'Button Color Settings',
        '__return_false',
        'leanwi-book-an-event-settings'
    );

    // Add the settings field for the table
    add_settings_field(
        'leanwi_event_button_colors',
        'Customize Button Colors',
        __NAMESPACE__ . '\\leanwi_event_render_button_color_settings',
        'leanwi-book-an-event-settings',
        'leanwi_event_button_colors_section'
    );

    /*
    // Add border color for highlighted buttons field
    add_settings_field(
        'leanwi_event_highlighted_button_border_color',  // Field ID
        'Border color for highlighted buttons',         // Label for the field
        __NAMESPACE__ . '\\leanwi_event_highlighted_button_border_color_field', // Function to display the input
        'leanwi-book-an-event-settings',  // Page slug
        'leanwi_event_main_section'           // Section ID
    );

    // Add bg color for highlighted buttons field
    add_settings_field(
        'leanwi_event_highlighted_button_bg_color',  // Field ID
        'Background color for highlighted buttons',         // Label for the field
        __NAMESPACE__ . '\\leanwi_event_highlighted_button_bg_color_field', // Function to display the input
        'leanwi-book-an-event-settings',  // Page slug
        'leanwi_event_main_section'           // Section ID
    );

    // Add text color for highlighted buttons field
    add_settings_field(
        'leanwi_event_highlighted_button_text_color',  // Field ID
        'Text color for highlighted buttons',         // Label for the field
        __NAMESPACE__ . '\\leanwi_event_highlighted_button_text_color_field', // Function to display the input
        'leanwi-book-an-event-settings',  // Page slug
        'leanwi_event_main_section'           // Section ID
    );
*/
    // Add field to enable/disable reCAPTCHA
    add_settings_field(
        'leanwi_event_enable_recaptcha',
        'Enable reCAPTCHA',
        __NAMESPACE__ . '\\leanwi_event_enable_recaptcha_field',
        'leanwi-book-an-event-settings',
        'leanwi_event_main_section'
    );

    // Add field for reCAPTCHA site key
    add_settings_field(
        'leanwi_event_recaptcha_site_key',
        'reCAPTCHA Site Key',
        __NAMESPACE__ . '\\leanwi_event_recaptcha_site_key_field',
        'leanwi-book-an-event-settings',
        'leanwi_event_main_section'
    );

    // Add field for reCAPTCHA secret key
    add_settings_field(
        'leanwi_event_recaptcha_secret_key',
        'reCAPTCHA Secret Key',
        __NAMESPACE__ . '\\leanwi_event_recaptcha_secret_key_field',
        'leanwi-book-an-event-settings',
        'leanwi_event_main_section'
    );
}

// Hook the settings registration function
add_action('admin_init', __NAMESPACE__ . '\\leanwi_event_register_settings');

// Function to display 'Send admin a booking email' dropdown
function leanwi_event_send_admin_booking_email_field() {
    $value = get_option('leanwi_event_send_admin_booking_email', 'no'); // Default to 'no' if no value is set
    ?>
    <select id="leanwi_event_send_admin_booking_email" name="leanwi_event_send_admin_booking_email">
        <option value="yes" <?php selected($value, 'yes'); ?>>Yes</option>
        <option value="no" <?php selected($value, 'no'); ?>>No</option>
    </select>
    <?php
}

// Function to display the admin email address input
function leanwi_event_admin_email_address_field() {
    $value = get_option('leanwi_event_admin_email_address', ''); // Get saved value or default to an empty string
    echo '<input type="email" id="leanwi_event_admin_email_address" name="leanwi_event_admin_email_address" value="' . esc_attr($value) . '"  style="width: 75%;"/>';
}

// Function to display the email from name input
function leanwi_event_email_from_name_field() {
    $value = get_option('leanwi_event_email_from_name', 'Library Events Team'); // Get saved value or default to an empty string
    echo '<input type="text" id="leanwi_event_email_from_name" name="leanwi_event_email_from_name" value="' . esc_attr($value) . '"  style="width: 75%;"/>';
}

// Function to display the admin email address input
function leanwi_event_feedback_form_link_field() {
    $value = get_option('leanwi_event_feedback_form_link', ''); // Get saved value or default to an empty string
    echo '<input type="url" id="leanwi_event_feedback_form_link" name="leanwi_event_feedback_form_link" value="' . esc_attr($value) . '"  style="width: 75%;"/>';
}

// Function to render the table
function leanwi_event_render_button_color_settings() {
    $buttons = [
        'button_type_1' => 'Button 1 (Find)',
        'button_type_2' => 'Button 2 (Signup / Submit)',
        'button_type_3' => 'Button 3 (Delete)',
    ];

    $colors = [
        'border' => ['label' => 'Border color', 'default' => '#000000'],  // Black
        'bg' => ['label' => 'Background color', 'default' => '#007BFF'], // Blue
        'text' => ['label' => 'Text color', 'default' => '#FFFFFF'],      // White
    ];

    echo '<table class="form-table"><thead><tr><th></th>'; // Empty top-left cell
    foreach ($buttons as $key => $label) {
        echo '<th>' . esc_html($label) . '</th>';
    }
    echo '</tr></thead><tbody>';

    foreach ($colors as $colorKey => $colorData) {
        echo '<tr>';
        echo '<th>' . esc_html($colorData['label']) . '</th>';
        foreach ($buttons as $buttonKey => $buttonLabel) {
            $option_name = 'leanwi_event_' . $buttonKey . '_' . $colorKey . '_color';
            $value = get_option($option_name, $colorData['default']); // Use specific default color
            echo '<td><input type="color" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '"></td>';
        }
        echo '</tr>';
    }

    echo '</tbody></table>';
}

/*
// Function to display the highlighted border color input
function leanwi_event_highlighted_button_border_color_field() {
    $value = get_option('leanwi_event_highlighted_button_border_color', '#ff9800'); // Get saved value or default to this hex vaue
    echo '<input type="color" id="leanwi_event_highlighted_button_border_color" name="leanwi_event_highlighted_button_border_color" value="' . esc_attr($value) . '" />';
}

// Function to display the highlighted bg color input
function leanwi_event_highlighted_button_bg_color_field() {
    $value = get_option('leanwi_event_highlighted_button_bg_color', '#ffe0b3'); // Get saved value or default to this hex vaue
    echo '<input type="color" id="leanwi_event_highlighted_button_bg_color" name="leanwi_event_highlighted_button_bg_color" value="' . esc_attr($value) . '" />';
}

// Function to display the highlighted bg color input
function leanwi_event_highlighted_button_text_color_field() {
    $value = get_option('leanwi_event_highlighted_button_text_color', '#000000'); // Get saved value or default to this hex vaue
    echo '<input type="color" id="leanwi_event_highlighted_button_text_color" name="leanwi_event_highlighted_button_text_color" value="' . esc_attr($value) . '" />';
    echo '<hr style="margin-top: 40px; border: 1px solid #ccc;">'; // Adds a horizontal line before the reCAPTCHA fields
}
*/
// Function to display 'Enable reCAPTCHA' dropdown
function leanwi_event_enable_recaptcha_field() {
    $value = get_option('leanwi_event_enable_recaptcha', 'no'); // Default to 'no' if not set
    ?>
    <select id="leanwi_event_enable_recaptcha" name="leanwi_event_enable_recaptcha">
        <option value="yes" <?php selected($value, 'yes'); ?>>Yes</option>
        <option value="no" <?php selected($value, 'no'); ?>>No</option>
    </select>
    <?php
}

// Function to display the reCAPTCHA site key input
function leanwi_event_recaptcha_site_key_field() {
    $value = get_option('leanwi_event_recaptcha_site_key', ''); // Get saved value or default to an empty string
    echo '<input type="password" id="leanwi_event_recaptcha_site_key" name="leanwi_event_recaptcha_site_key" value="' . esc_attr($value) . '" style="width: 75%;" />';
}

// Function to display the reCAPTCHA secret key input
function leanwi_event_recaptcha_secret_key_field() {
    $value = get_option('leanwi_event_recaptcha_secret_key', ''); // Get saved value or default to an empty string
    echo '<input type="password" id="leanwi_event_recaptcha_secret_key" name="leanwi_event_recaptcha_secret_key" value="' . esc_attr($value) . '" style="width: 75%;" />';
    echo '<hr style="margin-top: 40px; margin-bottom: 20px; border: 1px solid #ccc;">'; // Adds a horizontal line before the reCAPTCHA fields
}


/**************************************************************************************************
 * Event Staff Management Page
 **************************************************************************************************/

 function leanwi_event_staff_page() {
    // Ensure only administrators can access this page.
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Handle role assignment form submission.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leanwi_assign_event_staff'])) {
        $user_id = intval($_POST['user_id']);
        $user = get_user_by('ID', $user_id);
        if ($user) {
            $user->add_role('event_staff');
            echo '<div class="updated"><p>User assigned the "Event Staff" role successfully.</p></div>';
        } else {
            echo '<div class="error"><p>Invalid user selected.</p></div>';
        }
    }

    // Handle role unassignment form submission.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leanwi_unassign_event_staff'])) {
        $user_id = intval($_POST['leanwi_unassign_event_staff']);
        $user = get_user_by('ID', $user_id);
        if ($user) {
            $user->remove_role('event_staff');
            echo '<div class="updated"><p>User unassigned the "Event Staff" role successfully.</p></div>';
        } else {
            echo '<div class="error"><p>Invalid user selected for unassignment.</p></div>';
        }
    }

    // Display the user assignment form.
    echo '<div class="wrap">';
    echo '<h1>Manage Event Staff</h1>';
    echo '<form method="post">';
    echo '<label for="user_id">Select User:</label>';
    echo '<select name="user_id" id="user_id">';
    $users = get_users();
    foreach ($users as $user) {
        echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
    }
    echo '</select>';
    echo '<button type="submit" name="leanwi_assign_event_staff" class="button-primary">Assign Event Staff Role</button>';
    echo '</form>';

    // List users with the "event_staff" role and provide unassignment option.
    $staff_users = get_users(['role' => 'event_staff']);
    if (!empty($staff_users)) {
        echo '<h2>Current Event Staff</h2>';
        echo '<ul>';
        foreach ($staff_users as $staff_user) {
            echo '<li>';
            echo esc_html($staff_user->display_name) . ' (' . esc_html($staff_user->user_email) . ')';
            echo ' <form method="post" style="display:inline;">';
            echo '<input type="hidden" name="leanwi_unassign_event_staff" value="' . esc_attr($staff_user->ID) . '">';
            echo '<button type="submit" class="button-secondary">Remove</button>';
            echo '</form>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No users have been assigned the "Event Staff" role yet.</p>';
    }

    echo '</div>';
}


/**************************************************************************************************
 * Role Registration on Plugin Activation
 **************************************************************************************************/

 function leanwi_register_event_staff_role() {
    add_role(
        'event_staff',
        __('Event Staff', 'leanwi-book-an-event'), // Added text domain for translation.
        [
            'read' => true, // Allow basic dashboard access.
            'manage_staff_event_pages' => true, // Allow management of staff pages and use of staff functionality on booking event pages
        ]
    );
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\leanwi_register_event_staff_role');

// Ensure the "event_staff" role exists for backward compatibility on plugin load.
add_action('init', function() {
    if (!get_role('event_staff')) {
        leanwi_register_event_staff_role();
    }
});
