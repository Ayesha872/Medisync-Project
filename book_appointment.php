<?php

header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "123456789", "medisync", 3307);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit();
}

$patient_id = $_POST["patient_id"] ?? "";
$doctor_name = $_POST["doctor_name"] ?? "";
$specialization = $_POST["specialization"] ?? "";
$appointment_date = $_POST["appointment_date"] ?? "";
$appointment_time = $_POST["appointment_time"] ?? "";
$reason = $_POST["reason"] ?? "";

if (
    empty($patient_id) ||
    empty($doctor_name) ||
    empty($specialization) ||
    empty($appointment_date) ||
    empty($appointment_time)
) {
    echo json_encode([
        "success" => false,
        "message" => "Please fill all required fields."
    ]);
    exit();
}

$sql = "INSERT INTO appointments
        (patient_id, doctor_name, specialization, appointment_date, appointment_time, reason)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "isssss",
    $patient_id,
    $doctor_name,
    $specialization,
    $appointment_date,
    $appointment_time,
    $reason
);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Appointment booked successfully."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Could not book appointment."
    ]);
}

$stmt->close();
$conn->close();

?>