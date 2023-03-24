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

    /**
     * Reducing function to fold layers together.
     *
     * The types here are a lie.  Numeric keys will not work.
     * But array_reduce() says int|string, so PHPStan insists
     * we do here as well.
     *
     * @param array<int|string, mixed> $data
     * @param array<int|string, mixed> $layer
     * @return array<int|string, mixed>
     */
    private function integrateLayer(array $data, array $layer): array
    {
        foreach ($layer as $k => $v) {
            $data[$k] = isset($data[$k]) && is_array($data[$k])
                ? $this->integrateLayer($data[$k], $v)
                : $v;
        }
        return $data;
    }

    /**
     *
     * This might be syntactically easier with AttributeUtils,
     * but for just a single class-level attribute it's not worth
     * the extra CPU cycles.
     *
     * @param class-string $class
     */
    private function deriveId(string $class): string
    {
        $rClass = new \ReflectionClass($class);
        /** @var Config[] $attribs */
        $attribs = array_map(static fn(\ReflectionAttribute $a) => $a->newInstance(), $rClass->getAttributes(Config::class, \ReflectionAttribute::IS_INSTANCEOF));

        return $attribs[0]?->key ?? strtolower(str_replace('\\', '_', $class));
    }
}
