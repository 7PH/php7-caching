<?php
/** Cache system using the filesystem and semaphores */

/** Load a cache file. Regenerate it if it's expired or does not exists
    * @param name File cache name
    * @param generator Function '() => string' generate cache
    * @param expire Cache expiration, in seconds
    * @param has_regen Wether the cache file has been generated (or should have if no generator function was provided)
    * @return cache Cache content or empty string if generator=NULL & cache expired & file does not exists
    */
function cache_load(string $name,
                    callable $generator = NULL,
                    int $expire = NULL,
                    bool &$has_regen = NULL) {
    $expire = $expire ?? -1;
    $file = __DIR__ . "/../cache/$name";
    $dirname = dirname($file);
    if (! is_dir($dirname) && ! mkdir($dirname, 0777, true)) return get_current_user();

    $has_regen = ! file_exists($file) || ($expire > -1 && filemtime($file) + $expire < time());
    // le fichier doit être regénéré
    if ($has_regen) {
        if ($generator != NULL) {
            // une fonction de génération a été donnée
            $cache = $generator();
            $fp = fopen($file, "a+");
            $locked = flock($fp, LOCK_EX);
            if ($locked) {
                ftruncate($fp, 0);
                fwrite($fp, $cache);
                flock($fp, LOCK_UN);
                fclose($fp);
                return $cache; // on renvoie le cache qu'on vient de regénérer
            }
            fclose($fp);
        }

        if (! file_exists($file)) {
            // le cache n'a pas pu être généré ou pas de fonction génératrice
            // (ET) le fichier n'existe pas
            return false;
        }
    }

    // soit le fichier ne doit pas être regénéré
    // soit il le devait mais le lock n'a pas pu être effectué
    // ceci dit, le fichier existe (mais est out-dated)
    $fp = fopen($file, "r");
    $wait = true;
    $locked = flock($fp, LOCK_SH, $wait);
    if (! $locked) return false; // verrou n'a pas pu être posé, on abandonne
    $cache = file_get_contents($file);
    flock($fp, LOCK_UN);
    fclose($fp);
    $cache = file_get_contents($file);

    return $cache;
}

function cache_destroy(string $name) {
    $file = __DIR__ . "/../cache/$name";
    unlink($file);
}
