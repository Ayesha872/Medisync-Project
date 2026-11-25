
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
    exit;
}

// Receive data
$doctorID = $_POST["doctor_id"] ?? "";
$status = $_POST["status"] ?? "";

// Validate input
if (
    !ctype_digit((string)$doctorID) ||
    !in_array($status, ["approved", "rejected"], true)
) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid doctor ID or status."
    ]);
    $conn->close();
    exit;
}

// Check whether doctor exists
$check = $conn->prepare(
    "SELECT doctor_id FROM doctors WHERE doctor_id = ?"
);

$check->bind_param("i", $doctorID);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Doctor not found."
    ]);

    $check->close();
    $conn->close();
    exit;
}

$check->close();

// Update approval status
$stmt = $conn->prepare(
    "UPDATE doctors SET approval_status = ? WHERE doctor_id = ?"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Unable to prepare database query."
    ]);
    $conn->close();
    exit;
}

$stmt->bind_param("si", $status, $doctorID);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Doctor status updated successfully.",
        "doctor_id" => $doctorID,
        "approval_status" => $status
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Unable to update doctor status."
    ]);

}

$stmt->close();
$conn->close();

?>
