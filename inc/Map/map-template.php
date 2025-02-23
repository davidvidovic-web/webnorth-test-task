<?php

namespace Webnorth\Map;

use Webnorth\Map\Services\Weather_Station_Post_Data;

if (!defined('ABSPATH')) {
    exit;
}

class Map_Template
{
    private $map_app_path;
    private $station_service;

    public function __construct()
    {
        $this->map_app_path = plugin_dir_url(__FILE__);
        $this->station_service = new Weather_Station_Post_Data();

        add_filter('template_include', [$this, 'load_template']);
        add_action('wp_enqueue_scripts', [$this, 'conditionally_enqueue_scripts']); // Ensure template is loaded first before enqueuing scripts
    }

    public function conditionally_enqueue_scripts()
    {
        if (is_page_template('webnorth-map-template')) {
            $this->enqueue_map_scripts();
        }
    }

    public function enqueue_map_scripts()
    {
        // The core GSAP library and ScrollTrigger plugin
        wp_enqueue_script('gsap-js', $this->map_app_path . 'assets/js/gsap.min.js', array(), false, false);
        wp_enqueue_script('gsap-st', $this->map_app_path . 'assets/js/ScrollTrigger.min.js', array('gsap-js'), false, false);
        // The main JS files for the map app and the Leaflet library
        wp_register_script('webnorth-main-js', $this->map_app_path . 'assets/js/main.js', ['gsap-js', 'gsap-st'], null, false);
        wp_register_script('leaflet-js', $this->map_app_path . 'assets/js/leaflet.min.js', ['webnorth-main-js'], false, false);
        wp_register_script('webnorth-map-js', $this->map_app_path . 'assets/js/map.js', ['leaflet-js'], false, false);

        // The main CSS file for the map app and the Leaflet library
        wp_register_style('webnorth-main-css', $this->map_app_path . 'assets/css/mapStyles.css', [], null);
        wp_register_style('leaflet-css', $this->map_app_path . 'assets/css/leaflet.css', [], null);

        wp_enqueue_script('gsap');
        wp_enqueue_script('scrolltrigger');
        wp_enqueue_script('webnorth-main-js');
        wp_enqueue_script('leaflet-js');
        wp_enqueue_script('webnorth-map-js');
        wp_enqueue_style('webnorth-main-css');
        wp_enqueue_style('leaflet-css');

        $station_service = new Weather_Station_Post_Data();
        $stations = $station_service->get_all_stations();

        wp_localize_script('webnorth-map-js', 'weatherStationData', [
            'stations' => $stations,
            'map_app_path' => $this->map_app_path,
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('weather_data_nonce')
        ]);
    }

    public function load_template($template)
    {
        if (is_page_template('webnorth-map-template')) {
            $template = plugin_dir_path(__FILE__) . 'index.php';
        }
        return $template;
    }

    public function render_template()
    {
        // I used get_header so enqueues, SEO and other WP features can be used. For simplicity of the setup I will hide this header and just use the logo from a file
        get_header();
?>

        <header id="map-header">
            <a href="<?php echo get_bloginfo('url'); ?>">
                <img src="<?php echo $this->map_app_path; ?>/assets/images/webnorth-logo.png" alt="Webnorth Logo">
            </a>
        </header>
        <main id="main-content">
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div id="map-app" data-gradient="true">
                    <div class="gradient-overlay"></div>
                    <div id="hero">
                        <h1>WeatherWay</h1>
                        <span>Scroll</span>
                    </div>
                    <div id="map-container">
                        <div class="map-sidebar">
                            <div class="map-header">
                                <a class="webnorth-sidebar-logo" href="<?php echo get_bloginfo('url'); ?>">
                                    <img src="<?php echo $this->map_app_path; ?>/assets/images/webnorth-logo.png" alt="Webnorth Logo">
                                </a>
                                <div class="units-switcher">
                                    <span class="unit-option unit-celsius active">Celsius</span>
                                    <span>/</span>
                                    <span class="unit-option unit-fahrenheit">Fahrenheit</span>
                                </div>
                                <div class="bookmark-icon">
                                    <img class="bookmark" src="<?php echo $this->map_app_path; ?>/assets/images/bookmark.png" alt="Bookmark icon" />
                                    <img class="bookmarked" src="<?php echo $this->map_app_path; ?>/assets/images/bookmarked.png" alt="Bookmarked icon" />
                                </div>
                            </div>
                            <div class="map-content">
                                <b class="default-message">Click on the map to get weather data</b>
                                <div class="weather-content"></div>
                            </div>
                            <div class="map-footer">
                                <a href="#">My locations</a>
                            </div>
                        </div>
                        <div id="map"></div>
                    </div>
                </div>
            </article>
        </main>
<?php
    }
}
