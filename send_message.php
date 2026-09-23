<?php

header("Content-Type: application/json");

$conn = new mysqli(
    "localhost",
    "root",
    "123456789",
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


/* ================================
   GET FORM DATA
================================ */

$sender_id = isset($_POST["sender_id"])
    ? intval($_POST["sender_id"])
    : 0;

$receiver_id = isset($_POST["receiver_id"])
    ? intval($_POST["receiver_id"])
    : 0;

$doctor_name = isset($_POST["doctor_name"])
    ? trim($_POST["doctor_name"])
    : "";

$sender_role = isset($_POST["sender_role"])
    ? trim($_POST["sender_role"])
    : "";

$subject = isset($_POST["subject"])
    ? trim($_POST["subject"])
    : "";

$message = isset($_POST["message"])
    ? trim($_POST["message"])
    : "";


/* ================================
   VALIDATION
================================ */

if (
    $sender_id <= 0 ||
    $doctor_name === "" ||
    $sender_role === "" ||
    $subject === "" ||
    $message === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "Please fill all required information"
    ]);
    exit;
}


/*
   receiver_id is kept in the table for compatibility.
   Since the displayed doctors do not all have separate
   user accounts, we can use 0 for the receiver.
*/

$receiver_id = 0;


/* ================================
   INSERT MESSAGE
================================ */

$sql = "
    INSERT INTO messages
    (
        sender_id,
        receiver_id,
        doctor_name,
        sender_role,
        subject,
        message
    )
    VALUES (?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare database query"
    ]);
    exit;
}


$stmt->bind_param(
    "iissss",
    $sender_id,
    $receiver_id,
    $doctor_name,
    $sender_role,
    $subject,
    $message
);


/* ================================
   SAVE MESSAGE
================================ */

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Message sent successfully"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Unable to save message"
    ]);
}


$stmt->close();
$conn->close();

?>