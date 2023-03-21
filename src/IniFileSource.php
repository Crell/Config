<?php

declare(strict_types=1);

namespace Crell\Config;

readonly class IniFileSource implements ConfigSource
{
    public function __construct(
        private string $directory,
    ) {}

    public function load(string $id): array
    {
        $filePath = $this->directory . '/' . $id . '.ini';
        if (!file_exists($filePath)) {
            return [];
        }
        return parse_ini_file($filePath) ?: [];
    }
}
