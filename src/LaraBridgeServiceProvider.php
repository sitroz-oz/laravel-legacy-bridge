<?php

namespace Sitroz\LaraBridge;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Sitroz\LaraBridge\Console\InstallationCommand;

class LaraBridgeServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishFiles();
        }
    }

    public function publishFiles()
    {
        $source = realpath($raw = __DIR__.'/../bootstrap/init.php') ?: $raw;
        $this->publishes([$source => $this->app->basePath('bootstrap/init.php')]);

        $source = realpath($raw = __DIR__.'/../config/laraBridge.php') ?: $raw;
        $this->publishes([$source => $this->app->configPath('laraBridge.php')]);
    }

    public function register()
    {
        $source = realpath($raw = __DIR__.'/../config/laraBridge.php') ?: $raw;
        $this->mergeConfigFrom($source,'laraBridge');

        $this->app->singleton('command.laraBridge.publish', function () {
            return new InstallationCommand();
        });

        $this->commands(['command.laraBridge.publish']);
    }
}
