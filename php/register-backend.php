<?php
session_start();
require_once 'rdb.php';

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate name
function validateName($name) {
    return preg_match('/^[a-zA-Z\s]{2,100}$/', $name);
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = sanitizeInput($_POST['password']);
    $errors = [];

    // Validate name
    if (empty($name)) {
        $errors[] = "Name is required";
    } elseif (!validateName($name)) {
        $errors[] = "Name must be between 2-100 characters and can only contain letters and spaces";
    }

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validateEmail($email)) {
        $errors[] = "Invalid email format";
    }

    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }

    // If no validation errors, proceed with registration
    if (empty($errors)) {
        $conn = connect_db();

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Email already exists";
        } else {
            // Store plaintext password (simple setup)
            // No need to set role as it defaults to 'customer' in the database
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $password);

            if ($stmt->execute()) {
                $_SESSION['registration_success'] = true;
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }

        $stmt->close();
        $conn->close();
    }

    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        header("Location: register.php");
        exit();
    }
}
?>