<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) && !empty($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me']);

    if (count($parts) === 2) {
        [$selector, $validator] = $parts;

        $stmt = $db->prepare("
            SELECT 
                rt.user_id,
                rt.hashed_validator,
                rt.expires_at,
                u.user_id,
                u.username,
                r.role_name
            FROM remember_tokens rt
            INNER JOIN users u ON rt.user_id = u.user_id
            INNER JOIN user_roles ur ON u.user_id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.role_id
            WHERE rt.selector = :selector
            LIMIT 1
        ");
        $stmt->execute([
            ':selector' => $selector
        ]);
        $row = $stmt->fetch();

        if ($row) {
            $isValid = hash_equals($row['hashed_validator'], hash('sha256', $validator));
            $notExpired = strtotime($row['expires_at']) > time();

            if ($isValid && $notExpired) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role_name'];

                // rotate token
                $newSelector = bin2hex(random_bytes(8));
                $newValidator = bin2hex(random_bytes(32));
                $newHashedValidator = hash('sha256', $newValidator);
                $newExpires = date('Y-m-d H:i:s', strtotime('+30 days'));

                $update = $db->prepare("
                    UPDATE remember_tokens
                    SET selector = :selector,
                        hashed_validator = :hashed_validator,
                        expires_at = :expires_at
                    WHERE user_id = :user_id
                ");
                $update->execute([
                    ':selector' => $newSelector,
                    ':hashed_validator' => $newHashedValidator,
                    ':expires_at' => $newExpires,
                    ':user_id' => $row['user_id']
                ]);

                setcookie(
                    'remember_me',
                    $newSelector . ':' . $newValidator,
                    [
                        'expires' => time() + (60 * 60 * 24 * 30),
                        'path' => '/',
                        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );
            } else {
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

                $delete = $db->prepare("
                    DELETE FROM remember_tokens
                    WHERE selector = :selector
                ");
                $delete->execute([
                    ':selector' => $selector
                ]);
            }
        } else {
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
    } else {
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
}