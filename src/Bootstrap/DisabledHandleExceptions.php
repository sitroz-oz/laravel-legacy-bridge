<?php

namespace Sitroz\LaraBridge\Bootstrap;

use ErrorException;
use Illuminate\Contracts\Foundation\Application;

class DisabledHandleExceptions extends \Illuminate\Foundation\Bootstrap\HandleExceptions
{

    /**
     * Do nothing while bootstrapping laravel the application.
     *
     * @param Application $app
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        // DO NOTHING ^_^
        // $this->app = $app;
        //
        // if (Request::input('withErrors')) {
        //     error_reporting(-1);
        //
        //     set_error_handler([$this, 'handleError']);
        //
        //     set_exception_handler([$this, 'handleException']);
        //
        //     register_shutdown_function([$this, 'handleShutdown']);
        // }
    }

    /**
     * Handle the error.
     *
     * @param int $level The error level.
     * @param string $message The error message.
     * @param string $file The file in which the error occurred. (optional)
     * @param int $line The line number where the error occurred. (optional)
     * @param array $context The contextual information about the error. (optional)
     *
     * @throws ErrorException when the error level matches E_ERROR and is not suppressed by error_reporting()
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if ($level & (E_ERROR) && (error_reporting() & $level)) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }
}
