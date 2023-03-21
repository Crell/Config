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
        return file_exists($filePath) ? Yaml::parseFile($filePath) : [];
    }
}
