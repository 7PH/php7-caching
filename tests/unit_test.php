<?php

require_once '../src/cache.php';
require_once '../src/memcached.php';

$generator = function() {
    $a = [];
    for ($i = 0; $i < 4; $i ++)
        $a['time'] = time();
    return $a;
};

$cache = unserialize(cache_load('unit-test', $generator, 2, $has_regen));
var_dump($cache !== NULL);

sleep(3);

$old_cache = $cache;
$cache = unserialize(cache_load('unit-test', $generator, 2, $has_regen));
var_dump($old_cache['time'] !== $cache['time']);

$old_cache = $cache;
$cache = unserialize(cache_load('unit-test', $generator, 2, $has_regen));
var_dump($old_cache['time'] === $cache['time']);

$memcached = new Memcached();
$cache = memcached_load($memcached, 'unit-test', $generator, 2, $has_regen);
var_dump($cache !== NULL);

sleep(3);

$old_cache = $cache;
$cache = memcached_load($memcached, 'unit-test', $generator, 2, $has_regen);
var_dump($old_cache['time'] !== $cache['time']);

$old_cache = $cache;
$cache = memcached_load($memcached, 'unit-test', $generator, 2, $has_regen);
var_dump($old_cache['time'] === $cache['time']);