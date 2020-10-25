<?php

spl_autoload_register(function ($class) {
    $filename = str_replace( 'wmcz', '', str_replace( 'Wmcz', '', $class ) );
    $path = dirname( __FILE__ ) . '/includes/' . $filename . '.php';

    if ( file_exists( $path ) ) {
        include $path;
        return true;
    } else {
        $filename = str_replace( 'wmcz', '', strtolower($class) );
        $path = dirname( __FILE__ ) . '/includes/' . $filename . '.php';

        if ( file_exists( $path ) ) {
            include $path;
            return true;
        }
    }

    return false;
});
require_once 'vendor/autoload.php';
