# Config Loader

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

Config Loader is what it says it is: A simple, fast, but powerful configuration loading system
suitable for any framework.

## How it works

Config Loader is based on "configuration objects."  A configuration object is just a plain PHP class.  Every config object is defined entirely by a PHP class, and each property is a config value.  That means the name, type, and default value of every configuration value is defined and endorsed by ordinary PHP code.  If a value is required, it has no default value set.  If it's optional, the default is specified right there in the code.  While nothing in the system requires it, it's strongly recommended to make config classes `readonly`.

Config objects can be populated in "layers", from different sources.  Typically, that is a file on disk in whatever format you prefer (YAML, PHP, etc.)  The multiple layers allows for individual configuration properties to be overriden, say, to have a base configuration and then modifications for `dev` and `prod` environments.

Because each config object is its own class, it integrates seamlessly with a Dependency Injection Container and testing.  (See the section on DI below.)

Let's see an example.

```php
use Crell\Config\LayeredLoader;
use Crell\Config\YamlFileSource;

class EditorSettings
{
    public function __construct(
        public readonly string $color,
        public readonly string $bgcolor,
        public readonly int $fontSize = 14,
    ) {}
}

$loader = new LayeredLoader([
  new YamlFileSource('./config/common'),
  new YamlFileSource('./config/' . APP_ENV),
]);

$editorConfig = $loader->load(DbSettings::class);
```

Given these files on disk:

```yaml
# config/common/editorsettings.yaml
color: "#ccddee"
bgcolor: "#ffffff"
```

```yaml
# config/dev/editorsettings.yaml
bgcolor: '#eeff00'
```

Now, when this code is run, `$editorConfig` will have a color of `#ccddee`, a bgcolor of `#ffffff`, and a fontSize of `14` (because a default is provided).  If, however, it is run in a `dev` environment (`APP_ENV` is `dev`), then the bgcolor will be `#eeff00`.

The net result is a quick and easy way to load configuration for your application, with full support for per-environment overrides.  Of course, you can use layers in any other way you wish as well. (Vary per system language, for instance.)

You can and should define as many different config objects as you'd like.  They all load separately.  See the section on Dependency Injection below for how that comes in helpful.

## Source types

There are several file formats supported out of the box, including `JsonFileSource`, `YamlFileSource`, `PhpFileSource`, and even `IniFileSource` (because why not?).  Writing other sources is simple, as the `ConfigSource`interface has only a single method.  You can even stack multiple file types with the same directory to read from into a single list, if you want to support multiple file types.

## Custom file keys

By default, the identifier and thus filename for each config object is its class's full name, lowercased and with `\` replaced by `_`.  So `My\App\Config\EditorSettings` would become `my_app_config_editorsettings.yaml` (or whatever file format).

That is frequently not a nice filename.  However, you may customize the key via an attribute, like so:

```php
use Crell\Config\Config;

#[Config(key: 'editor_settings')]
class EditorSettings
{
    public function __construct(
        public readonly string $color,
        public readonly string $bgcolor,
        public readonly int $fontSize = 14,
    ) {}
}
```

Now, this class's filename will be `editor_settings.yaml` (and similar).

## Complex objects

