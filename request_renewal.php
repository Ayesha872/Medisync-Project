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

$medicine_id = intval($_POST["medicine_id"] ?? 0);
$patient_id = intval($_POST["patient_id"] ?? 0);
$medicine_name = $_POST["medicine_name"] ?? "";
$doctor_name = $_POST["doctor_name"] ?? "";

if (
    $medicine_id <= 0 ||
    $patient_id <= 0 ||
    empty($medicine_name)
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid renewal request"
    ]);

    exit;
}

$sql = "
INSERT INTO renewal_requests
(medicine_id, patient_id, medicine_name, doctor_name, status, requested_at)
VALUES (?, ?, ?, ?, 'Pending', NOW())
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iiss",
    $medicine_id,
    $patient_id,
    $medicine_name,
    $doctor_name
);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Renewal request submitted successfully"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Unable to submit renewal request"
    ]);

}

$stmt->close();
$conn->close();

?>