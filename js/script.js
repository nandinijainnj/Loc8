// Handle item click to show details
document.querySelectorAll('.item-card').forEach(card => {
    card.addEventListener('click', function(e) {
        // Don't redirect if clicking on a button inside the card
        if (e.target.tagName === 'BUTTON') return;
        
        const id = this.dataset.id;
        const type = this.dataset.type;
        window.location.href = `details.php?type=${type}&id=${id}`;
    });
});

// Claim button functionality (for found items)
const claimBtn = document.getElementById('claim-btn');
if (claimBtn) {
    claimBtn.addEventListener('click', function() {
        document.getElementById('claim-form').style.display = 'block';
        this.style.display = 'none';
    });
}

// Handle claim form submission
const claimForm = document.getElementById('claimForm');
if (claimForm) {
    claimForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch('process_claim.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = 'my_claims.php'; // Redirect to claims page
            } else {
                alert('Error: ' + data.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit Claim';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting your claim');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Submit Claim';
        });
    });
}

// Handle Messages slidebar button function
function toggleMessages() {
    const panel = document.getElementById("message-panel");
    panel.classList.toggle("active");
}

// Handle image preview in report form
const imageInput = document.querySelector('input[type="file"]');
const imagePreview = document.createElement('img');
imagePreview.style.maxWidth = '200px';
imagePreview.style.maxHeight = '200px';

if (imageInput) {
    // Insert preview element after the file input
    imageInput.insertAdjacentElement('afterend', imagePreview);
    
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Please select an image file');
                this.value = '';
                imagePreview.style.display = 'none';
                return;
            }
            
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('Image must be less than 2MB');
                this.value = '';
                imagePreview.style.display = 'none';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(event) {
                imagePreview.src = event.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });
}

// Password match validation for register.php
const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
if (confirmPasswordInput) {
    const passwordInput = document.querySelector('input[name="password"]');
    const passwordError = document.createElement('small');
    passwordError.style.color = 'red';
    passwordError.style.display = 'block';
    confirmPasswordInput.insertAdjacentElement('afterend', passwordError);
    
    function validatePassword() {
        if (passwordInput.value !== confirmPasswordInput.value) {
            passwordError.textContent = "Passwords don't match";
            confirmPasswordInput.setCustomValidity("Passwords don't match");
            return false;
        } else {
            passwordError.textContent = "";
            confirmPasswordInput.setCustomValidity('');
            return true;
        }
    }
    
    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validatePassword);
}

// Phone number formatting
const phoneInput = document.querySelector('input[name="phone"]');
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        // Remove all non-digit characters
        const numbers = e.target.value.replace(/\D/g, '');
        const length = numbers.length;
        
        // Format based on length
        if (length <= 3) {
            e.target.value = numbers;
        } else if (length <= 6) {
            e.target.value = `(${numbers.substring(0,3)}) ${numbers.substring(3)}`;
        } else {
            e.target.value = `(${numbers.substring(0,3)}) ${numbers.substring(3,6)}-${numbers.substring(6,10)}`;
        }
    });
    
    // Validate on form submission
    const form = phoneInput.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const digits = phoneInput.value.replace(/\D/g, '');
            if (digits.length !== 10) {
                e.preventDefault();
                alert('Please enter a valid 10-digit phone number');
                phoneInput.focus();
            }
        });
    }
}

// Date validation for report form
const dateInput = document.querySelector('input[type="date"]');
if (dateInput) {
    dateInput.addEventListener('change', function() {
        const today = new Date().toISOString().split('T')[0];
        if (this.value > today) {
            alert('Date cannot be in the future');
            this.value = today;
        }
    });
    
    // Set max date to today
    dateInput.max = new Date().toISOString().split('T')[0];
}