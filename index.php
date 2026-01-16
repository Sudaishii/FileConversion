<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Online Registration</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="styling/style.css">
</head>

<body>
<div id="wrapper">
    <div id="container">

        <!-- Title -->
        <div id="title-wrapper">
            <div id="title">
                <p>Republic of the Elements</p>
                <h6 class="header-six">STUDENT SCHOLARSHIP SYSTEM</h6>
                <h6 id="perso">PERSONAL RECORD</h6>
                <h6 class="header-six">FOR ISSUANCE OF PHONE NUMBER</h6>
            </div>
        </div>

        <p class="OnlineRegistration">Online Registration</p>
        <hr>

        <!-- Name Row -->
        <div class="Account-Details">
            <div class="field">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" placeholder="First Name">
            </div>

            <div class="field">
                <label for="middlename">Middle Name</label>
                <input type="text" id="middlename" placeholder="Middle Name">
            </div>

            <div class="field">
                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" placeholder="Last Name">
            </div>

            <div class="field field-suffix">
                <label for="suffix">Suffix</label>
                <select id="suffix" name="suffix">
                    <option value="">Select Suffix</option>
                    <option value="Jr">Jr.</option>
                    <option value="Sr">Sr.</option>
                    <option value="I">I</option>
                    <option value="II">II</option>
                    <option value="III">III</option>
                </select>
            </div>
        </div>

        <!-- DOB + Sex -->
        <div class="Account-Details">
            <div class="field">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob">
            </div>

            <div class="field">
                <label for="sex">Sex</label>
                <select id="sex">
                    <option value="">Select Sex</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
        </div>

        <!-- Civil Status + TIN -->
        <div class="Account-Details">
            <div class="field">
                <label for="civil_status">Civil Status</label>
                <select id="civil_status" name="civil_status">
                    <option value="">Select Civil Status</option>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Widowed">Widowed</option>
                    <option value="Legally Separated">Legally Separated</option>
                </select>
            </div>

            <div class="field">
                <label for="tin">Tax Identification Number (If Any)</label>
                <input type="text" id="tin" name="tin" placeholder="XXX-XXX-XXX">
            </div>
        </div>

        <!-- Place of Birth -->
        <div class="Account-Details">
            <div class="field">
                <label for="place_of_birth">Place of Birth</label>
                <input type="text" id="place_of_birth" name="place_of_birth" placeholder="City / Municipality, Province">
                <div class="same-address">
                    <input type="checkbox" id="same_address">
                    <label for="same_address">The same with Home Address</label>
                </div>
            </div>
        </div>

        <!-- Home Address + Zip Code -->
        <div class="Account-Details" id="home-address-row">
            <div class="field">
                <label for="home_address">Home Address</label>
                <input type="text" id="home_address" name="home_address" placeholder="House No., Street, Barangay, City, Province">
            </div>

            <div class="field">
                <label for="zipcode">Zip Code</label>
                <input type="text" id="zipcode" name="zipcode" placeholder="Zip Code">
            </div>
        </div>

        <!-- Phone + Email -->
        <div class="Account-Details">
            <div class="field">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" placeholder="09XXXXXXXXX" maxlength="11">
            </div>

            <div class="field">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="example@email.com">
            </div>
        </div>

        <hr>

        <!-- Father's Info -->
        <p class="parent-info">Father's Information</p>
        <div class="Account-Details">
            <div class="field">
                <label for="father_firstname">First Name</label>
                <input type="text" id="father_firstname" name="father_firstname" placeholder="First Name">
            </div>

            <div class="field">
                <label for="father_middlename">Middle Name</label>
                <input type="text" id="father_middlename" name="father_middlename" placeholder="Middle Name">
            </div>

            <div class="field">
                <label for="father_lastname">Last Name</label>
                <input type="text" id="father_lastname" name="father_lastname" placeholder="Last Name">
            </div>

            <div class="field field-suffix">
                <label for="father_suffix">Suffix</label>
                <select id="father_suffix" name="father_suffix">
                    <option value="">Select Suffix</option>
                    <option value="Jr">Jr.</option>
                    <option value="Sr">Sr.</option>
                    <option value="I">I</option>
                    <option value="II">II</option>
                    <option value="III">III</option>
                </select>
            </div>
        </div>

        <!-- Mother's Info -->
        <p class="parent-info">Mother's Information</p>
        <div class="Account-Details">
            <div class="field">
                <label for="mother_firstname">First Name</label>
                <input type="text" id="mother_firstname" name="mother_firstname" placeholder="First Name">
            </div>

            <div class="field">
                <label for="mother_middlename">Middle Name</label>
                <input type="text" id="mother_middlename" name="mother_middlename" placeholder="Middle Name">
            </div>

            <div class="field">
                <label for="mother_lastname">Last Name</label>
                <input type="text" id="mother_lastname" name="mother_lastname" placeholder="Last Name">
            </div>
        </div>

    </div>
</div>

</body>
</html>
