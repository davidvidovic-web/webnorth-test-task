<?php

namespace Webnorth\Handlers;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Map_Data_Handler
{
    private $api_base_url = 'https://nominatim.openstreetmap.org/search';
    private $location_priorities = [
        'city' => 1,
        'town' => 2,
        'village' => 3,
        'multipolygon' => 4
    ];

    public function __construct()
    {
        add_action('acf/save_post', [$this, 'update_location_data'], 999);
        add_action('save_post_weather_station', [$this, 'update_location_data'], 999, 1);
        add_action('rest_after_insert_weather_station', [$this, 'update_location_data'], 999, 1);
    }

    public function update_location_data($post_id)
    {
        if (is_object($post_id) && isset($post_id->ID)) {
            $post_id = $post_id->ID;
        }

        if ($this->should_skip_update($post_id)) {
            return;
        }

        //prevent loops
        remove_action('save_post_weather_station', [$this, 'update_location_data'], 999);
        remove_action('rest_after_insert_weather_station', [$this, 'update_location_data'], 999);

        $display_name = $this->get_display_name($post_id);
        if (empty($display_name)) {
            return;
        }

        try {
            $location_data = $this->get_location_data($display_name);
            if (!$location_data) {
                return;
            }

            $best_match = $this->get_best_match($location_data);
            if (!$best_match) {
                return;
            }

            $this->update_post_meta($post_id, $best_match);
        } catch (\Exception $e) {
        } finally {
            add_action('save_post_weather_station', [$this, 'update_location_data'], 999, 1);
            add_action('rest_after_insert_weather_station', [$this, 'update_location_data'], 999, 1);
        }
    }

    private function should_skip_update($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return true;
        }

        if (wp_is_post_revision($post_id)) {
            return true;
        }

        if (get_post_type($post_id) !== 'weather_station') {
            return true;
        }

        return false;
    }

    private function get_display_name($post_id)
    {
        $display_name = get_field('display_name', $post_id);

        if (empty($display_name)) {
            $display_name = get_the_title($post_id);
        }

        return $display_name;
    }

    private function update_post_meta($post_id, $location_data)
    {
        update_post_meta($post_id, 'lat', $location_data['lat']);
        update_post_meta($post_id, 'lon', $location_data['lon']);
        update_post_meta($post_id, 'display_name', $location_data['display_name']);
    }

    private function get_location_data($query)
    {
        //avoid rate limiting 
        usleep(1000000);

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
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data)) {
            return false;
        }

        return $data;
    }

    private function get_best_match($locations)
    {
        if (empty($locations)) {
            return null;
        }

        usort($locations, function ($a, $b) {
            $a_priority = $this->get_location_priority($a);
            $b_priority = $this->get_location_priority($b);

            return $a_priority - $b_priority;
        });

        return [
            'lat' => $locations[0]['lat'],
            'lon' => $locations[0]['lon'],
            'display_name' => $locations[0]['display_name']
        ];
    }

    private function get_location_priority($location)
    {
        if (empty($location['type'] || !in_array($location['type'], array_keys($this->location_priorities)))) {
            return 999;
        }

        return isset($this->location_priorities[$location['type']])
            ? $this->location_priorities[$location['type']]
            : 999;
    }
}
