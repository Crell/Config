<?php

declare(strict_types=1);

namespace Crell\Config\ConfigObjects;

use Crell\Serde\Attributes\ClassSettings;
use Crell\Serde\Renaming\Prefix;

#[ClassSettings(renameWith: new Prefix('db_'))]
class DbSettings
{
    public function __construct(
        public readonly string $name,
        public readonly string $host,
        public readonly string $user,
        public readonly string $pass,
        public readonly int $port = 3306
    ) {}
}
