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
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

$patient_id = isset($_GET["patient_id"])
    ? intval($_GET["patient_id"])
    : 0;

if ($patient_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid patient ID"
    ]);
    exit;
}

$sql = "
SELECT
    p.id AS prescription_id,
    p.patient_id,
    p.doctor_name,
    p.diagnosis,
    p.prescription_date,
    p.status AS prescription_status,
    p.notes,

    pm.id AS medicine_id,
    pm.medicine_name,
    pm.dosage,
    pm.frequency,
    pm.duration,
    pm.start_date,
    pm.instructions,
    pm.status AS medicine_status

FROM prescriptions p

INNER JOIN prescription_medicines pm
    ON p.id = pm.prescription_id

WHERE p.patient_id = ?

ORDER BY p.prescription_date DESC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $patient_id);

$stmt->execute();

$result = $stmt->get_result();

$prescriptions = [];

while ($row = $result->fetch_assoc()) {
    $prescriptions[] = $row;
}

echo json_encode([
    "success" => true,
    "prescriptions" => $prescriptions
]);

$stmt->close();
$conn->close();

?>