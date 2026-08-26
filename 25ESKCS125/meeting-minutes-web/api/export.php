<?php
require_once __DIR__ . '/../config/db.php';
$userId = requireAuth();
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
$format = $_GET['format'] ?? 'md';

$stmt = $db->prepare("SELECT * FROM meetings WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);
$m = $stmt->fetch();
if (!$m) { http_response_code(404); echo "Not found"; exit; }

$stmt2 = $db->prepare("SELECT * FROM action_items WHERE meeting_id = ? ORDER BY CASE priority WHEN 'High' THEN 1 WHEN 'Medium' THEN 2 ELSE 3 END");
$stmt2->execute([$id]);
$actions = $stmt2->fetchAll();

$title = $m['title'];
$safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title);

if ($format === 'html') {
    header('Content-Type: text/html; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$safeTitle.html\"");
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>" . htmlspecialchars($title) . "</title>
    <style>body{font-family:system-ui;max-width:800px;margin:40px auto;line-height:1.6}
    h1{border-bottom:2px solid #333} table{border-collapse:collapse;width:100%}
    th,td{border:1px solid #ccc;padding:8px;text-align:left} th{background:#f5f5f5}</style></head><body>";
    echo "<h1>Meeting Minutes: " . htmlspecialchars($title) . "</h1>";
    echo "<p><strong>Date:</strong> " . htmlspecialchars($m['meeting_date'] ?? '') . " | <strong>Organizer:</strong> " . htmlspecialchars($m['organizer'] ?? '') . "</p>";
    echo "<p><strong>Attendees:</strong> " . htmlspecialchars($m['attendees'] ?? '') . "</p>";
    echo "<h2>Executive Summary</h2><p>" . nl2br(htmlspecialchars($m['executive_summary'] ?? '')) . "</p>";
    echo "<h2>Key Discussion Points</h2><pre>" . htmlspecialchars($m['discussion_points'] ?? '') . "</pre>";
    echo "<h2>Decisions</h2><pre>" . htmlspecialchars($m['decisions'] ?? '') . "</pre>";
    if ($actions) {
        echo "<h2>Action Items</h2><table><tr><th>#</th><th>Action</th><th>Owner</th><th>Deadline</th><th>Priority</th><th>Status</th></tr>";
        foreach ($actions as $i => $a) {
            echo "<tr><td>" . ($i+1) . "</td><td>" . htmlspecialchars($a['action_text']) . "</td>
            <td>" . htmlspecialchars($a['owner']) . "</td><td>" . htmlspecialchars($a['deadline'] ?? '') . "</td>
            <td>" . htmlspecialchars($a['priority']) . "</td><td>" . htmlspecialchars($a['status']) . "</td></tr>";
        }
        echo "</table>";
    }
    echo "<h2>Open Questions</h2><pre>" . htmlspecialchars($m['open_questions'] ?? '') . "</pre>";
    echo "<h2>Next Steps</h2><pre>" . htmlspecialchars($m['next_steps'] ?? '') . "</pre>";
    echo "</body></html>";
} else {
    header('Content-Type: text/markdown; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$safeTitle.md\"");
    echo "# Meeting Minutes: $title\n\n";
    echo "**Date:** {$m['meeting_date']}  \n**Organizer:** {$m['organizer']}  \n**Attendees:** {$m['attendees']}\n\n";
    echo "## Executive Summary\n{$m['executive_summary']}\n\n";
    echo "## Key Discussion Points\n{$m['discussion_points']}\n\n";
    echo "## Decisions\n{$m['decisions']}\n\n";
    echo "## Action Items\n\n| # | Action Item | Owner | Deadline | Priority | Status |\n|---|-------------|-------|----------|----------|--------|\n";
    foreach ($actions as $i => $a) {
        echo "| " . ($i+1) . " | {$a['action_text']} | {$a['owner']} | {$a['deadline']} | {$a['priority']} | {$a['status']} |\n";
    }
    echo "\n## Open Questions\n{$m['open_questions']}\n\n## Next Steps\n{$m['next_steps']}\n";
}
