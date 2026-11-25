<?php

header("Content-Type: application/json");

date_default_timezone_set("Asia/Kolkata");

function sendResponse($success, $message) {
    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);
    exit();
}

// =====================================================
// DATABASE CONNECTION
// =====================================================

$conn = new mysqli(
    "localhost",
    "root",
    "123456789",
    "medisync",
    3307
);

if ($conn->connect_error) {
    sendResponse(false, "Database connection failed.");
}

$conn->set_charset("utf8mb4");

// =====================================================
// RECEIVE FORM DATA
// =====================================================

$patient_id = $_POST["patient_id"] ?? "";
$doctor_id = $_POST["doctor_id"] ?? "";
$doctor_name = trim($_POST["doctor_name"] ?? "");
$specialization = trim($_POST["specialization"] ?? "");
$appointment_date = $_POST["appointment_date"] ?? "";
$appointment_time = $_POST["appointment_time"] ?? "";
$reason = trim($_POST["reason"] ?? "");

// =====================================================
// VALIDATE REQUIRED FIELDS
// =====================================================

if (
    empty($patient_id) ||
    empty($doctor_id) ||
    empty($doctor_name) ||
    empty($specialization) ||
    empty($appointment_date) ||
    empty($appointment_time)
) {
    sendResponse(false, "Please fill all required fields.");
}

// =====================================================
// VALIDATE APPOINTMENT DATE FORMAT
// =====================================================

$dateObject = DateTime::createFromFormat(
    "!Y-m-d",
    $appointment_date
);

if (
    !$dateObject ||
    $dateObject->format("Y-m-d") !== $appointment_date
) {
    sendResponse(false, "Invalid appointment date.");
}

// Prevent past dates.

$today = new DateTime("today");

if ($dateObject < $today) {
    sendResponse(false, "You cannot book an appointment for a past date.");
}

// =====================================================
// GET DOCTOR AVAILABILITY FROM DATABASE
// =====================================================

// This uses the doctors table and its available_days
// column, which supplies the doctor information
// used by get_my_doctors.php.

$doctorQuery = "
    SELECT doctor_id, specialization, available_days
    FROM doctors
    WHERE doctor_id = ?
    LIMIT 1
";

$doctorStmt = $conn->prepare($doctorQuery);

if (!$doctorStmt) {
    sendResponse(false, "Unable to check doctor availability.");
}

$doctorStmt->bind_param("s", $doctor_id);

$doctorStmt->execute();

$doctorResult = $doctorStmt->get_result();

if ($doctorResult->num_rows === 0) {

    $doctorStmt->close();

    sendResponse(
        false,
        "Selected doctor was not found. Please select another doctor."
    );
}

$doctor = $doctorResult->fetch_assoc();

$doctorStmt->close();

// =====================================================
// VERIFY SPECIALIZATION
// =====================================================

if (
    strcasecmp(
        trim($doctor["specialization"]),
        $specialization
    ) !== 0
) {
    sendResponse(
        false,
        "Doctor specialization does not match. Please select the doctor again."
    );
}

// =====================================================
// NORMALIZE AVAILABLE DAYS
// Supports:
// Monday, Wednesday, Friday
// Mon, Wed, Fri
// Mon/Wed/Fri
// JSON array of weekdays
// =====================================================

$availableDays = $doctor["available_days"] ?? "";

$decodedDays = json_decode($availableDays, true);

if (is_array($decodedDays)) {

    $dayValues = $decodedDays;

} else {

    $dayValues = preg_split(
        '/[,\/|]+/',
        $availableDays
    );
}

$dayMap = [
    "mon" => 1,
    "monday" => 1,

    "tue" => 2,
    "tues" => 2,
    "tuesday" => 2,

    "wed" => 3,
    "wednesday" => 3,

    "thu" => 4,
    "thur" => 4,
    "thurs" => 4,
    "thursday" => 4,

    "fri" => 5,
    "friday" => 5,

    "sat" => 6,
    "saturday" => 6,

    "sun" => 7,
    "sunday" => 7
];

$allowedDays = [];

foreach ($dayValues as $day) {

    $normalizedDay = strtolower(
        preg_replace(
            '/[^a-z]/',
            '',
            trim((string)$day)
        )
    );

    if (isset($dayMap[$normalizedDay])) {

        $allowedDays[] = $dayMap[$normalizedDay];

    }
}

$allowedDays = array_unique($allowedDays);

// =====================================================
// CHECK SELECTED DATE AGAINST DOCTOR AVAILABILITY
// =====================================================

// PHP DateTime format N:
// Monday = 1
// Tuesday = 2
// Wednesday = 3
// Thursday = 4
// Friday = 5
// Saturday = 6
// Sunday = 7

$selectedWeekday = (int)$dateObject->format("N");

if (!in_array($selectedWeekday, $allowedDays, true)) {

    sendResponse(
        false,
        "This doctor is not available on the selected day. Please choose one of the highlighted available dates."
    );
}

// =====================================================
// INSERT APPOINTMENT
// =====================================================

// Keeping your existing appointments table columns
// unchanged to avoid breaking existing appointment
// history and cancellation functionality.

$sql = "
    INSERT INTO appointments
    (
        patient_id,
        doctor_name,
        specialization,
        appointment_date,
        appointment_time,
        reason
    )
    VALUES (?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    sendResponse(false, "Unable to prepare appointment booking.");
}

$stmt->bind_param(
    "isssss",
    $patient_id,
    $doctor_name,
    $specialization,
    $appointment_date,
    $appointment_time,
    $reason
);

// =====================================================
// EXECUTE BOOKING
// =====================================================

if ($stmt->execute()) {

    $stmt->close();
    $conn->close();

    sendResponse(
        true,
        "Appointment booked successfully."
    );

} else {

    $stmt->close();
    $conn->close();

    sendResponse(
        false,
        "Could not book appointment. Please try again."
    );
}

?>