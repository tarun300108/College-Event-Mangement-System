<?php
require_once 'db.php';

function ensure_auth_table(mysqli $conn): void {
    $conn->query(
        "CREATE TABLE IF NOT EXISTS auth_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_type VARCHAR(20) NOT NULL,
            user_id INT NOT NULL,
            selector VARCHAR(24) NOT NULL,
            validator_hash VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_selector (selector)
        )"
    );
}

function create_remember_token(mysqli $conn, string $user_type, int $user_id): void {
    ensure_auth_table($conn);

    $selector = bin2hex(random_bytes(6));
    $validator = bin2hex(random_bytes(32));
    $validator_hash = hash('sha256', $validator);
    $expires_at = (new DateTime('+30 days'))->format('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO auth_tokens (user_type, user_id, selector, validator_hash, expires_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $user_type, $user_id, $selector, $validator_hash, $expires_at);
    $stmt->execute();
    $stmt->close();

    setcookie('cems_auth', $selector . ':' . $validator, time() + 60 * 60 * 24 * 30, '/', '', false, true);
}

function clear_remember_token(mysqli $conn): void {
    if (empty($_COOKIE['cems_auth'])) {
        return;
    }

    ensure_auth_table($conn);

    $parts = explode(':', $_COOKIE['cems_auth'], 2);
    $selector = $parts[0] ?? '';

    if ($selector !== '') {
        $stmt = $conn->prepare("DELETE FROM auth_tokens WHERE selector = ?");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
    }

    setcookie('cems_auth', '', time() - 3600, '/', '', false, true);
}

function auto_login(): void {
    if (!empty($_SESSION['student_id']) || !empty($_SESSION['faculty_id'])) {
        return;
    }

    if (empty($_COOKIE['cems_auth'])) {
        return;
    }

    ensure_auth_table($GLOBALS['conn']);

    $parts = explode(':', $_COOKIE['cems_auth'], 2);
    if (count($parts) !== 2) {
        return;
    }

    [$selector, $validator] = $parts;
    $stmt = $GLOBALS['conn']->prepare("SELECT user_type, user_id, validator_hash, expires_at FROM auth_tokens WHERE selector = ? LIMIT 1");
    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $result = $stmt->get_result();
    $token = $result->fetch_assoc();
    $stmt->close();

    if (!$token) {
        return;
    }

    if (new DateTime($token['expires_at']) < new DateTime()) {
        clear_remember_token($GLOBALS['conn']);
        return;
    }

    $validator_hash = hash('sha256', $validator);
    if (!hash_equals($token['validator_hash'], $validator_hash)) {
        clear_remember_token($GLOBALS['conn']);
        return;
    }

    if ($token['user_type'] === 'student') {
        $stmt = $GLOBALS['conn']->prepare("SELECT Student_id AS student_id, name FROM student_table WHERE Student_id = ?");
        $stmt->bind_param("i", $token['user_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['student_name'] = $user['name'];
            clear_remember_token($GLOBALS['conn']);
            create_remember_token($GLOBALS['conn'], 'student', (int)$user['student_id']);
        }
    } elseif ($token['user_type'] === 'faculty') {
        $stmt = $GLOBALS['conn']->prepare("SELECT faculty_id, name FROM faculty_table WHERE faculty_id = ?");
        $stmt->bind_param("i", $token['user_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            $_SESSION['faculty_id'] = $user['faculty_id'];
            $_SESSION['faculty_name'] = $user['name'];
            clear_remember_token($GLOBALS['conn']);
            create_remember_token($GLOBALS['conn'], 'faculty', (int)$user['faculty_id']);
        }
    }
}
