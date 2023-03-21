<?php

declare(strict_types=1);

namespace Crell\Config;

use Crell\Serde\Serde;
use Crell\Serde\SerdeCommon;

readonly class ConfigLoader
{
    /**
     * @param ConfigSource[] $sources
     */
    public function __construct(
        private array $sources,
        private Serde $serde = New SerdeCommon(),
    ) {}

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public function load(string $class): object
    {
        $id = $this->deriveId($class);

        $layers = [];
        // Go through the sources in reverse order to account for the += behavior below.
        foreach (array_reverse($this->sources) as $source) {
            $layers[] = $source->load($id);
        }

        $data = [];
        foreach ($layers as $layer) {
            // This makes the first layer with a value win.
            $data += $layer;
        }

        $config = $this->serde->deserialize($data, from: 'array', to: $class);

        return $config;
    }

    /**
     * @param class-string $class
     * @todo Make this flexible. It's just a stub for now.
     */
    private function deriveId(string $class): string
    {
        return strtolower(str_replace('\\', '_', $class));
    }
}
