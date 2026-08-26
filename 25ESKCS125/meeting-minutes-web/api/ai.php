<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/../config/db.php';
$userId = requireAuth();
$db = getDB();

$stmt = $db->prepare("SELECT api_key, api_base FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (empty($user['api_key'])) {
    echo json_encode(['error' => 'No API key saved. Go to Settings and add your OpenAI-compatible key.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$notes = trim($data['notes'] ?? '');
$title = trim($data['title'] ?? '');
$date  = trim($data['date'] ?? '');

if (!$notes) {
    echo json_encode(['error' => 'Notes required']);
    exit;
}

$system = "You are an expert meeting minutes specialist. Return ONLY valid JSON with these keys: executive_summary (string), discussion_points (string with newlines), decisions (string with newlines), open_questions (string), next_steps (string), action_items (array of objects with action_text, owner, deadline, priority, status). Never invent facts. Priority must be High/Medium/Low. Status default Open.";

$userMsg = "Title: $title\nDate: $date\n\nRaw notes:\n$notes";

$payload = [
    'model' => $data['model'] ?? 'gpt-4o-mini',
    'messages' => [
        ['role' => 'system', 'content' => $system],
        ['role' => 'user', 'content' => $userMsg]
    ],
    'temperature' => 0.3,
    'response_format' => ['type' => 'json_object']
];

$ch = curl_init(rtrim($user['api_base'], '/') . '/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $user['api_key']
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 90
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['error' => 'cURL error: ' . $err]);
    exit;
}
if ($httpCode !== 200) {
    echo json_encode(['error' => 'AI API error (HTTP ' . $httpCode . ')', 'details' => $response]);
    exit;
}

$json = json_decode($response, true);
$content = $json['choices'][0]['message']['content'] ?? '';
$result = json_decode($content, true);

if (!$result) {
    echo json_encode(['error' => 'Failed to parse AI response', 'raw' => $content]);
    exit;
}

echo json_encode(['success' => true, 'data' => $result]);
