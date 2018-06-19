<?php namespace Nano7\Foundation\Exception;

use Exception;
use Nano7\Foundation\Support\ErrorsException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionHandler implements \Nano7\Foundation\Contracts\Exception\ExceptionHandler
{
    /**
     * Report or log an exception.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        // TODO: Implement report() method.
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Nano7\Http\Request $request
     * @param  \Exception $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        // Exceções Http
        if ($e instanceof HttpException) {
            // Veriifcar se foi implemetado uma view no app
            $view = 'errors.' . $e->getStatusCode();
            if (view()->exists($view)) {
                return view($view)->render();
            }

            // Verificar se foi implememtado uma view no theme
            $view = 'theme::errors.' . $e->getStatusCode();
            if (view()->exists($view)) {
                return view($view)->render();
            }
        }

        // ErrorsException
        if ($e instanceof ErrorsException) {
            $back = redirect()->back()->withInput()->withErrors($e->getErrors(), $e->getMessage());

            return $back;
        }

        return 'error: ' . $e->getStatusCode();
    }

    /**
     * Render an exception to the console.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @param  \Exception $e
     * @return void
     */
    public function renderForConsole($output, Exception $e)
    {
        // TODO: Implement renderForConsole() method.
    }
}