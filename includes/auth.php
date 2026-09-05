<?php
/**
 * VolNet Authentication & Session Helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the current user's ID
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get the current user's role
 */
function get_current_role() {
    return $_SESSION['role'] ?? $_SESSION['user_type'] ?? null;
}

/**
 * Enforce authentication and optionally role check
 * Redirects to login page if unauthorized
 */
function require_auth($allowed_roles = []) {
    if (!is_logged_in()) {
        header("Location: /auth/login.html?error=unauthorized");
        exit();
    }

    if (!empty($allowed_roles)) {
        if (is_string($allowed_roles)) {
            $allowed_roles = [$allowed_roles];
        }
        $current_role = get_current_role();
        if (!in_array($current_role, $allowed_roles, true)) {
            header("Location: /auth/login.html?error=forbidden");
            exit();
        }
    }
}
