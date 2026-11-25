
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

// Receive form data
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$specialization = trim($_POST["specialization"] ?? "");
$qualification = trim($_POST["qualification"] ?? "");
$experience = $_POST["experience_years"] ?? "";
$fee = $_POST["consultation_fee"] ?? "";
$days = trim($_POST["available_days"] ?? "");
$time = trim($_POST["available_time"] ?? "");

// Validate required fields
if (
    $name === "" || $email === "" || $password === "" ||
    $specialization === "" || $qualification === "" ||
    $experience === "" || $fee === "" ||
    $days === "" || $time === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "Please fill all required fields."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email address."
    ]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode([
        "success" => false,
        "message" => "Password must contain at least 6 characters."
    ]);
    exit;
}

if (!is_numeric($experience) || $experience < 0 ||
    !is_numeric($fee) || $fee < 0) {
    echo json_encode([
        "success" => false,
        "message" => "Enter valid experience and consultation fee."
    ]);
    exit;
}

// Check duplicate email
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$existing = $check->get_result();

if ($existing->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "This email is already registered."
    ]);
    exit;
}

$check->close();

// Generate password hash
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Start transaction
$conn->begin_transaction();

try {

    // Insert doctor into users table
    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, password, role)
         VALUES (?, ?, ?, 'doctor')"
    );

    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    $stmt->execute();

    $userID = $conn->insert_id;
    $stmt->close();

    // Insert professional details
$stmt = $conn->prepare(
    "INSERT INTO doctors
    (user_id, specialization, qualification, experience_years,
     consultation_fee, available_days, available_time, status, approval_status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'active', 'pending')"
);
    $experience = (int)$experience;
    $fee = (float)$fee;

    $stmt->bind_param(
        "issidss",
        $userID,
        $specialization,
        $qualification,
        $experience,
        $fee,
        $days,
        $time
    );

    $stmt->execute();

    $doctorID = $conn->insert_id;

    $stmt->close();

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Doctor registered successfully. Approval is pending.",
        "doctor_id" => "DOC" . $doctorID
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "success" => false,
        "message" => "Unable to register doctor. Please check your database tables."
    ]);

}

$conn->close();

?>
