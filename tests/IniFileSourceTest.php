<?php

declare(strict_types=1);

namespace Crell\Config;

class IniFileSourceTest extends FileSourceTestBase
{
    protected string $class = IniFileSource::class;

    protected string $format = 'ini';
}
