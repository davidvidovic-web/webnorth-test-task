<?php

namespace Webnorth\Pages;

if (!defined('ABSPATH')) {
    exit;
}

class Create_Map_Page
{
    private $page_slug = 'map';
    private $page_title = 'Map';
    private $page_template = 'webnorth-map-template';

    public function __construct()
    {
        add_filter('display_post_states', [$this, 'add_custom_post_state'], 10, 2);
        add_filter('template_include', [$this, 'load_custom_template']);
    }

    public function create_page_on_activation()
    {
        if (get_page_by_path($this->page_slug) === null) {
            $this->create_page();
        }
    }

    private function create_page()
    {
        $page_data = [
            'post_title'  => $this->page_title,
            'post_name'   => $this->page_slug,
            'post_status' => 'publish',
            'post_type'   => 'page',
        ];

        $page_id = wp_insert_post($page_data);

        if ($page_id) {
            update_post_meta($page_id, '_custom_page_message', 'Map Page');
            update_post_meta($page_id, '_wp_page_template', $this->page_template);
        }
    }

    public function add_custom_post_state($post_states, $post)
    {
        if (get_post_meta($post->ID, '_custom_page_message', true) === 'Map Page') {
            $post_states[] = __('Map Page', 'webnorth-frontend-plugin');
        }
        return $post_states;
    }

    public function load_custom_template($template)
    {
        if (is_page() && get_page_template_slug() === $this->page_template) {
            $plugin_template = plugin_dir_path(__FILE__) . '../Map/index.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }
}
