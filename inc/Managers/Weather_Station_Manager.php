<?php

namespace Webnorth\Managers;

use Webnorth\PostTypes\Weather_Station_Post_Type;
use Webnorth\ACFFields\Weather_Station_ACF_Fields;
use Webnorth\Pages\Create_Map_Page;

if (!defined('ABSPATH')) {
    exit;
}

class Weather_Station_Manager
{
    private static $instance = null;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', [$this, 'register_custom_post_type']);
        add_action('acf/init', [$this, 'register_acf_fields']);
    }

    public function check_acf_dependency()
    {
        if (!function_exists('acf_add_local_field_group')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                __('This plugin requires Advanced Custom Fields (ACF) to be installed and activated.', 'webnorth-frontend-plugin'),
                __('Plugin Activation Error', 'webnorth-frontend-plugin'),
                ['back_link' => true]
            );
        }
    }

    public function register_custom_post_type()
    {
        new Weather_Station_Post_Type();
    }

    public function register_acf_fields()
    {
        new Weather_Station_ACF_Fields();
    }

    public function create_map_page()
    {
        $create_map_page = new Create_Map_Page();
        $create_map_page->create_page_on_activation();
    }
}

Weather_Station_Manager::get_instance();
