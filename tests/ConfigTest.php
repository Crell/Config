<?php

declare(strict_types=1);

namespace Crell\Config;

use Crell\Config\ConfigObjects\Complex;
use Crell\Config\ConfigObjects\CustomKey;
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
        $loader = new LayeredLoader([
            new YamlFileSource($this->root->getChild('data/base')->url()),
            new YamlFileSource($this->root->getChild('data/dev')->url()),
        ]);

        $config = $loader->load(Sample::class);

        self::assertInstanceOf(Sample::class, $config);
        self::assertEquals(5, $config->int);
        self::assertEquals('beep', $config->string);
        self::assertEquals(3.14, $config->float);
    }

    #[Test]
    public function nested_sources_load_and_mask_top_level_data(): void
    {
        $loader = new LayeredLoader([
            new YamlFileSource($this->root->getChild('data/base')->url()),
            new YamlFileSource($this->root->getChild('data/dev')->url()),
        ]);

        $config = $loader->load(Complex::class);

        self::assertInstanceOf(Complex::class, $config);
        self::assertEquals('name', $config->db->name);
        self::assertEquals('host', $config->db->host);
        self::assertEquals('user', $config->db->user);
        self::assertEquals('pass', $config->db->pass);
        self::assertEquals(2000, $config->db->port);
        self::assertEquals(3, $config->sample->int);
        self::assertEquals('val', $config->sample->string);
        self::assertEquals(2.3, $config->sample->float);
        self::assertEquals('value', $config->anotherString);
    }

    #[Test]
    public function custom_keys_are_loaded_successfully(): void
    {
        $loader = new LayeredLoader([
            new YamlFileSource($this->root->getChild('data/base')->url()),
            new YamlFileSource($this->root->getChild('data/dev')->url()),
        ]);

        $config = $loader->load(CustomKey::class);

        self::assertInstanceOf(CustomKey::class, $config);
        self::assertEquals('foo', $config->stuff);
        self::assertEquals('bar', $config->things);
    }

}
