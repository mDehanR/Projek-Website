<?php
require_once 'config.php';

setHeaders();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        calculateGrade();
        break;
    case 'GET':
        getGradeHistory();
        break;
    default:
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Calculate final grade based on scores and weights
 */
function calculateGrade() {
    $data = getJsonInput();
    
    if (!$data) {
        // Try to get from form data
        $data = [
            'tugas' => $_POST['tugas'] ?? null,
            'bobot_tugas' => $_POST['bobot_tugas'] ?? null,
            'uts' => $_POST['uts'] ?? null,
            'bobot_uts' => $_POST['bobot_uts'] ?? null,
            'uas' => $_POST['uas'] ?? null,
            'bobot_uas' => $_POST['bobot_uas'] ?? null,
            'user_id' => $_POST['user_id'] ?? null
        ];
    }
    
    // Validate required fields
    $required = ['tugas', 'bobot_tugas', 'uts', 'bobot_uts', 'uas', 'bobot_uas'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            sendResponse(['success' => false, 'message' => "Field $field is required"], 400);
        }
    }
    
    // Parse values
    $tugas = floatval($data['tugas']);
    $bobotTugas = floatval($data['bobot_tugas']);
    $uts = floatval($data['uts']);
    $bobotUts = floatval($data['bobot_uts']);
    $uas = floatval($data['uas']);
    $bobotUas = floatval($data['bobot_uas']);
    
    // Validate ranges
    if ($tugas < 0 || $tugas > 100 || $uts < 0 || $uts > 100 || $uas < 0 || $uas > 100) {
        sendResponse(['success' => false, 'message' => 'Nilai harus antara 0-100'], 400);
    }
    
    if ($bobotTugas < 0 || $bobotTugas > 100 || $bobotUts < 0 || $bobotUts > 100 || $bobotUas < 0 || $bobotUas > 100) {
        sendResponse(['success' => false, 'message' => 'Bobot harus antara 0-100'], 400);
    }
    
    // Check total weight
    $totalBobot = $bobotTugas + $bobotUts + $bobotUas;
    if (abs($totalBobot - 100) > 0.01) {
        sendResponse(['success' => false, 'message' => "Total bobot harus 100%. Saat ini: $totalBobot%"], 400);
    }
    
    // Calculate final grade
    $nilaiAkhir = ($tugas * $bobotTugas / 100) + ($uts * $bobotUts / 100) + ($uas * $bobotUas / 100);
    $nilaiAkhir = round($nilaiAkhir, 2);
    
    // Determine grade
    $grade = getGrade($nilaiAkhir);
    
    // Save to database if user_id provided
    $result = null;
    if (isset($data['user_id']) && $data['user_id']) {
        $result = saveGrade($data);
    }
    
    sendResponse([
        'success' => true,
        'data' => [
            'nilai_akhir' => $nilaiAkhir,
            'grade' => $grade['letter'],
            'grade_info' => $grade['info'],
            'details' => [
                'tugas' => ['nilai' => $tugas, 'bobot' => $bobotTugas, 'hasil' => round($tugas * $bobotTugas / 100, 2)],
                'uts' => ['nilai' => $uts, 'bobot' => $bobotUts, 'hasil' => round($uts * $bobotUts / 100, 2)],
                'uas' => ['nilai' => $uas, 'bobot' => $bobotUas, 'hasil' => round($uas * $bobotUas / 100, 2)]
            ]
        ],
        'saved' => $result !== null
    ]);
}

/**
 * Determine grade based on score
 */
function getGrade($score) {
    if ($score >= 90) {
        return ['letter' => 'A', 'info' => 'Sangat Baik'];
    } elseif ($score >= 80) {
        return ['letter' => 'B', 'info' => 'Baik'];
    } elseif ($score >= 70) {
        return ['letter' => 'C', 'info' => 'Cukup'];
    } elseif ($score >= 60) {
        return ['letter' => 'D', 'info' => 'Kurang'];
    } else {
        return ['letter' => 'E', 'info' => 'Sangat Kurang'];
    }
}

/**
 * Save grade to database
 */
function saveGrade($data) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO grades (user_id, tugas, bobot_tugas, uts, bobot_uts, uas, bobot_uas, nilai_akhir, grade, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $nilaiAkhir = ($data['tugas'] * $data['bobot_tugas'] / 100) + ($data['uts'] * $data['bobot_uts'] / 100) + ($data['uas'] * $data['bobot_uas'] / 100);
    $grade = getGrade($nilaiAkhir);
    
    $stmt->bind_param("iddiddidd", 
        $data['user_id'],
        $data['tugas'],
        $data['bobot_tugas'],
        $data['uts'],
        $data['bobot_uts'],
        $data['uas'],
        $data['bobot_uas'],
        $nilaiAkhir,
        $grade['letter']
    );
    
    if ($stmt->execute()) {
        $result = ['id' => $stmt->insert_id, 'success' => true];
    } else {
        $result = ['success' => false, 'message' => $stmt->error];
    }
    
    $stmt->close();
    $conn->close();
    
    return $result;
}

/**
 * Get grade history for a user
 */
function getGradeHistory() {
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        sendResponse(['success' => false, 'message' => 'User ID is required'], 400);
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT * FROM grades WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $grades = [];
    
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    sendResponse([
        'success' => true,
        'data' => $grades,
        'count' => count($grades)
    ]);
}
