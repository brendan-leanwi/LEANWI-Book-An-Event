<?php
// Include WordPress functions
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Verify the nonce before processing the form
if (!isset($_POST['leanwi_event_generate_report_nonce']) || !wp_verify_nonce($_POST['leanwi_event_generate_report_nonce'], 'leanwi_event_generate_report')) {
    wp_die('Nonce verification failed. Please reload the page and try again.');
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the start and end dates from the form
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);

    // Format the start and end dates for the headings
    $formatted_start_date = date('F j, Y', strtotime($start_date));
    $formatted_end_date = date('F j, Y', strtotime($end_date));

    // Get checkbox values (will be 'yes' if checked, otherwise 'no')
    $include_paid = isset($_POST['include_paid']) ? 'yes' : 'no';
    $include_unpaid = isset($_POST['include_unpaid']) ? 'yes' : 'no';

    // Split the event_info to get event_id and name
    $event_data_id = '';
    $event_title = '';
    if (isset($_POST['event_info']) && !empty($_POST['event_info'])) {
        $event_info = sanitize_text_field($_POST['event_info']);
        list($event_data_id, $event_title) = explode('|', $event_info);
        $event_data_id = intval($event_data_id);
    }

    // Ensure the start date is not after the end date
    if (strtotime($start_date) > strtotime($end_date)) {
        die('Start date cannot be after end date.');
    }
    // Ensure they have selected one or both paid and unpaid
    if ($include_paid === 'no' && $include_unpaid === 'no') {
        die('Please select at least one payment type.');
    }

    // Fetch data from the database
    global $wpdb;
    $tec_occurrences_table = $wpdb->prefix . 'tec_occurrences';
    $booking_table = $wpdb->prefix . 'leanwi_event_booking'; 
    $cost_table = $wpdb->prefix . 'leanwi_event_cost';
    $booking_costs_table = $wpdb->prefix . 'leanwi_event_booking_costs'; 
    $booking_occurrences_table = $wpdb->prefix . 'leanwi_event_booking_occurrences';  
    $posts_table = $wpdb->prefix . 'posts';

    // Create a CSV file
    $upload_dir = wp_upload_dir();
    $csv_file_path = $upload_dir['basedir'] . '/leanwi_event_reports/';
    $csv_file_url = $upload_dir['baseurl'] . '/leanwi_event_reports/';

    // Ensure the reports folder exists
    if (!file_exists($csv_file_path)) {
        wp_mkdir_p($csv_file_path);
    }

    // Check the number of existing reports
    $report_files = glob($csv_file_path . '*.csv');
    if (count($report_files) > 100) {
        wp_die('You have reached the maximum number of saved reports (100). You will need to delete old reports before creating any new ones.');
    }

    // Generate a file name with a timestamp
    if (!empty($event_info)) {
        $csv_filename = 'payment_report_event_' . $event_data_id . '_' . time() . '.csv';
    } else {
        $csv_filename = 'payment_report_' . time() . '.csv';
    }
    $csv_file_path .= $csv_filename;
    $csv_file_url .= $csv_filename;

    // Open the file for writing
    $file = fopen($csv_file_path, 'w');

    if ($file === false) {
        die('Could not open the file for writing.');
    }

    // Prepare the arguments for the query
    $args = [$start_date, $end_date];

    // Add summary data regardless of category or audience
    $sql = "
        SELECT 
            b.booking_reference AS 'Booking Reference',
            o.start_date AS 'Date of Event',
            p.post_title AS 'Event Name',
            b.name AS 'Booker Name',
            SUM(bc.number_of_participants) AS '# Participants',
            SUM(c.cost_amount * bc.number_of_participants) AS 'Total Cost',
            CASE 
                WHEN b.has_paid = 1 THEN 'PAID'
                WHEN b.has_paid = 0 THEN 'UNPAID'
                ELSE ' '
            END AS 'Paid?'
        FROM 
            $booking_table b
        JOIN 
            $booking_occurrences_table bo ON b.booking_id = bo.booking_id
        JOIN 
            $booking_costs_table bc ON b.booking_id = bc.booking_id
        JOIN 
            $cost_table c ON bc.cost_id = c.cost_id
        JOIN 
            $tec_occurrences_table o ON bo.occurrence_id = o.occurrence_id
        JOIN
            $posts_table p ON p.ID = o.post_id
        WHERE o.start_date BETWEEN %s AND %s
    ";

    if (!empty($event_info)) {
        $sql .= " AND b.event_data_id = %d";
        $args[] = $event_data_id;
    }

    if ($include_paid === 'yes' && $include_unpaid === 'no') {
        $sql .= " AND b.has_paid = 1";
    }
    else if ($include_unpaid === 'yes' && $include_paid === 'no') {
        $sql .= " AND b.has_paid = 0";
    }

    $sql .= "
        GROUP BY 
            b.booking_reference, o.occurrence_id
        HAVING 
            SUM(c.cost_amount * bc.number_of_participants) > 0
        ORDER BY 
            b.booking_reference, o.start_date, b.name;";

    $results = $wpdb->get_results(
        $wpdb->prepare($sql, ...$args),
        ARRAY_A
    );

    if (!empty($results)) {
        // Add heading for the summary section
        fputcsv($file, [' ']);
        if (!empty($event_info)) {
            fputcsv($file, ["REPORT FOR EVENT: $event_title"]);
            fputcsv($file, [' ']);
        }

        fputcsv($file, ['~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~']);
        fputcsv($file, ["Payment Report - $formatted_start_date To $formatted_end_date"]);
        fputcsv($file, [' ']);

        // Add column headers to the CSV file
        fputcsv($file, array_keys($results[0]));
        
        // Add data rows to the CSV file
        foreach ($results as $row) {
            // Format the 'Total Cost' field
            $row['Total Cost'] = '$' . number_format($row['Total Cost'], 2);

            // Add data rows to the CSV file
            fputcsv($file, $row); 
        }
    }
    
    fputcsv($file, [' ']);
    fputcsv($file, ['~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~']);

    fclose($file);

    // Redirect to the CSV file for download
    header('Location: ' . $csv_file_url);
    exit;
}
?>
