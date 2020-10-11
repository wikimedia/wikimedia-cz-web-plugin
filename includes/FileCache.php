<?php

/**
 * File-backend cache. Do not use for high amount of data,
 * as the current implmentation always loads everything into memory.
 */
class FileCache implements Cache {
    /** @var string */
    private $cacheFile;

    /** @var array */
    private $cacheContent;

    /** @var int */
    private $expiry;

    /**
     * @param string $name Unique name of the cache
     * @param int $expiry When does the cache expire? Zero to disable.
     */
    public function __construct($name, $expiry = 0) {
        $this->cacheFile = dirname( __FILE__ ) .  '/../data/' . $name . '.json';
        $this->expiry = $expiry;
    }
    
    private function getCacheContent() {
        if (
            !file_exists( $this->cacheFile ) ||
            filesize( $this->cacheFile ) < 10
        ) {
            $this->invalidate();
            return [];
        }

        if (
            $this->expiry > 0 &&
            (time()-filemtime( $this->cacheFile  )) > $this->expiry
        ) {
            $this->invalidate();
            return [];
        }

        if ($this->cacheContent) {
            return $this->cacheContent;
        }

        $this->cacheContent = json_decode( file_get_contents( $this->cacheFile ), true );
        return $this->cacheContent;
    }

    private function invalidate() {
        file_put_contents( $this->cacheFile, json_encode( [] ) );
    }

    public function get($key) {
        $cache = $this->getCacheContent();
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        return null;
    }

    public function set($key, $value) {
        $cache = $this->getCacheContent();
        $cache[$key] = $value;
        file_put_contents( $this->cacheFile, json_encode( $cache ) );
        $this->cacheContent = $cache;
    }
}
