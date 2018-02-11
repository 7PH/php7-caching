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

for ($call_count = 1000; $call_count < 10000; $call_count += 1000) {

    /* FileSystem cache */
    $start_time = microtime(true);
    for ($i = 0; $i < $call_count; $i++) {
        cache_load('benchmark', $generator_function, 60, $has_regen);
    }
    $duration1 = (microtime(true) - $start_time) / $call_count;

    /* Memcached cache */
    $start_time = microtime(true);
    for ($i = 0; $i < $call_count; $i++) {
        memcached_load($memcached, 'benchmark', $generator_function, 60, $has_regen);
    }
    $duration2 = (microtime(true) - $start_time) / $call_count;

    echo "number of calls                   : " . $call_count . "\n";
    echo "duration for filesystem cache     : " . number_format($duration1, 8) . "s" . "\n";
    echo "duration for memcached            : " . number_format($duration2, 8) . "s" . "\n";
}

/*
Results (HDD+SSD):
    number of calls                   : 1000
    duration for filesystem cache     : 0.00028525s (HDD)
    duration for filesystem cache     : 0.00001211s (SSD)
    duration for memcached            : 0.00000140s
    number of calls                   : 5000
    duration for filesystem cache     : 0.00019725s (HDD)
    duration for filesystem cache     : 0.00001224s (SSD)
    duration for memcached            : 0.00000154s
    number of calls                   : 9000
    duration for filesystem cache     : 0.00019770s (HDD)
    duration for filesystem cache     : 0.00001209s (SSD)
    duration for memcached            : 0.00000142s
*/