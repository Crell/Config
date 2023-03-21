<?php

declare(strict_types=1);

namespace Crell\Config;

use Crell\Serde\Serde;
use Crell\Serde\SerdeCommon;

readonly class ConfigLoader
{
    /**
     * @param ConfigSource[] $sources
     *   A list of sources from which to load values. If a property is defined
     *   in multiple sources, the later one will take precedence.
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

        // @todo I so want to turn this into a simple pipe...

        // Go through the sources in reverse order to account for the += behavior below.
        $layers = array_map(fn(ConfigSource $source): array => $source->load($id), array_reverse($this->sources));

        $reducer = static fn(array $data, array $layer) => $data + $layer;

        $data = array_reduce($layers, $reducer, []);

        return $this->serde->deserialize($data, from: 'array', to: $class);
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
