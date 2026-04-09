<?php
header('Content-Type: application/json');

define('SB_DB_NAME', 'tglevel_support');
define('SB_DB_USER', 'tglevel_support');
define('SB_DB_PASSWORD', 'Tglevels@123$');
define('SB_DB_HOST', 'localhost');

$mysqli = new mysqli(SB_DB_HOST, SB_DB_USER, SB_DB_PASSWORD, SB_DB_NAME);

if ($mysqli->connect_errno) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error
    ]);
    exit;
}

$phone = isset($_GET['phone']) ? trim($_GET['phone']) : null;
$tag   = isset($_GET['tag']) ? trim($_GET['tag']) : null;

if (!$phone || !$tag || !in_array($tag, ['Free', 'Paid'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid phone or tag. Tag must be "free" or "paid".'
    ]);
    exit;
}

$stmt = $mysqli->prepare("SELECT user_id FROM sb_users_data WHERE slug='phone' AND value=? LIMIT 1");
$stmt->bind_param('s', $phone);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !isset($user['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not found.'
    ]);
    exit;
}

$user_id = $user['user_id'];

$stmt = $mysqli->prepare("UPDATE sb_conversations SET tags=? WHERE user_id=?");
$stmt->bind_param('si', $tag, $user_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode([
        'status' => 'success',
        'message' => "Tag updated to '$tag' for user_id $user_id."
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update tag.'
    ]);
}

$mysqli->close();
?>