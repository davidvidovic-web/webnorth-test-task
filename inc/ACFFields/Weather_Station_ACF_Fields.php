<?php

namespace Webnorth\ACFFields;

if (!defined('ABSPATH')) {
    exit;
}

class Weather_Station_ACF_Fields
{
    public function __construct()
    {
        add_action('acf/init', [$this, 'register_acf_fields']);
    }

    public function register_acf_fields()
    {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group([
                'key' => 'group_weather_station',
                'title' => __('Weather Station Details', 'webnorth-frontend-plugin'),
                'fields' => [
                    [
                        'key' => 'field_weather_station_lat',
                        'label' => __('Latitude', 'webnorth-frontend-plugin'),
                        'name' => 'weather_station_lat',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'field_weather_station_lng',
                        'label' => __('Longitude', 'webnorth-frontend-plugin'),
                        'name' => 'weather_station_lng',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'field_weather_station_weather_data',
                        'label' => __('Weather Data (24h)', 'webnorth-frontend-plugin'),
                        'name' => 'weather_station_weather_data',
                        'type' => 'textarea',
                    ],
                ],
                'location' => [
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'weather_station',
                        ],
                    ],
                ],
            ]);
        }
    }
}
