<?php

namespace Lewis\Presenter;

use Lewis\Presenter\Decorator;
use Illuminate\Support\ServiceProvider;

class PresenterServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider, set the bindings, and decorate view data.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom($configPath = realpath(__DIR__.'/../config/presenters.php'), 'presenters');

        if (class_exists('Illuminate\Foundation\Application', false)) {
            $this->publishes([
                $configPath => config_path('presenters.php'),
            ]);
        }

        $this->setDecoratorBindings();

        $this->app['view']->composer('*', function ($view) {
            $data = array_merge($view->getFactory()->getShared(), $view->getData());

            foreach ($data as $key => $value) {
                $view[$key] = $this->app['decorator']->decorate($value);
            }
        });
    }

    /**
     * Set the decorator bindings from the configuration file.
     *
     * @return void
     */
    protected function setDecoratorBindings()
    {
        $this->app['decorator']->setBindings($this->app['config']['presenters']);
    }

    /**
     * Register the decorator.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('decorator', function ($app) {
            return new Decorator($app);
        });
    }
}
