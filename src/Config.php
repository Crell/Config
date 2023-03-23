<?php

declare(strict_types=1);

namespace Crell\Config;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Config
{
    public function __construct(
        public readonly ?string $key = null,
    ) {}
}
