<?php

namespace Webnorth\ACF;

if (!defined('ABSPATH')) {
    exit;
}

class Weather_Station_ACF_Fields
{
    public function __construct()
    {
        add_action('acf/include_fields', [$this, 'register_acf_fields']);
    }

    public function register_acf_fields()
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key' => 'group_67ba407e8029a',
            'title' => 'Weather station data',
            'fields' => [
                [
                    'key' => 'field_67ba407e56d10',
                    'label' => 'Title',
                    'name' => 'display_name',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => '',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ],
                [
                    'key' => 'field_67ba407e56d0f',
                    'label' => 'Latitude',
                    'name' => 'lat',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => '',
                    'min' => '',
                    'max' => '',
                    'allow_in_bindings' => 1,
                    'placeholder' => '',
                    'step' => '',
                    'prepend' => '',
                    'append' => '',
                ],
                [
                    'key' => 'field_67ba4272b13b9',
                    'label' => 'Longitude',
                    'name' => 'lon',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => '',
                    'min' => '',
                    'max' => '',
                    'allow_in_bindings' => 0,
                    'placeholder' => '',
                    'step' => '',
                    'prepend' => '',
                    'append' => '',
                ],
                [
                    'key' => 'field_67ba44adf7e6f',
                    'label' => 'Weather data',
                    'name' => 'weather_data',
                    'aria-label' => '',
                    'type' => 'textarea',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => '',
                    'maxlength' => '',
                    'allow_in_bindings' => 0,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
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
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'show_in_rest' => 0,
        ]);
    }
}
