<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$jsonFile = "users.json";

// Create file if missing
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, "[]");
}

// Initialize variables
$name = $email = $password = $confirmPassword = "";
$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Sanitize inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validation
    if (empty($name)) $errors[] = "Name required";
    if (empty($email)) $errors[] = "Email required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
    if (empty($password)) $errors[] = "Password required";
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match";

    if (empty($errors)) {

        $users = json_decode(file_get_contents($jsonFile), true);
        if (!is_array($users)) $users = [];

        $users[] = [
            "name" => $name,
            "email" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT)
        ];

        if (file_put_contents($jsonFile, json_encode($users, JSON_PRETTY_PRINT))) {
            $success = "User saved successfully!";
        } else {
            $errors[] = "Could not write to JSON file";
        }
    }
}

// Display feedback
if (!empty($errors)) {
    foreach ($errors as $err) echo "<p style='color:red;'>$err</p>";
}
if ($success) echo "<p style='color:green;'>$success</p>";
?>
