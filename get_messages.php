<?php

header("Content-Type: application/json");

$conn = new mysqli(
    "localhost",
    "root",
    "12345",
    "medisync",
    3307
);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$user1 = isset($_GET["user1"])
    ? intval($_GET["user1"])
    : 0;

$user2 = isset($_GET["user2"])
    ? intval($_GET["user2"])
    : 0;

if ($user1 <= 0 || $user2 <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid user IDs"
    ]);
    exit;
}

$sql = "
    SELECT
        id,
        sender_id,
        receiver_id,
        sender_role,
        message,
        sent_at,
        is_read
    FROM messages
    WHERE
        (sender_id = ? AND receiver_id = ?)
        OR
        (sender_id = ? AND receiver_id = ?)
    ORDER BY sent_at ASC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare query"
    ]);
    exit;
}

$stmt->bind_param(
    "iiii",
    $user1,
    $user2,
    $user2,
    $user1
);

$stmt->execute();

$result = $stmt->get_result();

$messages = [];

while ($row = $result->fetch_assoc()) {

    $messages[] = [
        "id" => $row["id"],
        "sender_id" => $row["sender_id"],
        "receiver_id" => $row["receiver_id"],
        "sender_role" => $row["sender_role"],
        "message" => $row["message"],
        "sent_at" => $row["sent_at"],
        "is_read" => $row["is_read"]
    ];
}

echo json_encode([
    "success" => true,
    "messages" => $messages
]);

$stmt->close();
$conn->close();

?>