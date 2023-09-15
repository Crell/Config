<?php

declare(strict_types=1);

namespace Crell\Config\ConfigObjects\Dashboard;

use Crell\Serde\Attributes\StaticTypeMap;

interface DashboardComponent
{
    public function side(): Side;
}
