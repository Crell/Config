<?php

declare(strict_types=1);

namespace Crell\Config;

use Crell\Config\ConfigObjects\Sample;
use PHPUnit\Framework\Attributes\Test;

/**
 * Use this trait in a test class for each cache implementation.
 */
trait CacheTestMethods
{
    abstract public function getTestSubject(): ConfigLoader;

    protected function getMockLoader(): ConfigLoader
    {
        return new class implements ConfigLoader {
            public function load(string $class): Sample
            {
                return new Sample(5, 'beep', 3.14);
            }
        };
    }

    #[Test]
    public function cache_loader(): void
    {
        $loader = $this->getTestSubject();

        $o1 = $loader->load(Sample::class);
        $o2 = $loader->load(Sample::class);

        // We're only checking for value equality, not identity,
        // because the file system cache should not be enforcing identity.
        self::assertEquals($o1, $o2);
    }
}
