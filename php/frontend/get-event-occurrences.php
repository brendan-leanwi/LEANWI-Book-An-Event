<?php

// Include WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

global $wpdb;

// Get the event_data_id from the request
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if ($post_id > 0) {
    // Get the prefixed table names
    $occurrences_table = $wpdb->prefix . 'tec_occurrences';
    $participant_table = $wpdb->prefix . 'leanwi_event_participant';
    // Fetch occurrences associated with the post_id
    $sql = $wpdb->prepare("
        SELECT o.occurrence_id, o.start_date, o.end_date, IFNULL(SUM(p.total_number_of_participants), 0) AS total_participants
        FROM $occurrences_table o
        LEFT JOIN $participant_table p ON o.occurrence_id = p.event_occurrence_id
        WHERE o.post_id = %d
        GROUP BY o.occurrence_id, o.start_date, o.end_date
    ", $post_id);

    $occurrences = $wpdb->get_results($sql, ARRAY_A);

    if (!empty($occurrences)) {
        
        // Sanitize output data with specific handling
        $safe_results = array_map(function($result) {
            return [
                'occurrence_id' => intval($result['occurrence_id']),
                'start_date' => esc_html($result['start_date']), 
                'end_date' => esc_html($result['end_date']),
                'total_participants' => intval($result['total_participants']),
            ];
        }, $occurrences);

        // Return disclaimers as JSON
        echo json_encode(['event_occurrences' => $safe_results]);
    } else {
        echo json_encode(['event_occurrences' => []]); // Return empty if no ocurrences
    }
} else {
    echo json_encode(['event_occurrences' => []]); // Return empty if no post_id
}

?>
