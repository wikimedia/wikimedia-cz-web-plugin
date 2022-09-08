<?php

/**
 * Class represents /configuration.json
 */
class WmczConfiguration {

    /** @var self|null */
    private static $instance = null;

    /** @var array|null */
    private $configCache = null;

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
     * @return array
     */
    private function readConfigUncached() {
        return json_decode( file_get_contents( $this->getPath() ), true );
    }

    /**
     * Read configuration from disk
     *
     * @return array
     */
    public function readConfig() {
        if ( $this->configCache === null ) {
            $this->configCache = $this->readConfigUncached();
        }
        return $this->configCache;
    }

    /**
     * Get a variable from configuration
     *
     * @param string $variable
     * @return mixed
     */
    public function get( $variable ) {
        $config = $this->readConfig();
        if ( !array_key_exists( $variable, $config ) ) {
            return null;
        }

        return $config[$variable];
    }
}
