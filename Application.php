<?php namespace Nano7\Foundation;

use Dotenv\Dotenv;
use Nano7\Foundation\Support\ServiceProvider;

class Application extends \Illuminate\Container\Container
{
    /**
     * The Laravel framework version.
     *
     * @var string
     */
    const VERSION = '5.6.0';

    /**
     * The base path for the Laravel installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * @var bool
     */
    protected $booted = false;

    /**
     * @var array
     */
    protected $serviceProviders = [];

    /**
     * Create a new Illuminate application instance.
     *
     * @param  string|null $basePath
     * @return void
     */
    public function __construct($basePath = null)
    {
        error_reporting(E_ALL & ~E_NOTICE); // Exibe todos os erros, warnings, menos as noticias

        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->registerBaseBindings();
        $this->registerEnv();
        //$this->registerBaseServiceProviders();
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance('Nano7\Application', $this);
        $this->instance('Illuminate\Contracts\Container\Container', $this);
    }

    /**
     * Register the env bindings into the container.
     *
     * @return void
     */
    protected function registerEnv()
    {
        $env = new Dotenv($this->basePath());
        $env->load();
    }

    /**
     * Set the base path for the application.
     *
     * @param  string $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @param  string $path Optionally, a path to append to the base path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Bind all of the application paths in the container.
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        $this->instance('path.base',   $this->basePath());
        $this->instance('path.app',    $this->basePath('app'));
        $this->instance('path.config', $this->basePath('app/config'));
        $this->instance('path.theme',  $this->basePath('app/theme'));
        $this->instance('path.temp',   $this->basePath('app/temp'));
        $this->instance('path.lang',   $this->basePath('app/trans/langs'));
        $this->instance('path.jargon', $this->basePath('app/trans/jargons'));
    }

    /**
     * Boot application.
     */
    public function boot($callback = null)
    {
        // Add listen boot
        if (! is_null($callback)) {
            event()->listen('app.boot', $callback);
            return;
        }

        // Verificar se jah foi bootado
        if ($this->booted) {
            return;
        }

        // Boot providers
        foreach ($this->serviceProviders as $provider) {
            $this->bootProvider($provider);
        }

        // Fire boot app
        event('app.boot', [$this->app]);

        $this->booted = true;
    }

    /**
     * Register services providers.
     *
     * @param $provider
     * @return string
     */
    public function register($provider)
    {
        // Verificar se provider já foi registrado
        $keyProvider = is_string($provider) ? $provider : get_class($provider);
        if (array_key_exists($keyProvider, $this->serviceProviders)) {
            return $this->serviceProviders[$keyProvider];
        }

        // Carregar provider quando string
        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        // Marcar como registrado
        $this->serviceProviders[$keyProvider] = $provider;

        // Verificar se deve
        if ($this->booted) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    /**
     * Boot the given service provider.
     *
     * @param  ServiceProvider  $provider
     * @return mixed
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }

        return null;
    }

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get('app.locale');
    }

    /**
     * Set the current application locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this['config']->set('app.locale', $locale);

        //$this['translator']->setLocale($locale);

        //$this['events']->dispatch(new Events\LocaleUpdated($locale));
    }

    /**
     * Determine if application locale is the given locale.
     *
     * @param  string  $locale
     * @return bool
     */
    public function isLocale($locale)
    {
        return $this->getLocale() == $locale;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Return is running in mode web.
     *
     * @return bool
     */
    public function runningWeb()
    {
        return ($this['mode'] == 'web');
    }

    /**
     * Return is running in mode console.
     *
     * @return bool
     */
    public function runningConsole()
    {
        return ($this['mode'] == 'console');
    }
}