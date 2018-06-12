<?php namespace Nano7\Foundation\Stubs;

use Nano7\Foundation\Support\Filesystem;

class Stubs
{
    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var StubsParser
     */
    protected $parser;

    /**
     * @param $path
     */
    public function __construct($path)
    {
        $this->files = new Filesystem();
        $this->path = $path;

        $this->parser = new StubsParser($this);
    }

    /**
     * @param string|array $key
     * @param null|mixed $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key) && is_null($value)) {
            $this->data = array_merge([], $this->data, $key);

            return $this;
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param $stubName
     * @param null $outFile
     * @return string
     * @throws \Exception
     */
    public function exec($stubName, $outFile = null)
    {
        if (! $this->exists($stubName)) {
            throw new \Exception("Stub [$stubName] not found");
        }

        $content = $this->parser->make($this->makeNameFile($stubName), $this->data);

        // Verificar se deve gerar arquivo
        if (! is_null($outFile)) {
            $this->files->put($outFile, $content);
        }

        return $content;
    }

    /**
     * @param $stubName
     * @param array $data
     * @return \Exception
     */
    public function make($stubName, $data = [])
    {
        $sub = new Stubs($this->path);
        $sub->with($data);


        return $sub->exec($stubName);
    }

    /**
     * @param $stubName
     * @return bool
     */
    public function exists($stubName)
    {
        return $this->files->exists($this->makeNameFile($stubName));
    }

    /**
     * Clear data.
     *
     * @return $this
     */
    public function clear()
    {
        $this->data = [];

        return $this;
    }

    /**
     * @param $stubName
     * @return string
     */
    protected function makeNameFile($stubName)
    {
        return $this->files->combine($this->path, $stubName . '.stub');
    }

    /**
     * @param $list
     * @param $nPrefix
     * @param $format
     * @param string $limiter
     * @param bool $sepInLast
     * @param bool $rnInCloseList
     * @return string
     */
    public static function listToStr($list, $nPrefix, $format, $limiter = ',', $sepInLast = true, $rnInCloseList = true)
    {
        $str = '';
        $i = 0;
        $count = count($list);
        $prefix = str_pad('', $nPrefix, ' ', STR_PAD_LEFT);
        foreach ($list as $key => $value) {

            $sep = $limiter;
            if (!$sepInLast && ($i >= $count-1)) {
                $sep = '';
            }

            $line = sprintf("\r\n%s%s%s", $prefix, $format, $sep);
            $line = str_replace([':name:',':value:',':str:'], [$key, $value, $value], $line);

            $str .= $line;
            $i++;
        }

        // Terminar lista
        if (($str != '') && $rnInCloseList) {
            $prefix = str_pad('', $nPrefix - 4, ' ', STR_PAD_LEFT);
            $line = sprintf("\r\n%s", $prefix);

            $str .= $line;
        }

        return $str;
    }

    /**
     * Register a custom Blade compiler.
     *
     * @param  callable  $compiler
     * @return void
     */
    public function extend(callable $compiler)
    {
        $this->parser->extend($compiler);
    }

    /**
     * Register a handler for custom directives.
     *
     * @param  string  $name
     * @param  callable  $handler
     * @return void
     */
    public function directive($name, callable $handler)
    {
        $this->parser->directive($name, $handler);
    }

    /**
     * Add condition.
     *
     * @param $name
     * @param callable $callback
     */
    public function condition($name, callable $callback)
    {
        $this->parser->condition($name, $callback);
    }
}