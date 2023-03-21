<?php

declare(strict_types=1);

namespace Crell\Config;

class JsonFileSourceTest extends FileSourceTestBase
{
    protected string $class = JsonFileSource::class;

    protected string $format = 'json';
}
