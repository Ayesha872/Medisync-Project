<?php

header("Content-Type: application/json");

/* DATABASE CONNECTION */

$host = "127.0.0.1";
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


/* CHECK DATABASE CONNECTION */

if ($conn->connect_error) {

    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);

    exit();
}


/* ONLY ALLOW POST REQUEST */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);

    exit();
}


/* GET REGISTRATION DATA */

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$user_password = $_POST["password"] ?? "";


/* CHECK EMPTY FIELDS */

if ($name === "" || $email === "" || $user_password === "") {

    echo json_encode([
        "success" => false,
        "message" => "Please fill all fields."
    ]);

    exit();
}


/* CHECK IF EMAIL ALREADY EXISTS */

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


/* CREATE PATIENT ACCOUNT */

$role = "patient";

$sql = "INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, ?)";

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


/* INSERT DATA */

$stmt->bind_param(
    "ssss",
    $name,
    $email,
    $user_password,
    $role
);


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

        "message" => "Unable to create patient account: "
                     . $stmt->error

    ]);

}


/* CLOSE CONNECTION */

$stmt->close();

$check_stmt->close();

$conn->close();

?>