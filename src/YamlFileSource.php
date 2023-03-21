<?php

declare(strict_types=1);

namespace Crell\Config;

use Symfony\Component\Yaml\Yaml;

readonly class YamlFileSource implements ConfigSource
{
    public function __construct(
        private string $directory,
    ) {}

    public function load(string $id): array
    {
        $filePath = $this->directory . '/' . $id . '.yaml';
        if (file_exists($filePath)) {
            $data = Yaml::parseFile($filePath);
        }

        return $data ?? [];
    }
}
