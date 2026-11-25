
<?php

header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "123456789", "medisync", 3307);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

$sql = "
    SELECT
        d.doctor_id,
        u.name,
        u.email,
        d.specialization,
        d.qualification,
        d.experience_years,
        d.consultation_fee,
        d.available_days,
        d.available_time,
        d.approval_status
    FROM doctors d
    INNER JOIN users u ON d.user_id = u.id
    ORDER BY d.doctor_id DESC
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to retrieve doctors."
    ]);
    exit;
}

$doctors = [];

while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

echo json_encode([
    "success" => true,
    "doctors" => $doctors
]);

$conn->close();

?>
