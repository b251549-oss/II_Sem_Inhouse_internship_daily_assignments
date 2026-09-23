<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$db = getDB();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $pass = $data['password'] ?? '';
        if (!$name || !$email || strlen($pass) < 6) {
            echo json_encode(['error' => 'Name, email and password (min 6) required']);
            exit;
        }
        try {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?,?,?)");
            $stmt->execute([$name, $email, $hash]);
            $_SESSION['user_id'] = $db->lastInsertId();
            $_SESSION['user_name'] = $name;
            echo json_encode(['success' => true, 'name' => $name]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Email already registered']);
        }
        break;

    case 'login':
        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');
        $pass = $data['password'] ?? '';
        $stmt = $db->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            echo json_encode(['success' => true, 'name' => $user['name']]);
        } else {
            echo json_encode(['error' => 'Invalid email or password']);
        }
        break;

    case 'logout':
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    case 'me':
        $user = getCurrentUser();
        if ($user) {
            unset($user['api_key']);
            echo json_encode(['logged_in' => true, 'user' => $user]);
        } else {
            echo json_encode(['logged_in' => false]);
        }
        break;

    case 'save_api':
        $userId = requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        $key = trim($data['api_key'] ?? '');
        $base = trim($data['api_base'] ?? 'https://api.openai.com/v1');
        $stmt = $db->prepare("UPDATE users SET api_key=?, api_base=? WHERE id=?");
        $stmt->execute([$key, $base, $userId]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}
