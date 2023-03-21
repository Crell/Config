<?php

declare(strict_types=1);

namespace Crell\Config;

use Crell\Config\ConfigObjects\Sample;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    use FakeFilesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupFilesystem();
    }

    #[Test]
    public function real_sources_load_and_mask(): void
    {
        $loader = new ConfigLoader([
            new YamlFileSource($this->root->getChild('data/base')->url()),
            new YamlFileSource($this->root->getChild('data/dev')->url()),
        ]);

        $config = $loader->load(Sample::class);

        self::assertInstanceOf(Sample::class, $config);
        self::assertEquals(5, $config->int);
        self::assertEquals('beep', $config->string);
        self::assertEquals(3.14, $config->float);
    }


    // @todo Still need to test more complex objects.
}
