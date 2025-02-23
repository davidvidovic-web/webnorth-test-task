<?php

namespace Webnorth\Handlers;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Map_Data_Handler
{
    private $api_base_url = 'https://api.openweathermap.org/geo/1.0/direct';

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
            $location_data = $this->get_location_data($display_name, $post_id);
            if (!$location_data) {
                return;
            }

            $this->update_post_meta($post_id, $location_data);
        } catch (\Exception $e) {
        } finally {
            //add actions back
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
        // update if we don't have coordinates
        if ($this->has_coordinates($post_id)) {
            return;
        }

        update_post_meta($post_id, 'lat', $location_data['lat']);
        update_post_meta($post_id, 'lon', $location_data['lon']);
        update_post_meta($post_id, 'display_name', $location_data['display_name']);
    }

    private function get_location_data($query, $post_id)
    {
        if ($this->has_coordinates($post_id)) {
            return false;
        }

        $api_key = get_option('webnorth_openweather_api_key');
        if (empty($api_key)) {
            return false;
        }

        $args = [
            'timeout' => 15, //ratelimiting
            'headers' => [
                'User-Agent' => 'WordPress/' . get_bloginfo('version')
            ]
        ];

        $url = add_query_arg([
            'q' => urlencode($query),
            'limit' => 5,
            'appid' => $api_key
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

        $location = reset($data);

        if (!$location) {
            return false;
        }

        return [
            'lat' => $location['lat'],
            'lon' => $location['lon'],
            'display_name' => sprintf(
                '%s%s, %s',
                $location['name'],
                !empty($location['state']) ? ', ' . $location['state'] : '',
                $location['country']
            )
        ];
    }

    private function has_coordinates($post_id)
    {
        $lat = get_post_meta($post_id, 'lat', true);
        $lon = get_post_meta($post_id, 'lon', true);

        return !empty($lat) && !empty($lon);
    }
}
