<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');
require_once __DIR__ . '/config.php';

$result = mysqli_query($connection, "SELECT id, title, description, image, type, date FROM adminDashboard ORDER BY date DESC, id DESC LIMIT 30");
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load posts.']);
    exit;
}

$posts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $image = $row['image'] ?? '';
    $posts[] = [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'description' => $row['description'],
        'image' => $image !== '' && basename($image) === $image ? 'uploads/' . rawurlencode($image) : null,
        'type' => $row['type'],
        'date' => $row['date'],
    ];
}
echo json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
