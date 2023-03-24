<?php

declare(strict_types=1);

namespace Crell\Config;

use PHPUnit\Framework\TestCase;

class SerializedFilesystemCacheTest extends TestCase
{
    use CacheTestMethods;
    use FakeFilesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupFilesystem();
    }

    public function getTestSubject(): ConfigLoader
    {
        return new SerializedFilesystemCache($this->getMockLoader(), $this->root->getChild('cache')->url());
    }

}