Config Loader uses the highly powerful and flexible [`Crell/Serde`](https://github.com/Crell/Serde) library to hydrate the config objects.  That means all of Serde's flexibility and power is available via attributes.  See Serde's documentation for all of the possible options, but especially its ability to collect properties up into a sub-object, add or remove prefixes, fold the case of different properties between camelCase and snake_case, and more.  You can also use post-load callback methods for validation to enforce rules beyond what the type system can handle.

If you do not pass a Serde instance to LayeredLoader, one will be created automatically.  For simple usage that is fine, but if wiring the config loader into a Dependency Injection container it is better to inject a managed Serde instance instead.

## Caching

While Serde is reasonably fast, it still has a cost.  If you're loading many configuration objects then the time could add up.

Config Loader ships with two cache wrappers that can be easily wrapped around `LayeredLoader`.

* `Psr6CacheConfigLoader` - Feed it a configured [PSR-6](https://www.php-fig.org/psr/psr-6/) Pool object and an instance of `LayerdLoader` (or, really, any `ConfigLoader` object) and it will transparently cache each config object as it's loaded.
* `SerializedFiesystemCache` - The PSR-6 wrapper has the limitation that you need to have your cache backend already booted up, and Configuration is usually loaded very early in a request.  Instead, you can use a file system cache that saves each object as a PHP serialized value on disk.  All it requires is a path.  This is the fastest possible option, and recommended for most configurations.

Note: Make certain the directory where the file cache is stored is not publicly accessible and secured tightly.  Deserializing PHP objects is fast, but also a potential security vulnerability.  Never allow anything but the cache wrapper to write to that directory.

Example:

```php
use Crell\Config\LayeredLoader;
use Crell\Config\YamlFileSource;
use Crell\Config\SerializedFilesystemCache;

$loader = new LayeredLoader([
  new YamlFileSource('./config/common'),
  new YamlFileSource('./config/' . APP_ENV),
]);

$cachedLoader = new SerializedFilesytemCache($loader, '/path/to/cache/dir');

$cachedLoader->load(EditorSettings::class);
```

## Dependency Injection

What Config Loader is optimized for is wiring into a Dependency Injection Container.  Specifically, it can be used as a factory to produce objects of each config type, which can then be exposed to the container's autowiring functionality.

Consider the following service class:

```php
class EditorForm
{
    public function __construct(
        private EditorSettings $settings,
    ) {}
    
    public function renderForm(): string
    {
        // Do stuff here.
        $this->settings->color;
        
        ...
    }
}
```

It depends on an `EditorSettings` instance.  How it gets it, no one cares.  But it can rely on all the guarantees that PHP provides around type safety, values being defined, autocomplete in your IDE, etc.

You can now trivially test that service in a test by making your own `EditorSettings` instance and passing it in:

```php
class EditorFormTest extends TestCase
{
    #[Test]
    public function some_test(): void
    {
        $settings = new EditorSettings(color: '#fff', bgcolor: '#000');
        
        $subject = new EditorForm($settings);
        
        // Make various assertions.
    }
}
```

For the running application, you would register each config object as a service, keyed by its class name, and define it to load as a factory call to the Config Loader service.  Now, the container's autowiring will automatically create, and cache, each config object as it's needed and inject it into services that need it, just like any other service.

For example, in Laravel you could do something like this:

```php
namespace App\Providers;
 
use App\Environment;
use Crell\EnvMapper\EnvMapper;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
 
class ConfigServiceProvider extends ServiceProvider
{
    public $singletons = [
        // Wire up Serde first.  See its documentation for more
        // robust ways to configure it.
        Serde::class => SerdeCommon::class,
    ];
    
    public function register(): void
    {
        // Set up some sources.
        $this->app->singleton('base_config', fn(Application $app) 
            => new PhpFileSource('config/base')
        );
        $this->app->singleton('env_config', fn(Application $app) 
            => new PhpFileSource('config/'. APP_ENV)
        );
        
        // Register the loader, and wrap it in a cache.
        $this->app->singleton(LayeredLoader::class, fn(Application $app)
            => new LayeredLoader(
                [$app['base_config'], $app['env_config']],
                $app[Serde::class],
            )
        );
        $this->app->singleton(ConfigLoader::class, fn(Application $app)
            => new SerializedFilesystemCache($app[LayeredLoader::class], 'cache/config')
        );
        
        // Now register the config objects.
        // You could also use a compiler pass to discover these from disk and
        // auto-register them, if your framework has that ability.
        $this->app->singleton(EditorSettings::class, fn(Application $app)
            => $app[ConfigLoader::class]->load(EditorSettings::class);
    }
}
```

Now, the first time a service that wants `EditorSettings` is loaded (such as `EditorForm`), the `EditorSettings` config object service will be created, populated, cached to disk, and cached in memory by the container as a singleton.  All transparently!  Any service that wants `EditorSettings` can simply declare a constructor dependency, and it's done.  On subsequent loads, the cached version will be loaded from disk for even more speed.



## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email larry at garfieldtech dot com instead of using the issue tracker.

## Credits

- [Larry Garfield][link-author]
- [All Contributors][link-contributors]

## License

The Lesser GPL version 3 or later. Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/Crell/Config.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/License-LGPLv3-green.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/Crell/Config.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/Crell/Config
[link-scrutinizer]: https://scrutinizer-ci.com/g/Crell/Config/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/Crell/Config
[link-downloads]: https://packagist.org/packages/Crell/Config
[link-author]: https://github.com/Crell
[link-contributors]: ../../contributors
