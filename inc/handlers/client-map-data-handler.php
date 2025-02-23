<?php

namespace Webnorth\Handlers;

if (!defined('ABSPATH')) {
    exit;
}

class Client_Map_Data_Handler
{
    private $api_base_url = 'https://api.openweathermap.org/data/3.0/onecall';

    public function __construct()
    {
        add_action('wp_ajax_get_weather_data', [$this, 'handle_weather_data_request']);
        add_action('wp_ajax_nopriv_get_weather_data', [$this, 'handle_weather_data_request']);
    }

    public function handle_weather_data_request()
    {
        if (!check_ajax_referer('weather_data_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $lat = isset($_POST['lat']) ? sanitize_text_field($_POST['lat']) : '';
        $lon = isset($_POST['lon']) ? sanitize_text_field($_POST['lon']) : '';

        if (empty($post_id)) {
            wp_send_json_error('Missing post ID');
        }

        if (empty($lat) || empty($lon)) {
            wp_send_json_error('Missing coordinates');
        }

        $api_key = get_option('webnorth_openweather_api_key');
        if (empty($api_key)) {
            wp_send_json_error('Missing API key');
        }

        // check if cached
        $transient_key = "weather_data_{$post_id}";
        $cached_data = get_transient($transient_key);
        
        if ($cached_data !== false) {
            error_log("Cache hit for station {$post_id}");
            wp_send_json_success([
                'data' => $cached_data,
                'source' => 'cache',
                'cached_at' => get_post_meta($post_id, 'weather_data_cached_at', true)
            ]);
            return;
        }

        error_log("Cache miss for station {$post_id}, fetching from API");

        $url = add_query_arg([
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $api_key,
            'exclude' => 'minutely,hourly,daily,alerts'
        ], $this->api_base_url);

        $response = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version')
            ]
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error('API request failed: ' . $response->get_error_message());
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($data) || empty($data['current'])) {
            wp_send_json_error('Invalid API response');
        }

        // conversion for units so we don't have to make multiple API calls
        $kelvin_temp = $data['current']['temp'];
        $kelvin_feels_like = $data['current']['feels_like'];

        $celsius = $kelvin_temp - 273.15;
        $celsius_feels_like = $kelvin_feels_like - 273.15;

        $fahrenheit = ($celsius * 9 / 5) + 32;
        $fahrenheit_feels_like = ($celsius_feels_like * 9 / 5) + 32;

        $weather_data = [
            'post_id' => $post_id,
            'weather' => [
                'main' => $data['current']['weather'][0]['main'],
                'description' => $data['current']['weather'][0]['description'],
                'pressure' => $data['current']['pressure'],
                'humidity' => $data['current']['humidity'],
            ],
            'metric' => [
                'temp' => number_format($celsius, 1, '.', ''),
                'feels_like' => number_format($celsius_feels_like, 1, '.', '')
            ],
            'imperial' => [
                'temp' => number_format($fahrenheit, 1, '.', ''),
                'feels_like' => number_format($fahrenheit_feels_like, 1, '.', '')
            ]
        ];

        // Store cache timestamp
        $cache_timestamp = current_time('timestamp');
        set_transient($transient_key, $weather_data, 24 * HOUR_IN_SECONDS);
        update_post_meta($post_id, 'weather_data_cached_at', $cache_timestamp);
        update_field('weather_data', wp_json_encode($weather_data), $post_id);

        wp_send_json_success([
            'data' => $weather_data,
            'source' => 'api',
            'cached_at' => $cache_timestamp
        ]);
    }
}
