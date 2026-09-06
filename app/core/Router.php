<?php
class Router
{
    private array $routes = [];
    private string $notFound = '';

    public function get(string $pattern, string $action): void { $this->add('GET', $pattern, $action); }
    public function post(string $pattern, string $action): void { $this->add('POST', $pattern, $action); }

    private function add(string $method, string $pattern, string $action): void
    {
        $this->routes[] = [$method, $pattern, $action];
    }

    public function notFound(string $action): void { $this->notFound = $action; }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $this->path();

        foreach ($this->routes as [$m, $pattern, $action]) {
            if ($m !== $method) {
                continue;
            }
            $regex = $this->compile($pattern);
            if (preg_match($regex, $path, $matches)) {
                $params = [];
                foreach ($matches as $k => $v) {
                    if (!is_int($k)) {
                        $params[$k] = $v;
                    }
                }
                $this->invoke($action, $params);
                return;
            }
        }
        if ($this->notFound) {
            http_response_code(404);
            $this->invoke($this->notFound, []);
            return;
        }
        http_response_code(404);
        echo '404 Not Found';
    }

    private function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $base = app_base_url();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = trim($path, '/');
        return $path;
    }

    private function compile(string $pattern): string
    {
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $pattern);
        return '#^'.$regex.'$#u';
    }

    private function invoke(string $action, array $params): void
    {
        [$class, $method] = explode('@', $action, 2);
        $controller = new $class();
        $controller->{$method}(...$this->orderParams($method, $class, $params));
    }

    private function orderParams(string $method, string $class, array $params): array
    {
        $r = new ReflectionMethod($class, $method);
        $ordered = [];
        foreach ($r->getParameters() as $p) {
            $name = $p->getName();
            if (array_key_exists($name, $params)) {
                $ordered[] = $params[$name];
            } elseif ($p->isDefaultValueAvailable()) {
                $ordered[] = $p->getDefaultValue();
            } else {
                $ordered[] = null;
            }
        }
        return $ordered;
    }
}
