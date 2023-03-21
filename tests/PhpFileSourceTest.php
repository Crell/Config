<?php

declare(strict_types=1);

namespace Crell\Config;

class PhpFileSourceTest extends FileSourceTestBase
{
    protected string $class = PhpFileSource::class;

    protected string $format = 'php';
}
