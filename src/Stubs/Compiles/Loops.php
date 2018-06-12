<?php namespace Nano7\Foundation\Stubs\Compiles;

trait Loops
{
    /**
     * Compile the for-each statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileForeach($expression)
    {
        return "<?php foreach{$expression}: ?>";
    }

    /**
     * Compile the end-if statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndforeach()
    {
        return '<?php endforeach; ?>';
    }
}