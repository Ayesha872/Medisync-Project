<?php

header("Content-Type: application/json");

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = "123456789";
$database = "medisync";
$port = 3307;

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database,
    $port
);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit();
}

$conn->set_charset("utf8mb4");

// Get patient ID from request
$patient_id = filter_input(INPUT_POST, "patient_id", FILTER_VALIDATE_INT);

if (!$patient_id || $patient_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Patient information is missing."
    ]);
    exit();
}

// Verify patient account
$userSql = "SELECT id FROM users WHERE id = ? AND role = 'patient'";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $patient_id);
$userStmt->execute();

$userResult = $userStmt->get_result();

if ($userResult->num_rows !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid patient account."
    ]);
    exit();
}

$userStmt->close();

// Fetch only this patient's reports
$sql = "SELECT
            id,
            test_name,
            report_date,
            lab_name,
            referring_doctor,
            file_name,
            file_path,
            status
        FROM lab_reports
        WHERE patient_id = ?
        ORDER BY report_date DESC, id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();

$result = $stmt->get_result();

$reports = [];

while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

echo json_encode([
    "success" => true,
    "reports" => $reports
]);

$stmt->close();
$conn->close();

?>