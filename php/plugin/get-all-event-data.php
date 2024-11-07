<?php

// Load WordPress environment to access $wpdb
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

global $wpdb; // Access the global $wpdb object

// Query to get venues
$posts_table = $wpdb->prefix . 'posts';
$category_table = $wpdb->prefix . 'leanwi_event_category';
$audience_table = $wpdb->prefix . 'leanwi_event_audience';
$event_data_table = $wpdb->prefix . 'leanwi_event_data';
$tex_occurrences_table = $wpdb->prefix . 'tec_occurrences';

// Construct the SQL query to join tables and retrieve relevant event data
$sql = "SELECT 
            ed.event_data_id, 
            ed.capacity, 
            ed.historic, 
            p.post_title AS title, 
            c.category_name AS category, 
            a.audience_name AS audience,
            (SELECT MIN(occ.start_date)
             FROM $tex_occurrences_table occ
             WHERE occ.post_id = p.ID
             AND occ.start_date > NOW()
            ) AS next_start_date
        FROM $event_data_table ed
        JOIN $posts_table p ON ed.post_id = p.ID
        JOIN $category_table c ON ed.category_id = c.category_id
        JOIN $audience_table a ON ed.audience_id = a.audience_id
        ORDER BY next_start_date";

// Execute the query
$events = $wpdb->get_results($sql, ARRAY_A); // Fetch results as an associative array

// Function to sanitize venue data
function sanitize_event_data($event) {
    return [
        'event_data_id'    => intval($event['event_data_id']), // Sanitize integer
        'title'        => esc_html($event['title']), // Sanitize string
        'capacity'    => intval($event['capacity']), // Sanitize integer
        'category' => esc_html($event['category']), // Sanitize string
        'audience'    => esc_html($event['audience']), // Sanitize string
        'historic'    => intval($event['historic']), // Sanitize boolean
        'next_start_date' => esc_html($event['next_start_date']),
    ];
}

// Sanitize each event before returning
$sanitized_events = array_map('sanitize_event_data', $events);

// Output as JSON
header('Content-Type: application/json');
echo json_encode(['events' => $sanitized_events]);
?>
