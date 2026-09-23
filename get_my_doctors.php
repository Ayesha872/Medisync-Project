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


/*
    Get doctors who have appointments
    with this patient.

    Cancelled appointments are ignored.
*/

$sql = "
    SELECT DISTINCT
        doctor_name,
        specialization
    FROM appointments
    WHERE patient_id = ?
      AND (
          status IS NULL
          OR LOWER(status) <> 'cancelled'
      )
    ORDER BY doctor_name
";


$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $patient_id);

$stmt->execute();

$result = $stmt->get_result();

$doctors = [];

while ($row = $result->fetch_assoc()) {

    $doctors[] = [
        "doctor_name" => $row["doctor_name"],
        "specialization" => $row["specialization"]
    ];

}


echo json_encode([
    "success" => true,
    "doctors" => $doctors
]);


$stmt->close();
$conn->close();

?>