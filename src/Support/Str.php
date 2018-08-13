<?php namespace Nano7\Foundation\Support;

class Str extends \Illuminate\Support\Str
{
    /**
     * Convert a value to unstudly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public static function unstudly($value)
    {
        $rets = [];
        $value = ucfirst($value);

        preg_match_all('/([A-Z]{1}[a-z0-9_]+)/', $value, $items, PREG_PATTERN_ORDER);
        for ($i = 0; $i < count($items[0]); $i++) {
            $item = $items[1][$i];
            $rets[] = strtolower($item);
        }

        return $rets;
    }

    /**
     * @param $value
     * @return bool|null|string
     */
    public static function value($value)
    {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * Change all link relation to absolute link.
     *
     * @param $html
     * @param $base
     * @return string
     */
    public static function changeUrlRelToAbs($html, $base)
    {
        $re = '/(href|src)=["\']([a-zA-Z_\.0-9\/\-\! :\@\$]*)/mi'; //   (href|src)=["\']([a-zA-Z_\.0-9\/\-\! :\@\$]*)
        preg_match_all($re, $html, $links, PREG_SET_ORDER, 0);

        foreach($links as $link) {

            $url = $link[2];
            $url_is_full = (Str::startsWith('http', $url) || Str::startsWith('ftp', $url) || Str::startsWith('mailto', $url));

            if ((!empty($url)) && (!$url_is_full)) {
                //força URLs absolutas
                $url_abs = self::urlRelToAbs($url, $base);

                //troca no HTML as URLs para absolutas
                $re = '/(href|src)(=["\'])(' . str_replace(['/', '.'], ['\/', '\.'], $url) . ')/mi';
                $subst = '$1$2' . $url_abs;
                $html = preg_replace($re, $subst, $html);
            }
        }

        return $html;
    }

    /**
     * Change link relation to absolute.
     *
     * @param $rel
     * @param $base
     * @return string
     */
    public static function urlRelToAbs($rel, $base)
    {
        /* return if already absolute URL */
        if (parse_url($rel, PHP_URL_SCHEME) != '' || substr($rel, 0, 2) == '//') {
            return $rel;
        }

        /* queries and anchors */
        if ($rel[0]=='#' || $rel[0]=='?') {
            return $base . $rel;
        }

        /* parse base URL and convert to local variables:
         $scheme, $host, $path */
        extract(parse_url($base));

        /* remove non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);

        /* destroy path if relative url points to root */
        if ($rel[0] == '/') $path = '';

        /* dirty absolute URL */
        $abs = "$host$path/$rel";

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

        /* absolute URL is ready! */
        return sprintf('%s://%s', $scheme, $abs);
    }
}