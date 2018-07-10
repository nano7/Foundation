<?php namespace Nano7\Foundation\Stubs;

use Nano7\Foundation\Support\Arr;
use Nano7\Foundation\Support\Str;
use Nano7\Foundation\Support\Filesystem;

class StubsParser
{
    use Compiles\Loops;
    use Compiles\Comments;
    use Compiles\Includes;
    use Compiles\Conditions;

    /**
     * @var Stubs
     */
    protected $stubs;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * All custom "directive" handlers.
     *
     * @var array
     */
    protected $customDirectives = [];

    /**
     * All custom "condition" handlers.
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * All of the registered extensions.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * Array of opening and closing tags for regular echos.
     *
     * @var array
     */
    protected $contentTags = ['{{', '}}'];

    /**
     * The "regular" / legacy echo string format.
     *
     * @var string
     */
    protected $echoFormat = '%s';

    /**
     * All of the available compiler functions.
     *
     * @var array
     */
    protected $compilers = [
        'Comments',
        'Extensions',
        'Statements',
        'Echos',
    ];

    /**
     * Construtor.
     *
     * @param $stubs
     */
    public function __construct($stubs)
    {
        $this->stubs = $stubs;
        $this->files = new Filesystem();
    }

    /**
     * @param $file
     * @param array $data
     * @return string
     */
    public function make($file, $data = [])
    {
        // Verificar se arquivo existe
        if (! $this->files->exists($file)) {
            throw new \Exception("File stub [$file] not found");
        }

        // Carregar conteudo
        $content = $this->files->get($file);
        $content = str_replace('<?php', '##?php##', $content);

        $content = $this->compileString($content);

        $content = $this->evaluateContent($content, $data);

        $content = str_replace('##?php##', '<?php', $content);
        $content = str_replace('#@#', '@', $content);
        $content = str_replace('#{', '{{', $content);
        $content = str_replace('}#', '}}', $content);

        // Tratar resultado bonito
        $content = $this->adjustFormatLines($content);

        return $content;
    }

    /**
     * @param $__content
     * @param $__data
     * @return string
     */
    protected function evaluateContent($__content, $__data)
    {
        $__path = tempnam(sys_get_temp_dir(), 'Stub');
        //$__path = 'D:\teste.txt';

        $__stubs  = $this->stubs;
        $__parser = $this;

        $obLevel = ob_get_level();

        ob_start();

        file_put_contents($__path, $__content);

        extract($__data, EXTR_SKIP);

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            include $__path;
        } catch (\Exception $e) {
            $this->handleViewException($e, $obLevel);
        } finally {
            @unlink($__path);
        }

