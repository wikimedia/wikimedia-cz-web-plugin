<?php

/**
 * Class represents /configuration.json
 */
class WmczConfiguration {

    /** @var self|null */
    private static $instance = null;

    private function __construct() {
    }

    /**
     * Get configuration singleton
     */
    public static function singleton() {
        if ( self::$instance === null ) {
            self::$instance = new WmczConfiguration();
        }
        return self::$instance;
    }

    /**
     * Get path to the configuration object
     *
     * @return string
     */
    private function getPath() {
        return realpath( dirname( __FILE__ ) . '/../configuration.json' );
    }

    /**
     * Read configuration from disk, with no caching
     *
     * @param bool $assoc
     * @return stdClass|array
     */
    private function readConfigUncached( bool $assoc ) {
        return json_decode( file_get_contents( $this->getPath() ), $assoc );
    }

    /**
     * Read configuration from disk
     *
     * @param bool $assoc
     * @return stdClass|array
     */
    public function readConfig( bool $assoc = false ) {
        // TODO: Implement caching
        return $this->readConfigUncached( $assoc );
    }

    /**
     * Get a variable from configuration
     *
     * @param string $variable
     * @return mixed
     */
    public function get( $variable ) {
        $config = $this->readConfig( true );
        if ( !array_key_exists( $variable, $config ) ) {
            return null;
        }

        return $config[$variable];
    }
}
