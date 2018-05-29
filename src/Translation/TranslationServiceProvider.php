<?php namespace Nano7\Foundation\Translation;

use Illuminate\Translation\Translator;
use Illuminate\Translation\FileLoader;
use Nano7\Foundation\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Register objetos base.
     */
    public function register()
    {
        $this->registerLoader();

        $this->registerTraslator();

        $this->registerJargons();
    }

    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        // to Translator
        $this->app->singleton('translation.loader', function ($app) {
            return new FileLoader($app['files'], $app['path.lang']);
        });

        // to Jargon
        $this->app->singleton('jargon.loader', function ($app) {
            return new FileLoader($app['files'], $app['path.jargon']);
        });
    }

    /**
     * Register translator.
     *
     * @return void
     */
    protected function registerTraslator()
    {
        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];

            $trans = new Translator($loader, $locale);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });
    }

    /**
     * Register jargons.
     *
     * @return void
     */
    protected function registerJargons()
    {
        $this->app->singleton('jargon', function ($app) {
            $loader = $app['jargon.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.jargon'];

            $trans = new Translator($loader, $locale);

            $trans->setFallback($app['config']['app.fallback_jargon']);

            return $trans;
        });
    }
}