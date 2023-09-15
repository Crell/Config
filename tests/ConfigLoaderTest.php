<?php

declare(strict_types=1);

namespace Crell\Config;

use Crell\Config\ConfigObjects\Dashboard\Dashboard;
use Crell\Config\ConfigObjects\Dashboard\LatestPosts;
use Crell\Config\ConfigObjects\Dashboard\PostsNeedModeration;
use Crell\Config\ConfigObjects\Dashboard\Side;
use Crell\Config\ConfigObjects\Dashboard\UserStatus;
use Crell\Config\ConfigObjects\Sample;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConfigLoaderTest extends TestCase
{
    #[Test]
    public function trivial_loading_from_one_source(): void
    {
        $source = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['int' => 5, 'string' => 'beep', 'float' => 3.14];
            }
        };

        $loader = new LayeredLoader([$source]);

        $config = $loader->load(Sample::class);

        self::assertInstanceOf(Sample::class, $config);
        self::assertEquals(5, $config->int);
        self::assertEquals('beep', $config->string);
        self::assertEquals(3.14, $config->float);
    }

    #[Test]
    public function loading_from_multiple_sources_merge_cleanly(): void
    {
        $source1 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['int' => 5];
            }
        };

        $source2 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['string' => 'beep'];
            }
        };

        $source3 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['float' => 3.14];
            }
        };

        $loader = new LayeredLoader([$source1, $source2, $source3]);

        $config = $loader->load(Sample::class);

        self::assertInstanceOf(Sample::class, $config);
        self::assertEquals(5, $config->int);
        self::assertEquals('beep', $config->string);
        self::assertEquals(3.14, $config->float);
    }

    #[Test]
    public function loading_from_multiple_overlapping_sources_merge_cleanly(): void
    {
        $source1 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['int' => 3];
            }
        };

        $source2 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['string' => 'beep', 'int' => 5, 'float' => 1.2];
            }
        };

        $source3 = new class implements ConfigSource {
            public function load(string $id): array
            {
                return ['float' => 3.14];
            }
        };

        $loader = new LayeredLoader([$source1, $source2, $source3]);

        $config = $loader->load(Sample::class);

        self::assertInstanceOf(Sample::class, $config);
        self::assertEquals(5, $config->int);
        self::assertEquals('beep', $config->string);
        self::assertEquals(3.14, $config->float);
    }

    #[Test]
    public function complex_objects_load_cleanly(): void
    {
        $user = new class implements ConfigSource {
            public function load(string $id): array
            {
                return [
                    'name' => 'User dashboard',
                    'me' => ['type' => 'user_status'],
                    'movie_talk' => ['type' => 'latest_posts', 'category' => 'movies'],
                    'music_talk' => ['type' => 'latest_posts', 'category' => 'music', 'side' => 'right'],
                ];
            }
        };

        $admin = new class implements ConfigSource {
            public function load(string $id): array
            {
                return [
                    'name' => 'Admin dashboard',
                    'mod_todo' => ['type' => 'pending', 'side' => 'right'],
                ];
            }
        };

        $loader = new LayeredLoader([$user, $admin]);

        $config = $loader->load(Dashboard::class);

        self::assertInstanceOf(Dashboard::class, $config);
        self::assertEquals('Admin dashboard', $config->name);
        self::assertInstanceOf(UserStatus::class, $config->components['me']);
        self::assertEquals(Side::Left, $config->components['me']->side);
        self::assertInstanceOf(LatestPosts::class, $config->components['movie_talk']);
        self::assertEquals('movies', $config->components['movie_talk']->category);
        self::assertEquals(Side::Left, $config->components['movie_talk']->side);
        self::assertInstanceOf(LatestPosts::class, $config->components['music_talk']);
        self::assertEquals('music', $config->components['music_talk']->category);
        self::assertEquals(Side::Right, $config->components['music_talk']->side);
        self::assertInstanceOf(PostsNeedModeration::class, $config->components['mod_todo']);
        self::assertEquals(Side::Right, $config->components['mod_todo']->side);
    }
}
