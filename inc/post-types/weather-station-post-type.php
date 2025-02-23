<?php

namespace Webnorth\PostTypes;

if (!defined('ABSPATH')) {
    exit;
}

class Weather_Station_Post_Type
{
    public function __construct()
    {
        $this->register_post_type();
    }

    public function register_post_type()
    {
        register_post_type('weather_station', [
            'labels' => [
                'name' => __('Weather Stations', 'webnorth-frontend-plugin'),
                'singular_name' => __('Weather Station', 'webnorth-frontend-plugin'),
                'menu_name' => __('Weather Stations', 'webnorth-frontend-plugin'),
                'name_admin_bar' => __('Weather Station', 'webnorth-frontend-plugin'),
                'add_new' => __('Add New', 'webnorth-frontend-plugin'),
                'add_new_item' => __('Add New Weather Station', 'webnorth-frontend-plugin'),
                'new_item' => __('New Weather Station', 'webnorth-frontend-plugin'),
                'edit_item' => __('Edit Weather Station', 'webnorth-frontend-plugin'),
                'view_item' => __('View Weather Station', 'webnorth-frontend-plugin'),
                'all_items' => __('All Weather Stations', 'webnorth-frontend-plugin'),
                'search_items' => __('Search Weather Stations', 'webnorth-frontend-plugin'),
                'parent_item_colon' => __('Parent Weather Stations:', 'webnorth-frontend-plugin'),
                'not_found' => __('No weather stations found.', 'webnorth-frontend-plugin'),
                'not_found_in_trash' => __('No weather stations found in Trash.', 'webnorth-frontend-plugin'),
            ],
            'public' => false,
            'show_ui' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'weather-stations'],
            'supports' => ['title', 'editor', 'custom-fields'],
            'show_in_rest' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-cloud',
            'capability_type' => 'post',
            'hierarchical' => false,
            'query_var' => true,
        ]);
    }
}
