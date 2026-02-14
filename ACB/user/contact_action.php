<?php
session_start();
include("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if ($name && $email && $message) {
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
        $stmt = $conn->prepare("INSERT INTO messages (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Your message has been sent successfully!";
        } else {
            $_SESSION['error'] = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}

header("Location: contact.php");
exit();
?>
