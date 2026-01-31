<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "fileconversion";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB connection error']);
    exit;
}

$action = $_GET['action'] ?? null;
try {
    if ($action === 'list') {
        $res = $conn->query("SELECT * FROM personal_data ORDER BY created_at DESC");
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode($rows);
        exit;
    }

    if ($action === 'get') {
        $account = $_GET['account'] ?? null;
        if (!$account) { echo json_encode(['error' => 'Account required']); exit; }
        $stmt = $conn->prepare("SELECT * FROM personal_data WHERE account_number = ? LIMIT 1");
        $stmt->bind_param('s', $account);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        if (!$row) {
            echo json_encode(['error' => 'Not found']); exit;
        }

        // fetch related data: benefeciaries, overseas, certification
        $stmt = $conn->prepare("SELECT * FROM benefeciaries WHERE account_number = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $account);
            $stmt->execute();
            $res2 = $stmt->get_result();
            $row['beneficiaries'] = $res2->fetch_assoc() ?: null;
            $stmt->close();
        }

        $stmt = $conn->prepare("SELECT * FROM overseas WHERE account_number = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $account);
            $stmt->execute();
            $res3 = $stmt->get_result();
            $row['overseas'] = $res3->fetch_assoc() ?: null;
            $stmt->close();
        }

        $stmt = $conn->prepare("SELECT * FROM certification WHERE account_number = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $account);
            $stmt->execute();
            $res4 = $stmt->get_result();
            $row['certification'] = $res4->fetch_assoc() ?: null;
            $stmt->close();
        }

        echo json_encode($row);
        exit;
    }

    // lookup existing record by phone / email / account (admin helper)
    if ($action === 'lookup') {
        $phoneQ = $_GET['phone'] ?? null;
        $emailQ = $_GET['email'] ?? null;
        $accountQ = $_GET['account'] ?? null;

        if (!$phoneQ && !$emailQ && !$accountQ) {
            echo json_encode(['error' => 'phone, email, or account required']);
            exit;
        }

        if ($accountQ) {
            $stmt = $conn->prepare("SELECT account_number, first_name, last_name, dob, mobile_number, email_add, created_at FROM personal_data WHERE account_number = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $accountQ);
            }
        } else {
            $stmt = $conn->prepare("SELECT account_number, first_name, last_name, dob, mobile_number, email_add, created_at FROM personal_data WHERE mobile_number = ? OR email_add = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('ss', $phoneQ, $emailQ);
            }
        }

        if ($stmt) {
            $stmt->execute();
            $resL = $stmt->get_result();
            $found = $resL->fetch_assoc();
            $stmt->close();
            if ($found) echo json_encode(['found' => true, 'record' => $found]);
            else echo json_encode(['found' => false]);
        } else {
            echo json_encode(['error' => 'Lookup failed']);
        }
        exit;
    }

    if ($action === 'create' || $action === 'update') {
        // collect and sanitize
        $account = $_POST['account_number'] ?? null;
        $firstname = $_POST['firstname'] ?? '';
        $middlename = $_POST['middlename'] ?? '';
        $lastname = $_POST['lastname'] ?? '';
        $dob = $_POST['dob'] ?? null;
        $pob = $_POST['pob'] ?? '';
        $home = $_POST['home_address'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $phone = preg_replace('/\D/', '', $phone);
        $email = $_POST['email'] ?? '';

        // basic validation
        if (empty($firstname) || empty($lastname)) {
            echo json_encode(['error' => 'First and last name required']); exit;
        }
        if ($phone !== '' && !preg_match('/^09\d{9}$/', $phone)) {
            echo json_encode(['error' => 'Phone must be 11 digits starting with 09']); exit;
        }

        if ($action === 'create') {
            // prevent duplicate registration by phone or email
            $dupStmt = $conn->prepare("SELECT account_number FROM personal_data WHERE mobile_number = ? OR email_add = ? LIMIT 1");
            if ($dupStmt) {
                $dupStmt->bind_param('ss', $phone, $email);
                $dupStmt->execute();
                $dupRes = $dupStmt->get_result();
                $existing = $dupRes->fetch_assoc();
                $dupStmt->close();
                if ($existing) {
                    echo json_encode(['error' => 'Already registered', 'account' => $existing['account_number']]); exit;
                }
            }

            // generate unique 10-digit account
            do {
                $account = strval(random_int(1000000000, 9999999999));
                $stmt = $conn->prepare("SELECT 1 FROM personal_data WHERE account_number = ? LIMIT 1");
                $stmt->bind_param('s', $account);
                $stmt->execute();
                $stmt->store_result();
                $exists = ($stmt->num_rows > 0);
                $stmt->close();
            } while ($exists);

            $stmt = $conn->prepare("INSERT INTO personal_data (account_number, first_name, middle_name, last_name, dob, pob, home_address, mobile_number, email_add) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssss', $account, $firstname, $middlename, $lastname, $dob, $pob, $home, $phone, $email);
            if (!$stmt->execute()) throw new Exception('Insert failed: ' . $stmt->error);

            // insert beneficiaries if provided
            $spouse_ln = $_POST['spouse_lastname'] ?? '';
            $spouse_fn = $_POST['spouse_firstname'] ?? '';
            $spouse_mn = $_POST['spouse_middlename'] ?? '';

            $child_ln = $_POST['child1_lastname'] ?? '';
            $child_fn = $_POST['child1_firstname'] ?? '';
            $child_mn = $_POST['child1_middlename'] ?? '';
            $child_dob = $_POST['child1_dob'] ?? null;

            $other_ln = $_POST['other_benef1_lastname'] ?? '';
            $other_fn = $_POST['other_benef1_firstname'] ?? '';
            $other_mn = $_POST['other_benef1_middlename'] ?? '';
            $other_rel = $_POST['other_benef1_relationship'] ?? '';
            $other_dob = $_POST['other_benef1_dob'] ?? null;

            $stmt = $conn->prepare("INSERT INTO benefeciaries (account_number, spouse_fname, spouse_lname, child_fname, child_mname, child_lname, dob, other_fname, other_mname, other_lname, relation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $dob_for_ben = $child_dob ?: $other_dob;
                $stmt->bind_param('sssssssssss', $account, $spouse_fn, $spouse_ln, $child_fn, $child_mn, $child_ln, $dob_for_ben, $other_fn, $other_mn, $other_ln, $other_rel);
                $stmt->execute();
                $stmt->close();
            }

            echo json_encode(['success' => true, 'account' => $account]);
            exit;
        } else {
            if (!$account) { echo json_encode(['error' => 'Account required for update']); exit; }
            $stmt = $conn->prepare("UPDATE personal_data SET first_name=?, middle_name=?, last_name=?, dob=?, pob=?, home_address=?, mobile_number=?, email_add=? WHERE account_number=?");
            $stmt->bind_param('sssssssss', $firstname, $middlename, $lastname, $dob, $pob, $home, $phone, $email, $account);
            // ensure mobile_number / email don't conflict with other accounts
            $dupCheck = $conn->prepare("SELECT account_number FROM personal_data WHERE (mobile_number = ? OR email_add = ?) AND account_number <> ? LIMIT 1");
            if ($dupCheck) {
                $dupCheck->bind_param('sss', $phone, $email, $account);
                $dupCheck->execute();
                $resDup = $dupCheck->get_result();
                $conflict = $resDup->fetch_assoc();
                $dupCheck->close();
                if ($conflict) {
                    echo json_encode(['error' => 'Phone or email already in use by another account']); exit;
                }
            }

            if (!$stmt->execute()) throw new Exception('Update failed: ' . $stmt->error);

            // update or insert beneficiaries for this account
            $spouse_ln = $_POST['spouse_lastname'] ?? '';
            $spouse_fn = $_POST['spouse_firstname'] ?? '';
            $spouse_mn = $_POST['spouse_middlename'] ?? '';

            $child_ln = $_POST['child1_lastname'] ?? '';
            $child_fn = $_POST['child1_firstname'] ?? '';
            $child_mn = $_POST['child1_middlename'] ?? '';
            $child_dob = $_POST['child1_dob'] ?? null;

            $other_ln = $_POST['other_benef1_lastname'] ?? '';
            $other_fn = $_POST['other_benef1_firstname'] ?? '';
            $other_mn = $_POST['other_benef1_middlename'] ?? '';
            $other_rel = $_POST['other_benef1_relationship'] ?? '';
            $other_dob = $_POST['other_benef1_dob'] ?? null;

            $res = $conn->prepare("SELECT 1 FROM benefeciaries WHERE account_number = ? LIMIT 1");
            if ($res) {
                $res->bind_param('s', $account);
                $res->execute();
                $res->store_result();
                $exists = ($res->num_rows > 0);
                $res->close();

                if ($exists) {
                    $stmt = $conn->prepare("UPDATE benefeciaries SET spouse_fname=?, spouse_lname=?, child_fname=?, child_mname=?, child_lname=?, dob=?, other_fname=?, other_mname=?, other_lname=?, relation=? WHERE account_number=?");
                    if ($stmt) {
                        $dob_for_ben = $child_dob ?: $other_dob;
                        $stmt->bind_param('sssssssssss', $spouse_fn, $spouse_ln, $child_fn, $child_mn, $child_ln, $dob_for_ben, $other_fn, $other_mn, $other_ln, $other_rel, $account);
                        $stmt->execute();
                        $stmt->close();
                    }
                } else {
                    $stmt = $conn->prepare("INSERT INTO benefeciaries (account_number, spouse_fname, spouse_lname, child_fname, child_mname, child_lname, dob, other_fname, other_mname, other_lname, relation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt) {
                        $dob_for_ben = $child_dob ?: $other_dob;
                        $stmt->bind_param('sssssssssss', $account, $spouse_fn, $spouse_ln, $child_fn, $child_mn, $child_ln, $dob_for_ben, $other_fn, $other_mn, $other_ln, $other_rel);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }

            echo json_encode(['success' => true]);
            exit;
        }
    }

    if ($action === 'delete') {
        $account = $_POST['account_number'] ?? null;
        if (!$account) { echo json_encode(['error' => 'Account required']); exit; }

        // delete dependent rows first
        $stmt = $conn->prepare("DELETE FROM benefeciaries WHERE account_number = ?");
        $stmt->bind_param('s', $account);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM certification WHERE account_number = ?");
        $stmt->bind_param('s', $account);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM overseas WHERE account_number = ?");
        $stmt->bind_param('s', $account);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM personal_data WHERE account_number = ?");
        $stmt->bind_param('s', $account);
        if (!$stmt->execute()) throw new Exception('Delete failed: ' . $stmt->error);
        $stmt->close();

        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['error' => 'Invalid action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>