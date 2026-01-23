<?php
$servername = "localhost";  
$username = "root";         
$password = "";            
$dbname = "fileconversion";  


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $sex = trim($_POST['sex'] ?? '');
    $civil_status = trim($_POST['civil_status'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $place_of_birth = trim($_POST['place_of_birth'] ?? '');
    $home_address = trim($_POST['home_address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $same_address = isset($_POST['same_address']) ? 1 : 0;
    
   
    $errors = [];
    
    if (empty($firstname)) {
        $errors[] = "First Name is required";
    }
    if (empty($lastname)) {
        $errors[] = "Last Name is required";
    }
    if (empty($dob)) {
        $errors[] = "Date of Birth is required";
    }
    if (empty($sex)) {
        $errors[] = "Sex is required";
    }
    if (empty($civil_status)) {
        $errors[] = "Civil Status is required";
    }
    if (empty($nationality)) {
        $errors[] = "Nationality is required";
    }
    if (empty($place_of_birth)) {
        $errors[] = "Place of Birth is required";
    }
    if (empty($phone)) {
        $errors[] = "Mobile/Cellphone Number is required";
    }
    if (empty($email)) {
        $errors[] = "Email Address is required";
    }
    
  
    if ($same_address == 1) {
        $home_address = $place_of_birth;
    } else {
        if (empty($home_address)) {
            $errors[] = "Home Address is required";
        }
    }
    
    
    if (!empty($errors)) {
        echo "Validation Errors:<br>";
        foreach ($errors as $error) {
            echo "- " . htmlspecialchars($error) . "<br>";
        }
        exit;
    }
 
    echo "Form submitted successfully!<br>";
    echo "First Name: " . htmlspecialchars($firstname) . "<br>";
    echo "Last Name: " . htmlspecialchars($lastname) . "<br>";
    echo "Date of Birth: " . htmlspecialchars($dob) . "<br>";
    echo "Sex: " . htmlspecialchars($sex) . "<br>";
    echo "Civil Status: " . htmlspecialchars($civil_status) . "<br>";
    echo "Nationality: " . htmlspecialchars($nationality) . "<br>";
    echo "Place of Birth: " . htmlspecialchars($place_of_birth) . "<br>";
    echo "Home Address: " . htmlspecialchars($home_address) . "<br>";
    echo "Phone: " . htmlspecialchars($phone) . "<br>";
    echo "Email: " . htmlspecialchars($email) . "<br>";
    echo "Same Address: " . ($same_address ? "Yes" : "No") . "<br>";
} else {
    echo "Connected successfully";
}

$conn->close();
