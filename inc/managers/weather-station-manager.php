<?php

namespace Webnorth\Managers;

use Webnorth\PostTypes\Weather_Station_Post_Type;
use Webnorth\ACF\Weather_Station_ACF_Fields;
use Webnorth\Pages\Create_Map_Page;
use Webnorth\Handlers\Admin_Map_Data_Handler;
use Webnorth\Handlers\Client_Map_Data_Handler;

if (!defined('ABSPATH')) {
    exit;
}

class Weather_Station_Manager
{
    private static $instance = null;
    //property declaration to avoid deprecated warnings
    private $data_handler;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action('init', [$this, 'register_custom_post_type']);
        add_action('admin_init', [$this, 'register_weather_settings']);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('init', [$this, 'init_handlers']);
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

    public function register_weather_settings()
    {
        register_setting(
            'webnorth_weather_options',
            'webnorth_openweather_api_key',
            [
                'type' => 'string',
                'description' => 'OpenWeather API Key',
                'sanitize_callback' => 'sanitize_text_field',
                'show_in_rest' => false,
                'default' => ''
            ]
        );

        add_settings_section(
            'webnorth_weather_api_section',
            'Weather API Settings',
            [$this, 'render_weather_section'],
            'webnorth_weather_settings'
        );

        add_settings_field(
            'webnorth_openweather_api_key',
            'OpenWeather API Key',
            [$this, 'render_api_key_field'],
            'webnorth_weather_settings',
            'webnorth_weather_api_section'
        );
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
        $this->data_handler = new Admin_Map_Data_Handler();
    }

    public function add_settings_page()
    {
        add_options_page(
            'Weather Settings',
            'Weather Settings',
            'manage_options',
            'webnorth_weather_settings',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page()
    {
?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('webnorth_weather_options');
                do_settings_sections('webnorth_weather_settings');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    public function render_weather_section()
    {
        echo '<p>Enter your OpenWeather API key below. You can get one from <a href="https://openweathermap.org/api" target="_blank">OpenWeather</a>.</p>';
    }

    public function render_api_key_field()
    {
        $api_key = get_option('webnorth_openweather_api_key');
    ?>
        <div class="api-key-wrapper">
            <input
                type="password"
                id="webnorth_openweather_api_key"
                name="webnorth_openweather_api_key"
                value="<?php echo esc_attr($api_key); ?>"
                class="regular-text"
                autocomplete="off" />
            <button
                type="button"
                class="button-link toggle-visibility"
                aria-label="Toggle API key visibility">
                <span class="dashicons dashicons-visibility"></span>
            </button>
        </div>
<?php
    }

    public function enqueue_admin_scripts($hook)
    {
        if ($hook === 'settings_page_webnorth_weather_settings') {
            wp_enqueue_style('dashicons');
            wp_enqueue_style(
                'webnorth-admin-styles',
                plugin_dir_url(__FILE__) . '../../assets/css/adminStyles.css'
            );
            wp_enqueue_script(
                'webnorth-admin-js',
                plugin_dir_url(__FILE__) . '../../assets/js/admin.js',
                [],
                '1.0.0',
                true
            );
        }
    }

    public function init_handlers()
    {
        new \Webnorth\Handlers\Client_Map_Data_Handler();
        $this->init_data_handler();
    }
}
