<?php

use Nano7\Support\Carbon;
use Illuminate\Container\Container;

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string  $abstract
     * @param  array   $parameters
     * @return mixed|\Nano7\Foundation\Application
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath().($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('event')) {
    /**
     * Dispatch an event and call the listeners.
     *
     * @param null|string|object $event
     * @param array $payload
     * @param bool $halt
     * @return mixed|array|\Nano7\Foundation\Events\Dispatcher
     */
    function event($event = null, $payload = [], $halt = false)
    {
        if (is_null($event)) {
            return app('events');
        }

        return app('events')->fire($event, $payload, $halt);
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed|\Nano7\Foundation\Config\Repository
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (! function_exists('crypter')) {
    /**
     * @param null|string $value
     * @return string|\Nano7\Foundation\Encryption\Encrypter
     */
    function crypter($value = null)
    {
        $encryp = app('encrypter');
        if (is_null($value)) {
            return $encryp;
        }

        return $encryp->encryptString($value);
    }
}

if (! function_exists('bcrypt')) {
    /**
     * @param string|null $value
     * @param array $options
     * @return string|\Nano7\Foundation\Encryption\BcryptHasher
     */
    function bcrypt($value = null, $options = [])
    {
        $bcrypt = app('bcrypt');
        if (is_null($value)) {
            return $bcrypt;
        }

        return $bcrypt->make($value, $options);
    }
}

if (! function_exists('app_path')) {
    /**
     * Get the application path.
     *
     * @param  string  $path
     * @return string
     */
    function app_path($path = '')
    {
        return app()->make('path.app') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->make('path.config') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('trans')) {
    /**
     * @param null $key
     * @param array $replace
     * @return string
     */
    function trans($key, array $replace = [])
    {
        // TRaduzir para jargon
        $value = jargon($key, $replace);

        // Traduzir idioma
        $value = lang($value, $replace);

        return $value;
    }
}

if (! function_exists('lang')) {
    /**
     * @param null $key
     * @param array $replace
     * @param null $locale
     * @return \Illuminate\Translation\Translator|string
     */
    function lang($key = null, array $replace = [], $locale = null)
    {
        $trans = app('translator');

        if (is_null($key)) {
            return $trans;
        }

        return $trans->getFromJson($key, $replace, $locale);
    }
}

if (! function_exists('jargon')) {
    /**
     * @param null $key
     * @param array $replace
     * @param null $locale
     * @return \Illuminate\Translation\Translator|string
     */
    function jargon($key = null, array $replace = [], $locale = null)
    {
        $trans = app('jargon');

        if (is_null($key)) {
            return $trans;
        }

        return $trans->getFromJson($key, $replace, $locale);
    }
}

if (! function_exists('theme_path')) {
    /**
     * Get the theme path.
     *
     * @param  string  $path
     * @return string
     */
    function theme_path($path = '')
    {
        return app()->make('path.theme') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('temp_path')) {
    /**
     * Get the temp path.
     *
     * @param  string  $path
     * @return string
     */
    function temp_path($path = '')
    {
        return app()->make('path.temp') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}