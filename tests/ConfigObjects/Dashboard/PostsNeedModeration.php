<?php

declare(strict_types=1);

namespace Crell\Config\ConfigObjects\Dashboard;

class PostsNeedModeration implements DashboardComponent
{
    public function __construct(
        public int $count = 5,
        public Side $side = Side::Left,
    ) {}

    public function side(): Side
    {
        return $this->side;
    }
}
