
document.addEventListener('DOMContentLoaded', function() {
    const sameAddressCheckbox = document.getElementById('same_address');
    const homeAddressRow = document.getElementById('home-address-row');
    const homeAddressField = document.getElementById('home_address');
    const placeOfBirthField = document.getElementById('place_of_birth');
    const registrationForm = document.getElementById('registrationForm');

    
    if (sameAddressCheckbox) {
        sameAddressCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Hide home address row
                homeAddressRow.style.display = 'none';
                
                // Copy place of birth value to home address
                const placeOfBirthValue = placeOfBirthField.value.trim();
                homeAddressField.value = placeOfBirthValue;
                
                // Clear error message for home address when hiding
                clearFieldError('home_address');
            } else {
                // Show home address row
                homeAddressRow.style.display = 'flex';
                
               
                homeAddressField.value = '';
                clearFieldError('home_address');
            }
        });
    }

   
    if (placeOfBirthField && sameAddressCheckbox) {
        placeOfBirthField.addEventListener('input', function() {
            if (sameAddressCheckbox.checked) {
                homeAddressField.value = this.value.trim();
            }
        });
    }


    const requiredInputs = document.querySelectorAll('#registrationForm input[required], #registrationForm select[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });

        input.addEventListener('change', function() {
            validateField(this);
        });

        input.addEventListener('input', function() {
            
            if (this.value.trim() !== '') {
                clearFieldError(this.id);
            }
        });
    });

    
    const beneficiaryDateFields = document.querySelectorAll('#spouse_dob, #child1_dob, #child2_dob, #other_benef1_dob, #other_benef2_dob');
    beneficiaryDateFields.forEach(dateField => {
        dateField.addEventListener('blur', function() {
            validateField(this);
        });

        dateField.addEventListener('change', function() {
            validateField(this);
        });
    });

    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            
            clearAllErrors();

           
            const isValid = validateFormOnSubmit();

            if (!isValid) {
                return false;
            }

           
            const sameAddress = sameAddressCheckbox.checked;
            if (sameAddress) {
                const placeOfBirth = document.getElementById('place_of_birth').value;
                document.getElementById('home_address').value = placeOfBirth;
            }

            
            this.submit();
        });
    }
});


function validateField(field) {
    const value = field.value.trim();
    const fieldId = field.id;
    const errorElement = document.getElementById(fieldId + '-error');

    if (!errorElement) return true;

  
    field.classList.remove('error');
    errorElement.classList.remove('show');
    errorElement.textContent = '';

    
    if (value === '') {
        field.classList.add('error');
        errorElement.textContent = 'This field is required';
        errorElement.classList.add('show');
        return false;
    }

    
    if (fieldId === 'email') {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
            field.classList.add('error');
            errorElement.textContent = 'Please enter a valid email address';
            errorElement.classList.add('show');
            return false;
        }
    }

   
    if (fieldId === 'phone') {
        const phonePattern = /^09\d{9}$/;
        if (!phonePattern.test(value)) {
            field.classList.add('error');
            errorElement.textContent = 'Phone must be 11 digits starting with 09 (format: 09XXXXXXXXX)';
            errorElement.classList.add('show');
            return false;
        }
    }

 
    if (fieldId === 'dob') {
        const dobDate = new Date(value);
        const today = new Date();
        const year2008 = new Date('2008-12-31');

        if (dobDate > today) {
            field.classList.add('error');
            errorElement.textContent = 'Date of birth cannot be in the future';
            errorElement.classList.add('show');
            return false;
        }

        if (dobDate > year2008) {
            field.classList.add('error');
            errorElement.textContent = 'Date of birth must be 2008 or earlier (applicant must be at least 16 years old)';
            errorElement.classList.add('show');
            return false;
        }
    }

 
    if (['spouse_dob', 'child1_dob', 'child2_dob', 'other_benef1_dob', 'other_benef2_dob'].includes(fieldId)) {
        if (value !== '') {
            const beneficiaryDate = new Date(value);
            const today = new Date();

            if (beneficiaryDate > today) {
                field.classList.add('error');
                errorElement.textContent = 'Date of birth cannot be in the future';
                errorElement.classList.add('show');
                return false;
            }
        }
    }

    field.classList.remove('error');
    return true;
}

function validateFormOnSubmit() {
    let isFormValid = true;

    const requiredFields = [
        { id: 'firstname', name: 'First Name' },
        { id: 'lastname', name: 'Last Name' },
        { id: 'dob', name: 'Date of Birth' },
        { id: 'sex', name: 'Sex' },
        { id: 'civil_status', name: 'Civil Status' },
        { id: 'nationality', name: 'Nationality' },
        { id: 'place_of_birth', name: 'Place of Birth' },
        { id: 'phone', name: 'Mobile/Cellphone Number' },
        { id: 'email', name: 'Email Address' }
    ];

  
    requiredFields.forEach(fieldInfo => {
        const field = document.getElementById(fieldInfo.id);
        if (field) {
            if (!validateField(field)) {
                isFormValid = false;
            }
        }
    });


    const sameAddressCheckbox = document.getElementById('same_address');
    if (sameAddressCheckbox && !sameAddressCheckbox.checked) {
        const homeAddressField = document.getElementById('home_address');
        const homeAddressError = document.getElementById('home_address-error');
        const homeAddressValue = homeAddressField.value.trim();

        if (homeAddressValue === '') {
            homeAddressField.classList.add('error');
            homeAddressError.textContent = 'This field is required';
            homeAddressError.classList.add('show');
            isFormValid = false;
        }
    }

    return isFormValid;
}


function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + '-error');

    if (field) {
        field.classList.remove('error');
    }
    if (errorElement) {
        errorElement.classList.remove('show');
        errorElement.textContent = '';
    }
}


function clearAllErrors() {
    const allErrorElements = document.querySelectorAll('.error-message');
    const allErrorInputs = document.querySelectorAll('input.error, select.error');

    allErrorElements.forEach(element => {
        element.classList.remove('show');
        element.textContent = '';
    });

    allErrorInputs.forEach(input => {
        input.classList.remove('error');
    });
}
