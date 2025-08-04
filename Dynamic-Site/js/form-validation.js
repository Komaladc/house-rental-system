/**
 * Form Validation for House Rental System
 * Comprehensive client-side validation for all forms
 */

// Validation utilities
const ValidationUtils = {
    // Enhanced email validation
    isValidEmail: function(email) {
        // More robust email regex that requires proper domain
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        // Additional checks
        if (!emailRegex.test(email)) {
            return false;
        }

        // Check for valid domain (must have at least one dot and valid extension)
        const domain = email.split('@')[1];
        if (!domain || domain.split('.').length < 2) {
            return false;
        }

        // Check domain extension length (at least 2 characters)
        const extension = domain.split('.').pop();
        if (extension.length < 2) {
            return false;
        }

        // Reject common fake domains
        const fakeDomains = ['test.com', 'example.com', 'temp.com', 'fake.com'];
        if (fakeDomains.includes(domain.toLowerCase())) {
            return false;
        }

        return true;
    },

    // Password strength validation
    isStrongPassword: function(password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
        const strongPasswordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        return strongPasswordRegex.test(password);
    },

    // Enhanced phone number validation (Nepal format)
    isValidNepalPhone: function(phone) {
        // Remove all non-numeric characters
        const cleanPhone = phone.replace(/\D/g, '');

        // Nepal phone numbers: exactly 10 digits starting with 98 or 97
        const phoneRegex = /^(98|97)\d{8}$/;

        // Must be exactly 10 digits and match pattern
        return cleanPhone.length === 10 && phoneRegex.test(cleanPhone);
    },

    // Name validation (no numbers or special characters)
    isValidName: function(name) {
        const nameRegex = /^[a-zA-Z\s]+$/;
        return nameRegex.test(name) && name.trim().length >= 2;
    },

    // Username validation (alphanumeric and underscore only)
    isValidUsername: function(username) {
        const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
        return usernameRegex.test(username);
    }
};

// Error display functions
const ErrorDisplay = {
    showError: function(field, message) {
        this.clearError(field);
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    },

    clearError: function(field) {
        field.classList.remove('error');
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
    },

    clearAllErrors: function(form) {
        const errorFields = form.querySelectorAll('.error');
        const errorMessages = form.querySelectorAll('.error-message');

        errorFields.forEach(field => field.classList.remove('error'));
        errorMessages.forEach(message => message.remove());
    }
};

