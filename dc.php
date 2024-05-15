<?php
$host = 'localhost';
$dbname = 'dlc';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Check if data is set for QR scanner
if (isset($_POST['name']) && isset($_POST['subject']) && isset($_POST['timein']) && isset($_POST['timeout']) && isset($_POST['room'])) {
    // Prepare SQL statement for QR scanner
    $stmt_qr = $pdo->prepare("INSERT INTO qrscanner (name, subject, timein, timeout, room) 
                            VALUES (:name, :subject, :timein, :timeout, :room)");

    // Bind parameters for QR scanner
    $name = $_POST['name'];
    $subject = $_POST['subject'];
    $timein = $_POST['timein'];
    $timeout = $_POST['timeout'];
    $room = $_POST['room'];

    $stmt_qr->bindParam(':name', $name);
    $stmt_qr->bindParam(':subject', $subject);
    $stmt_qr->bindParam(':timein', $timein);
    $stmt_qr->bindParam(':timeout', $timeout);
    $stmt_qr->bindParam(':room', $room);

    // Execute the prepared statement for QR scanner
    $stmt_qr->execute();
    echo "Data inserted into qrscanner successfully<br>";
}

// Check if data is set for student scan
if (isset($_POST['student_id']) && isset($_POST['name']) && isset($_POST['subject']) && isset($_POST['timein']) && isset($_POST['timeout']) && isset($_POST['room']) && isset($_POST['remarks'])) {
    // Prepare SQL statement for student scan
    $stmt_student = $pdo->prepare("INSERT INTO studentscan (studentid, name, subject, timein, timeout, room, remarks) 
                            VALUES (:studentid, :name, :subject, :timein, :timeout, :room, :remarks)");

    // Bind parameters for student scan
    $studentid = $_POST['student_id'];
    $name = $_POST['name'];
    $subject = $_POST['subject'];
    $timein = $_POST['timein'];
    $timeout = $_POST['timeout'];
    $room = $_POST['room'];
    $remarks = $_POST['remarks'];

    $stmt_student->bindParam(':studentid', $studentid);
    $stmt_student->bindParam(':name', $name);
    $stmt_student->bindParam(':subject', $subject);
    $stmt_student->bindParam(':timein', $timein);
    $stmt_student->bindParam(':timeout', $timeout);
    $stmt_student->bindParam(':room', $room);
    $stmt_student->bindParam(':remarks', $remarks);

    // Execute the prepared statement for student scan
    $stmt_student->execute();
    echo "Data inserted into studentscan successfully";
}
?>
