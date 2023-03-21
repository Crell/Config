<?php

declare(strict_types=1);

namespace Crell\Config;

use Crell\Config\ConfigObjects\Sample;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConfigLoaderTest extends TestCase
{
    #[Test]
    public function trivial_loading_from_one_source(): void
    {
        $source = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['int' => 5, 'string' => 'beep', 'float' => 3.14];
            }
        };

        $loader = new ConfigLoader([$source]);

        $config = $loader->load(Sample::class);

        self::assertInstanceOf(Sample::class, $config);
        self::assertEquals(5, $config->int);
        self::assertEquals('beep', $config->string);
        self::assertEquals(3.14, $config->float);
    }

    #[Test]
    public function loading_from_multiple_sources_merge_cleanly(): void
    {
        $source1 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['int' => 5];
            }
        };

        $source2 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['string' => 'beep'];
            }
        };

        $source3 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['float' => 3.14];
            }
        };

        $loader = new ConfigLoader([$source1, $source2, $source3]);

        $config = $loader->load(Sample::class);

        self::assertInstanceOf(Sample::class, $config);
        self::assertEquals(5, $config->int);
        self::assertEquals('beep', $config->string);
        self::assertEquals(3.14, $config->float);
    }


    #[Test]
    public function loading_from_multiple_overlapping_sources_merge_cleanly(): void
    {
        $source1 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['int' => 3];
            }
        };

        $source2 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['string' => 'beep', 'int' => 5, 'float' => 1.2];
            }
        };

        $source3 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['float' => 3.14];
            }
        };

        $loader = new ConfigLoader([$source1, $source2, $source3]);

        $config = $loader->load(Sample::class);

        self::assertInstanceOf(Sample::class, $config);
        self::assertEquals(5, $config->int);
        self::assertEquals('beep', $config->string);
        self::assertEquals(3.14, $config->float);
    }


}
