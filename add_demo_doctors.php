
<?php

header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "123456789", "medisync", 3307);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed."]));
}

$conn->set_charset("utf8mb4");

$doctors = [

    // CARDIOLOGY (Rahul already exists)
    ["Arjun Mehta","arjun.mehta@medisync.com","Arjun@123","Cardiology","MBBS, MD Cardiology",10,800,"Tuesday, Thursday, Sunday","10:00 AM - 1:00 PM"],
    ["Priya Verma","priya.verma@medisync.com","Priya@123","Cardiology","MBBS, MD Cardiology",8,750,"Monday, Wednesday, Friday","10:00 AM - 1:00 PM"],
    ["Neha Singh","neha.singh@medisync.com","Neha@123","Cardiology","MBBS, DM Cardiology",6,700,"Tuesday, Thursday, Sunday","10:00 AM - 1:00 PM"],

    // GENERAL PHYSICIAN
    ["Aman Khan","aman.khan@medisync.com","Aman@123","General Physician","MBBS, MD Medicine",9,500,"Monday, Wednesday, Friday","9:00 AM - 12:00 PM"],
    ["Rohit Malhotra","rohit.malhotra@medisync.com","Rohit@123","General Physician","MBBS, MD Medicine",7,450,"Tuesday, Thursday, Sunday","9:00 AM - 12:00 PM"],
    ["Sana Ahmed","sana.ahmed@medisync.com","Sana@123","General Physician","MBBS, MD Medicine",8,500,"Monday, Wednesday, Friday","9:00 AM - 12:00 PM"],
    ["Kavya Sharma","kavya.sharma@medisync.com","Kavya@123","General Physician","MBBS, MD Medicine",5,400,"Tuesday, Thursday, Sunday","9:00 AM - 12:00 PM"],

    // DERMATOLOGY
    ["Karan Kapoor","karan.kapoor@medisync.com","Karan@123","Dermatology","MBBS, MD Dermatology",11,700,"Monday, Wednesday, Friday","11:00 AM - 2:00 PM"],
    ["Vikram Sethi","vikram.sethi@medisync.com","Vikram@123","Dermatology","MBBS, MD Dermatology",6,600,"Tuesday, Thursday, Sunday","11:00 AM - 2:00 PM"],
    ["Ananya Gupta","ananya.gupta@medisync.com","Ananya@123","Dermatology","MBBS, MD Dermatology",9,650,"Monday, Wednesday, Friday","11:00 AM - 2:00 PM"],
    ["Meera Joshi","meera.joshi@medisync.com","Meera@123","Dermatology","MBBS, DDVL",5,550,"Tuesday, Thursday, Sunday","11:00 AM - 2:00 PM"],

    // NEUROLOGY
    ["Aditya Rao","aditya.rao@medisync.com","Aditya@123","Neurology","MBBS, DM Neurology",12,1000,"Monday, Wednesday, Friday","3:00 PM - 6:00 PM"],
    ["Varun Das","varun.das@medisync.com","Varun@123","Neurology","MBBS, DM Neurology",8,900,"Tuesday, Thursday, Sunday","3:00 PM - 6:00 PM"],
    ["Isha Kapoor","isha.kapoor@medisync.com","Isha@123","Neurology","MBBS, DM Neurology",7,850,"Monday, Wednesday, Friday","3:00 PM - 6:00 PM"],
    ["Riya Malhotra","riya.malhotra@medisync.com","Riya@123","Neurology","MBBS, DM Neurology",6,800,"Tuesday, Thursday, Sunday","3:00 PM - 6:00 PM"],

    // ORTHOPEDICS
    ["Kabir Verma","kabir.verma@medisync.com","Kabir@123","Orthopedics","MBBS, MS Orthopedics",13,800,"Monday, Wednesday, Friday","10:00 AM - 1:00 PM"],
    ["Nikhil Jain","nikhil.jain@medisync.com","Nikhil@123","Orthopedics","MBBS, MS Orthopedics",9,750,"Tuesday, Thursday, Sunday","10:00 AM - 1:00 PM"],
    ["Pooja Nair","pooja.nair@medisync.com","Pooja@123","Orthopedics","MBBS, MS Orthopedics",8,700,"Monday, Wednesday, Friday","10:00 AM - 1:00 PM"],
    ["Simran Kaur","simran.kaur@medisync.com","Simran@123","Orthopedics","MBBS, MS Orthopedics",6,650,"Tuesday, Thursday, Sunday","10:00 AM - 1:00 PM"],

    // PEDIATRICS
    ["Dev Malhotra","dev.malhotra@medisync.com","Dev@123","Pediatrics","MBBS, MD Pediatrics",10,700,"Monday, Wednesday, Friday","9:00 AM - 12:00 PM"],
    ["Rahul Bansal","rahul.bansal@medisync.com","Rahul@123","Pediatrics","MBBS, MD Pediatrics",7,650,"Tuesday, Thursday, Sunday","9:00 AM - 12:00 PM"],
    ["Aditi Sharma","aditi.sharma@medisync.com","Aditi@123","Pediatrics","MBBS, MD Pediatrics",8,600,"Monday, Wednesday, Friday","9:00 AM - 12:00 PM"],
    ["Nisha Gupta","nisha.gupta@medisync.com","Nisha@123","Pediatrics","MBBS, MD Pediatrics",5,550,"Tuesday, Thursday, Sunday","9:00 AM - 12:00 PM"]

];

$added = 0;
$skipped = 0;
$failed = [];

$conn->begin_transaction();

try {

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");

    $userInsert = $conn->prepare(
        "INSERT INTO users (name, email, password, role)
         VALUES (?, ?, ?, 'doctor')"
    );

    $doctorInsert = $conn->prepare(
        "INSERT INTO doctors
        (user_id, specialization, qualification, experience_years,
         consultation_fee, available_days, available_time, status, approval_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'active', 'pending')"
    );

    foreach ($doctors as $d) {

        [$name, $email, $plainPassword, $specialization,
         $qualification, $experience, $fee, $days, $time] = $d;

        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $skipped++;
            continue;
        }

        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $userInsert->bind_param("sss", $name, $email, $hashedPassword);
        $userInsert->execute();

        $userID = $conn->insert_id;

        $doctorInsert->bind_param(
            "issidss",
            $userID,
            $specialization,
            $qualification,
            $experience,
            $fee,
            $days,
            $time
        );

        $doctorInsert->execute();

        $added++;
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Demo doctors processed successfully.",
        "added" => $added,
        "skipped" => $skipped,
        "failed" => $failed
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "success" => false,
        "message" => "Database operation failed. No new records were committed."
    ], JSON_PRETTY_PRINT);

}

$conn->close();

?>
