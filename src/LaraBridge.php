<?php

namespace Sitroz\LaraBridge;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Sitroz\LaraBridge\Bootstrap\HttpKernel as LibHttpKernel;

class LaraBridge
{
    CONST BOUND_KEY = "sitroz/laraBridge";

    /**
     * @var Application
     */
    private $app;

    /**
     * Create and bind a LaraBridge instance in the Laravel service container
     * And load extra requirements defined in LaraBridge config.
     *
     * @param Application $app The Laravel application instance to bind the class to.
     * @return mixed Returns an instance of the LaraBridge.
     */
    public static function init(Application $app)
    {
        if (!$app->bound(self::BOUND_KEY)){
            $app->instance(self::BOUND_KEY, new static($app));
        }
        return $app->make(self::BOUND_KEY);
    }

    /**
     * Create instance and load extra requirements defined in LaraBridge config.
     *
     * @param Application $app The Laravel application instance.
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->loadProjectRequirements();
    }

    /**
     * Load project extra requirements, defined in config.
     *
     * @return void
     */
    private function loadProjectRequirements()
    {
        (new LoadConfiguration())->bootstrap($this->app);

        $projectRoot = config('laralib.root_path');
        if ($projectRoot){
            foreach (config('laralib.requirements', []) as $path) {
                require_once $this->join_paths($projectRoot, $path);
            }
        }
    }

    /**
     * Join the given paths together.
     *
     * @param  string|null  $basePath
     * @param  string  ...$paths
     * @return string
     */
    private function join_paths($basePath, ...$paths)
    {
        foreach ($paths as $index => $path) {
            if (empty($path) && $path !== '0') {
                unset($paths[$index]);
            } else {
                $paths[$index] = DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
            }
        }

        return $basePath.implode('', $paths);
    }

    public function handleHttpRequest()
    {
        if ($this->app->runningInConsole()) {
            return [null, null];
        }

        $this->app->singleton(HttpKernel::class, LibHttpKernel::class);

        $kernel = $this->app->make(HttpKernel::class);

        $response = $kernel->handle(
            $this->rewriteServerInfo(
                $request = \Illuminate\Http\Request::capture()
            )
        );

        if ($response->isNotFound()){
            return [$request, $response];
        }

        $response->send();
        $kernel->terminate($request, $response);
        exit();
    }

    private function rewriteServerInfo($request)
    {
        if (config('laralib.rewrite_path_info', FALSE) !== TRUE){
            return $request;
        }

        $uri = str_replace(['/index.php','.php'], '', $_SERVER['REQUEST_URI']);
        $pathInfo = str_replace(['/index.php','.php'], '', $_SERVER['SCRIPT_NAME']);
        $pathInfo.= isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO']:'';
        if (strlen($pathInfo) > 1){
            $pathInfo = rtrim($pathInfo,'/');
        }

        $request->server->set('REQUEST_URI', $uri);
        $request->server->set('SCRIPT_NAME', '/index.php');
        $request->server->set('PATH_INFO', $pathInfo);

        return $request;
    }
}
