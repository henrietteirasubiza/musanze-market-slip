<?php
// -----------------------------------------------
// UserModel.php — login and user management
// -----------------------------------------------

require_once __DIR__ . '/../config/database.php';

class UserModel {

    // Find user by username (for login)
    public static function findByUsername(string $username): ?array {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // Verify password and return user or null
    public static function login(string $username, string $password): ?array {
        $user = self::findByUsername($username);
        if (!$user) return null;

        // password_verify works with PHP's password_hash (bcrypt)
        if (!password_verify($password, $user['password'])) return null;

        return $user;
    }

    // All users (admin panel)
    public static function getAll(): array {
        $db = getDB();
        return $db->query("SELECT id, username, role, full_name, created_at FROM users ORDER BY id")
                  ->fetch_all(MYSQLI_ASSOC);
    }

    // Create user
    public static function create(array $data): int {
        $db   = getDB();
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare(
            "INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('ssss', $data['username'], $hash, $data['role'], $data['full_name']);
        $stmt->execute();
        return $db->insert_id;
    }
}
