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
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM meetings WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            $meeting = $stmt->fetch();
            if ($meeting) {
                $stmt2 = $db->prepare("SELECT * FROM action_items WHERE meeting_id = ? ORDER BY CASE priority WHEN 'High' THEN 1 WHEN 'Medium' THEN 2 ELSE 3 END, deadline");
                $stmt2->execute([$id]);
                $meeting['action_items'] = $stmt2->fetchAll();
            }
            echo json_encode($meeting ?: ['error' => 'Not found']);
        } else {
            $q = $_GET['q'] ?? '';
            if ($q) {
                $like = "%$q%";
                $stmt = $db->prepare("SELECT id, title, meeting_date, organizer, created_at FROM meetings WHERE user_id=? AND (title LIKE ? OR executive_summary LIKE ?) ORDER BY meeting_date DESC");
                $stmt->execute([$userId, $like, $like]);
            } else {
                $stmt = $db->prepare("SELECT id, title, meeting_date, organizer, created_at FROM meetings WHERE user_id=? ORDER BY meeting_date DESC, id DESC");
                $stmt->execute([$userId]);
            }
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("INSERT INTO meetings (user_id, title, meeting_date, location, organizer, attendees, executive_summary, discussion_points, decisions, open_questions, next_steps, raw_notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $userId, $data['title'], $data['meeting_date'] ?? null, $data['location'] ?? null,
            $data['organizer'] ?? null, $data['attendees'] ?? null,
            $data['executive_summary'] ?? null, $data['discussion_points'] ?? null,
            $data['decisions'] ?? null, $data['open_questions'] ?? null,
            $data['next_steps'] ?? null, $data['raw_notes'] ?? null
        ]);
        $meetingId = $db->lastInsertId();

        if (!empty($data['action_items']) && is_array($data['action_items'])) {
            $stmtA = $db->prepare("INSERT INTO action_items (meeting_id, action_text, owner, deadline, priority, status) VALUES (?,?,?,?,?,?)");
            foreach ($data['action_items'] as $item) {
                $stmtA->execute([
                    $meetingId,
                    $item['action_text'],
                    $item['owner'] ?? 'Unassigned',
                    $item['deadline'] ?: null,
                    $item['priority'] ?? 'Medium',
                    $item['status'] ?? 'Open'
                ]);
            }
        }
        echo json_encode(['success' => true, 'id' => $meetingId]);
        break;

    case 'DELETE':
        if (!$id) { echo json_encode(['error' => 'ID required']); break; }
        $stmt = $db->prepare("DELETE FROM meetings WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
