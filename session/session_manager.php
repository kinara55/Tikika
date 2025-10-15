<?php
class SessionManager {
    private $conf;
    public function __construct($conf) {
        $this->conf = $conf;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkSessionTimeout();
    }
    public function login($user_id, $username, $role_id = 3) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role_id'] = $role_id;
        $_SESSION['login_time'] = time();
        $_SESSION['is_logged_in'] = true;
        
        session_regenerate_id(true);
    }
    
    public function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        // Kill the session on logout
        session_destroy();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
        // Check if the user is logged in
    }
    
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function getUsername() {
        return $_SESSION['username'] ?? null;
    }
    
    public function getRoleId() {
        return $_SESSION['role_id'] ?? 3;
    }
    // Redirecting the user to login page now 
    public function requireLogin($redirect_url = 'forms.html') {
        if (!$this->isLoggedIn()) {
            header("Location: $redirect_url");
            exit;
        }
    }
    // the message to be displayed for the user
    public function setMessage($type, $message) {
        $_SESSION['messages'][$type] = $message;
    }
    //getting the message for the user
    public function getMessage($type) {
        $message = $_SESSION['messages'][$type] ?? '';
        unset($_SESSION['messages'][$type]);
        return $message;
    }
    public function setFormData($data) {
        $_SESSION['form_data'] = $data;
    }

    public function getFormData($field) {
        return $_SESSION['form_data'][$field] ?? '';
    }
    // clearing the form data
    public function clearFormData() {
        unset($_SESSION['form_data']);
    }
    // setting the errors
    public function setErrors($errors) {
        $_SESSION['errors'] = $errors;
    }
    
    public function getErrors() {
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        return $errors;
    }
    
    public function clearErrors() {
        unset($_SESSION['errors']);
    }
    
    private function checkSessionTimeout() {
        if (isset($_SESSION['login_time'])) {
            $timeout = $this->conf['session_timeout'] ?? 3600;
            if (time() - $_SESSION['login_time'] > $timeout) {
                $this->logout();
                header("Location: forms.html?timeout=1");
                exit;
            }
        }
    }
     // This is where we keep track of the login attempts
    public function incrementLoginAttempts() {
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $_SESSION['login_attempts'] = $attempts + 1;
        $_SESSION['last_attempt'] = time();
    }
    // Are you locked out?
    public function isAccountLocked() {
        $max_attempts = $this->conf['max_login_attempts'] ?? 5;
        $lockout_duration = $this->conf['lockout_duration'] ?? 900;
        
        if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= $max_attempts) {
            $last_attempt = $_SESSION['last_attempt'] ?? 0;
            if (time() - $last_attempt < $lockout_duration) {
                return true;
            } else {
                $_SESSION['login_attempts'] = 0;
            }
        }
        return false;
    }
    // Reset the login attempts to 0
    public function resetLoginAttempts() {
        unset($_SESSION['login_attempts']);
        unset($_SESSION['last_attempt']);
    }
}
