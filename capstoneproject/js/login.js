document.querySelectorAll('.password-toggle').forEach(toggle => {
  toggle.addEventListener('click', () => {
    const input = toggle.previousElementSibling;
    input.type = input.type === 'password' ? 'text' : 'password';
  });
});

const urlParams = new URLSearchParams(window.location.search);
const error = urlParams.get('error');
const fieldsParam = urlParams.get('fields');

const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const usernameError = document.getElementById('usernameError');
const passwordError = document.getElementById('passwordError');

if (error) {
  if (fieldsParam) {
    const fields = fieldsParam.split(',');
    fields.forEach(field => {
      if (field === 'username') {
        usernameError.textContent = error;
        usernameInput.classList.add('invalid');
      }
      if (field === 'password') {
        passwordError.textContent = error;
        passwordInput.classList.add('invalid');
      }
    });
  }
}

if (urlParams.get('registered') === '1') {
  alert('Registration successful! Please log in.');
}


