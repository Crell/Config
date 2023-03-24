<?php

declare(strict_types=1);

namespace Crell\Config;

class SerializedFilesystemCache implements ConfigLoader
{
    public function __construct(
        private readonly ConfigLoader $loader,
        private readonly string $directory,
    ) {}

    public function load(string $class): object
    {
        $cacheFile = $this->directory . '/' . $this->buildKey($class);

        if (file_exists($cacheFile) && $cached = file_get_contents($cacheFile)) {
            return unserialize($cached);
        }

        $config = $this->loader->load($class);

        file_put_contents($cacheFile, serialize($config));
        return $config;
    }

    /**
     * Generates the cache key for this request.
     */
    private function buildKey(string $class): string
    {
        return str_replace('\\', '_', $class);
    }
}
