<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/../config/db.php';
$userId = requireAuth();
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    case 'GET':
        $status = $_GET['status'] ?? null;
        $sql = "SELECT a.*, m.title as meeting_title FROM action_items a 
                JOIN meetings m ON a.meeting_id = m.id WHERE m.user_id = ?";
        if ($status) {
            $sql .= " AND a.status = ?";
            $stmt = $db->prepare($sql . " ORDER BY CASE a.priority WHEN 'High' THEN 1 WHEN 'Medium' THEN 2 ELSE 3 END, a.deadline");
            $stmt->execute([$userId, $status]);
        } else {
            $stmt = $db->prepare($sql . " ORDER BY CASE a.priority WHEN 'High' THEN 1 WHEN 'Medium' THEN 2 ELSE 3 END, a.deadline");
            $stmt->execute([$userId]);
        }
        echo json_encode($stmt->fetchAll());
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $mid = (int)($data['meeting_id'] ?? 0);
        $check = $db->prepare("SELECT id FROM meetings WHERE id=? AND user_id=?");
        $check->execute([$mid, $userId]);
        if (!$check->fetch()) {
            echo json_encode(['error' => 'Invalid meeting']); exit;
        }
        $stmt = $db->prepare("INSERT INTO action_items (meeting_id, action_text, owner, deadline, priority, status) VALUES (?,?,?,?,?,?)");
        $stmt->execute([
            $mid, $data['action_text'],
            $data['owner'] ?? 'Unassigned',
            $data['deadline'] ?: null,
            $data['priority'] ?? 'Medium',
            $data['status'] ?? 'Open'
        ]);
        echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$id) { echo json_encode(['error' => 'ID required']); break; }
        $stmt = $db->prepare("UPDATE action_items SET action_text=?, owner=?, deadline=?, priority=?, status=? 
                              WHERE id=? AND meeting_id IN (SELECT id FROM meetings WHERE user_id=?)");
        $stmt->execute([
            $data['action_text'], $data['owner'], $data['deadline'] ?: null,
            $data['priority'], $data['status'], $id, $userId
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        if (!$id) { echo json_encode(['error' => 'ID required']); break; }
        $stmt = $db->prepare("DELETE FROM action_items WHERE id=? AND meeting_id IN (SELECT id FROM meetings WHERE user_id=?)");
        $stmt->execute([$id, $userId]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
