<?php

declare(strict_types=1);

namespace Crell\Config\ConfigObjects\Dashboard;

use Crell\Config\Config;
use Crell\Serde\Attributes\DictionaryField;
use Crell\Serde\Attributes\Field;
use Crell\Serde\Attributes\StaticTypeMap;
use Crell\Serde\KeyType;

#[Config('dashboard')]
class Dashboard
{
    /**
     * @param array<string, LatestPosts|UserStatus|PostsNeedModeration> $components
     */
    public function __construct(
        public string $name,
        #[Field(flatten: true)]
        #[DictionaryField(arrayType: DashboardComponent::class, keyType: KeyType::String)]
        #[StaticTypeMap(key: 'type', map: [
            'latest_posts' => LatestPosts::class,
            'user_status' => UserStatus::class,
            'pending' => PostsNeedModeration::class,
        ])]
        public array $components = [],
    ) {}
}
