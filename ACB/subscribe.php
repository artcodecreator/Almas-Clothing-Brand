<?php
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $response = ['status' => 'error', 'message' => 'Something went wrong.'];

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if already subscribed
        $check = $conn->prepare("SELECT id FROM subscribers WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $response = ['status' => 'warning', 'message' => 'You are already subscribed!'];
        } else {
            $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Successfully subscribed!'];
            } else {
                $response = ['status' => 'error', 'message' => 'Database error.'];
            }
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Invalid email address.'];
    }

    // Return JSON if AJAX, otherwise redirect (for now, let's assume simple redirect or JS alert)
    // But since footer is included everywhere, better to use JS fetch or just redirect back with parameter.
    // For simplicity, let's echo a script to alert and go back.
    echo "<script>alert('" . $response['message'] . "'); window.history.back();</script>";
}
?>
