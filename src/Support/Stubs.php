<?php namespace Nano7\Foundation\Support;

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
     * @param $path
     */
    public function __construct($path)
    {
        $this->files = new Filesystem();
        $this->path = $path;
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
        if ($this->exists($stubName)) {
            throw new \Exception("Stub [$stubName] not found");
        }

        $parser = new StubsParser();
        $content = $parser->parser($this->makeNameFile($stubName), $this->data);

        // Verificar se deve gerar arquivo
        if (! is_null($outFile)) {
            $this->files->put($outFile, $content);
        }

        return $content;
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
}