<?php

namespace Webnorth\Map\Services;

if (!defined('ABSPATH')) {
    exit;
}

class Weather_Station_Post_Data
{
    public function get_all_stations()
    {
        $stations = get_posts([
            'post_type' => 'weather_station',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);

        return array_map(function ($station) {
            return [
                'id' => $station->ID,
                'title' => get_the_title($station->ID),
                'lat' => get_post_meta($station->ID, 'lat', true),
                'lon' => get_post_meta($station->ID, 'lon', true),
                'display_name' => get_post_meta($station->ID, 'display_name', true)
            ];
        }, $stations);
    }
}
