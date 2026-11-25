<?php

header("Content-Type: application/json");

function respond($success, $message) {
    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);
    exit();
}

// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, "Invalid request.");
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
    respond(false, "Database connection failed.");
}

$conn->set_charset("utf8mb4");

// Get patient ID sent by the form
$patient_id = filter_input(INPUT_POST, "patient_id", FILTER_VALIDATE_INT);

if (!$patient_id || $patient_id <= 0) {
    respond(false, "Patient information is missing. Please log in again.");
}

// Verify that this ID belongs to a patient account
$userSql = "SELECT id FROM users WHERE id = ? AND role = 'patient'";
$userStmt = $conn->prepare($userSql);

if (!$userStmt) {
    respond(false, "Unable to verify patient.");
}

$userStmt->bind_param("i", $patient_id);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows !== 1) {
    respond(false, "Invalid patient account.");
}

$userStmt->close();

// Read form fields
$test_name = trim($_POST["test_name"] ?? "");
$report_date = trim($_POST["report_date"] ?? "");
$lab_name = trim($_POST["lab_name"] ?? "");
$referring_doctor = trim($_POST["referring_doctor"] ?? "");

if ($test_name === "" || $report_date === "") {
    respond(false, "Please enter the test name and report date.");
}

// Validate date format and prevent future dates
$dateObject = DateTime::createFromFormat("Y-m-d", $report_date);

if (!$dateObject || $dateObject->format("Y-m-d") !== $report_date) {
    respond(false, "Invalid report date.");
}

if ($report_date > date("Y-m-d")) {
    respond(false, "Report date cannot be in the future.");
}

// Check uploaded file
if (!isset($_FILES["report_file"]) ||
    $_FILES["report_file"]["error"] !== UPLOAD_ERR_OK) {
    respond(false, "Please select a valid report file.");
}

$file = $_FILES["report_file"];

// Maximum file size: 5 MB
$maxFileSize = 5 * 1024 * 1024;

if ($file["size"] > $maxFileSize) {
    respond(false, "File size must not exceed 5 MB.");
}

// Allowed extensions
$allowedExtensions = ["pdf", "jpg", "jpeg", "png"];
$originalName = basename($file["name"]);
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions, true)) {
    respond(false, "Only PDF, JPG, JPEG and PNG files are allowed.");
}

// Validate actual file type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file["tmp_name"]);

$allowedMimeTypes = [
    "pdf"  => "application/pdf",
    "jpg"  => "image/jpeg",
    "jpeg" => "image/jpeg",
    "png"  => "image/png"
];

if (!isset($allowedMimeTypes[$extension]) ||
    $mimeType !== $allowedMimeTypes[$extension]) {
    respond(false, "The selected file type is not valid.");
}

// Create upload directory if it doesn't exist
$uploadDirectory = __DIR__ . "/uploads/lab_reports/";

if (!is_dir($uploadDirectory)) {
    if (!mkdir($uploadDirectory, 0755, true)) {
        respond(false, "Unable to create upload folder.");
    }
}

// Generate a unique file name
$newFileName = bin2hex(random_bytes(16)) . "." . $extension;
$destination = $uploadDirectory . $newFileName;

// Move uploaded file
if (!move_uploaded_file($file["tmp_name"], $destination)) {
    respond(false, "Unable to save the uploaded file.");
}

// Store relative path in database
$filePath = "uploads/lab_reports/" . $newFileName;

// Insert report record
$sql = "INSERT INTO lab_reports
        (patient_id, test_name, report_date, lab_name,
         referring_doctor, file_name, file_path, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Completed')";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    unlink($destination);
    respond(false, "Unable to prepare report record.");
}

$stmt->bind_param(
    "issssss",
    $patient_id,
    $test_name,
    $report_date,
    $lab_name,
    $referring_doctor,
    $originalName,
    $filePath
);

if (!$stmt->execute()) {
    unlink($destination);
    respond(false, "Unable to save report details.");
}

$stmt->close();
$conn->close();

respond(true, "Lab report uploaded successfully.");

?>