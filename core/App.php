<?php
/**
 * Albashiro - Islamic Spiritual Hypnotherapy
 * Application Router with Custom Routes
 */

class App
{
    protected $controller = 'Home';
    protected $method = 'index';
    protected $params = [];

    // Custom route mappings (simple routes to controller/method)
    protected $routes = [
        'tentang' => ['Pages', 'tentang'],
        'layanan' => ['Pages', 'layanan'],
        'terapis' => ['Pages', 'terapis'],
        'kontak' => ['Pages', 'kontak'],
        'reservasi' => ['Pages', 'reservasi'],
        'blog' => ['Pages', 'blog'],
        'login' => ['Auth', 'login'],
    ];

    public function __construct()
    {
        $url = $this->parseUrl();
        $route = strtolower($url[0] ?? '');

        // Check for custom routes first
        if (isset($this->routes[$route])) {
            $this->controller = $this->routes[$route][0];
            $this->method = $this->routes[$route][1];
            unset($url[0]);

            // For blog, the next segment is the slug parameter
            if ($route === 'blog' && isset($url[1])) {
                $this->params = [strtolower($url[1])];
                unset($url[1]);
            }
        } else {
            // Default controller/method routing
            $urlController = $url[0] ?? 'Home';
            $controllerFile = SITE_ROOT . '/app/controllers/' . ucfirst($urlController) . '.php';

            if (file_exists($controllerFile)) {
                $this->controller = ucfirst($urlController);
                unset($url[0]);
            }

            // Check if method exists (for non-custom routes)
            if (isset($url[1])) {
                // Load controller first to check method
                $controllerPath = SITE_ROOT . '/app/controllers/' . $this->controller . '.php';
                if (file_exists($controllerPath)) {
                    require_once $controllerPath;
                    // We need to instantiate to check method_exists, 
                    // but we can't assume the class name always matches file name perfectly if case sensitivity differs,
                    // basically rely on $this->controller being correct class name.
                    if (class_exists($this->controller)) {
                        $tempController = new $this->controller;
                        if (method_exists($tempController, $url[1])) {
                            $this->method = $url[1];
                            unset($url[1]);
                        }
                    }
                }
            }

            // Get remaining URL parts as params
            $this->params = $url ? array_values($url) : [];
        }

        // Load controller if not already loaded
        $controllerPath = SITE_ROOT . '/app/controllers/' . $this->controller . '.php';
        if (!class_exists($this->controller)) {
            require_once $controllerPath;
        }

        $controllerInstance = new $this->controller;

        // Call the controller method with params
        call_user_func_array([$controllerInstance, $this->method], $this->params);
    }

    /**
     * Parse URL into array
     */
    protected function parseUrl()
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }

        // Fallback: Check PATH_INFO (e.g., index.php/login)
        if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
            $url = trim($_SERVER['PATH_INFO'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }

        // Fallback: Check REQUEST_URI manually (e.g., /albashiro/login)
        // This attempts to support pretty URLs even if .htaccess fails but Apache sends 404 to index.php (rare config but possible)
        // Or if user uses index.php/login style
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);

        // Remove script path from URI
        $url = str_replace($scriptName, '', $requestUri);
        // Remove index.php if present
        $url = str_replace('/index.php', '', $url);
        $url = trim($url, '/');

        if (!empty($url)) {
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }

        return [];
    }
}
