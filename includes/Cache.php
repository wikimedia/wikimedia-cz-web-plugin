<?php

interface Cache {
    public function get($key);

    public function set($key, $value);
}