<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $users = getUsers();
    foreach ($users as $user) {
        if ($user['id'] === $_SESSION['user_id']) {
            return $user;
        }
    }
    
    return null;
}

function hasRole($role) {
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    if ($user['role'] === 'admin') {
        return true;
    }
    
    if (is_array($role)) {
        return in_array($user['role'], $role);
    }
    
    return $user['role'] === $role;
}

function requireRole($role) {
    requireLogin();
    
    if (!hasRole($role)) {
        header('Location: dashboard.php');
        exit;
    }
}

function login($username, $password) {
    $user = getUserByUsername($username);
    
    if (!$user) {
        return false;
    }
    
    if (!password_verify($password, $user['password'])) {
        return false;
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
    return true;
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
