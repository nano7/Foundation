<?php namespace Nano7\Foundation;

use Nano7\Foundation\Discover\PackageManifest;
use Nano7\Foundation\Support\ServiceProvider;

class FoundationServiceProviders extends ServiceProvider
{
    /**
     * Register objetos base.
     */
    public function register()
    {
        $this->registerEvents();

        $this->registerFiles();

        $this->registerConfigs();

        $this->registerDiscover();
    }

    /**
     * Register events.
     */
    protected function registerEvents()
    {
        $this->app->singleton('events', function () {
            return new \Nano7\Foundation\Events\Dispatcher($this->app);
        });
    }

    /**
     * Register files.
     */
    protected function registerFiles()
    {
        $this->app->singleton('files', function () {
            return new \Nano7\Foundation\Support\Filesystem();
        });
    }

    /**
     * Register configs.
     */
    protected function registerConfigs()
    {
        $this->app->singleton('config', function () {
            return new \Nano7\Foundation\Config\Repository();
        });
    }

    /**
     * Register discover.
     */
    protected function registerDiscover()
    {
        $this->app->singleton('manifest', function () {
            return new PackageManifest($this->app['files'], $this->app->basePath(), $this->app->basePath('app/packages.php'));
        });
        $this->app->alias('manifest', 'Nano7\Foundation\Discover\PackageManifest');

        $this->command('\Nano7\Foundation\Discover\Console\PackageDiscoverCommand');
    }
}