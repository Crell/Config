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
     *   Nested values will merge, meaning foo.bar and foo.baz, if defined in
     *   different layers, will both end up used in the nested foo object.
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

        $layers = array_map(static fn(ConfigSource $source): array => $source->load($id), $this->sources);

        $data = array_reduce($layers, $this->integrateLayer(...), []);

        return $this->serde->deserialize($data, from: 'array', to: $class);
    }

    private function integrateLayer(array $data, array $layer): array
    {
        foreach ($layer as $k => $v) {
            if (isset($data[$k]) && is_array($data[$k])) {
                $data[$k] = $this->integrateLayer($data[$k], $v);
            } else {
                $data[$k] = $v;
            }
        }
        return $data;
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
