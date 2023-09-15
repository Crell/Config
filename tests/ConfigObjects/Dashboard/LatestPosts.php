<?php

declare(strict_types=1);

namespace Crell\Config\ConfigObjects\Dashboard;

class LatestPosts implements DashboardComponent
{
    public function __construct(
        public string $category,
        public Side $side = Side::Left,
    ) {}

    public function side(): Side
    {
        return $this->side;
    }
}
