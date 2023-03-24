<?php

declare(strict_types=1);

namespace Crell\Config;

use Fig\Cache\Memory\MemoryPool;
use PHPUnit\Framework\TestCase;

class Psr6ConfigLoaderTest extends TestCase
{
    use CacheTestMethods;

    public function getTestSubject(): ConfigLoader
    {
        return new Psr6ConfigLoader($this->getMockLoader(), new MemoryPool());
    }

}
