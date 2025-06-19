<?php
// imdb_clone/routes/Route.php
namespace App\Routes;

class Route {
    private static $routes = [];

    public static function get($url, $controller){
        self::$routes[] = ['url' => $url, 'controller' => $controller, 'method' => 'GET'];
    }

    public static function post($url, $controller){
        self::$routes[] = ['url' => $url, 'controller' => $controller, 'method' => 'POST'];
    }

    public static function dispatch(\PDO $pdo, \Twig\Environment $twig){ 
        $url = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        $urlSegments = explode('?', $url);
        $requestedPath = rtrim($urlSegments[0], '/');

        error_log("Route Dispatch: Requested Path = '{$requestedPath}'");
        error_log("Route Dispatch: Request Method = '{$method}'");
        error_log("Route Dispatch: BASE constant = '" . (defined('BASE') ? BASE : 'NOT DEFINED') . "'");


        foreach(self::$routes as $route){
            $definedRoutePath = rtrim(BASE . $route['url'], '/');

            error_log("Checking Route: Defined Path = '{$definedRoutePath}', Defined Method = '{$route['method']}'");

            if($definedRoutePath === $requestedPath && $route['method'] === $method){ 
                error_log("Route Match Found!");

                $controllerSegments = explode('@', $route['controller']);

                $controllerName = 'App\\Controllers\\'.$controllerSegments[0];
                $methodName = $controllerSegments[1];

                if (!class_exists($controllerName)) {
                    error_log("Error: Controller class '{$controllerName}' not found.");
                    http_response_code(500); 
                    echo $twig->render('error/500.html.twig'); 
                    return;
                }

                $controllerInstance = new $controllerName($pdo, $twig);

                if (!method_exists($controllerInstance, $methodName)) {
                    error_log("Error: Method '{$methodName}' not found in controller '{$controllerName}'.");
                    http_response_code(500); 
                    echo $twig->render('error/500.html.twig'); 
                    return;
                }

                if($method == 'GET'){
                    if(isset($urlSegments[1]) && !empty($urlSegments[1])){ 
                         parse_str($urlSegments[1], $queryParams);
                         error_log("GET Request: Passing Query Params: " . print_r($queryParams, true) . " to {$controllerName}@{$methodName}");
                        $controllerInstance->$methodName($queryParams);
                    }else{
                        error_log("GET Request: No Query Params, calling {$controllerName}@{$methodName} without arguments");
                        $controllerInstance->$methodName();
                    }
                }elseif($method == 'POST'){
                     if(isset($urlSegments[1]) && !empty($urlSegments[1])){ 
                         parse_str($urlSegments[1], $queryParams);
                         error_log("POST Request: Passing POST Data and Query Params: " . print_r($_POST, true) . " / " . print_r($queryParams, true) . " to {$controllerName}@{$methodName}");
                        $controllerInstance->$methodName($_POST, $queryParams);
                    }else{
                         error_log("POST Request: Passing POST Data only: " . print_r($_POST, true) . " to {$controllerName}@{$methodName}");
                         $controllerInstance->$methodName($_POST);
                    }
                }

                return; 
            }
        }
        error_log("Route Dispatch: No matching route found for '{$requestedPath}' ({$method})");
        http_response_code(404);
        echo $twig->render('error/404.html.twig');
    }
}