<?php namespace Nano7\Foundation;

use Nano7\Foundation\Discover\PackageManifest;
use Nano7\Foundation\Encryption\BcryptHasher;
use Nano7\Foundation\Encryption\Encrypter;
use Nano7\Foundation\Support\ServiceProvider;
use Nano7\Foundation\Support\Str;

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

        $this->registerEncrypter();

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

    /**
     * Register encrypter.
     */
    protected function registerEncrypter()
    {
        $this->app->singleton('encrypter', function ($app) {
            $key    = $app['config']->get('app.key');
            $cipher = $app['config']->get('app.cipher');
            if (empty($key)) {
                throw new \RuntimeException('No application encryption key has been specified.');
            }

            // If the key starts with "base64:", we will need to decode the key before handing
            // it off to the encrypter. Keys may be base-64 encoded for presentation and we
            // want to make sure to convert them back to the raw bytes before encrypting.
            if (Str::startsWith($key, 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            return new Encrypter($key, $cipher);
        });

        $this->app->singleton('bcrypt', function ($app) {
            return new BcryptHasher($app['config']->get('encrypter', []));
        });
    }
}