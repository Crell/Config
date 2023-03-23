<?php

declare(strict_types=1);

namespace Crell\Config\ConfigObjects;

class Complex
{
    public function __construct(
        public readonly Sample $sample,
        public readonly DbSettings $db,
        public readonly string $anotherString = 'default'
    ) {}
}
