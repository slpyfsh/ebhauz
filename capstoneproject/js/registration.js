function debounce(func, wait) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

const usernamePattern = /^[a-zA-Z0-9_]+$/;
const namePattern = /^[a-zA-Z ]+$/;
const contactPattern = /^09\d{9}$/;

const usernameInput = document.getElementById("username");
const passwordInput = document.getElementById("password");
const confirmPasswordInput = document.getElementById("confirmPassword");
const firstNameInput = document.getElementById("firstName");
const midNameInput = document.getElementById("midName");
const lastNameInput = document.getElementById("lastName");
const addressInput = document.getElementById("address");
const contactNumberInput = document.getElementById("contactNumber");
const permitNumberInput = document.getElementById("permitNumber");
const bhNameInput = document.getElementById("bhName");
const bhAddressInput = document.getElementById("bhAddress");

const usernameError = document.getElementById("usernameError");
const passwordError = document.getElementById("passwordError");
const confirmPasswordError = document.getElementById("confirmPasswordError");
const firstNameError = document.getElementById("firstNameError");
const midNameError = document.getElementById("midNameError");
const lastNameError = document.getElementById("lastNameError");
const addressError = document.getElementById("addressError");
const contactNumberError = document.getElementById("contactNumberError");
const permitNumberError = document.getElementById("permitNumberError");
const bhNameError = document.getElementById("bhNameError");
const bhAddressError = document.getElementById("bhAddressError");

function capitalizeWords(str) {
  return str.replace(/\b\w/g, c => c.toUpperCase());
}

function validateUsername() {
  const username = usernameInput.value.trim();
  if (username === "") {
    usernameError.textContent = "Username is required.";
    usernameInput.classList.add("invalid");
    return false;
  }
  if (!usernamePattern.test(username)) {
    usernameError.textContent = "Only letters, numbers, and underscores allowed.";
    usernameInput.classList.add("invalid");
    return false;
  }
  usernameError.textContent = "Checking availability...";
  usernameInput.classList.remove("invalid");
  checkUsernameAvailability(username).then(exists => {
    if (exists) {
      usernameError.textContent = "Username already taken.";
      usernameInput.classList.add("invalid");
    } else {
      usernameError.textContent = "";
      usernameInput.classList.remove("invalid");
    }
  }).catch(() => {
    usernameError.textContent = "Error checking username.";
    usernameInput.classList.add("invalid");
  });
  return true;
}

const debouncedValidateUsername = debounce(validateUsername, 600);

function validatePermitNumberLive() {
  const permit = permitNumberInput.value.trim();
  if (permit === "") {
    permitNumberError.textContent = "Business Permit Number is required.";
    permitNumberInput.classList.add("invalid");
    return false;
  }
  permitNumberError.textContent = "Checking availability...";
  permitNumberInput.classList.remove("invalid");
  checkPermitAvailability(permit).then(exists => {
    if (exists) {
      permitNumberError.textContent = "This Business Permit Number is already registered.";
      permitNumberInput.classList.add("invalid");
    } else {
      permitNumberError.textContent = "";
      permitNumberInput.classList.remove("invalid");
    }
  }).catch(() => {
    permitNumberError.textContent = "Error checking Business Permit Number.";
    permitNumberInput.classList.add("invalid");
  });
  return true;
}

const debouncedValidatePermit = debounce(validatePermitNumberLive, 600);

function validateName(inputEl, errorEl, fieldName) {
  const val = inputEl.value.trim();
  if (val === "") {
    errorEl.textContent = `${fieldName} is required.`;
    inputEl.classList.add("invalid");
    return false;
  }
  if (!namePattern.test(val)) {
    errorEl.textContent = `${fieldName} can only contain letters.`;
    inputEl.classList.add("invalid");
    return false;
  }
  errorEl.textContent = "";
  inputEl.classList.remove("invalid");
  return true;
}

function validateAddress() {
  const val = addressInput.value.trim();
  if (val === "") {
    addressError.textContent = "Address is required.";
    addressInput.classList.add("invalid");
    return false;
  }
  addressError.textContent = "";
  addressInput.classList.remove("invalid");
  return true;
}

function validateContactNumber() {
  const val = contactNumberInput.value.trim();
  if (val === "") {
    contactNumberError.textContent = "Contact number is required.";
    contactNumberInput.classList.add("invalid");
    return false;
  }
  if (!contactPattern.test(val)) {
    contactNumberError.textContent = "Contact number must be 11 digits and start with 09.";
    contactNumberInput.classList.add("invalid");
    return false;
  }
  contactNumberError.textContent = "";
  contactNumberInput.classList.remove("invalid");
  return true;
}

function validatePermitNumber() {
  const val = permitNumberInput.value.trim();
  if (val === "") {
    permitNumberError.textContent = "Business Permit Number is required.";
    permitNumberInput.classList.add("invalid");
    return false;
  }
  if (permitNumberError.textContent !== "") {
    permitNumberInput.classList.add("invalid");
    return false;
  }
  permitNumberError.textContent = "";
  permitNumberInput.classList.remove("invalid");
  return true;
}

function validateBhName() {
  const val = bhNameInput.value.trim();
  if (val === "") {
    bhNameError.textContent = "Boarding House Name is required.";
    bhNameInput.classList.add("invalid");
    return false;
  }
  bhNameError.textContent = "";
  bhNameInput.classList.remove("invalid");
  return true;
}

