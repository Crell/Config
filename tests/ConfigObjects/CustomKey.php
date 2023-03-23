<?php

declare(strict_types=1);

namespace Crell\Config\ConfigObjects;

use Crell\Config\Config;

#[Config(key: 'myname')]
class CustomKey
{
    public function __construct(
        public readonly string $stuff = 'stuff',
        public readonly string $things = 'things',
    ) {}
}
