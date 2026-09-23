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

$sql = "SELECT appointment_date
        FROM appointments
        WHERE patient_id = ?
        AND appointment_date < CURDATE()
        ORDER BY appointment_date DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $patient_id);

$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    echo json_encode([
        "success" => true,
        "visit_date" => $row["appointment_date"]
    ]);

} else {

    echo json_encode([
        "success" => true,
        "visit_date" => null
    ]);

}

$stmt->close();
$conn->close();

?>