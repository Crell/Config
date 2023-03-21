<?php

declare(strict_types=1);

namespace Crell\Config;

readonly class PhpFileSource implements ConfigSource
{
    /**
     * @param array<string> $formats
     */
    public function __construct(
        private string $directory,
    ) {}

    public function load(string $id): array
    {
        $filePath = $this->directory . '/' . $id . '.php';
        if (file_exists($filePath)) {
            $data = require $filePath;
        }

        return $data ?? [];
    }
}
