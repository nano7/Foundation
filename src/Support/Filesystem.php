<?php namespace Nano7\Foundation\Support;

use Illuminate\Support\Str;

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

    /**
     * Delete files in dir.
     *
     * @param $path
     * @param string $pattern
     * @return int
     */
    public function deleteFiles($path, $pattern = '*')
    {
        $deleteds = 0;

        $items = new \FilesystemIterator($path);
        foreach ($items as $item) {
            if ($item->isFile()) {
                $filename = $item->getFilename();

                if (Str::is($pattern, $filename)) {
                    $deleteds += $this->delete($item->getPathname()) ? 1 : 0;
                }
            }
        }

        return $deleteds;
    }
}