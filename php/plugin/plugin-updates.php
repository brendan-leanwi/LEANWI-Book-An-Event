<?php
namespace LEANWI_Book_An_Event;

function leanwi_check_for_plugin_updates($transient) {
    // Don't proceed if the transient is empty
    if (empty($transient->checked)) {
        return $transient;
    }

    // Define GitHub repo URL and API endpoint
    $repo = 'brendan-leanwi/LEANWI-Book-An-Event';
    $api_url = "https://api.github.com/repos/{$repo}/releases/latest";

    // Get release information from GitHub
    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
        //error_log("GitHub API error: " . $response->get_error_message());
        return $transient;
    }

    $release = json_decode(wp_remote_retrieve_body($response));
    if (!isset($release->tag_name)) {
        //error_log("GitHub tag_name is not set.");
        return $transient;
    }

    // Get the main plugin file
    $plugin_file = 'LEANWI-Book-An-Event/leanwi-book-an-event.php';
    //error_log("Plugin file: " . $plugin_file);
    //error_log("Checked plugins: " . print_r($transient->checked, true));

    if (!isset($transient->checked[$plugin_file])) {
        //error_log("Plugin not found in the checked plugins list.");
        return $transient; // Ensure the plugin is in the checked list
    }

    $latest_version = ltrim($release->tag_name, 'v'); // Remove 'v' if present in the GitHub tag
    $current_version = $transient->checked[$plugin_file] ?? '';

    //error_log("Current version: " . $current_version);
    //error_log("Latest version from GitHub: " . $latest_version);

    if (version_compare((string)$current_version, (string)$latest_version, '<')) {
        // Define the update data
        //error_log("Update available: Current version is older than the latest version.");
        $transient->response[$plugin_file] = (object) array(
            'slug'        => basename(__DIR__),
            'new_version' => $latest_version,
            'package'     => $release->zipball_url, // GitHub zip URL for the release
            'url'         => "https://github.com/{$repo}",
        );
    }
    /*
    else {
        error_log("No update needed: Current version is up-to-date.");
    }
    */
    //error_log("Transient response after check: " . print_r($transient, true));
    return $transient;
}
add_filter('site_transient_update_plugins',__NAMESPACE__ . '\\leanwi_check_for_plugin_updates');


function leanwi_plugin_update_info($res, $action, $args) {
    // Ensure that we only affect the 'query_plugins' action
    if ( 'query_plugins' === $action ) {
        // Check if the plugin in question is being queried
        if ( isset( $args->browse ) && 'featured' === $args->browse ) {
            // If it's a featured request, we just return early and ignore our plugin
            return $res; // Don't return any plugin data for the 'featured' tab
        }

        // Otherwise, we check if the 'slug' matches and if we need to fetch update info
        if ( isset( $args->slug ) && 'leanwi-book-an-event' === $args->slug ) {
            // Define GitHub repo URL and API endpoint
            $repo = 'brendan-leanwi/LEANWI-Book-An-Event';
            $api_url = "https://api.github.com/repos/{$repo}/releases/latest";

            // Fetch release information from GitHub API
            $remote_info = wp_remote_get($api_url, array('timeout' => 15));
            if (!is_wp_error($remote_info)) {
                $release = json_decode(wp_remote_retrieve_body($remote_info));

                // Check if we got valid release information
                if (isset($release->tag_name)) {
                    // Define the latest version available on GitHub
                    $latest_version = $release->tag_name; // e.g., 'v1.1.0'

                    // Prepare the plugin response with update information
                    $res = new stdClass();
                    $res->plugins = array(
                        (object) array(
                            'slug' => 'leanwi-book-an-event',
                            'name' => 'LEANWI Book An Event',
                            'version' => '0.0.1', // Your current plugin version
                            'new_version' => $latest_version, // The latest version from GitHub
                            'description' => 'Event Booking functionality compatible with LEANWI Divi WordPress websites.',
                            'homepage' => 'https://github.com/brendan-leanwi/LEANWI-Book-An-Event',
                            'icons' => array(
                                'svg' => 'https://example.com/icon.svg',
                            ),
                            'rating' => 4.5,
                            'active_installs' => 1,
                            'last_updated' => '2024-11-05',
                            'tags' => array('booking', 'event', 'LEANWI'),
                            'author' => 'Brendan Tuckey',
                            'author_profile' => 'https://github.com/brendan-leanwi',
                            'download_link' => 'https://github.com/brendan-leanwi/LEANWI-Book-An-Event/archive/refs/heads/main.zip',
                        ),
                    );

                    // Add necessary fields (e.g., 'results' and 'external')
                    $res->results = $res->plugins;
                    $res->external = 1;

                    // Optionally: Add update information like 'update_notice' if needed
                    if (version_compare($latest_version, '1.0.0', '>')) {
                        $res->update_notice = 'A new version is available: ' . $latest_version;
                    }
                }
            }
        }
    }

    return $res;
}
add_filter('plugins_api', __NAMESPACE__ . '\\leanwi_plugin_update_info', 10, 3);




function leanwi_override_post_install($true, $hook_extra, $result) {
    global $wp_filesystem;

    // Verify it's our plugin by checking the main plugin file
    if (isset($hook_extra['plugin']) && strpos($hook_extra['plugin'], 'leanwi-book-an-event.php') !== false) {
        $source = $result['destination'];
        $corrected_path = trailingslashit(WP_PLUGIN_DIR) . 'LEANWI-Book-An-Event';

        // Rename the plugin directory to the expected folder name
        if ($wp_filesystem->move($source, $corrected_path, true)) {
            $result['destination'] = $corrected_path; // Update the result with the new path
        }
    }

    return $result;
}
add_filter('upgrader_post_install', __NAMESPACE__ . '\\leanwi_override_post_install', 10, 3);
