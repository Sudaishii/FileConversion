
// Initialize registration form behavior for a scoped container (document or any element)
function initRegistrationForm(container = document, options = {}) {
    const sameAddressCheckbox = container.querySelector('#same_address');
    const homeAddressRow = container.querySelector('#home-address-row');
    const homeAddressField = container.querySelector('#home_address');
    const placeOfBirthField = container.querySelector('#place_of_birth');
    const registrationForm = container.querySelector('#registrationForm');
    // prevent duplicate submissions
    let isSubmitting = false;

    if (sameAddressCheckbox) {
        sameAddressCheckbox.addEventListener('change', function() {
            if (this.checked) {
                if (homeAddressRow) homeAddressRow.style.display = 'none';
                const placeOfBirthValue = (placeOfBirthField && placeOfBirthField.value) ? placeOfBirthField.value.trim() : '';
                if (homeAddressField) homeAddressField.value = placeOfBirthValue;
                clearFieldError('home_address', container);
            } else {
                if (homeAddressRow) homeAddressRow.style.display = 'flex';
                if (homeAddressField) homeAddressField.value = '';
                clearFieldError('home_address', container);
            }
        });
    }

    if (placeOfBirthField && sameAddressCheckbox) {
        placeOfBirthField.addEventListener('input', function() {
            if (sameAddressCheckbox.checked && homeAddressField) {
                homeAddressField.value = this.value.trim();
            }
        });
    }

    const requiredSelector = 'input[required], select[required]';
    const requiredInputs = container.querySelectorAll(requiredSelector);
    requiredInputs.forEach(input => {
        input.addEventListener('blur', function() { validateField(this, container); });
        input.addEventListener('change', function() { validateField(this, container); });
        input.addEventListener('input', function() {
            if (this.id === 'phone') {
                validateField(this, container);
            } else if (this.value.trim() !== '') {
                clearFieldError(this.id, container);
            }
        });
    });

    const beneficiaryDateFields = container.querySelectorAll('#spouse_dob, #child1_dob, #child2_dob, #other_benef1_dob, #other_benef2_dob');
    beneficiaryDateFields.forEach(dateField => {
        dateField.addEventListener('blur', function() { validateField(this, container); });
        dateField.addEventListener('change', function() { validateField(this, container); });
    });

    if (registrationForm) {
        console.log('[initRegistrationForm] attach submit for', registrationForm.id || 'registrationForm');

        // ensure clicking a submit button inside the container triggers the form submit handler
        container.addEventListener('click', function(e) {
            const btn = e.target.closest('button[type="submit"], input[type="submit"]');
            if (!btn) return;
            const btnForm = btn.closest('form') || registrationForm;
            if (btnForm) {
                // allow native submit flow which will trigger the handler below
                try {
                    if (typeof btnForm.requestSubmit === 'function') btnForm.requestSubmit();
                    else btnForm.submit();
                } catch (ex) {
                    console.error('[initRegistrationForm] requestSubmit failed', ex);
                    btnForm.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }
        });

        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('[initRegistrationForm] form submit intercepted');
            clearAllErrors(container);

            // prevent duplicate submissions
            if (isSubmitting) {
                console.log('[initRegistrationForm] submission blocked: already in progress');
                return false;
            }

            const isValid = validateFormOnSubmit(container);
            if (!isValid) {
                console.log('[initRegistrationForm] validation failed');
                return false;
            }

            const sameAddress = sameAddressCheckbox && sameAddressCheckbox.checked;
            if (sameAddress && placeOfBirthField && homeAddressField) {
                homeAddressField.value = placeOfBirthField.value;
            }

            // extra validation: ensure phone validated on submit
            const phoneField = container.querySelector('#phone');
            if (phoneField && !validateField(phoneField, container)) {
                console.log('[initRegistrationForm] phone validation failed');
                return false;
            }

            // mark submitting and disable submit buttons to avoid double-post
            isSubmitting = true;
            const submitButtons = registrationForm.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach(b => b.disabled = true);

            const formData = new FormData(registrationForm);
            // determine correct validate endpoint: when on admin pages (under /pages/) use ../validate.php
            let validatePath = 'validate.php';
            if (window.location && window.location.pathname && window.location.pathname.indexOf('/pages/') !== -1) {
                validatePath = '../validate.php';
            }
            console.log('[initRegistrationForm] submitting to', validatePath);
            fetch(validatePath, { method: 'POST', body: formData })
            .then(response => response.text())
            .then(data => {
                console.log('[initRegistrationForm] server response:', data);
                if (data.includes('Successfully registered')) {
                    registrationForm.reset();
                    if (options.onSuccess && typeof options.onSuccess === 'function') {
                        options.onSuccess(data);
                    } else {
                        alert('Successfully registered!');
                    }
                } else {
                    // show response inside a small notice if available, else fallback to alert
                    if (container) {
                        let resp = container.querySelector('#response-container');
                        if (!resp) {
                            resp = document.createElement('div');
                            resp.id = 'response-container';
                            resp.style.cssText = 'margin:0.5rem 0;padding:0.6rem;border-radius:0.4rem;background:#fff7e6;border:1px solid #ffd699;color:#6b4a00;';
                            registrationForm.parentNode.insertBefore(resp, registrationForm);
                        }
                        resp.textContent = data;
                    } else alert('Response: ' + data);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('An error occurred while submitting the form.');
            })
            .finally(() => {
                isSubmitting = false;
                submitButtons.forEach(b => b.disabled = false);
            });
        });
    }
}

// Auto-initialize on main page
document.addEventListener('DOMContentLoaded', function() {
    initRegistrationForm(document, {});
});


function validateField(field, container = document) {
    if (!field) return true;
    const value = (field.value || '').trim();
    const fieldId = field.id;
    const formScope = field.closest('form') || container;
    const errorElement = formScope.querySelector(`#${fieldId}-error`);

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

    if (fieldId === 'phone' || fieldId === 'mobile_number') {
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

function validateFormOnSubmit(container = document) {
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
        const field = container.querySelector(`#${fieldInfo.id}`);
        if (field) {
            if (!validateField(field, container)) {
                isFormValid = false;
            }
        }
    });

    const sameAddressCheckbox = container.querySelector('#same_address');
    if (sameAddressCheckbox && !sameAddressCheckbox.checked) {
        const homeAddressField = container.querySelector('#home_address');
        const homeAddressError = container.querySelector('#home_address-error');
        const homeAddressValue = homeAddressField ? homeAddressField.value.trim() : '';

        if (homeAddressValue === '') {
            if (homeAddressField) homeAddressField.classList.add('error');
            if (homeAddressError) {
                homeAddressError.textContent = 'This field is required';
                homeAddressError.classList.add('show');
            }
            isFormValid = false;
        }
    }

    return isFormValid;
}


function clearFieldError(fieldId, container = document) {
    const field = container.querySelector(`#${fieldId}`);
    const errorElement = container.querySelector(`#${fieldId}-error`);

    if (field) field.classList.remove('error');
    if (errorElement) {
        errorElement.classList.remove('show');
        errorElement.textContent = '';
    }
}


function clearAllErrors(container = document) {
    const allErrorElements = container.querySelectorAll('.error-message');
    const allErrorInputs = container.querySelectorAll('input.error, select.error');

    allErrorElements.forEach(element => {
        element.classList.remove('show');
        element.textContent = '';
    });

    allErrorInputs.forEach(input => {
        input.classList.remove('error');
    });
}
