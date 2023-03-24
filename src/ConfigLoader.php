<?php

declare(strict_types=1);

namespace Crell\Config;

interface ConfigLoader
{
    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public function load(string $class): object;
}
