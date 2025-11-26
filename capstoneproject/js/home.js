document.getElementById('addNewCard').addEventListener('click', openPopup);
document.getElementById('addNewCard').addEventListener('keypress', (e) => {
  if (e.key === 'Enter' || e.key === ' ') {
    openPopup();
  }
});

function openPopup() {
  document.getElementById('popupOverlay').style.display = 'block';
  document.getElementById('popupForm').style.display = 'block';
}

document.getElementById('cancelBtn').addEventListener('click', closePopup);
document.getElementById('popupOverlay').addEventListener('click', closePopup);

function closePopup() {
  document.getElementById('popupOverlay').style.display = 'none';
  document.getElementById('popupForm').style.display = 'none';
  clearFormErrors();
  document.getElementById('addBhForm').reset();
}

// Boarding house cards click (redirect placeholder)
document.querySelectorAll('.bh-card').forEach(card => {
  if (!card.classList.contains('add-new')) {
    card.addEventListener('click', () => {
      const permitNo = card.getAttribute('data-permit');
      // Placeholder redirect (future page)
      alert(`Redirect to boarding house page for permit_no: ${permitNo}`);
      // e.g. window.location.href = `boarding_house.php?permit=${permitNo}`;
    });

    card.addEventListener('keypress', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        card.click();
      }
    });
  }
});

const permitNumberInput = document.getElementById('permitNumber');
const bhNameInput = document.getElementById('bhName');
const bhAddressInput = document.getElementById('bhAddress');

const permitNumberError = document.getElementById('permitNumberError');
const bhNameError = document.getElementById('bhNameError');
const bhAddressError = document.getElementById('bhAddressError');

document.getElementById('addBhForm').addEventListener('submit', (e) => {
  e.preventDefault();

  let valid = true;

  if (permitNumberInput.value.trim() === '') {
    permitNumberError.textContent = 'Business Permit Number is required.';
    permitNumberInput.classList.add('invalid');
    valid = false;
  } else {
    permitNumberError.textContent = '';
    permitNumberInput.classList.remove('invalid');
  }

  if (bhNameInput.value.trim() === '') {
    bhNameError.textContent = 'Boarding House Name is required.';
    bhNameInput.classList.add('invalid');
    valid = false;
  } else {
    bhNameError.textContent = '';
    bhNameInput.classList.remove('invalid');
  }

  if (bhAddressInput.value.trim() === '') {
    bhAddressError.textContent = 'Boarding House Address is required.';
    bhAddressInput.classList.add('invalid');
    valid = false;
  } else {
    bhAddressError.textContent = '';
    bhAddressInput.classList.remove('invalid');
  }

  if (!valid) return;

  // Submit the form via fetch/ajax
  const formData = new FormData(e.target);

  fetch('php/add_boarding_house.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Boarding house added successfully.');
      closePopup();
      // Reload page or ideally update cards dynamically
      location.reload();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(() => alert('Server error. Please try again later.'));
});

function clearFormErrors() {
  permitNumberError.textContent = '';
  permitNumberInput.classList.remove('invalid');
  bhNameError.textContent = '';
  bhNameInput.classList.remove('invalid');
  bhAddressError.textContent = '';
  bhAddressInput.classList.remove('invalid');
}
