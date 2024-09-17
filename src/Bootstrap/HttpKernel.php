<?php

namespace Sitroz\LaraBridge\Bootstrap;

use App\Http\Kernel;
use Illuminate\Foundation\Bootstrap\HandleExceptions;

class HttpKernel extends Kernel
{

    /**
     * Dispatch the request to the router, if router is enabled in config.
     *
     * @return \Closure
     */
    protected function dispatchToRouter()
    {
        return function ($request) {
            $this->app->instance('request', $request);

            if (config('laraBridge.use_router') !== TRUE) {
                return response('Router disabled', 404);
            }

            return $this->router->dispatch($request);
        };
    }

    /**
     * Send the given request through the middleware / router.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendRequestThroughRouter($request)
    {
        if (config('laraBridge.handling_exceptions.disabled')){
            $name = config('laraBridge.handling_exceptions.enable_param.name');
            $value = (string) config('laraBridge.handling_exceptions.enable_param.value');

            if ($name === NULL || $value !== (string) $request->input($name) ) {
                $this->replaceBootstrapper(HandleExceptions::class, DisabledHandleExceptions::class);
            }
        }

        return parent::sendRequestThroughRouter($request);
    }

    /**
     * Replace a bootstrapper in the array of bootstrappers.
     *
     * @param mixed $search The bootstrapper to search for
     * @param mixed $replacement The bootstrapper to replace the found bootstrapper with
     * @return void
     */
    private function replaceBootstrapper($search, $replacement)
    {
        foreach ($this->bootstrappers as &$item) {
            if ($item === $search){
                $item = $replacement;
                return;
            }
        }
    }
}
