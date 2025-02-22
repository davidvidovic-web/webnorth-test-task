<?php

namespace Webnorth\Managers;

use Webnorth\PostTypes\Weather_Station_Post_Type;
use Webnorth\ACFFields\Weather_Station_ACF_Fields;
use Webnorth\Pages\Create_Map_Page;
use Webnorth\Handlers\Admin_Map_Data_Handler;
use Webnorth\Map\Admin_Map_Data_Handler as MapAdmin_Map_Data_Handler;

if (!defined('ABSPATH')) {
    exit;
}

class Weather_Station_Manager
{
    private static $instance = null;
    private $data_handler;

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
        $this->check_acf_dependency();
        $this->register_acf_fields();
        $this->init_data_handler();
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

    private function init_data_handler()
    {
        // Initialize the data handler
        $this->data_handler = new Admin_Map_Data_Handler();
        
        // Add debug log to confirm initialization
        error_log('Map_Data_Handler initialized in Weather_Station_Manager');
    }

    public function get_data_handler()
    {
        return $this->data_handler;
    }
}

Weather_Station_Manager::get_instance();
