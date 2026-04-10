<?php
require_once __DIR__ . '/../../config/db.php';

if (!empty($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me']);

    if (count($parts) === 2) {
        $selector = $parts[0];

        $stmt = $db->prepare("
            DELETE FROM remember_tokens
            WHERE selector = :selector
        ");
        $stmt->execute([
            ':selector' => $selector
        ]);
    }

    setcookie(
        'remember_me',
        '',
        [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}

$_SESSION = [];
session_destroy();

header("Location: login.php");
exit;