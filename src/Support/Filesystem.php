<?php namespace Nano7\Foundation\Support;

class Filesystem extends \Illuminate\Filesystem\Filesystem
{
    /**
     * @param $path1
     * @param $path2
     * @param null $pathn
     * @return string
     */
    public function combine($path1, $path2, $pathn = null)
    {
        $args = func_get_args();

        $path = '';
        foreach ($args as $arg) {
            $arg = str_replace('/', DIRECTORY_SEPARATOR, $arg);
            $arg = str_replace('\\', DIRECTORY_SEPARATOR, $arg);

            $path .= ($path != '') ? DIRECTORY_SEPARATOR  : '';
            $path .= $arg;
        }

        return $path;
    }

    /**
     * Alias of makeDirectory.
     *
     * @return bool
     */
    public function force($path, $mode = 0777, $recursive = true)
    {
        if ($this->exists($path)) {
            return true;
        }

        return $this->makeDirectory($path, $mode, $recursive);
    }
}