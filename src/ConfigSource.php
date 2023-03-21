<?php

declare(strict_types=1);

namespace Crell\Config;

interface ConfigSource
{
    /**
     * @return array<mixed, mixed>
     */
    public function load(string $id): array;
}
