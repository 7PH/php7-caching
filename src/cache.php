<?php
/** Cache system using the filesystem and semaphores */

/** Charge un fichier cache, et le cas échéant le génère
    * @param name Nom du fichier cache
    * @param generator De type '() => string' génère le cache (s'il a expiré ou s'il n'existe pas)
    * @param expire Nombre de secondes après lesquels le cache est considéré comme expiré
    * @param has_regen flag indiquant si le cache a été regénéré (ou s'il aurait du l'être mais que cela était impossible en l'absence de fonction génératrice)
    * @return cache Le fichier de cache généré, trouvé, ou une chaine vide s'il n'a pas été trouvé
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
