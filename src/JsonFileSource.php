<?php

declare(strict_types=1);

namespace Crell\Config;

readonly class JsonFileSource implements ConfigSource
{
    public function __construct(
        private string $directory,
    ) {}

    public function load(string $id): array
    {
        $filePath = $this->directory . '/' . $id . '.json';
        try {
            return file_exists($filePath)
                ? json_decode(file_get_contents($filePath) ?: '{}', true, 512, JSON_THROW_ON_ERROR)
                : [];
        } catch (\JsonException) {
            // @todo Probably some kind of error handling.
            return [];
        }
    }
}
