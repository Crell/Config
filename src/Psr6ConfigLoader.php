<?php

declare(strict_types=1);

namespace Crell\Config;

use Psr\Cache\CacheItemPoolInterface;

/**
 * PSR-6 bridge for caching the loader.
 */
class Psr6ConfigLoader implements ConfigLoader
{
    public function __construct(
        private readonly ConfigLoader $loader,
        private readonly CacheItemPoolInterface $pool,
    ) {}

    public function load(string $class): object
    {
        $key = $this->buildKey($class);

        $item = $this->pool->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        // No expiration; the cached data would only need to change
        // if the config files change.
        $value = $this->loader->load($class);
        $item->set($value);
        $this->pool->save($item);
        return $value;
    }

    /**
     * Generates the cache key for this request.
     */
    private function buildKey(string $class): string
    {
        return str_replace('\\', '_', $class);
    }
}
