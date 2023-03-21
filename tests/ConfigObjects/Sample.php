<?php

declare(strict_types=1);

namespace Crell\Config\ConfigObjects;

class Sample
{
    public function __construct(
        public int $int,
        public string $string,
        public float $float,
    ) {}
}
