
<?php

header("Content-Type: application/json");

$host = "localhost";
$username = "root";
$password = "123456789";
$database = "medisync";
$port = 3307;

$hospitalEmail = "khanayeshamehreen@gmail.com";

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);
    exit;
}

// Accept either email or doctor ID from the login form
$identifier = trim(
    $_POST["email"] ??
    $_POST["login_id"] ??
    $_POST["doctor_id"] ??
    ""
);

$user_password = $_POST["password"] ?? "";

if ($identifier === "" || $user_password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Please enter your login ID and password."
    ]);
    exit;
}

$user = null;
$doctorID = null;
$otpEmail = "";
$stmt = null;


// ========================================
// DOCTOR LOGIN USING DOCTOR ID
// ========================================

if (preg_match("/^DOC(\d+)$/i", $identifier, $matches)) {

    $doctorID = (int)$matches[1];

    $sql = "SELECT
                u.id,
                u.name,
                u.email,
                u.password,
                u.role,
                d.doctor_id,
                d.approval_status,
                d.status
            FROM doctors d
            INNER JOIN users u ON d.user_id = u.id
            WHERE d.doctor_id = ?
            AND u.role = 'doctor'";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Database query failed."
        ]);
        exit;
    }

    $stmt->bind_param("i", $doctorID);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid Doctor ID. Please check your Doctor ID."
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $user = $result->fetch_assoc();

    if ($user["approval_status"] === "pending") {
        echo json_encode([
            "success" => false,
            "message" => "Your account is waiting for Admin approval."
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    if ($user["approval_status"] === "rejected") {
        echo json_encode([
            "success" => false,
            "message" => "Your doctor account has been rejected."
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    if ($user["approval_status"] !== "approved") {
        echo json_encode([
            "success" => false,
            "message" => "Your account is not approved."
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    if ($user["status"] !== "active") {
        echo json_encode([
            "success" => false,
            "message" => "Your doctor account is inactive."
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $otpEmail = $hospitalEmail;

}


// ========================================
// PATIENT AND ADMIN LOGIN USING EMAIL
// ========================================

else {

    $sql = "SELECT id, name, email, password, role
            FROM users
            WHERE email = ?
            AND role IN ('patient', 'admin')";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Database query failed."
        ]);
        exit;
    }

    $stmt->bind_param("s", $identifier);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        echo json_encode([
            "success" => false,
            "message" => "User not found. Please check your email."
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $user = $result->fetch_assoc();

    $otpEmail = $user["email"];

}


// ========================================
// PASSWORD CHECK
// ========================================

if ($user["role"] === "doctor") {

    $passwordValid = password_verify(
        $user_password,
        $user["password"]
    );

} else {

    $passwordValid = (
        $user_password === $user["password"]
    );

}

if (!$passwordValid) {

    echo json_encode([
        "success" => false,
        "message" => "Incorrect password."
    ]);

    $stmt->close();
    $conn->close();
    exit;
}


// ========================================
// LOGIN SUCCESS
// ========================================

echo json_encode([
    "success" => true,
    "message" => "Login credentials verified.",
    "id" => $user["id"],
    "name" => $user["name"],
    "email" => $user["email"],
    "role" => $user["role"],
    "otp_email" => $otpEmail,
    "doctor_id" => $doctorID
]);

$stmt->close();
$conn->close();

?>
