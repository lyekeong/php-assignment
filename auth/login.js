// login.js - Handles login validation and UI logic

document.addEventListener('DOMContentLoaded', function () {
  const passwordInput = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');
  const toggleIcon = document.getElementById('toggleIcon');
  const form = document.getElementById('loginForm');
  const emailInput = document.getElementById('email');

  // Dummy user data
  const dummyUser = {
    email: 'test@gmail.com',
    password: 'Test@1234'
  };

  // Show/hide password
  togglePassword.addEventListener('click', function () {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    toggleIcon.textContent = isPassword ? 'Hide' : 'Show';
  });

  // Toast function
  function showToast(message, color = '#e74c3c') {
    const toastContainer = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    toast.style.background = color;
    toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.remove();
    }, 3000);
  }

  // Form validation
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const rememberMe = document.getElementById('rememberMe').checked;
    if (email === '' || password === '') {
      showToast('Email and password are required.');
      return;
    }
    if (email === dummyUser.email && password === dummyUser.password) {
      showToast('Login successful!', '#27ae60');
      // Set cookie for 28 days if rememberMe, else session cookie
      if (rememberMe) {
        document.cookie = `user_name=${encodeURIComponent(email.split('@')[0])}; max-age=${28*24*60*60}; path=/`;
      } else {
        document.cookie = `user_name=${encodeURIComponent(email.split('@')[0])}; path=/`;
      }
      setTimeout(() => {
        window.location.href = '../index.php?welcome=' + encodeURIComponent(email.split('@')[0]);
      }, 1200);
    } else {
      showToast('Invalid email or password.');
    }
  });
});
