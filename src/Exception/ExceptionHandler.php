<?php namespace Nano7\Foundation\Exception;

use Exception;
use Nano7\Foundation\Support\ErrorsException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionHandler implements \Nano7\Foundation\Contracts\Exception\ExceptionHandler
{
    /**
     * @var null|\Nano7\View\Factory
     */
    protected $viewService = null;

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
        // Verificar se deve responser como API
        if (app()->runningWebApi()) {
            return $this->renderApi($request, $e);
        }

        // Exceções Http
        if ((! is_null($this->viewService)) && ($e instanceof HttpException)) {
            // Veriifcar se foi implemetado uma view no app
            $view = 'errors.' . $e->getStatusCode();
            if ($this->getViewService()->exists($view)) {
                return $this->getViewService()->make($view)->render();
            }

            // Verificar se foi implememtado uma view no theme
            $view = 'theme::errors.' . $e->getStatusCode();
            if ($this->getViewService()->exists($view)) {
                return $this->getViewService()->make($view)->render();
            }
        }

        // ErrorsException
        if ($e instanceof ErrorsException) {
            $back = redirect()->back()->withInput()->withErrors($e->getErrors(), $e->getMessage());

            return $back;
        }

        return 'error: ' . $e->getMessage();
    }

    /**
     * @param $request
     * @param Exception $e
     * @return \Nano7\Http\JsonResponse
     */
    protected function renderApi($request, Exception $e)
    {
        // Error via web.api response
        $ej = ['error' => ['message' => $e->getMessage()]];

        if ($e->getCode() > 0) {
            $ej['error']['code'] = $e->getCode();
        }

        if ($e instanceof HttpException) {
            $ej['error']['code'] = $e->getStatusCode();
        }

        if ($e instanceof ErrorsException) {
            $ej['error']['errors'] = $e->getErrors();
        }

        if (app()->runningDebug()) {
            $ej['error']['file'] = $e->getFile();
            $ej['error']['line'] = $e->getLine();
            $ej['error']['trace'] = $e->getTrace();
        }

        return response()->json($ej);
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

    /**
     * @param $service
     * @return $this
     */
    public function setViewService($service)
    {
        $this->viewService = $service;

        return $this;
    }

    /**
     * @return \Nano7\View\Factory|null
     */
    public function getViewService()
    {
        return $this->viewService;
    }
}