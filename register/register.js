// register.js - Handles form validation and UI logic

document.addEventListener('DOMContentLoaded', function () {
  const passwordInput = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');
  const toggleIcon = document.getElementById('toggleIcon');
  const form = document.getElementById('registerForm');
  const nameInput = document.getElementById('name');
  const emailInput = document.getElementById('email');

  // Show/hide password
  togglePassword.addEventListener('click', function () {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    toggleIcon.textContent = isPassword ? 'Hide' : 'Show';
  });

  // Toast function
  function showToast(message) {
    const toastContainer = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.remove();
    }, 3000);
  }

  // Password validation
  function validatePassword(password) {
    const errors = [];
    if (password.length < 8) {
      errors.push('Password must be at least 8 characters.');
    }
    if (!/[a-z]/.test(password)) {
      errors.push('Password must contain a lowercase letter.');
    }
    if (!/[A-Z]/.test(password)) {
      errors.push('Password must contain an uppercase letter.');
    }
    if (!/[0-9]/.test(password)) {
      errors.push('Password must contain a number.');
    }
    if (!/[^A-Za-z0-9]/.test(password)) {
      errors.push('Password must contain a symbol.');
    }
    return errors;
  }

  // Email validation
  function validateEmail(email) {
    // Basic email format check
    const emailPattern = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
    if (!emailPattern.test(email)) {
      return 'Please enter a valid email address.';
    }
    // Accept common domains, block random alphabet-only domains
    const allowedDomains = [
      'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com',
      'aol.com', 'mail.com', 'protonmail.com', 'zoho.com', 'gmx.com', 'yandex.com',
      'live.com', 'msn.com', 'comcast.net', 'me.com', 'mac.com', 'ymail.com', 'rocketmail.com'
    ];
    const parts = email.split('@');
    const localPart = parts[0];
    const domain = parts[1]?.toLowerCase();
    if (!domain) return 'Please enter a valid email address.';
    if (localPart.length < 4) {
      return 'The part before @ must be at least 4 characters.';
    }
    if (allowedDomains.includes(domain)) return '';
    return 'Only common email providers are allowed (e.g., gmail.com, yahoo.com, etc).';
  }

  // Form validation
  form.addEventListener('submit', function (e) {
    let hasError = false;
    // Name validation
    if (nameInput.value.trim() === '') {
      showToast('Name cannot be empty.');
      hasError = true;
    }
    // Email validation
    const emailError = validateEmail(emailInput.value.trim());
    if (emailError) {
      showToast(emailError);
      hasError = true;
    }
    // Password validation
    const passwordErrors = validatePassword(passwordInput.value);
    if (passwordErrors.length > 0) {
      passwordErrors.forEach(msg => showToast(msg));
      hasError = true;
    }
    if (hasError) {
      e.preventDefault();
    }
  });
});
