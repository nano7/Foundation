<?php namespace Nano7\Foundation\Config;

class Repository extends \Illuminate\Config\Repository
{
    /**
     * Lista de key já buscados.
     * @var array
     */
    protected $finded = [];

    /**
     * Get the specified configuration value.
     *
     * @param  array|string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        // Veriifcar se deve carregar
        if (! is_array($key)) {
            $this->loadKey($key);
        }

        // Retornar key
        return parent::get($key, $default);
    }

    /**
     * Load key.
     * @param $key
     */
    protected function loadKey($key)
    {
        // Pegar soh o primeiro key
        $parts = explode('.', $key);
        $first = $parts[0];

        // Verificar se item já foi adicionado
        if (array_key_exists($first, $this->items)) {
            return;
        }

        if (array_key_exists($first, $this->finded)) {
            return;
        }

        $file = config_path(sprintf('%s.php', $first));
        if (file_exists($file)) {
            $this->set($first, require $file);
        }

        $this->finded[$first] = true;
    }
}