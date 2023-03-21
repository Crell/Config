<?php

declare(strict_types=1);

namespace Crell\Config;

use Symfony\Component\Yaml\Yaml;

readonly class JsonFileSource implements ConfigSource
{
    /**
     * @param array<string> $formats
     */
    public function __construct(
        private string $directory,
    ) {}

    public function load(string $id): array
    {
        $filePath = $this->directory . '/' . $id . '.json';
        try {
            if (file_exists($filePath)) {
                $data = json_decode(file_get_contents($filePath), true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (\JsonException) {
            // @todo Probably some kind of error handling.
            return [];
        }

        return $data ?? [];
    }
}
