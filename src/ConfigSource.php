<?php

declare(strict_types=1);

namespace Crell\Config;

interface ConfigSource
{
    public function load(string $id): array;
}
