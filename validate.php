<?php
$servername = "localhost";  
$username = "root";         
$password = "";            
$dbname = "fileconversion";  

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Generate random account number
function generateAccountNumber($length = 10) {
    return str_pad(mt_rand(0, pow(10, $length)-1), $length, '0', STR_PAD_LEFT);
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
    if (empty($firstname)) $errors[] = "First Name is required";
    if (empty($lastname)) $errors[] = "Last Name is required";
    if (empty($dob)) $errors[] = "Date of Birth is required";
    if (empty($sex)) $errors[] = "Sex is required";
    if (empty($civil_status)) $errors[] = "Civil Status is required";
    if (empty($nationality)) $errors[] = "Nationality is required";
    if (empty($place_of_birth)) $errors[] = "Place of Birth is required";
    if (empty($phone)) $errors[] = "Phone is required";
    if (empty($email)) $errors[] = "Email is required";

    if ($same_address) $home_address = $place_of_birth;
    elseif (empty($home_address)) $errors[] = "Home Address is required";

    if (!empty($errors)) {
        echo "<div style='color:red'><strong>Validation Errors:</strong><br>";
        foreach ($errors as $error) echo "- " . htmlspecialchars($error) . "<br>";
        echo "</div>";
        echo "<a href='index.php'>Go Back</a>";
        exit;
    }

    $account_number = generateAccountNumber(10);

    $stmt = $conn->prepare("INSERT INTO users (account_number, firstname, lastname, dob, sex, civil_status, nationality, place_of_birth, home_address, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $account_number, $firstname, $lastname, $dob, $sex, $civil_status, $nationality, $place_of_birth, $home_address, $phone, $email);
    $stmt->execute();

    echo "<div style='color:green'><strong>Form submitted successfully!</strong><br>";
    echo "Account Number: $account_number<br>";
    echo "<a href='index.php'>Back to Form</a></div>";

    $stmt->close();
}

$conn->close();
?>