        return ltrim(ob_get_clean());
    }

    /**
     * Handle a view exception.
     *
     * @param  \Exception  $e
     * @param  int  $obLevel
     * @return void
     *
     * @throws \Exception
     */
    protected function handleViewException(\Exception $e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $e;
    }

    /**
     * Compile the given Blade template contents.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileString($value)
    {
        $result = '';

        foreach (token_get_all($value) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        return $result;
    }

    /**
     * Execute the user defined extensions.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileExtensions($value)
    {
        foreach ($this->extensions as $compiler) {
            $value = call_user_func($compiler, $value, $this);
        }

        return $value;
    }

    /**
     * Compile Blade statements that start with "@".
     *
     * @param  string  $value
     * @return string
     */
    protected function compileStatements($value)
    {
        return preg_replace_callback(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', function ($match) {
                return $this->compileStatement($match) . ' ';
            }, $value
        );
    }

    /**
     * Compile a single Blade @ statement.
     *
     * @param  array  $match
     * @return string
     */
    protected function compileStatement($match)
    {
        if (Str::contains($match[1], '@')) {
            $match[0] = isset($match[3]) ? $match[1].$match[3] : $match[1];
        } elseif (isset($this->customDirectives[$match[1]])) {
            $match[0] = $this->callCustomDirective($match[1], Arr::get($match, 3));
        } elseif (method_exists($this, $method = 'compile'.ucfirst($match[1]))) {
            $match[0] = $this->$method(Arr::get($match, 3));
        }

        return isset($match[3]) ? $match[0] : $match[0].$match[2];
    }

    /**
     * Compile echos.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];

            $wrapped = sprintf($this->echoFormat, preg_replace('/^(?=\$)(.+?)(?:\s+or\s+)(.+?)$/si', 'isset($1) ? $1 : $2', $matches[2]));

            return $matches[1] ? substr($matches[0], 1) : "<?php echo {$wrapped}; ?>{$whitespace}";
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Parser: ajustar formatação das linhas.
     *
     * @param $content
     * @return string
     */
    protected function adjustFormatLines($content)
    {
        $text = str_replace(["\r\n","\r"], "\n", $content);
        $lines = explode("\n", $text);
        $return = [];

        $lastLineBlank = false;
        $lastLineComment = false;
        $lastChar = '';
        $lastLine = '';

        foreach ($lines as $line) {
            $lineTrim = trim($line);

            if ($lineTrim == '') {
                // Verificar se ultima linha foi branco
                if ($lastLineBlank) {
                    $line = false;
                }

                // Verificar se ultima linha foi um comentario
                if ($lastLineComment) {
                    $line = false;
                }

                // Verificar ultima linha é um abre chaves
                if ($lastChar == '{') {
                    $line = false;
                }

                $lastLineBlank = true;
                $lastLineComment = false;
            } else {
                $endChaves = ($lineTrim == '}');
                $lastLineComment = Str::startsWith($lineTrim, '//');

                // Verificar se separador , deve subir para a linha anterior
                if (($lineTrim == ',') && (! Str::endsWith($lastLine, ','))) {
                    $line = false;
                    $last = array_pop($return);
                    $last .= ',';
                    $return[] = $last;
                }

                // Verificar se deve remover ultima linha em branco
                if ($endChaves && $lastLineBlank) {
                    array_pop($return);
                }

                $lastLineBlank = false;
            }

            // verificar se deve adicionar a linha
            if ($line !== false) {
                $return[] = $line;
            }

            $lastChar = ($lineTrim != '') ? substr($lineTrim, -1) : '';
            $lastLine = $lineTrim;
        }

        return implode("\r\n", $return);
    }

    /**
     * Parse the tokens from the template.
     *
     * @param  array  $token
     * @return string
     */
    protected function parseToken($token)
    {
        list($id, $content) = $token;

        foreach ($this->compilers as $type) {
            $content = $this->{"compile{$type}"}($content);
        }

        return $content;
    }

    /**
     * Call the given directive with the given value.
     *
     * @param  string  $name
     * @param  string|null  $value
     * @return string
     */
    protected function callCustomDirective($name, $value)
    {
        if (Str::startsWith($value, '(') && Str::endsWith($value, ')')) {
            $value = Str::substr($value, 1, -1);
        }

        return call_user_func($this->customDirectives[$name], trim($value));
    }

    /**
     * Register a custom Blade compiler.
     *
     * @param  callable  $compiler
     * @return void
     */
    public function extend(callable $compiler)
    {
        $this->extensions[] = $compiler;
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
        $this->customDirectives[$name] = $handler;
    }

    /**
     * Add condition.
     *
     * @param $name
     * @param callable $callback
     */
    public function condition($name, callable $callback)
    {
        $this->conditions[$name] = $callback;

        // If
        $this->directive($name, function($expr) use ($name) {
            return $expr ? '<?php if ($__parser->check(\'' . $name . '\', $expr)): ?>'
                : '<?php if ($__parser->check(\'' . $name . '\')): ?>';
        });

        // elseif
        $this->directive('else' . $name, function($expr) use ($name) {
            return $expr ? '<?php elseif ($__parser->check(\'' . $name . '\', $expr)): ?>'
                : '<?php elseif ($__parser->check(\'' . $name . '\')): ?>';
        });

        // end
        $this->directive('end' . $name, function() {
            return '<?php endif; ?>';
        });
    }

    /**
     * Check the result of a condition.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @return bool
     */
    public function check($name, $parameters)
    {
        $parameters = func_get_args();

        // Remove $name
        array_shift($parameters);

        return call_user_func_array($this->conditions[$name], $parameters);
    }
    /**
     * Strip the parentheses from the given expression.
     *
     * @param  string  $expression
     * @return string
     */
    public function stripParentheses($expression)
    {
        if (Str::startsWith($expression, '(')) {
            $expression = substr($expression, 1, -1);
        }

        return $expression;
    }
}