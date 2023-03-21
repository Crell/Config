<?php

declare(strict_types=1);

namespace Crell\Config;

class YamlFileSourceTest extends FileSourceTestBase
{
    protected string $class = YamlFileSource::class;

    protected string $format = 'yaml';
}
