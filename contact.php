<?php
header('Content-Type: application/json');

// Database Configuration - Update these with your deployed DB credentials
$host = 'localhost';
$db_name = 'shree_bajrang_furniture';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Fails securely without dropping DB details 
    echo json_encode(["status" => "error", "message" => "Database connection failed. Please check credentials."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(strip_tags($_POST['name'] ?? ''));
    $phone = htmlspecialchars(strip_tags($_POST['phone'] ?? ''));
    $product = htmlspecialchars(strip_tags($_POST['product'] ?? ''));
    $msg = htmlspecialchars(strip_tags($_POST['message'] ?? ''));

    // Validate Required Fields
    if (empty($name) || empty($phone)) {
        echo json_encode(["status" => "error", "message" => "Name and Phone number are required."]);
        exit;
    }

    try {
        // Insert into database securely to prevent SQL Injection
        // Assuming table matches HTML form: name, phone, product, message
        $query = "INSERT INTO inquiries (name, phone, message) VALUES (:name, :phone, :message)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        // Concatenating product into message for simplicity based on the previous DB schema
        $fullMessage = "Product Interest: $product\n\n$msg";
        $stmt->bindParam(':message', $fullMessage);
        
        if ($stmt->execute()) {
            
            // Send Email Notification
            $to = "inquiries@bajrangsteels.in"; // Update this with your actual email
            $subject = "New Inquiry from " . $name . " (Shree Bajrang Furniture)";
            $body = "You have received a new inquiry.\n\nName: $name\nPhone: $phone\nProduct: $product\nMessage: $msg";
            $headers = "From: noreply@bajrangsteels.in";
            
            // Using @ to suppress errors if local mail server is not configured
            @mail($to, $subject, $body, $headers);
            
            echo json_encode(["status" => "success", "message" => "Thank you! Your inquiry has been submitted successfully."]);
        }
    } catch(Exception $e) {
        echo json_encode(["status" => "error", "message" => "Something went wrong. Please try again later."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
