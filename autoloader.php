<?php

if (!defined('ABSPATH')) {
    exit;
}

class Autoloader
{

    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * PSR-4 autoloader.
     *
     * @param string $class The fully-qualified class name.
     * @return void
     */
    public static function autoload($class)
    {
        $prefix = 'Webnorth\\';
        $base_dir = __DIR__ . '/inc/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // No, move to the next registered autoloader
            return;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
        } else {
            error_log("File not found for class: $class");
        }
    }
}

Autoloader::register();
