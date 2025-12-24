<?php
/**
 * Albashiro - Islamic Spiritual Hypnotherapy
 * Base Controller
 */

class Controller
{

    /**
     * Load a model
     */
    protected function model($model)
    {
        $modelFile = SITE_ROOT . '/app/models/' . $model . '.php';

        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        }

        throw new Exception("Model {$model} not found");
    }

    /**
     * Load a view with data
     */
    protected function view($view, $data = [])
    {
        $viewFile = SITE_ROOT . '/app/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            // Extract data array to variables
            extract($data);

            // Start output buffering
            ob_start();

            // Include header template
            $headerFile = SITE_ROOT . '/app/views/templates/header.php';
            if (file_exists($headerFile)) {
                include $headerFile;
            }

            // Include main view
            include $viewFile;

            // Include footer template
            $footerFile = SITE_ROOT . '/app/views/templates/footer.php';
            if (file_exists($footerFile)) {
                include $footerFile;
            }

            // Get and return buffer content
            return ob_get_clean();
        }

        throw new Exception("View {$view} not found");
    }

    /**
     * Load a view without templates (for partials/AJAX)
     */
    protected function partial($view, $data = [])
    {
        $viewFile = SITE_ROOT . '/app/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            extract($data);
            ob_start();
            include $viewFile;
            return ob_get_clean();
        }

        throw new Exception("Partial view {$view} not found");
    }

    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get POST or GET data safely
     */
    protected function input($key, $default = null)
    {
        if (isset($_POST[$key])) {
            return trim($_POST[$key]);
        }
        if (isset($_GET[$key])) {
            return trim($_GET[$key]);
        }
        return $default;
    }

    /**
     * Check if request is POST
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Set flash message
     */
    protected function setFlash($type, $message)
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Get and clear flash message
     */
    protected function getFlash()
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
