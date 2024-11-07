<?php
namespace LEANWI_Book_An_Event;

// Function to create the necessary tables on plugin activation
function leanwi_event_create_tables() {
    // Load WordPress environment to access $wpdb
    require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $engine = "ENGINE=InnoDB";
    
    // SQL for creating leanwi_event_category table
    $sql1 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}leanwi_event_category (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(255) NOT NULL,
        historic TINYINT(1) DEFAULT 0
    ) $engine $charset_collate;";

    // SQL for creating leanwi_event_audience table
    $sql2 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}leanwi_event_audience (
        audience_id INT AUTO_INCREMENT PRIMARY KEY,
        audience_name VARCHAR(255) NOT NULL,
        historic TINYINT(1) DEFAULT 0
    ) $engine $charset_collate;";

    // SQL for creating leanwi_event_data table
    $sql3 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}leanwi_event_data (
        event_data_id INT AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT(20) unsigned NOT NULL,
        event_url CHAR(255) NOT NULL,
        event_image CHAR(255),
        capacity INT NOT NULL,
        category_id INT NOT NULL,
        audience_id INT NOT NULL,
        historic TINYINT(1) DEFAULT 0,
        FOREIGN KEY (category_id) REFERENCES  {$wpdb->prefix}leanwi_event_category(category_id),
        FOREIGN KEY (audience_id) REFERENCES  {$wpdb->prefix}leanwi_event_audience(audience_id)
    ) $engine $charset_collate;";

    // SQL for creating leanwi_event_cost table
    $sql4 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}leanwi_event_cost (
        cost_id INT AUTO_INCREMENT PRIMARY KEY,
        event_data_id INT NOT NULL,
        cost_name VARCHAR(50) NOT NULL,
        cost_amount DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (event_data_id) REFERENCES {$wpdb->prefix}leanwi_event_data(event_data_id) ON DELETE CASCADE
    ) $engine $charset_collate;";

    // SQL for creating leanwi_event_participant table
    $sql5 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}leanwi_event_user (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(20)
    ) $engine $charset_collate;";

    // SQL for creating leanwi_event_participant table
    $sql6 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}leanwi_event_participant (
        participant_id INT AUTO_INCREMENT PRIMARY KEY,
        unique_booking_id CHAR(7) NOT NULL,
        event_data_id INT NOT NULL,
        event_occurrence_id BIGINT(20) unsigned NOT NULL,
        user_id INT NOT NULL,
        total_number_of_participants INT NOT NULL,
        FOREIGN KEY (event_data_id) REFERENCES {$wpdb->prefix}leanwi_event_data(event_data_id) ON DELETE CASCADE,
        FOREIGN KEY (event_occurrence_id) REFERENCES {$wpdb->prefix}tec_occurrences(occurrence_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}leanwi_event_user(user_id) ON DELETE CASCADE
    ) $engine $charset_collate;";

    // SQL for creating leanwi_event_participant table
    $sql7 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}leanwi_event_participant_cost (
        participant_cost_id INT AUTO_INCREMENT PRIMARY KEY,
        participant_id INT NOT NULL,
        cost_id INT NOT NULL,
        number_of_participants INT NOT NULL,
        FOREIGN KEY (participant_id) REFERENCES {$wpdb->prefix}leanwi_event_participant(participant_id) ON DELETE CASCADE,
        FOREIGN KEY (cost_id) REFERENCES {$wpdb->prefix}leanwi_event_cost(cost_id) ON DELETE CASCADE
    ) $engine $charset_collate;";

    // SQL for creating leanwi_event_affirmation table
    $sql8 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}leanwi_event_affirmation (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_data_id INT NOT NULL,
        affirmation TEXT NOT NULL,
        FOREIGN KEY (event_data_id) REFERENCES {$wpdb->prefix}leanwi_event_data(event_data_id) ON DELETE CASCADE
    ) $engine $charset_collate;";

    // SQL for creating leanwi_booking_category table
    $sql9 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}leanwi_event_saved_affirmation (
        id INT AUTO_INCREMENT PRIMARY KEY,
        affirmation TEXT NOT NULL
    ) $engine $charset_collate;";

    // Execute the SQL queries
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    //*************************************************************************************** */
    // Doesn't work as one call like this - perhaps it runs them all at the same time?
    //dbDelta([$sql1, $sql2, $sql3, $sql4, $sql5, $sql6]);
    //*************************************************************************************** */
    
    dbDelta($sql1);
    // Debug logging to track SQL execution
    if ($wpdb->last_error) {
        error_log('DB Error1: ' . $wpdb->last_error); // Logs the error to wp-content/debug.log
    }
    dbDelta($sql2);
    // Debug logging to track SQL execution
    if ($wpdb->last_error) {
        error_log('DB Error2: ' . $wpdb->last_error); // Logs the error to wp-content/debug.log
    }
    dbDelta($sql3);
    // Debug logging to track SQL execution
    if ($wpdb->last_error) {
        error_log('DB Error3: ' . $wpdb->last_error); // Logs the error to wp-content/debug.log
    }
    dbDelta($sql4);
    // Debug logging to track SQL execution
    if ($wpdb->last_error) {
        error_log('DB Error4: ' . $wpdb->last_error); // Logs the error to wp-content/debug.log
    }
    dbDelta($sql5);
    // Debug logging to track SQL execution
    if ($wpdb->last_error) {
        error_log('DB Error5: ' . $wpdb->last_error); // Logs the error to wp-content/debug.log
    }
    dbDelta($sql6);
    // Debug logging to track SQL execution
    if ($wpdb->last_error) {
        error_log('DB Error6: ' . $wpdb->last_error); // Logs the error to wp-content/debug.log
    }
    dbDelta($sql7);
    // Debug logging to track SQL execution
    if ($wpdb->last_error) {
        error_log('DB Error7: ' . $wpdb->last_error); // Logs the error to wp-content/debug.log
    }
    dbDelta($sql8);
    // Debug logging to track SQL execution
    if ($wpdb->last_error) {
        error_log('DB Error8: ' . $wpdb->last_error); // Logs the error to wp-content/debug.log
    }
    dbDelta($sql9);
    // Debug logging to track SQL execution
    if ($wpdb->last_error) {
        error_log('DB Error9: ' . $wpdb->last_error); // Logs the error to wp-content/debug.log
    }

    // Insert default category
    $wpdb->insert(
        "{$wpdb->prefix}leanwi_event_category",
        array(
            'category_id' => 1,
            'category_name' => 'Uncategorized',
            'historic' => 0
        ),
        array('%d', '%s', '%d') // Data types
    );

    // Insert default audience
    $wpdb->insert(
        "{$wpdb->prefix}leanwi_event_audience",
        array(
            'audience_id' => 1,
            'audience_name' => 'Uncategorized',
            'historic' => 0
        ),
        array('%d', '%s', '%d') // Data types
    );
}


// Function to drop the tables on plugin uninstall
function leanwi_event_drop_tables() {
    global $wpdb;

    // SQL to drop the tables
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}leanwi_event_participant");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}leanwi_event_participant_cost");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}leanwi_event_affirmation");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}leanwi_event_cost");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}leanwi_event_data");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}leanwi_event_category");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}leanwi_event_audience");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}leanwi_event_user");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}leanwi_event_saved_affirmation");
}

