<?php

namespace Webnorth\Pages;

if (!defined('ABSPATH')) {
    exit;
}

class Create_Map_Page
{
    private $page_slug = 'map';
    private $page_title = 'Map';

    public function __construct()
    {
        add_filter('display_post_states', [$this, 'add_custom_post_state'], 10, 2);
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
        }
    }

    public function add_custom_post_state($post_states, $post)
    {
        if (get_post_meta($post->ID, '_custom_page_message', true) === 'Map Page') {
            $post_states[] = __('Map Page', 'webnorth-frontend-plugin');
        }
        return $post_states;
    }
}
