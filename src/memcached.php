<?php
/** Cache system using Memcached */

/** Load a cache file. Regenerate it if it's expired or does not exists
    * @param mem MemCached client
    * @param name Name of the cache file to retrieve
    * @param generator Function '() => string' generate cache
    * @param expire Cache expiration, in seconds
    * @param has_regen Wether the cache file has been generated (or should have if no generator function was provided)
    * @return cache Cache content or 'false' if generator=NULL & cache expired & file does not exists
    */
function memcached_load(Memcached &$mem,
                    string $name,
                    callable $generator = NULL,
                    int $expire = NULL,
                    bool &$has_regen = NULL) {
    $value = $mem->get($name);
    $has_regen = $value === false;
    if ($has_regen) {
        // element not set
        if ($generator != NULL) {
            $value = $generator();
            $mem->set($name, $value, $expire);
        } else {
            $value = false;
        }
    }
    return $value;
}
