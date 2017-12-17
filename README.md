# PHP7 Tools for caching

Just a set of useful functions in order to manage cache system in PHP7

## FileSystem Cache

```php
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
```
