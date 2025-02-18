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

class Autoloader
{
    /**
     * Register the autoloader.
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
        error_log('Autoloader registered');
    }

    /**
     * PSR-4 autoloader.
     *
     * @param string $class The fully-qualified class name.
     * @return void
     */
    public static function autoload($class)
    {
        // Project-specific namespace prefix
        $prefix = 'Webnorth\\';

        // Base directory for the namespace prefix
        $base_dir = __DIR__ . '/inc/';

        // Does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // No, move to the next registered autoloader
            return;
        }

        // Get the relative class name
        $relative_class = substr($class, $len);

        // Replace the namespace prefix with the base directory, replace namespace
        // separators with directory separators in the relative class name, append
        // with .php
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // Log the class name and file path
        error_log("Autoloading class: $class, file: $file");

        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
            error_log("Class $class loaded successfully from $file");
        } else {
            error_log("File not found for class: $class");
        }
    }
}

Autoloader::register();
