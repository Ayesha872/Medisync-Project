<?php

header("Content-Type: application/json");

$host = "localhost";
$username = "root";
$password = "12345";
$database = "medisync";
$port = 3306;

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

$email = trim($_POST["email"] ?? "");
$user_password = $_POST["password"] ?? "";

if ($email === "" || $user_password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Please enter email and password."
    ]);
    exit();
}

$sql = "SELECT id, name, email, password, role
        FROM users
        WHERE email = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database query error."
    ]);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "User not found."
    ]);
    exit();
}

$user = $result->fetch_assoc();

if ($user_password !== $user["password"]) {
    echo json_encode([
        "success" => false,
        "message" => "Incorrect password."
    ]);
    exit();
}

echo json_encode([
    "success" => true,
    "message" => "Login credentials verified.",
    "id" => $user["id"],
    "name" => $user["name"],
    "email" => $user["email"],
    "role" => $user["role"]
]);

$stmt->close();
$conn->close();

?>