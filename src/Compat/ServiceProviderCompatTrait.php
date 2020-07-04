<?php

namespace Barryvdh\LaravelIdeHelper\Compat;

/**
 * Class ServiceProviderCompatTrait
 *
 * @mixin \Illuminate\Support\ServiceProvider
 */
trait ServiceProviderCompatTrait
{
    /**
     * Register a view file namespace.
     *
     * @param  string|array  $path
     * @param  string  $namespace
     * @return void
     */
    protected function loadViewsFrom($path, $namespace)
    {
        $this->app['view']->addNamespace($namespace, $path);
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        $this->app['config']->set($key, array_merge(
            require $path, $this->app['config']->get($key, [])
        ));
    }
}
