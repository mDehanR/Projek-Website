<?php
require_once 'config.php';

setHeaders();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $action = $_GET['action'] ?? 'login';
        if ($action === 'register') {
            registerUser();
        } else {
            loginUser();
        }
        break;
    case 'GET':
        verifyToken();
        break;
    default:
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * User login
 */
function loginUser() {
    $data = getJsonInput();
    
    if (!$data) {
        $data = [
            'email' => $_POST['email'] ?? null,
            'password' => $_POST['password'] ?? null
        ];
    }
    
    if (!isset($data['email']) || !isset($data['password'])) {
        sendResponse(['success' => false, 'message' => 'Email and password are required'], 400);
    }
    
    $email = trim($data['email']);
    $password = $data['password'];
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        sendResponse(['success' => false, 'message' => 'Email atau password salah'], 401);
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    if (password_verify($password, $user['password'])) {
        // Generate token
        $token = generateToken($user['id']);
        
        sendResponse([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'token' => $token
            ]
        ]);
    } else {
        sendResponse(['success' => false, 'message' => 'Email atau password salah'], 401);
    }
}

/**
 * User registration
 */
function registerUser() {
    $data = getJsonInput();
    
    if (!$data) {
        $data = [
            'name' => $_POST['name'] ?? null,
            'email' => $_POST['email'] ?? null,
            'password' => $_POST['password'] ?? null,
            'role' => $_POST['role'] ?? 'murid'
        ];
    }
    
    // Validate required fields
    $required = ['name', 'email', 'password'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            sendResponse(['success' => false, 'message' => "$field is required"], 400);
        }
    }
    
    $name = trim($data['name']);
    $email = trim($data['email']);
    $password = $data['password'];
    $role = isset($data['role']) ? trim($data['role']) : 'murid';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(['success' => false, 'message' => 'Format email tidak valid'], 400);
    }
    
    // Validate password length
    if (strlen($password) < 6) {
        sendResponse(['success' => false, 'message' => 'Password minimal 6 karakter'], 400);
    }
    
    // Validate role
    if (!in_array($role, ['murid', 'guru'])) {
        $role = 'murid';
    }
    
    // Check if email already exists
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        $conn->close();
        sendResponse(['success' => false, 'message' => 'Email sudah terdaftar'], 400);
    }
    $stmt->close();
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $stmt->close();
        $conn->close();
        
        // Generate token
        $token = generateToken($userId);
        
        sendResponse([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role
                ],
                'token' => $token
            ]
        ]);
    } else {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        sendResponse(['success' => false, 'message' => 'Registrasi gagal: ' . $error], 500);
    }
}

/**
 * Verify token
 */
function verifyToken() {
    $token = $_GET['token'] ?? null;
    
    if (!$token) {
        sendResponse(['success' => false, 'message' => 'Token is required'], 400);
    }
    
    $conn = getDBConnection();
    
    // Decode token (simple base64 encoding for demo)
    $decoded = json_decode(base64_decode($token), true);
    
    if (!$decoded || !isset($decoded['user_id']) || !isset($decoded['exp'])) {
        $conn->close();
        sendResponse(['success' => false, 'message' => 'Invalid token'], 401);
    }
    
    // Check expiration
    if ($decoded['exp'] < time()) {
        $conn->close();
        sendResponse(['success' => false, 'message' => 'Token expired'], 401);
    }
    
    // Get user
    $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $decoded['user_id']);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        sendResponse(['success' => false, 'message' => 'User not found'], 404);
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'data' => ['user' => $user]
    ]);
}

/**
 * Generate auth token
 */
function generateToken($userId) {
    $payload = [
        'user_id' => $userId,
        'exp' => time() + (7 * 24 * 60 * 60) // 7 days
    ];
    
    return base64_encode(json_encode($payload));
}