// Real-time validation
const RealTimeValidation = {
    init: function() {
        // Email validation with enhanced checking
        const emailFields = document.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value && !ValidationUtils.isValidEmail(this.value)) {
                    ErrorDisplay.showError(this, 'Please enter a valid email address with proper domain');
                } else {
                    ErrorDisplay.clearError(this);
                }
            });

            // Also validate on input for immediate feedback
            field.addEventListener('input', function() {
                if (this.value.length > 0 && this.value.includes('@')) {
                    if (!ValidationUtils.isValidEmail(this.value)) {
                        this.setCustomValidity('Please enter a valid email address');
                    } else {
                        this.setCustomValidity('');
                    }
                }
            });
        });

        // Phone validation with enhanced checking
        const phoneFields = document.querySelectorAll('input[name="cellno"], input[name="phone"]');
        phoneFields.forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value && !ValidationUtils.isValidNepalPhone(this.value)) {
                    ErrorDisplay.showError(this, 'Please enter a valid Nepal phone number (98xxxxxxxx or 97xxxxxxxx)');
                } else {
                    ErrorDisplay.clearError(this);
                }
            });

            // Restrict input to numbers only
            field.addEventListener('input', function() {
                // Remove non-numeric characters
                let value = this.value.replace(/\D/g, '');

                // Limit to 10 digits
                if (value.length > 10) {
                    value = value.slice(0, 10);
                }

                this.value = value;

                // Set custom validity
                if (value.length > 0) {
                    if (!ValidationUtils.isValidNepalPhone(value)) {
                        this.setCustomValidity('Must be 10 digits starting with 98 or 97');
                    } else {
                        this.setCustomValidity('');
                    }
                }
            });
        });

        // Password validation
        const passwordFields = document.querySelectorAll('input[name="password"]');
        passwordFields.forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value) {
                    if (this.value.length < 6) {
                        ErrorDisplay.showError(this, 'Password must be at least 6 characters long');
                    } else if (!ValidationUtils.isStrongPassword(this.value)) {
                        ErrorDisplay.showError(this, 'Password should contain uppercase, lowercase, number and special character');
                    } else {
                        ErrorDisplay.clearError(this);
                    }
                }
            });
        });

        // Confirm password validation
        const confirmPasswordFields = document.querySelectorAll('input[name="cnf_password"]');
        confirmPasswordFields.forEach(field => {
            field.addEventListener('blur', function() {
                const passwordField = document.querySelector('input[name="password"]');
                if (this.value && passwordField && this.value !== passwordField.value) {
                    ErrorDisplay.showError(this, 'Passwords do not match');
                } else {
                    ErrorDisplay.clearError(this);
                }
            });
        });

        // Name validation
        const nameFields = document.querySelectorAll('input[name="fname"], input[name="lname"], input[name="name"]');
        nameFields.forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value && !ValidationUtils.isValidName(this.value)) {
                    ErrorDisplay.showError(this, 'Name should only contain letters and be at least 2 characters');
                } else {
                    ErrorDisplay.clearError(this);
                }
            });
        });

        // Username validation
        const usernameFields = document.querySelectorAll('input[name="username"]');
        usernameFields.forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value && !ValidationUtils.isValidUsername(this.value)) {
                    ErrorDisplay.showError(this, 'Username must be 3-20 characters, letters, numbers and underscore only');
                } else {
                    ErrorDisplay.clearError(this);
                }
            });
        });
    }
};

