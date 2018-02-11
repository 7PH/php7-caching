<?php
/** Cache system using the filesystem and semaphores */

/** Load a cache file. Regenerate it if it's expired or does not exists
    * @param name File cache name
    * @param generator Function '() => string' generate cache
    * @param expire Cache expiration, in seconds
    * @param has_regen Wether the cache file has been generated (or should have if no generator function was provided)
    * @return cache Cache content or 'false' if generator=NULL & cache expired & file does not exists
    */
function cache_load(string $name,
                    callable $generator = NULL,
                    int $expire = NULL,
                    bool &$has_regen = NULL): ?string {
    $expire = $expire ?? -1;
    $file = __DIR__ . "/../cache/$name";
    $dirname = dirname($file);
    if (! is_dir($dirname) && ! mkdir($dirname, 0777, true)) return false;

    $has_regen = ! file_exists($file) || ($expire > -1 && filemtime($file) + $expire < time());
    // cache has expired or does not exist
    if ($has_regen) {
        if ($generator != NULL) {
            // generator function is provided
            $cache = $generator();
            if (gettype($cache) !== 'string') $cache = serialize($cache);
            $fp = fopen($file, "a+");
            $locked = flock($fp, LOCK_EX);
            if ($locked) {
                ftruncate($fp, 0);
                fwrite($fp, $cache);
                flock($fp, LOCK_UN);
                fclose($fp);
                return $cache; // we return the generated cache
            }
            fclose($fp);
        }

        if (! file_exists($file)) {
            // No generator was provided & the file does not exist
            return false;
        }
    }

    // wether the cache is still valid,
    // or he should be regenerated but no generator was provided
    // in this case we return the expired cache
    $fp = fopen($file, "r");
    $wait = true;
    $locked = flock($fp, LOCK_SH, $wait);
    if (! $locked) return false; // lock failed for some reason?
    $cache = file_get_contents($file);
    flock($fp, LOCK_UN);
    fclose($fp);
    $cache = file_get_contents($file);

    return $cache;
}

/** Destroy a cache file
    * @param name Name of the cache file to destroy
    */
function cache_destroy(string $name) {
    $file = __DIR__ . "/../cache/$name";
    unlink($file);
}
