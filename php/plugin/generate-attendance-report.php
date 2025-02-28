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
    $include_category = isset($_POST['include_category']) ? 'yes' : 'no';
    $include_audience = isset($_POST['include_audience']) ? 'yes' : 'no';

    // Split the event_info to get venue_id and name
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

    // Fetch data from the database
    global $wpdb;
    $tec_occurrences_table = $wpdb->prefix . 'tec_occurrences';
    $data_table = $wpdb->prefix . 'leanwi_event_data';
    $booking_table = $wpdb->prefix . 'leanwi_event_booking'; 
    $cost_table = $wpdb->prefix . 'leanwi_event_cost';
    $booking_costs_table = $wpdb->prefix . 'leanwi_event_booking_costs'; 
    $booking_occurrences_table = $wpdb->prefix . 'leanwi_event_booking_occurrences';  
    $audience_table = $wpdb->prefix . 'leanwi_event_audience';
    $category_table = $wpdb->prefix . 'leanwi_event_category'; 

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
        $csv_filename = 'attendance_report_event_' . $event_data_id . '_' . time() . '.csv';
    } else {
        $csv_filename = 'attendance_report_' . time() . '.csv';
    }
    $csv_file_path .= $csv_filename;
    $csv_file_url .= $csv_filename;

    // Open the file for writing
    $file = fopen($csv_file_path, 'w');

    if ($file === false) {
        die('Could not open the file for writing.');
    }

    // Initialize the arguments for the query
    $args = [];
    $sql = "";
    // Add summary data regardless of category or audience
    if (!empty($event_info)) {
        $sql = "
            SELECT 
                (SELECT COUNT(DISTINCT o.occurrence_id)
                FROM $tec_occurrences_table o
                JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                JOIN $data_table ed ON ed.post_id = o.post_id
                WHERE DATE(o.start_date) BETWEEN %s AND %s
                AND ed.event_data_id = %d) AS 'Total Event Occurrences',

                (SELECT SUM(bo.number_of_participants)
                FROM $booking_occurrences_table bo
                JOIN $tec_occurrences_table o ON bo.occurrence_id = o.occurrence_id
                JOIN $data_table ed ON ed.post_id = o.post_id
                WHERE DATE(o.start_date) BETWEEN %s AND %s
                AND ed.event_data_id = %d) AS 'Total Participants',

                (SELECT SUM(duration_minutes) 
                FROM (SELECT DISTINCT o.occurrence_id, (o.duration/60) AS duration_minutes
                    FROM $tec_occurrences_table o
                    JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                    JOIN $data_table ed ON ed.post_id = o.post_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    AND ed.event_data_id = %d
                    ) AS subquery) AS 'Total Minutes',

                (SELECT SUM(total_cost) 
                FROM (SELECT b.booking_id, SUM(c.cost_amount * bc.number_of_participants) AS total_cost
                    FROM $booking_table b
                    JOIN $booking_costs_table bc ON bc.booking_id = b.booking_id
                    JOIN $cost_table c ON c.cost_id = bc.cost_id
                    JOIN $booking_occurrences_table bo ON bo.booking_id = b.booking_id
                    JOIN $tec_occurrences_table o ON o.occurrence_id = bo.occurrence_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    AND b.event_data_id = %d
                    GROUP BY b.booking_id
                    ) AS subquery) AS 'Total Income'
        ";
        // Prepare the arguments for the query
        $args = [$start_date, $end_date, $event_data_id, $start_date, $end_date, $event_data_id, $start_date, $end_date, $event_data_id, $start_date, $end_date, $event_data_id];
    }
    else {
        $sql = "
            SELECT 
                (SELECT COUNT(DISTINCT o.occurrence_id)
                FROM $tec_occurrences_table o
                JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                JOIN $data_table ed ON ed.post_id = o.post_id
                WHERE DATE(o.start_date) BETWEEN %s AND %s) AS 'Total Event Occurrences',

                (SELECT SUM(bo.number_of_participants)
                FROM $booking_occurrences_table bo
                JOIN $tec_occurrences_table o ON bo.occurrence_id = o.occurrence_id
                JOIN $data_table ed ON ed.post_id = o.post_id
                WHERE DATE(o.start_date) BETWEEN %s AND %s) AS 'Total Participants',

                (SELECT SUM(duration_minutes) 
                FROM (SELECT DISTINCT o.occurrence_id, (o.duration/60) AS duration_minutes
                    FROM $tec_occurrences_table o
                    JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                    JOIN $data_table ed ON ed.post_id = o.post_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    ) AS subquery) AS 'Total Minutes',

                (SELECT SUM(total_cost) 
                FROM (SELECT b.booking_id, SUM(c.cost_amount * bc.number_of_participants) AS total_cost
                    FROM $booking_table b
                    JOIN $booking_costs_table bc ON bc.booking_id = b.booking_id
                    JOIN $cost_table c ON c.cost_id = bc.cost_id
                    JOIN $booking_occurrences_table bo ON bo.booking_id = b.booking_id
                    JOIN $tec_occurrences_table o ON o.occurrence_id = bo.occurrence_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    GROUP BY b.booking_id
                    ) AS subquery) AS 'Total Income'
        ";
        // Prepare the arguments for the query
        $args = [$start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date];

    }

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
        fputcsv($file, ["Summary of all Data - $formatted_start_date To $formatted_end_date"]);
        
        // Add column headers with a blank first cell
        $headers = array_keys($results[0]);
        array_unshift($headers, ''); // Insert blank cell at the beginning so columns line up with audiences and categories
        fputcsv($file, $headers);

        // Format the 'Total Income' field
        $results[0]['Total Income'] = '$' . number_format($results[0]['Total Income'], 2);

        // Add data row with a blank first cell
        $data_row = array_values($results[0]); // Get row values
        array_unshift($data_row, ''); // Insert blank cell at the beginning
        fputcsv($file, $data_row);
    }

    // Include audience data if checked
    if ($include_audience === 'yes') {
        if (!empty($event_info)) {
            $sql = "
                SELECT 
                    ea.audience_name,
                    COALESCE(total_occurrences, 0) AS 'Total Occurrences',
                    COALESCE(total_participants, 0) AS 'Total Participants',
                    COALESCE(total_minutes, 0) AS 'Total Minutes',
                    COALESCE(final_total, 0) AS 'Total Income'
                FROM $audience_table ea
                LEFT JOIN (
                    SELECT 
                        ed.audience_id, 
                        COUNT(DISTINCT o.occurrence_id) AS total_occurrences
                    FROM $tec_occurrences_table o
                    JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                    JOIN $booking_table b ON b.booking_id = bo.booking_id
                    JOIN $data_table ed ON ed.event_data_id = b.event_data_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    AND ed.event_data_id = %d
                    GROUP BY ed.audience_id
                ) occurrences ON ea.audience_id = occurrences.audience_id

                LEFT JOIN (
                    SELECT 
                        ed.audience_id, 
                        SUM(bo.number_of_participants) AS total_participants
                    FROM $booking_occurrences_table bo
                    JOIN $tec_occurrences_table o ON bo.occurrence_id = o.occurrence_id
                    JOIN $data_table ed ON ed.post_id = o.post_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    AND ed.event_data_id = %d
                    GROUP BY ed.audience_id
                ) participants ON ea.audience_id = participants.audience_id

                LEFT JOIN (
                    SELECT 
                        subquery.audience_id, 
                        SUM(subquery.duration_minutes) AS total_minutes
                    FROM (
                        SELECT DISTINCT o.occurrence_id, 
                            (o.duration / 60) AS duration_minutes, 
                            ed.audience_id
                        FROM $tec_occurrences_table o
                        JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                        JOIN $data_table ed ON ed.post_id = o.post_id
                        WHERE DATE(o.start_date) BETWEEN %s AND %s
                        AND ed.event_data_id = %d
                    ) AS subquery
                    GROUP BY subquery.audience_id
                ) durations ON ea.audience_id = durations.audience_id

                LEFT JOIN (
                    SELECT 
                        subquery.audience_id, 
                        SUM(subquery.total_cost) AS final_total
                    FROM (
                        SELECT 
                            b.booking_id, 
                            ed.audience_id,
                            SUM(c.cost_amount * bc.number_of_participants) AS total_cost
                        FROM $booking_table b
                        JOIN $booking_costs_table bc ON bc.booking_id = b.booking_id
                        JOIN $cost_table c ON c.cost_id = bc.cost_id
                        JOIN $booking_occurrences_table bo ON bo.booking_id = b.booking_id
                        JOIN $tec_occurrences_table o ON o.occurrence_id = bo.occurrence_id
                        JOIN $data_table ed ON ed.event_data_id = b.event_data_id
                        WHERE DATE(o.start_date) BETWEEN %s AND %s
                        AND b.event_data_id = %d
                        GROUP BY b.booking_id, ed.audience_id
                    ) subquery
                    GROUP BY subquery.audience_id
                ) costs ON ea.audience_id = costs.audience_id;
            ";
            // Prepare the arguments for the query
            $args = [$start_date, $end_date, $event_data_id, $start_date, $end_date, $event_data_id, $start_date, $end_date, $event_data_id, $start_date, $end_date, $event_data_id];
        }
        else {
            $sql = "
                SELECT 
                    ea.audience_name,
                    COALESCE(total_occurrences, 0) AS 'Total Occurrences',
                    COALESCE(total_participants, 0) AS 'Total Participants',
                    COALESCE(total_minutes, 0) AS 'Total Minutes',
                    COALESCE(final_total, 0) AS 'Total Income'
                FROM $audience_table ea
                LEFT JOIN (
                    SELECT 
                        ed.audience_id, 
                        COUNT(DISTINCT o.occurrence_id) AS total_occurrences
                    FROM $tec_occurrences_table o
                    JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                    JOIN $booking_table b ON b.booking_id = bo.booking_id
                    JOIN $data_table ed ON ed.event_data_id = b.event_data_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    GROUP BY ed.audience_id
                ) occurrences ON ea.audience_id = occurrences.audience_id

                LEFT JOIN (
                    SELECT 
                        ed.audience_id, 
                        SUM(bo.number_of_participants) AS total_participants
                    FROM $booking_occurrences_table bo
                    JOIN $tec_occurrences_table o ON bo.occurrence_id = o.occurrence_id
                    JOIN $data_table ed ON ed.post_id = o.post_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    GROUP BY ed.audience_id
                ) participants ON ea.audience_id = participants.audience_id

                LEFT JOIN (
                    SELECT 
                        subquery.audience_id, 
                        SUM(subquery.duration_minutes) AS total_minutes
                    FROM (
                        SELECT DISTINCT o.occurrence_id, 
                            (o.duration / 60) AS duration_minutes, 
                            ed.audience_id
                        FROM $tec_occurrences_table o
                        JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                        JOIN $data_table ed ON ed.post_id = o.post_id
                        WHERE DATE(o.start_date) BETWEEN %s AND %s
                    ) AS subquery
                    GROUP BY subquery.audience_id
                ) durations ON ea.audience_id = durations.audience_id

                LEFT JOIN (
                    SELECT 
                        subquery.audience_id, 
                        SUM(subquery.total_cost) AS final_total
                    FROM (
                        SELECT 
                            b.booking_id, 
                            ed.audience_id,
                            SUM(c.cost_amount * bc.number_of_participants) AS total_cost
                        FROM $booking_table b
                        JOIN $booking_costs_table bc ON bc.booking_id = b.booking_id
                        JOIN $cost_table c ON c.cost_id = bc.cost_id
                        JOIN $booking_occurrences_table bo ON bo.booking_id = b.booking_id
                        JOIN $tec_occurrences_table o ON o.occurrence_id = bo.occurrence_id
                        JOIN $data_table ed ON ed.event_data_id = b.event_data_id
                        WHERE DATE(o.start_date) BETWEEN %s AND %s
                        GROUP BY b.booking_id, ed.audience_id
                    ) subquery
                    GROUP BY subquery.audience_id
                ) costs ON ea.audience_id = costs.audience_id;
            ";
            // Prepare the arguments for the query
            $args = [$start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date];
        }
        

        $results = $wpdb->get_results(
            $wpdb->prepare($sql, ...$args),
            ARRAY_A
        );

        if (!empty($results)) {
            // Add heading for the summary section
            fputcsv($file, [' ']);
            fputcsv($file, ['~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~']);
            fputcsv($file, ["Summary of Data by Audience - $formatted_start_date To $formatted_end_date"]);

            // Add column headers to the CSV file
            fputcsv($file, array_keys($results[0]));

            // Add data rows to the CSV file
            foreach ($results as $row) {
                $row['Total Income'] = '$' . number_format($row['Total Income'], 2); // Format as currency
                fputcsv($file, $row);
            }
        }
    }

    // Include category data if checked
    if ($include_category === 'yes') {
        if (!empty($event_info)) {
            $sql = "
                SELECT 
                    ec.category_name,
                    COALESCE(total_occurrences, 0) AS 'Total Occurrences',
                    COALESCE(total_participants, 0) AS 'Total Participants',
                    COALESCE(total_minutes, 0) AS 'Total Minutes',
                    COALESCE(final_total, 0) AS 'Total Income'
                FROM $category_table ec
                LEFT JOIN (
                    SELECT 
                        ed.category_id, 
                        COUNT(DISTINCT o.occurrence_id) AS total_occurrences
                    FROM $tec_occurrences_table o
                    JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                    JOIN $booking_table b ON b.booking_id = bo.booking_id
                    JOIN $data_table ed ON ed.event_data_id = b.event_data_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    AND ed.event_data_id = %d
                    GROUP BY ed.category_id
                ) occurrences ON ec.category_id = occurrences.category_id

                LEFT JOIN (
                    SELECT 
                        ed.category_id, 
                        SUM(bo.number_of_participants) AS total_participants
                    FROM $booking_occurrences_table bo
                    JOIN $tec_occurrences_table o ON bo.occurrence_id = o.occurrence_id
                    JOIN $data_table ed ON ed.post_id = o.post_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    AND ed.event_data_id = %d
                    GROUP BY ed.category_id
                ) participants ON ec.category_id = participants.category_id

                LEFT JOIN (
                    SELECT 
                        subquery.category_id, 
                        SUM(subquery.duration_minutes) AS total_minutes
                    FROM (
                        SELECT DISTINCT o.occurrence_id, 
                            (o.duration / 60) AS duration_minutes, 
                            ed.category_id
                        FROM $tec_occurrences_table o
                        JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                        JOIN $data_table ed ON ed.post_id = o.post_id
                        WHERE DATE(o.start_date) BETWEEN %s AND %s
                        AND ed.event_data_id = %d
                    ) AS subquery
                    GROUP BY subquery.category_id
                ) durations ON ec.category_id = durations.category_id

                LEFT JOIN (
                    SELECT 
                        subquery.category_id, 
                        SUM(subquery.total_cost) AS final_total
                    FROM (
                        SELECT 
                            b.booking_id, 
                            ed.category_id,
                            SUM(c.cost_amount * bc.number_of_participants) AS total_cost
                        FROM $booking_table b
                        JOIN $booking_costs_table bc ON bc.booking_id = b.booking_id
                        JOIN $cost_table c ON c.cost_id = bc.cost_id
                        JOIN $booking_occurrences_table bo ON bo.booking_id = b.booking_id
                        JOIN $tec_occurrences_table o ON o.occurrence_id = bo.occurrence_id
                        JOIN $data_table ed ON ed.event_data_id = b.event_data_id
                        WHERE DATE(o.start_date) BETWEEN %s AND %s
                        AND b.event_data_id = %d
                        GROUP BY b.booking_id, ed.category_id
                    ) subquery
                    GROUP BY subquery.category_id
                ) costs ON ec.category_id = costs.category_id;
            ";
            // Prepare the arguments for the query
            $args = [$start_date, $end_date, $event_data_id, $start_date, $end_date, $event_data_id, $start_date, $end_date, $event_data_id, $start_date, $end_date, $event_data_id];
        }
        else {
            $sql = "
                SELECT 
                    ec.category_name,
                    COALESCE(total_occurrences, 0) AS 'Total Occurrences',
                    COALESCE(total_participants, 0) AS 'Total Participants',
                    COALESCE(total_minutes, 0) AS 'Total Minutes',
                    COALESCE(final_total, 0) AS 'Total Income'
                FROM $category_table ec
                LEFT JOIN (
                    SELECT 
                        ed.category_id, 
                        COUNT(DISTINCT o.occurrence_id) AS total_occurrences
                    FROM $tec_occurrences_table o
                    JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                    JOIN $booking_table b ON b.booking_id = bo.booking_id
                    JOIN $data_table ed ON ed.event_data_id = b.event_data_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    GROUP BY ed.category_id
                ) occurrences ON ec.category_id = occurrences.category_id

                LEFT JOIN (
                    SELECT 
                        ed.category_id, 
                        SUM(bo.number_of_participants) AS total_participants
                    FROM $booking_occurrences_table bo
                    JOIN $tec_occurrences_table o ON bo.occurrence_id = o.occurrence_id
                    JOIN $data_table ed ON ed.post_id = o.post_id
                    WHERE DATE(o.start_date) BETWEEN %s AND %s
                    GROUP BY ed.category_id
                ) participants ON ec.category_id = participants.category_id

                LEFT JOIN (
                    SELECT 
                        subquery.category_id, 
                        SUM(subquery.duration_minutes) AS total_minutes
                    FROM (
                        SELECT DISTINCT o.occurrence_id, 
                            (o.duration / 60) AS duration_minutes, 
                            ed.category_id
                        FROM $tec_occurrences_table o
                        JOIN $booking_occurrences_table bo ON bo.occurrence_id = o.occurrence_id
                        JOIN $data_table ed ON ed.post_id = o.post_id
                        WHERE DATE(o.start_date) BETWEEN %s AND %s
                    ) AS subquery
                    GROUP BY subquery.category_id
                ) durations ON ec.category_id = durations.category_id

                LEFT JOIN (
                    SELECT 
                        subquery.category_id, 
                        SUM(subquery.total_cost) AS final_total
                    FROM (
                        SELECT 
                            b.booking_id, 
                            ed.category_id,
                            SUM(c.cost_amount * bc.number_of_participants) AS total_cost
                        FROM $booking_table b
                        JOIN $booking_costs_table bc ON bc.booking_id = b.booking_id
                        JOIN $cost_table c ON c.cost_id = bc.cost_id
                        JOIN $booking_occurrences_table bo ON bo.booking_id = b.booking_id
                        JOIN $tec_occurrences_table o ON o.occurrence_id = bo.occurrence_id
                        JOIN $data_table ed ON ed.event_data_id = b.event_data_id
                        WHERE DATE(o.start_date) BETWEEN %s AND %s
                        GROUP BY b.booking_id, ed.category_id
                    ) subquery
                    GROUP BY subquery.category_id
                ) costs ON ec.category_id = costs.category_id;
            ";
            // Prepare the arguments for the query
            $args = [$start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date];
        }

        $results = $wpdb->get_results(
            $wpdb->prepare($sql, ...$args),
            ARRAY_A
        );

        if (!empty($results)) {
            // Add heading for the summary section
            fputcsv($file, [' ']);
            fputcsv($file, ['~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~']);
            fputcsv($file, ["Summary of Data by Category - $formatted_start_date To $formatted_end_date"]);

            // Add column headers to the CSV file
            fputcsv($file, array_keys($results[0]));

            // Add data rows to the CSV file
            foreach ($results as $row) {
                $row['Total Income'] = '$' . number_format($row['Total Income'], 2); // Format as currency
                fputcsv($file, $row);
            }
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
