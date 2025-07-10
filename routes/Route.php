<?php
namespace App\Routes;

class Route {
    private static $routes = [];

    public static function get($url, $controller){
        self::$routes[] = ['url' => $url, 'controller' => $controller, 'method' => 'GET'];
    }

    public static function post($url, $controller){
        self::$routes[] = ['url' => $url, 'controller' => $controller, 'method' => 'POST'];
    }

    public static function dispatch(\PDO $pdo, \Twig\Environment $twig, array $config){
        $url = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        $urlSegments = explode('?', $url);
        $requestedPath = rtrim($urlSegments[0], '/');

        foreach(self::$routes as $route){
            $definedRoutePath = rtrim(BASE . $route['url'], '/');

            if($definedRoutePath === $requestedPath && $route['method'] === $method){
                $controllerSegments = explode('@', $route['controller']);

                $controllerName = 'App\\Controllers\\'.$controllerSegments[0];
                $methodName = $controllerSegments[1];
                $controllerInstance = new $controllerName($pdo, $twig, $config);

                if (method_exists($controllerInstance, 'setConfig')) {
                    $controllerInstance->setConfig($config);
                }

                if($method == 'GET'){
                    if(isset($urlSegments[1]) && !empty($urlSegments[1])){
                        parse_str($urlSegments[1], $queryParams);
                        $controllerInstance->$methodName($queryParams);
                    }else{
                        $controllerInstance->$methodName();
                    }
                }elseif($method == 'POST'){
                    if(isset($urlSegments[1]) && !empty($urlSegments[1])){
                        parse_str($urlSegments[1], $queryParams);
                        $controllerInstance->$methodName($_POST, $queryParams);
                    }else{
                        $controllerInstance->$methodName($_POST);
                    }
                }

                return;
            }
        }
        http_response_code(404);
        echo $twig->render('error/404.html.twig');
    }
}