function validateBhAddress() {
  const val = bhAddressInput.value.trim();
  if (val === "") {
    bhAddressError.textContent = "Boarding House Address is required.";
    bhAddressInput.classList.add("invalid");
    return false;
  }
  bhAddressError.textContent = "";
  bhAddressInput.classList.remove("invalid");
  return true;
}

document.querySelectorAll('.password-toggle').forEach(toggle => {
  toggle.addEventListener('click', () => {
    const input = toggle.previousElementSibling;
    input.type = input.type === 'password' ? 'text' : 'password';
  });
});

function validatePasswords() {
  const pass = passwordInput.value.trim();
  const confirmPass = confirmPasswordInput.value.trim();
  if (pass === "") {
    passwordError.textContent = "Password is required.";
    passwordInput.classList.add("invalid");
    return false;
  } else {
    passwordError.textContent = "";
    passwordInput.classList.remove("invalid");
  }
  if (confirmPass === "") {
    confirmPasswordError.textContent = "Confirm your password.";
    confirmPasswordInput.classList.add("invalid");
    return false;
  } else {
    confirmPasswordError.textContent = "";
    confirmPasswordInput.classList.remove("invalid");
  }
  if (pass !== confirmPass) {
    confirmPasswordError.textContent = "Passwords do not match.";
    confirmPasswordInput.classList.add("invalid");
    return false;
  }
  confirmPasswordError.textContent = "";
  confirmPasswordInput.classList.remove("invalid");
  return true;
}

function checkUsernameAvailability(username) {
  return fetch("php/check_username.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ username }),
  })
  .then(res => res.json())
  .then(data => data.exists === true)
  .catch(() => false);
}

function checkPermitAvailability(permit) {
  return fetch("php/check_permit.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ permitNumber: permit }),
  })
  .then(res => res.json())
  .then(data => data.exists === true)
  .catch(() => false);
}

function capitalizeInputs() {
  firstNameInput.value = capitalizeWords(firstNameInput.value.trim());
  midNameInput.value = capitalizeWords(midNameInput.value.trim());
  lastNameInput.value = capitalizeWords(lastNameInput.value.trim());
  addressInput.value = capitalizeWords(addressInput.value.trim());
  bhAddressInput.value = capitalizeWords(bhAddressInput.value.trim());
  bhNameInput.value = capitalizeWords(bhNameInput.value.trim());
}

usernameInput.addEventListener("input", debouncedValidateUsername);
permitNumberInput.addEventListener("input", debouncedValidatePermit);
firstNameInput.addEventListener("input", () => validateName(firstNameInput, firstNameError, "First Name"));
midNameInput.addEventListener("input", () => validateName(midNameInput, midNameError, "Middle Name"));
lastNameInput.addEventListener("input", () => validateName(lastNameInput, lastNameError, "Last Name"));
addressInput.addEventListener("input", validateAddress);
contactNumberInput.addEventListener("input", validateContactNumber);
bhNameInput.addEventListener("input", validateBhName);
bhAddressInput.addEventListener("input", validateBhAddress);
passwordInput.addEventListener("input", validatePasswords);
confirmPasswordInput.addEventListener("input", validatePasswords);

const form = document.getElementById("registrationForm");

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const validUsername = usernamePattern.test(usernameInput.value.trim()) && usernameError.textContent === "";
  const validFirstName = validateName(firstNameInput, firstNameError, "First Name");
  const validMidName = validateName(midNameInput, midNameError, "Middle Name");
  const validLastName = validateName(lastNameInput, lastNameError, "Last Name");
  const validAddress = validateAddress();
  const validContact = validateContactNumber();
  const validPermit = validatePermitNumber();
  const validBhName = validateBhName();
  const validBhAddress = validateBhAddress();
  const validPasswords = validatePasswords();

  if (!validUsername || !validFirstName || !validMidName || !validLastName || !validAddress || !validContact || !validPermit || !validBhName || !validBhAddress || !validPasswords) {
    if (!validUsername) usernameInput.focus();
    else if (!validFirstName) firstNameInput.focus();
    else if (!validMidName) midNameInput.focus();
    else if (!validLastName) lastNameInput.focus();
    else if (!validAddress) addressInput.focus();
    else if (!validContact) contactNumberInput.focus();
    else if (!validPermit) permitNumberInput.focus();
    else if (!validBhName) bhNameInput.focus();
    else if (!validBhAddress) bhAddressInput.focus();
    else if (!validPasswords) passwordInput.focus();
    return;
  }

  const policies = document.querySelectorAll(".policy-item");
  let allPoliciesSelected = true;

  policies.forEach((policy, index) => {
    const radios = policy.querySelectorAll("input[type=radio]");
    const checked = Array.from(radios).some(radio => radio.checked);
    if (!checked) {
      policy.classList.add("invalid-policy");
      allPoliciesSelected = false;
    } else {
      policy.classList.remove("invalid-policy");
    }
    const label = policy.querySelector(".policy-label");
    if (label) {
      label.textContent = `${index + 1}. ${label.textContent.replace(/^\d+\.\s*/, "")}`;
    }
  });

  if (!allPoliciesSelected) {
    return;
  }

  capitalizeInputs();

  form.submit();
});

document.addEventListener('DOMContentLoaded', () => {
  const policyLabels = document.querySelectorAll('.policy-label');
  policyLabels.forEach((label, index) => {
    label.textContent = `${index + 1}. ` + label.textContent.replace(/^\d+\.\s*/, '');
  });
});