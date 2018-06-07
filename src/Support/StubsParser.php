<?php namespace Nano7\Foundation\Support;

class StubsParser
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Lista de sequencia dos parsers.
     * @var array
     */
    protected $parsers = [
        'parserParams',
        'parserFuncs',
    ];

    /**
     * Construtor.
     */
    public function __construct()
    {
        $this->files = new Filesystem();
    }

    /**
     * @param $file
     * @param array $data
     * @return string
     */
    public function parser($file, $data = [])
    {
        // Verificar se arquivo existe
        if (! $this->files->exists($file)) {
            throw new \Exception("File stub [$file] not found");
        }

        // Carregar conteudo
        $content = $this->files->get($file);

        // Executar parsers
        foreach ($this->parsers as $method) {
            if (method_exists($this, $method)) {
                $content = call_user_func_array([$this, $method], [$content, $data]);
            }
        }

        return $content;
    }

    /**
     * Parser: params.
     *
     * @param $content
     * @param $data
     * @return string
     */
    protected function parserParams($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Parser: funcs.
     *
     * @param $content
     * @param $data
     * @return string
     */
    protected function parserFuncs($content, $data)
    {
        //while (preg_match('/@([a-zA-Z_]+){1}\\((.*?)\\)(.*?)(@else(.*?))?@end\\1/s', $content, $args)) {

        while (preg_match('/@((no)?([a-zA-Z_]+)){1}\\((.*?)\\)(.*?)(@else(.*?))?@end\\1/s', $content, $args)) {
            list($original, $cmd, $not, $func, $param, $true, $ignore, $false) = $args;

            $method = sprintf('func%s', Str::studly($func));
            if (method_exists($this, $method)) {
                // Verificar se inverte codigos
                if ($not == 'no') {
                    list($true, $false) = [$false, $true];
                }
                $content = str_replace($original, call_user_func_array([$this, $method], [$data, $param, $true, $false]), $content);

            } else {
                $content = str_replace($original, '#ERROR: function not found#' . str_replace('@', '', $original), $content);
            }
        }

        return $content;
    }

    /**
     * @param array $data
     * @param string $param
     * @param string $true
     * @param string $false
     * @return string
     */
    protected function funcHas($data, $param, $true, $false)
    {
        return array_key_exists($param, $data) ? $true : $false;
    }

    /**
     * @param array $data
     * @param string $param
     * @param string $true
     * @param string $false
     * @return string
     */
    protected function funcEmpty($data, $param, $true, $false)
    {
        if (! array_key_exists($param, $data)) {
            return $true;
        }

        if (trim($data[$param]) == '') {
            return $true;
        }

        return $false;
    }

    /**
     * @param array $data
     * @param string $param
     * @param string $true
     * @param string $false
     * @return string
     */
    protected function funcIf($data, $param, $true, $false)
    {
        $boolean = $this->expr($param, $data);

        return $boolean ? $true : $false;
    }

    /**
     * @param string $expr
     * @param array $data
     * @return bool
     */
    protected function expr($expr, $data)
    {
        if (preg_match('/^([$a-zA-Z0-9"_-]+)+ *?([!=<>]+)+([$a-zA-Z0-9"+_ -]+)+$/s', $expr, $args)) {
            list($original, $value1, $comp, $value2) = $args;

            // Tratar parametros
            $value1 = $this->changeVars($value1, $data);
            $value2 = $this->changeVars($value2, $data);

            $result = false;
            $code = sprintf('$return = ((%s) %s (%s))', $value1, $comp, $value2);
            eval($code);
            return $result;
        }

        throw new \Exception("Invalid expression [$expr]");
    }

    /**
     * Trocar vars.
     *
     * @param $str
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    protected function changeVars($str, $data)
    {
        preg_match_all('/\\$([a-zA-Z0-9_-]+)+/i', $str, $vars, PREG_PATTERN_ORDER);
        for ($i = 0; $i < count($vars[0]); $++) {
            $var = $vars[1][$i];
            if (! array_key_exists($vars, $data)) {
                throw new \Exception("var [$var] not identify");
            }

            $str = str_replace($vars[0][$i], '"' . $data[$vars] . '"', $str);
        }

        return $str;
    }
}