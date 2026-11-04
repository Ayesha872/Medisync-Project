<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");

/* DATABASE CONNECTION */
$host = "localhost";
$username = "root";
$password = "12345"; // Agar aapke XAMPP me password nahi hai toh ise khali chhorein
$database = "medisync";
$port = 3307; // Apne XAMPP ka sahi port yahan rakhein

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);
    exit();
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$user_password = $_POST["password"] ?? "";

if ($name === "" || $email === "" || $user_password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Please fill all fields."
    ]);
    exit();
}

$check_sql = "SELECT id FROM users WHERE email = ?";
$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database query error."
    ]);
    exit();
}

$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "An account with this email already exists."
    ]);
    $check_stmt->close();
    $conn->close();
    exit();
}

$role = "patient";
$sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database query error."
    ]);
    $check_stmt->close();
    $conn->close();
    exit();
}

$stmt->bind_param("ssss", $name, $email, $user_password, $role);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Patient account created successfully.",
        "id" => $stmt->insert_id,
        "name" => $name,
        "email" => $email,
        "role" => "patient"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Unable to create patient account: " . $stmt->error
    ]);
}

$stmt->close();
$check_stmt->close();
$conn->close();
?>