// Form validation
const FormValidation = {
    validateSignupForm: function(form) {
        let isValid = true;
        const errors = [];

        // Get form fields
        const fname = form.querySelector('input[name="fname"]');
        const lname = form.querySelector('input[name="lname"]');
        const username = form.querySelector('input[name="username"]');
        const email = form.querySelector('input[name="email"]');
        const phone = form.querySelector('input[name="cellno"]');
        const address = form.querySelector('textarea[name="address"]');
        const password = form.querySelector('input[name="password"]');
        const confirmPassword = form.querySelector('input[name="cnf_password"]');
        const level = form.querySelector('input[name="level"]:checked');

        // Clear previous errors
        ErrorDisplay.clearAllErrors(form);

        // Validate first name
        if (!fname.value.trim()) {
            ErrorDisplay.showError(fname, 'First name is required');
            isValid = false;
        } else if (!ValidationUtils.isValidName(fname.value)) {
            ErrorDisplay.showError(fname, 'First name should only contain letters');
            isValid = false;
        }

        // Validate last name
        if (!lname.value.trim()) {
            ErrorDisplay.showError(lname, 'Last name is required');
            isValid = false;
        } else if (!ValidationUtils.isValidName(lname.value)) {
            ErrorDisplay.showError(lname, 'Last name should only contain letters');
            isValid = false;
        }

        // Validate username
        if (!username.value.trim()) {
            ErrorDisplay.showError(username, 'Username is required');
            isValid = false;
        } else if (!ValidationUtils.isValidUsername(username.value)) {
            ErrorDisplay.showError(username, 'Username must be 3-20 characters, letters, numbers and underscore only');
            isValid = false;
        }

        // Validate email
        if (!email.value.trim()) {
            ErrorDisplay.showError(email, 'Email is required');
            isValid = false;
        } else if (!ValidationUtils.isValidEmail(email.value)) {
            ErrorDisplay.showError(email, 'Please enter a valid email address');
            isValid = false;
        }

        // Validate phone
        if (!phone.value.trim()) {
            ErrorDisplay.showError(phone, 'Phone number is required');
            isValid = false;
        } else if (!ValidationUtils.isValidNepalPhone(phone.value)) {
            ErrorDisplay.showError(phone, 'Please enter a valid Nepal phone number (98xxxxxxxx or 97xxxxxxxx)');
            isValid = false;
        }

        // Validate address
        if (!address.value.trim()) {
            ErrorDisplay.showError(address, 'Address is required');
            isValid = false;
        } else if (address.value.trim().length < 10) {
            ErrorDisplay.showError(address, 'Please provide a detailed address (at least 10 characters)');
            isValid = false;
        }

        // Validate password
        if (!password.value) {
            ErrorDisplay.showError(password, 'Password is required');
            isValid = false;
        } else if (password.value.length < 6) {
            ErrorDisplay.showError(password, 'Password must be at least 6 characters long');
            isValid = false;
        } else if (!ValidationUtils.isStrongPassword(password.value)) {
            ErrorDisplay.showError(password, 'Password should contain uppercase, lowercase, number and special character');
            isValid = false;
        }

        // Validate confirm password
        if (!confirmPassword.value) {
            ErrorDisplay.showError(confirmPassword, 'Please confirm your password');
            isValid = false;
        } else if (password.value !== confirmPassword.value) {
            ErrorDisplay.showError(confirmPassword, 'Passwords do not match');
            isValid = false;
        }

        // Validate user level
        if (!level) {
            const levelSection = form.querySelector('input[name="level"]').closest('tr');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = 'Please select a user type';
            levelSection.appendChild(errorDiv);
            isValid = false;
        }

        return isValid;
    },

    validateSigninForm: function(form) {
        let isValid = true;

        const email = form.querySelector('input[name="email"]');
        const password = form.querySelector('input[name="password"]');

        // Clear previous errors
        ErrorDisplay.clearAllErrors(form);

        // Validate email
        if (!email.value.trim()) {
            ErrorDisplay.showError(email, 'Email is required');
            isValid = false;
        } else if (!ValidationUtils.isValidEmail(email.value)) {
            ErrorDisplay.showError(email, 'Please enter a valid email address');
            isValid = false;
        }

        // Validate password
        if (!password.value) {
            ErrorDisplay.showError(password, 'Password is required');
            isValid = false;
        }

        return isValid;
    },

    validateContactForm: function(form) {
        let isValid = true;

        const name = form.querySelector('input[name="name"]');
        const phone = form.querySelector('input[name="phone"]');
        const email = form.querySelector('input[name="email"]');
        const message = form.querySelector('textarea[name="message"]');

        // Clear previous errors
        ErrorDisplay.clearAllErrors(form);

        // Validate name
        if (!name.value.trim()) {
            ErrorDisplay.showError(name, 'Name is required');
            isValid = false;
        } else if (!ValidationUtils.isValidName(name.value)) {
            ErrorDisplay.showError(name, 'Name should only contain letters');
            isValid = false;
        }

        // Validate phone
        if (!phone.value.trim()) {
            ErrorDisplay.showError(phone, 'Phone number is required');
            isValid = false;
        } else if (!ValidationUtils.isValidNepalPhone(phone.value)) {
            ErrorDisplay.showError(phone, 'Please enter a valid Nepal phone number');
            isValid = false;
        }

        // Validate email
        if (!email.value.trim()) {
            ErrorDisplay.showError(email, 'Email is required');
            isValid = false;
        } else if (!ValidationUtils.isValidEmail(email.value)) {
            ErrorDisplay.showError(email, 'Please enter a valid email address');
            isValid = false;
        }

        // Validate message
        if (!message.value.trim()) {
            ErrorDisplay.showError(message, 'Message is required');
            isValid = false;
        } else if (message.value.trim().length < 10) {
            ErrorDisplay.showError(message, 'Message must be at least 10 characters long');
            isValid = false;
        }

        return isValid;
    },

    validateChangePasswordForm: function(form) {
        let isValid = true;

        const oldPassword = form.querySelector('input[name="oldpass"]');
        const newPassword = form.querySelector('input[name="newpass"]');
        const confirmPassword = form.querySelector('input[name="cnf_password"]');

        // Clear previous errors
        ErrorDisplay.clearAllErrors(form);

        // Validate old password
        if (!oldPassword.value) {
            ErrorDisplay.showError(oldPassword, 'Current password is required');
            isValid = false;
        }

        // Validate new password
        if (!newPassword.value) {
            ErrorDisplay.showError(newPassword, 'New password is required');
            isValid = false;
        } else if (newPassword.value.length < 6) {
            ErrorDisplay.showError(newPassword, 'New password must be at least 6 characters long');
            isValid = false;
        }

        // Validate confirm password
        if (!confirmPassword.value) {
            ErrorDisplay.showError(confirmPassword, 'Please confirm your new password');
            isValid = false;
        } else if (newPassword.value !== confirmPassword.value) {
            ErrorDisplay.showError(confirmPassword, 'Passwords do not match');
            isValid = false;
        }

        // Check if new password is different from old password
        if (oldPassword.value && newPassword.value && oldPassword.value === newPassword.value) {
            ErrorDisplay.showError(newPassword, 'New password must be different from current password');
            isValid = false;
        }

        return isValid;
    }
};

