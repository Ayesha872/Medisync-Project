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
        "message" => "Database connection failed."
    ]);
    exit();
}

$patient_id = $_GET["patient_id"] ?? "";

if (empty($patient_id)) {
    echo json_encode([
        "success" => false,
        "message" => "Patient ID not found."
    ]);
    exit();
}

$sql = "SELECT
            id,
            doctor_name,
            specialization,
            appointment_date,
            appointment_time,
            reason,
            status
        FROM appointments
        WHERE patient_id = ?
        ORDER BY appointment_date ASC, appointment_time ASC";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $patient_id);

$stmt->execute();

$result = $stmt->get_result();

$appointments = [];

while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode([
    "success" => true,
    "appointments" => $appointments
]);

$stmt->close();
$conn->close();

?>