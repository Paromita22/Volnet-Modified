// This is the main function called when the form is submitted
async function handleRegistration(event) {
    // 1. PREVENT THE FORM FROM SUBMITTING NORMALLY
    event.preventDefault();
    const form = document.getElementById('registration-form');

    // 2. PERFORM ALL BASIC CLIENT-SIDE VALIDATION FIRST
    if (!isClientSideValid()) {
        return; // Stop if basic validation fails
    }

    // --- Get email and mobile for the duplicate check ---
    const email = document.getElementById("email-input").value.trim();
    const mobile = document.getElementById("mobile-input").value.trim();

    // 3. CHECK FOR DUPLICATES ON THE SERVER (AJAX CALL)
    try {
        const response = await fetch('/auth/check_duplicates.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}&mobile=${encodeURIComponent(mobile)}`
        });

        if (!response.ok) {
            throw new Error('Server error during duplicate check.');
        }

        const result = await response.json();

        // If a duplicate is found, show an error POPUP and STOP
        if (result.emailExists) {
            alert("Registration Failed: This email address is already registered.");
            return; // Exit the function
        }
        if (result.mobileExists) {
            alert("Registration Failed: This mobile number is already registered.");
            return; // Exit the function
        }

        // 4. IF NO DUPLICATES, SUBMIT THE FORM TO THE PHP SCRIPT
        // No success popup, just submit and let the PHP handle the redirect.
        form.submit();

    } catch (error) {
        console.error('Error:', error);
        alert('Could not check for duplicates. Please try again later.');
    }
}


// Helper function for all client-side checks (No changes needed)
function isClientSideValid() {
    const email = document.getElementById("email-input").value.trim();
    const mobile = document.getElementById("mobile-input").value.trim();
    const firstName = document.getElementById("first-name").value.trim();
    const lastName = document.getElementById("last-name").value.trim();
    const address = document.getElementById("address-input").value.trim();
    const username = document.getElementById("username").value.trim();
    const dob = document.getElementById("dob").value;
    const genderInput = document.querySelector('input[name="gender"]:checked');
    const gender = genderInput ? genderInput.value : "";
    const password = document.getElementById("password").value;

    if (email === "" || mobile === "" || firstName === "" || lastName === "" || address === "" || username === "" || dob === "" || gender === "" || password === "") {
        alert("Please fill up all the fields!");
        return false;
    }

    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!pattern.test(email)) {
        alert("Invalid email address format. Please try again.");
        return false;
    }

    if (mobile.length !== 10 || !/^\d+$/.test(mobile)) {
        alert("Invalid mobile number. It must be exactly 10 digits.");
        return false;
    }

    // --- Age / Date of Birth Validation ---
    const birthDate = new Date(dob);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (birthDate >= today) {
        alert("Invalid Date of Birth. Date of birth cannot be today or in the future.");
        return false;
    }

    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    if (age < 13) {
        alert("Registration Failed: You must be at least 13 years old to register.");
        return false;
    }
    if (age > 100) {
        alert("Please enter a valid Date of Birth.");
        return false;
    }

    const LongEnough = password.length >= 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);

    if (!LongEnough) {
        alert("Password must be at least 8 characters long.");
        return false;
    } else if (!hasUpper || !hasLower) {
        alert("Password must contain both upper and lower case letters.");
        return false;
    } else if (!hasNumber) {
        alert("Password must contain numbers.");
        return false;
    }

    return true; // All client-side checks passed
}

// Automatically restrict date picker to minimum 13 years old
document.addEventListener('DOMContentLoaded', () => {
    const dobInput = document.getElementById('dob');
    if (dobInput) {
        const today = new Date();
        const maxYear = today.getFullYear() - 13;
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        dobInput.max = `${maxYear}-${month}-${day}`;
    }
});

// The buildSummary function is no longer needed.