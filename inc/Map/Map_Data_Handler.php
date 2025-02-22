<?php

namespace Webnorth\Map;

if (!defined('ABSPATH')) {
    exit;
}

class Map_Data_Handler
{
    private $api_base_url = 'https://nominatim.openstreetmap.org/search';
    private $location_priorities = [
        'city' => 1,
        'town' => 2,
        'village' => 3
    ];

    public function __construct()
    {
        error_log('Map_Data_Handler constructor called');

        // Add hooks with lower priority (higher number) to run after ACF
        add_action('acf/save_post', [$this, 'update_location_data'], 999);
        add_action('save_post_weather_station', [$this, 'update_location_data'], 999, 1);
        add_action('rest_after_insert_weather_station', [$this, 'update_location_data'], 999, 1);
    }

    public function update_location_data($post_id)
    {
        error_log('Weather Station Save Triggered - Post ID: ' . $post_id);

        // Prevent infinite loops
        remove_action('save_post_weather_station', [$this, 'update_location_data'], 999);
        remove_action('rest_after_insert_weather_station', [$this, 'update_location_data'], 999);

        // Log save type
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            error_log('Autosave detected - skipping location update');
            return;
        }

        if (wp_is_post_revision($post_id)) {
            error_log('Revision detected - skipping location update');
            return;
        }

        // Verify post type
        if (get_post_type($post_id) !== 'weather_station') {
            error_log('Not a weather station post type - skipping location update');
            return;
        }

        // Try getting the title first if display_name is empty
        $display_name = get_field('display_name', $post_id);
        error_log('Display name from ACF: ' . ($display_name ? $display_name : 'not found'));

        if (empty($display_name)) {
            $display_name = get_the_title($post_id);
            error_log('Display name from title: ' . ($display_name ? $display_name : 'not found'));
        }

        if (empty($display_name)) {
            $display_name = get_post_meta($post_id, 'display_name', true);
            error_log('Display name from post meta: ' . ($display_name ? $display_name : 'not found'));
        }

        if (empty($display_name)) {
            error_log('No display name found - skipping location update');
            return;
        }

        // Get location data from Nominatim
        $location_data = $this->get_location_data($display_name);
        if (!$location_data) {
            return;
        }

        // Save the best match to post meta
        $best_match = $this->get_best_match($location_data);
        if ($best_match) {
            update_post_meta($post_id, 'lat', $best_match['lat']);
            update_post_meta($post_id, 'lon', $best_match['lon']);
            update_post_meta($post_id, 'display_name', $best_match['display_name']);
        }

        // Re-add actions with correct priority
        add_action('save_post_weather_station', [$this, 'update_location_data'], 999, 1);
        add_action('rest_after_insert_weather_station', [$this, 'update_location_data'], 999, 1);
    }

    private function get_location_data($query)
    {
        // Add delay to respect Nominatim usage policy
        usleep(1000000); // 1 second delay

        $args = [
            'timeout' => 15,
            'headers' => [
                'User-Agent' => 'WordPress/' . get_bloginfo('version'),
                'Referer' => get_site_url()
            ]
        ];

        $url = add_query_arg([
            'format' => 'json',
            'q' => urlencode($query)
        ], $this->api_base_url);

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            error_log('Nominatim API Error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data)) {
            error_log('No location data found for: ' . $query);
            return false;
        }

        return $data;
    }

    private function get_best_match($locations)
    {
        if (empty($locations)) {
            return null;
        }

        // Sort locations by type priority
        usort($locations, function ($a, $b) {
            $a_priority = $this->get_location_priority($a);
            $b_priority = $this->get_location_priority($b);

            return $a_priority - $b_priority;
        });

        // Return the first (highest priority) location
        return [
            'lat' => $locations[0]['lat'],
            'lon' => $locations[0]['lon'],
            'display_name' => $locations[0]['display_name']
        ];
    }

    private function get_location_priority($location)
    {
        if (empty($location['type'] || !in_array($location['type'], array_keys($this->location_priorities)))) {
            return 999; // Lowest priority for unknown types
        }

        return isset($this->location_priorities[$location['type']])
            ? $this->location_priorities[$location['type']]
            : 999;
    }
}
