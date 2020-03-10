<?php

namespace App\KernelFoundation;

class Router
{
    private static $_instance = null;
    private static $routes = [];
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    /*
     * find matching routes
     */
    public function dispatch(): Response
    {
        foreach (self::$routes as $route => $conf) {
            ['controller' => $handler, 'requirements' => $requirements, 'regex_pattern' => $pattern] = $conf;
            if(preg_match($pattern, $this->request->uri)){
                [$class, $method] = explode("::", $handler);

                $args = [];
                foreach ($requirements as $name => $val){
                    $start = strpos($route, ':'.$name);
                    $end = strpos($this->request->uri, '/', $start);
                    if(!$end){
                        $end = strlen($this->request->uri);
                    }

                    $args[$name] = substr($this->request->uri, $start, $end-$start);
                }

                $instance = new $class($this->request);
                return call_user_func_array([$instance, $method], $args);
            }
        }

        return new Response("", [], 404);
    }


    /**
     * Removes trailing forward slashes from the right of the route.
     * @param string $route
     * @return string
     */
    private function formatRoute(string $route): string
    {
        //Remove last slash
        $result = rtrim($route, '/');
        if ($result === '') {
            return '/';
        }

        //Remove url params from uri
        $pos = strpos($route, "?");
        return substr($route, 0, $pos);
    }


    public static function getInstance(Request $req): self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Router($req);
        }

        return self::$_instance;
    }

    public static function GenerateRoutes()
    {
        if (file_exists('../../var/cache/routesGenerated.php')) {
            self::$routes = require '../../var/cache/routesGenerated.php';
        } else {
            $json = json_decode(file_get_contents(__DIR__ . '/../routes.json', true), true);
            foreach ($json as $route => $conf) {
                $regex_uri = '/^('.str_replace('/' , '\/', $route).')$/';

                if(isset($conf['requirements'])){
                    foreach ($conf['requirements'] as $name => $reg){
                        $regex_uri = str_replace(':'.$name , $reg, $regex_uri);
                    }
                }

                $conf["regex_pattern"] = $regex_uri;
                $conf["controller"] = "Altech\\Controller\\".$conf["controller"];
                self::$routes = [$route => $conf];
            }

            $handle = fopen('../var/cache/routesGenerated.php', 'w') or die('Cannot open file routesGenerated');
            $content = "<?php return " . var_export(self::$routes, true) . ";";

            fwrite($handle, $content);
        }
    }
}