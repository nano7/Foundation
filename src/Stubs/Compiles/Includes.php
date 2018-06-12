<?php namespace Nano7\Foundation\Stubs\Compiles;

trait Includes
{
    /**
     * Compile the include statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileInclude($expression)
    {
        $expression = $this->stripParentheses($expression);

        return "<?php echo \$__stubs->make({$expression}, array_except(get_defined_vars(), array('__data', '__path', '__stubs', '__parser'))); ?>";
    }
}