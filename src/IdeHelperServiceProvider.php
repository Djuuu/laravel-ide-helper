<?php

/**
 * Laravel IDE Helper Generator
 *
 * @author    Barry vd. Heuvel <barryvdh@gmail.com>
 * @copyright 2014 Barry vd. Heuvel / Fruitcake Studio (http://www.fruitcakestudio.nl)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/barryvdh/laravel-ide-helper
 */

namespace Barryvdh\LaravelIdeHelper;

use Barryvdh\LaravelIdeHelper\Compat\ServiceProviderCompatTrait;
use Barryvdh\LaravelIdeHelper\Console\AllCommand;
use Barryvdh\LaravelIdeHelper\Console\EloquentCommand;
use Barryvdh\LaravelIdeHelper\Console\GeneratorCommand;
use Barryvdh\LaravelIdeHelper\Console\MetaCommand;
use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

class IdeHelperServiceProvider extends ServiceProvider
{
    use ServiceProviderCompatTrait;

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $viewPath = __DIR__ . '/../resources/views';
        $this->loadViewsFrom($viewPath, 'ide-helper');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/ide-helper.php';
        $this->mergeConfigFrom($configPath, 'ide-helper');
        $localViewFactory = $this->createLocalViewFactory();

        $this->app->singleton(
            'command.ide-helper.generate',
            function ($app) use ($localViewFactory) {
                return new GeneratorCommand($app['config'], $app['files'], $localViewFactory);
            }
        );

        $this->app->singleton(
            'command.ide-helper.models',
            function ($app) {
                return new ModelsCommand($app['files']);
            }
        );

        $this->app->singleton(
            'command.ide-helper.meta',
            function ($app) use ($localViewFactory) {
                return new MetaCommand($app['files'], $localViewFactory, $app['config']);
            }
        );

        $this->app->singleton(
            'command.ide-helper.eloquent',
            function ($app) {
                return new EloquentCommand($app['files']);
            }
        );

        $this->app->singleton(
            'command.ide-helper.all',
            function ($app) {
                return new AllCommand();
            }
        );

        $this->commands(
            'command.ide-helper.generate',
            'command.ide-helper.models',
            'command.ide-helper.meta',
            'command.ide-helper.eloquent',
            'command.ide-helper.all'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.ide-helper.generate',
            'command.ide-helper.models',
            'command.ide-helper.meta',
            'command.ide-helper.eloquent',
            'command.ide-helper.all',
        ];
    }

    /**
     * @return Factory
     */
    private function createLocalViewFactory()
    {
        $resolver = new EngineResolver();
        $resolver->register('php', function () {
            return new PhpEngine();
        });
        $finder = new FileViewFinder($this->app['files'], [__DIR__ . '/../resources/views']);
        $factory = new Factory($resolver, $finder, $this->app['events']);
        $factory->addExtension('php', 'php');

        return $factory;
    }
}
