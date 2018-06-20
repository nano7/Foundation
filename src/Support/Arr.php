<?php namespace Nano7\Foundation\Support;

class Arr extends \Illuminate\Support\Arr
{
    /**
     * Determine whether the given value is array accessible.
     *
     * @param  array $array
     * @param  array|mixed $find
     * @return bool
     */
    public static function in($array, $find, $all = true)
    {
        $find = (array) $find;

        foreach ($find as $item) {
            if (in_array($item, $array)) {
                if (! $all) {
                    return true;
                }
            } else {
                if ($all) {
                    return false;
                }
            }
        }

        return $all;
    }
}