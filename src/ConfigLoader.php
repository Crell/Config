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
        $id = $this->deriveID($class);

        $layers = [];
        foreach ($this->sources as $source) {
            $layers[] = $source->load($id);
        }

        $data = [];
        foreach ($layers as $layer) {
            foreach ($layer as $k => $v) {
                $data[$k] = $v;
            }
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
