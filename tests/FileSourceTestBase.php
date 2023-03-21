<?php

declare(strict_types=1);

namespace Crell\Config;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

abstract class FileSourceTestBase extends TestCase
{
    use FakeFilesystem;

    /**
     * The PHP class to test.  Child classes should set this.
     */
    protected string $class;

    /**
     * The file format to look for.  Child classes should set this.
     */
    protected string $format;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupFilesystem();
    }

    #[Test]
    public function missing_file_returns_empty_array(): void
    {
        $source = new ($this->class)($this->dataDir->url() . '/empty');

        $ret = $source->load('crell_config_sample');

        self::assertEmpty($ret);
    }

    #[Test]
    public function found_file_returns_correct_array(): void
    {
        $source = new ($this->class)($this->dataDir->url() . '/' . $this->format);

        $ret = $source->load('crell_config_sample');

        self::assertEquals(5, $ret['int']);
    }
}
