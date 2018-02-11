<?php


require_once '../src/cache.php';
require_once '../src/memcached.php';

$start_time = microtime(true);
$memcached = new Memcached();
$memcached_load_duration = microtime(true) - $start_time;

/** Heavy function which takes time to compute */
$generator_function = function(): array {
    $a = [];
    $a['time'] = microtime(true);
    return $a;
};

for ($call_count = 100; $call_count < 10000; $call_count += floor($call_count / 5)) {

    /* FileSystem cache */
    $start_time = microtime(true);
    for ($i = 0; $i < $call_count; $i++) {
        cache_load('benchmark', $generator_function, 60, $has_regen);
    }
    $duration1 = (microtime(true) - $start_time);

    /* Memcached cache */
    $start_time = microtime(true);
    for ($i = 0; $i < $call_count; $i++) {
        cache_load('benchmark', $generator_function, 60, $has_regen);
    }
    $duration2 = (microtime(true) - $start_time);

    echo "number of calls                   : " . $call_count . "\n";
    echo "duration for filesystem cache     : " . $duration1 . "s" . "\n";
    echo "duration for memcached            : " . $duration2 . "s" . "\n";
}