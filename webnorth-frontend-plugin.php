<?php

/**
 * Plugin Name: Webnorth Frontend Plugin
 * Description: A plugin to manage weather stations for Webnorth coding interview.
 * Version:     1.0.0
 * Author:      David Vidovic
 * Author URI:  https://davidvidovic.com
 * Text Domain: webnorth-frontend-plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'autoloader.php';

use Webnorth\Managers\Weather_Station_Manager;
use Webnorth\Pages\Create_Map_Page;

$weather_station_manager = Weather_Station_Manager::get_instance();

//instace here so that the page is created on activation and avoids headers already sent error
$create_map_page = new Create_Map_Page();

register_activation_hook(__FILE__, [$weather_station_manager, 'check_acf_dependency']);
register_activation_hook(__FILE__, [$weather_station_manager, 'create_map_page']);
