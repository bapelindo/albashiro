<?php
/**
 * Albashiro - Auth Controller
 * Handles admin login/logout
 */

class Auth extends Controller
{

    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    /**
     * Login page
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->isLoggedIn()) {
            redirect('admin');
        }

        if ($this->isPost()) {
            // Verify CSRF
            if (!verify_csrf($this->input('csrf_token'))) {
                $this->setFlash('error', 'Sesi tidak valid. Silakan coba lagi.');
                redirect('auth/login');
            }

            $email = $this->input('email');
            $password = $this->input('password');

            // Find user
            $user = $this->userModel->findByEmail($email);

            if ($user && $this->userModel->verifyPassword($password, $user->password)) {
                // Login success
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_name'] = $user->name;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_role'] = $user->role;

                $this->userModel->updateLastLogin($user->id);

                redirect('admin');
            } else {
                $this->setFlash('error', 'Email atau password salah.');
                redirect('auth/login');
            }
        }

        $data = [
            'title' => 'Login Admin',
            'flash' => $this->getFlash()
        ];

        echo $this->viewAdmin('auth/login', $data);
    }

    /**
     * Logout
     */
    public function logout()
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        session_destroy();

        redirect('auth/login');
    }

    /**
     * Check if user is logged in
     */
    private function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Load admin view (no header/footer templates)
     */
    protected function viewAdmin($view, $data = [])
    {
        $viewFile = SITE_ROOT . '/app/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            extract($data);
            ob_start();
            include $viewFile;
            return ob_get_clean();
        }

        throw new Exception("View {$view} not found");
    }
}