// Initialize validation when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize real-time validation
    RealTimeValidation.init();

    // Handle signup form submission - more specific selector
    const signupForm = document.querySelector('form button[name="signup"]') ? .closest('form');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            console.log('Signup form validation triggered');
            if (!FormValidation.validateSignupForm(this)) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Signup form validation failed - submission prevented');
                // Scroll to first error
                const firstError = this.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        });
    }

    // Handle signin form submission - more specific selector
    const signinForm = document.querySelector('form button[name="signin"]') ? .closest('form');
    if (signinForm) {
        signinForm.addEventListener('submit', function(e) {
            console.log('Signin form validation triggered');
            if (!FormValidation.validateSigninForm(this)) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Signin form validation failed - submission prevented');
                const firstError = this.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        });
    }

    // Handle contact form submission - more specific selector
    const contactForm = document.querySelector('form button[name="sendmessage"]') ? .closest('form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            console.log('Contact form validation triggered');
            if (!FormValidation.validateContactForm(this)) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Contact form validation failed - submission prevented');
                const firstError = this.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        });
    }

    // Handle change password form submission
    const changePasswordForm = document.querySelector('form button[name="updatepass"]') ? .closest('form');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(e) {
            console.log('Change password form validation triggered');
            if (!FormValidation.validateChangePasswordForm(this)) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Change password form validation failed - submission prevented');
                const firstError = this.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        });
    }

    // Password strength indicator
    const passwordField = document.querySelector('input[name="password"]');
    if (passwordField) {
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        passwordField.parentNode.appendChild(strengthIndicator);

        passwordField.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let strengthText = '';
            let strengthColor = '';

            if (password.length >= 6) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[@$!%*?&]/.test(password)) strength++;

            switch (strength) {
                case 0:
                case 1:
                    strengthText = 'Very Weak';
                    strengthColor = '#ff4444';
                    break;
                case 2:
                    strengthText = 'Weak';
                    strengthColor = '#ff8800';
                    break;
                case 3:
                    strengthText = 'Medium';
                    strengthColor = '#ffbb00';
                    break;
                case 4:
                    strengthText = 'Strong';
                    strengthColor = '#88cc00';
                    break;
                case 5:
                    strengthText = 'Very Strong';
                    strengthColor = '#00aa00';
                    break;
            }

            if (password.length > 0) {
                strengthIndicator.innerHTML = `<span style="color: ${strengthColor}">Password Strength: ${strengthText}</span>`;
            } else {
                strengthIndicator.innerHTML = '';
            }
        });
    }
});