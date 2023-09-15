<?php

declare(strict_types=1);

namespace Crell\Config\ConfigObjects\Dashboard;

readonly class UserStatus implements DashboardComponent
{
    public function __construct(
        public string $user,
        public Side $side = Side::Left,
    ) {}

    public function side(): Side
    {
        return $this->side;
    }
}
