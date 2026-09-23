<?php

header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "123456789", "medisync", 3307);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

if (!isset($_POST["appointment_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Appointment ID is missing."
    ]);
    exit;
}

$appointment_id = intval($_POST["appointment_id"]);

$sql = "DELETE FROM appointments WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "SQL prepare failed: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("i", $appointment_id);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Appointment cancelled successfully."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Unable to cancel appointment: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();

?>