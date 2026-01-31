<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "fileconversion";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function generateAccountNumber($conn) {
    // Generate sequential account number starting from 20000000000
    $stmt = $conn->prepare("SELECT MAX(account_number) AS max_acc FROM personal_data");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $max_acc = $row['max_acc'] ?? 0;
    $new_acc = max($max_acc + 1, 20000000000);
    $stmt->close();
    return $new_acc;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        $errors = [];

        if (empty($_POST['firstname']))      $errors[] = "First Name is required";
        if (empty($_POST['lastname']))       $errors[] = "Last Name is required";
        if (empty($_POST['dob']))            $errors[] = "Date of Birth is required";
        if (empty($_POST['sex']))            $errors[] = "Sex is required";
        if (empty($_POST['civil_status']))   $errors[] = "Civil Status is required";
        if (empty($_POST['nationality']))    $errors[] = "Nationality is required";
        if (empty($_POST['place_of_birth'])) $errors[] = "Place of Birth is required";
        if (empty($_POST['phone']))          $errors[] = "Phone is required";
        if (empty($_POST['email']))          $errors[] = "Email is required";

        $same_address = isset($_POST['same_address']) ? 1 : 0;
        if ($same_address) {
            $_POST['home_address'] = $_POST['place_of_birth'];
        } elseif (empty($_POST['home_address'])) {
            $errors[] = "Home Address is required";
        }

        // Server-side phone sanitization and validation
        if (!empty($_POST['phone'])) {
            $phone_raw = $_POST['phone'];
            $phone = preg_replace('/\D/', '', $phone_raw); // keep digits only
            if (!preg_match('/^09\d{9}$/', $phone)) {
                $errors[] = "Phone must be 11 digits starting with 09";
            }
            // normalize phone back into POST so later assignment uses sanitized value
            $_POST['phone'] = $phone;
        }

        if (!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }

        $account_number = generateAccountNumber($conn);

        $firstname   = $_POST['firstname'] ?? '';
        $middlename  = $_POST['middlename'] ?? '';
        $lastname    = $_POST['lastname'] ?? '';
        $suffix      = $_POST['suffix'] ?? '';
        $dob         = $_POST['dob'] ?? '';
        $sex         = $_POST['sex'] ?? '';
        $civil       = $_POST['civil_status'] ?? '';
        $nationality = $_POST['nationality'] ?? '';
        $pob         = $_POST['place_of_birth'] ?? '';
        $home_addr   = $_POST['home_address'] ?? '';
        $phone       = $_POST['phone'] ?? '';
        $email       = $_POST['email'] ?? '';

        $father_fn   = $_POST['father_firstname'] ?? '';
        $father_mn   = $_POST['father_middlename'] ?? '';
        $father_ln   = $_POST['father_lastname'] ?? '';
        $father_suf  = $_POST['father_suffix'] ?? '';

        $mother_fn   = $_POST['mother_firstname'] ?? '';
        $mother_mn   = $_POST['mother_middlename'] ?? '';
        $mother_ln   = $_POST['mother_lastname'] ?? '';


        $stmt = $conn->prepare("
            INSERT INTO personal_data (
                account_number, first_name, middle_name, last_name, suffix,
                dob, sex, civil_status, nationality, pob, home_address,
                mobile_number, email_add,
                father_fname, father_mname, father_lname, father_suffix,
                mother_fname, mother_mname, mother_lname
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare personal_data failed: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssssssssssssssssss",
            $account_number,
            $firstname,
            $middlename,
            $lastname,
            $suffix,
            $dob,
            $sex,
            $civil,
            $nationality,
            $pob,
            $home_addr,
            $phone,
            $email,
            $father_fn,
            $father_mn,
            $father_ln,
            $father_suf,
            $mother_fn,
            $mother_mn,
            $mother_ln
        );

        // Debug log - values about to be inserted
        error_log("[validate.php] Inserting personal_data: account={$account_number}, phone={$phone}, email={$email}");
        if ($phone === $account_number) {
            error_log("[validate.php][ALERT] phone equals generated account for account={$account_number}");
        }

        if (!$stmt->execute()) {
            throw new Exception("personal_data insert failed: " . $stmt->error);
        }
        $stmt->close();

        // Confirm inserted mobile_number for debugging (duplicate-branch case)
        $res_check_dup = $conn->query("SELECT mobile_number FROM personal_data WHERE account_number = '" . $conn->real_escape_string($account_number) . "' LIMIT 1");
        if ($res_check_dup) {
            $row_check_dup = $res_check_dup->fetch_assoc();
            error_log("[validate.php] After (retry) insert: account={$account_number} mobile_number=" . ($row_check_dup['mobile_number'] ?? 'NULL'));
        }

        $spouse_fn = $_POST['spouse_firstname'] ?? '';
        $spouse_ln = $_POST['spouse_lastname'] ?? '';

        $child_fn  = $_POST['child1_firstname'] ?? '';
        $child_mn  = $_POST['child1_middlename'] ?? '';
        $child_ln  = $_POST['child1_lastname'] ?? '';

        $dob_value = $_POST['child1_dob'] ?? ($_POST['other_benef1_dob'] ?? null);

        $other_fn  = $_POST['other_benef1_firstname'] ?? '';
        $other_mn  = $_POST['other_benef1_middlename'] ?? '';
        $other_ln  = $_POST['other_benef1_lastname'] ?? '';
        $relation  = $_POST['other_benef1_relationship'] ?? '';

        $stmt = $conn->prepare("
            INSERT INTO benefeciaries (
                account_number, spouse_fname, spouse_lname,
                child_fname, child_mname, child_lname, dob,
                other_fname, other_mname, other_lname, relation
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare benefeciaries failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssssss",
            $account_number,
            $spouse_fn,
            $spouse_ln,
            $child_fn,
            $child_mn,
            $child_ln,
            $dob_value,
            $other_fn,
            $other_mn,
            $other_ln,
            $relation
        );

        if (!$stmt->execute()) {
            throw new Exception("benefeciaries insert failed: " . $stmt->error);
        }
        $stmt->close();

        $profession  = $_POST['se_profession'] ?? '';
        $started     = $_POST['se_year_started'] ?? '';
        $foreign_add = $_POST['ofw_address'] ?? '';
        $flexi       = $_POST['flexi_fund'] ?? '';

        $monthly_earning = $_POST['se_monthly_earnings']
            ?? ($_POST['ofw_monthly_earnings'] ?? null);

        $nws_ss = $_POST['nws_sss'] ?? null;
        $signature = $_POST['nws_spouse_signature'] ?? '';

        $stmt = $conn->prepare("
            INSERT INTO overseas (
                account_number, profession_business, business_started,
                foreign_address, flexi_fund, monthly_earning,
                nws_ss_number, signature_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare overseas failed: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssssss",
            $account_number,
            $profession,
            $started,
            $foreign_add,
            $flexi,
            $monthly_earning,
            $nws_ss,
            $signature
        );

        if (!$stmt->execute()) {
            throw new Exception("overseas insert failed: " . $stmt->error);
        }
        $stmt->close();

        $cert_name  = $_POST['cert_printed_name'] ?? '';
        $cert_sign  = $_POST['cert_signature'] ?? '';
        $cert_date  = $_POST['cert_date'] ?? '';
        $thumb      = $_POST['biometric_right_thumb'] ?? '';
        $index      = $_POST['biometric_right_index'] ?? '';

        $stmt = $conn->prepare("
            INSERT INTO certification (
                account_number, printedName_path, signature_path,
                date_path, thumb_path, index_path
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare certification failed: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssss",
            $account_number,
            $cert_name,
            $cert_sign,
            $cert_date,
            $thumb,
            $index
        );

        if (!$stmt->execute()) {
            throw new Exception("certification insert failed: " . $stmt->error);
        }
        $stmt->close();

        echo "Successfully registered. Account: " . $account_number;

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        error_log("Form submission error: " . $e->getMessage());
    }
} else {
    echo "Invalid request method";
}

$conn->close();
?>
