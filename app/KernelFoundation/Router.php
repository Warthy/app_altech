<?php

namespace App\KernelFoundation;

use Exception;

class Router
{

    private static $_instance = null;
    private static $routes = [];
    private $request;
    private $firewall;

    public function __construct(Request $request, array $firewall)
    {
        $this->request = $request;
        $this->firewall = $firewall;
    }


    /**
     * find matching routes
     */
    public function dispatch(): Response
    {
        // Check each route to see if one's valid
        foreach (self::$routes as $route => $conf) {
            ['controller' => $handler, 'regex_pattern' => $pattern] = $conf;
            // Check is routes pattern match with URI
            if (preg_match($pattern, $this->request->uri)) {
                // Check if the matched route is protected by a firewall,
                // and if so we check that the logged user has the required role
                foreach ($this->firewall as $fwpattern => $role) {
                    if (preg_match("/".$fwpattern."/", $this->request->uri) &&
                        !Security::hasPermission($role))
                    {
                        throw new Exception("Access refused: rôle insuffisant", 403);
                    }
                }
                [$class, $method] = explode("::", $handler);


                // Recover parameters inside URI
                $args = [];
                $patternFrags = explode("/", trim($route, "/"));
                $routeFrags = explode("/", trim($this->request->uri, "/"));
                foreach ($patternFrags as $index => $frag){
                    if(isset($frag[0]) && $frag[0] === ":")
                        $args[substr($frag[0], 1)] = $routeFrags[$index];
                }

                // Call route's controller method
                return call_user_func_array([new $class($this->request), $method], $args);
            }
        }

        return new Response([], 404);
    }

    public static function GenerateRoutes()
    {
        $cachedRoutes = __DIR__ . "/../../var/cache/routesGenerated.php";
        // If routes are already cached and up to date
        // there is no need to regenerate file
        if (file_exists($cachedRoutes)) {
            $data = require $cachedRoutes;
            if (md5_file(__DIR__ . '/../routes.json') === $data["sha"]) {
                self::$routes = $data["routes"];
                return;
            }
        }
        $json = json_decode(file_get_contents(__DIR__ . '/../routes.json', true), true);

        $data["sha"] = md5_file(__DIR__ . '/../routes.json');
        foreach ($json as $route => $conf) {
            $regex_uri = '/^(' . str_replace('/', '\/', $route) . ')$/';

            if (isset($conf['requirements'])) {
                foreach ($conf['requirements'] as $name => $reg) {
                    $regex_uri = str_replace(':' . $name, $reg, $regex_uri);
                }
            }

            $conf["regex_pattern"] = $regex_uri;
            $conf["controller"] = "Altech\Controller\\" . $conf["controller"];
            self::$routes[$route] = $conf;
        }
        $data["routes"] = self::$routes;

        $handle = fopen('../var/cache/routesGenerated.php', 'w') or die('Cannot open file routesGenerated');
        $content = "<?php return " . var_export($data, true) . ";";

        fwrite($handle, $content);

    }

    public
    static function getInstance(Request $req, $firewall): self
    {
        // Get instance if not already created
        if (is_null(self::$_instance)) {
            self::$_instance = new Router($req, $firewall);
        }

        return self::$_instance;
    }

}