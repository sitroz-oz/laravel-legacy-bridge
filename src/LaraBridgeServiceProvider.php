<?php

namespace Sitroz\LaraBridge;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Sitroz\LaraBridge\Console\InstallationCommand;
use Sitroz\LaraBridge\Console\RemoveCommand;
use Sitroz\LaraBridge\Console\TestCommand;

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

        $this->app->singleton('command.laraBridge.install', function () {
            return new InstallationCommand();
        });
        $this->app->singleton('command.laraBridge.test', function () {
            return new TestCommand();
        });
        $this->app->singleton('command.laraBridge.remove', function () {
            return new RemoveCommand();
        });

        $this->commands([
            'command.laraBridge.install',
            'command.laraBridge.test',
            'command.laraBridge.remove',
        ]);
    }
}
