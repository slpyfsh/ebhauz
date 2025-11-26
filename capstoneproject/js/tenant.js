/* --- tenant.js (full) --- */
let lastScrollTop = 0;

function toggleMenu() {
  document.getElementById("sidebar").classList.toggle("show");
  document.getElementById("overlay").classList.toggle("show");
}

window.addEventListener("scroll", () => {
  const header = document.querySelector("header");
  let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  if (scrollTop > lastScrollTop) header.style.top = "-100px";
  else header.style.top = "0";
  lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
});

function toTitleCase(str) {
  return str
    .toLowerCase()
    .replace(/\b\w/g, (char) => char.toUpperCase());
}

const addTenantBtn = document.getElementById("addTenantBtn");
const toggleEditBtn = document.getElementById("toggleEditBtn");
const popupForm = document.getElementById("popupForm");
const cancelBtn = document.getElementById("cancelBtn");
const submitBtn = document.getElementById("submitBtn");
const formTitle = document.getElementById("formTitle");

const studentId = document.getElementById("studentId");
const firstName = document.getElementById("firstName");
const middleName = document.getElementById("middleName");
const lastName = document.getElementById("lastName");
const guardianName = document.getElementById("guardianName");
const guardianContact = document.getElementById("guardianContact");

const studentIdError = document.getElementById("studentIdError");
const firstNameError = document.getElementById("firstNameError");
const middleNameError = document.getElementById("middleNameError");
const lastNameError = document.getElementById("lastNameError");
const guardianNameError = document.getElementById("guardianNameError");
const guardianContactError = document.getElementById("guardianContactError");

const tenantListContainer = document.getElementById("tenantListContainer");

const alertPopup = document.getElementById("alertPopup");
const confirmModal = document.getElementById("confirmModal");
const confirmMessage = document.getElementById("confirmMessage");
const confirmCancelBtn = document.getElementById("confirmCancelBtn");
const confirmOkBtn = document.getElementById("confirmOkBtn");

let tenants = [];
let editMode = false;
let pendingDeleteId = null;

addTenantBtn.addEventListener("click", () => {
  openAddForm();
});

function openAddForm() {
  clearForm();
  delete popupForm.dataset.editingStudId;
  submitBtn.textContent = "Add";
  formTitle.textContent = "Add Tenant";
  popupForm.classList.add("show");
}

cancelBtn.addEventListener("click", () => {
  clearForm();
  popupForm.classList.remove("show");
  delete popupForm.dataset.editingStudId;
});

const namePattern = /^[a-zA-Z\s]+$/;
const guardianNamePattern = /^[a-zA-Z\s]+(\.)?[a-zA-Z\s]*$/;
const numberPattern = /^[0-9]+$/;
const contactPattern = /^09\d{9}$/;

function validateInput(input, errorEl, pattern, errorMsg) {
  const value = input.value.trim();
  if (value === "") {
    if (input) input.classList.add("error");
    if (errorEl) errorEl.textContent = "This field is required.";
    return false;
  }
  if (pattern && !pattern.test(value)) {
    if (input) input.classList.add("error");
    if (errorEl) errorEl.textContent = errorMsg;
    return false;
  }
  if (input) input.classList.remove("error");
  if (errorEl) errorEl.textContent = "";
  return true;
}

function validateGuardianContact(input, errorEl) {
  const value = input.value.trim();
  if (value === "") {
    if (input) input.classList.add("error");
    if (errorEl) errorEl.textContent = "This field is required.";
    return false;
  }
  if (!numberPattern.test(value)) {
    if (input) input.classList.add("error");
    if (errorEl) errorEl.textContent = "Field can only contain numbers.";
    return false;
  }
  if (value.length !== 11) {
    if (input) input.classList.add("error");
    if (errorEl) errorEl.textContent = "Contact number must be exactly 11 digits.";
    return false;
  }
  if (!contactPattern.test(value)) {
    if (input) input.classList.add("error");
    if (errorEl) errorEl.textContent = "Field can only contain valid contact number.";
    return false;
  }
  if (input) input.classList.remove("error");
  if (errorEl) errorEl.textContent = "";
  return true;
}

function validateAll() {
  const validStudentId = validateInput(studentId, studentIdError, numberPattern, "Field can only contain numbers.");
  const validFirstName = validateInput(firstName, firstNameError, namePattern, "Only letters and spaces allowed.");
  const validMiddleName = validateInput(middleName, middleNameError, namePattern, "Only letters and spaces allowed.");
  const validLastName = validateInput(lastName, lastNameError, namePattern, "Only letters and spaces allowed.");
  const validGuardianName = validateInput(guardianName, guardianNameError, guardianNamePattern, "Only letters, spaces, and one period (.) allowed.");
  const validGuardianContact = validateGuardianContact(guardianContact, guardianContactError);

  return validStudentId && validFirstName && validMiddleName && validLastName && validGuardianName && validGuardianContact;
}

function addLiveValidation(input, errorEl, pattern, errorMsg, customValidator) {
  input.addEventListener("input", () => {
    if (customValidator) {
      customValidator(input, errorEl);
    } else {
      validateInput(input, errorEl, pattern, errorMsg);
    }
  });
}

addLiveValidation(studentId, studentIdError, numberPattern, "Field can only contain numbers.");
addLiveValidation(firstName, firstNameError, namePattern, "Only letters and spaces allowed.");
addLiveValidation(middleName, middleNameError, namePattern, "Only letters and spaces allowed.");
addLiveValidation(lastName, lastNameError, namePattern, "Only letters and spaces allowed.");
addLiveValidation(guardianName, guardianNameError, guardianNamePattern, "Only letters, spaces, and one period (.) allowed.");
addLiveValidation(guardianContact, guardianContactError, null, null, validateGuardianContact);

function showAlert(message, duration = 3000) {
  alertPopup.textContent = message;
  alertPopup.classList.add("show");
  setTimeout(() => alertPopup.classList.remove("show"), duration);
}

function clearForm() {
  studentId.value = "";
  firstName.value = "";
  middleName.value = "";
  lastName.value = "";
  guardianName.value = "";
  guardianContact.value = "";

  studentIdError.textContent = "";
  firstNameError.textContent = "";
  middleNameError.textContent = "";
  lastNameError.textContent = "";
  guardianNameError.textContent = "";
  guardianContactError.textContent = "";

  // remove error classes from inputs
  [studentId, firstName, middleName, lastName, guardianName, guardianContact].forEach((el) => {
    if (el) el.classList.remove("error");
  });

  submitBtn.textContent = "Add";
  formTitle.textContent = "Add Tenant";
  delete popupForm.dataset.editingStudId;
}

function fetchTenants() {
  fetch("php/get_tenant.php")
    .then((res) => res.json())
    .then((data) => {
      if (Array.isArray(data)) {
        tenants = data;
        renderTenantDropdowns(data);
      } else {
        tenantListContainer.innerHTML = "<p>No tenants found.</p>";
      }
    })
    .catch(() => {
      tenantListContainer.innerHTML = "<p>Error loading tenants.</p>";
    });
}

function daysSince(dateString) {
  if (!dateString) return Infinity;
  const then = new Date(dateString);
  if (isNaN(then)) return Infinity;
  const now = new Date();
  const diff = (now - then) / (1000 * 60 * 60 * 24);
  return diff;
}

function escapeHtml(str = "") {
  return String(str)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

function renderTenantDropdowns(tenantsArray) {
  if (!tenantsArray.length) {
    tenantListContainer.innerHTML = "<p>No tenants found.</p>";
    return;
  }

  tenantListContainer.innerHTML = tenantsArray
    .map((t) => {
      const disabled = t.last_notified && daysSince(t.last_notified) < 30;
      const buttonClass = disabled ? "notify-btn disabled" : "notify-btn";
      const buttonText = disabled ? "Notified" : "Notify";
      const lastNotifiedText = t.last_notified ? new Date(t.last_notified).toLocaleDateString() : "Never";

      return `
      <div class="tenant-dropdown">
        <div class="tenant-btn" role="button" tabindex="0" data-studid="${escapeHtml(t.stud_id)}">
          <div class="left-section">
            <span class="trash-btn" data-studid="${escapeHtml(t.stud_id)}" title="Delete tenant">&#128465;</span>
            <span class="tenant-name">${toTitleCase(escapeHtml(t.stud_first_name))} ${toTitleCase(escapeHtml(t.stud_mid_name))} ${toTitleCase(escapeHtml(t.stud_last_name))}</span>
          </div>
          <span class="right-controls">
            <button class="${buttonClass}" 
              data-studid="${escapeHtml(t.stud_id)}" 
              data-guarcontact="${escapeHtml(t.guar_cont_no)}"
              ${disabled ? "disabled title='Can only notify again after 30 days'" : "title='Send SMS to guardian'"}
              type="button">${buttonText}</button>
            <button class="rent-toggle-btn ${t.rent_stat === 'yes' ? 'paid' : 'not-paid'}" data-studid="${escapeHtml(t.stud_id)}" type="button">
              ${t.rent_stat === 'yes' ? 'PAID' : 'NOT PAID'}
            </button>
            <span class="caret"></span>
          </span>
        </div>
        <div class="tenant-details">
          <p><strong>Student ID:</strong> ${escapeHtml(t.stud_id)}</p>
          <p><strong>Guardian Name:</strong> ${toTitleCase(escapeHtml(t.guar_name))}</p>
          <p><strong>Guardian Contact:</strong> ${escapeHtml(t.guar_cont_no)}</p>
          <p class="last-notified"><strong>Last Notified:</strong> ${lastNotifiedText}</p>
        </div>
      </div>
    `;
    })
    .join("");

  attachTenantEvents();
}

function attachTenantEvents() {
  const tenantButtons = document.querySelectorAll(".tenant-btn");
  tenantButtons.forEach((btn) => {
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
  });

  const freshTenantButtons = document.querySelectorAll(".tenant-btn");
  freshTenantButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      if (editMode) return;
      const details = btn.nextElementSibling;
      const isOpen = details.classList.contains("open");

      document.querySelectorAll(".tenant-details").forEach((d) => d.classList.remove("open"));
      document.querySelectorAll(".tenant-btn").forEach((b) => b.classList.remove("active"));
      document.querySelectorAll(".tenant-dropdown").forEach((td) => td.classList.remove("tenant-open"));

      if (!isOpen) {
        details.classList.add("open");
        btn.classList.add("active");
        btn.closest(".tenant-dropdown").classList.add("tenant-open");
      }
    });

    btn.addEventListener("keydown", (ev) => {
      if (ev.key === "Enter" || ev.key === " ") {
        ev.preventDefault();
        btn.click();
      }
    });
  });

  const rentToggleButtons = document.querySelectorAll(".rent-toggle-btn");
  rentToggleButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const studId = btn.dataset.studid;
      const currentStatus = btn.classList.contains("paid") ? "yes" : "no";
      const newStatus = currentStatus === "yes" ? "no" : "yes";

      fetch("php/update_rent_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ studId, rentStat: newStatus }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            btn.classList.toggle("paid");
            btn.classList.toggle("not-paid");
            btn.textContent = newStatus === "yes" ? "PAID" : "NOT PAID";
            showAlert(`Rent status updated to ${newStatus.toUpperCase()}`);
          } else {
            showAlert("Failed to update rent status");
          }
        })
        .catch(() => {
          showAlert("Error updating rent status");
        });
    });
  });

  // Notify buttons
  document.querySelectorAll(".notify-btn:not(.disabled)").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const studId = btn.dataset.studid;
      const contact = btn.dataset.guarcontact;

      fetch("php/notify_guardian.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ studId, contact }),
      })
        .then((res) => res.json())
        .then((data) => {
          // show server-provided message
          showAlert(data.message || "No response message");

          // If backend says success and returns last_notified -> disable + update UI
          if (data.success) {
            btn.classList.add("disabled");
            btn.disabled = true;
            btn.textContent = "Notified";
            btn.title = "Can only notify again after 30 days";

            // Update the Last Notified element in the tenant details
            const tenantDiv = btn.closest(".tenant-dropdown");
            if (tenantDiv && data.last_notified) {
              const details = tenantDiv.querySelector(".tenant-details");
              if (details) {
                let el = details.querySelector(".last-notified");
                const pretty = new Date(data.last_notified).toLocaleDateString();
                if (el) {
                  el.innerHTML = `<strong>Last Notified:</strong> ${pretty}`;
                } else {
                  const p = document.createElement("p");
                  p.className = "last-notified";
                  p.innerHTML = `<strong>Last Notified:</strong> ${pretty}`;
                  details.appendChild(p);
                }
              }
            }
          } else {
            // If server returns last_notified even for failure (cooldown), respect it and disable the button
            if (data.last_notified) {
              const days = daysSince(data.last_notified);
              if (days < 30) {
                btn.classList.add("disabled");
                btn.disabled = true;
                btn.textContent = "Notified";
                btn.title = "Can only notify again after 30 days";
                // update UI
                const tenantDiv = btn.closest(".tenant-dropdown");
                if (tenantDiv) {
                  const details = tenantDiv.querySelector(".tenant-details");
                  if (details) {
                    let el = details.querySelector(".last-notified");
                    const pretty = new Date(data.last_notified).toLocaleDateString();
                    if (el) el.innerHTML = `<strong>Last Notified:</strong> ${pretty}`;
                    else {
                      const p = document.createElement("p");
                      p.className = "last-notified";
                      p.innerHTML = `<strong>Last Notified:</strong> ${pretty}`;
                      details.appendChild(p);
                    }
                  }
                }
              }
            } else {
              // If provider returned provider_response, log for debugging
              if (data.provider_response) console.warn('Provider response:', data.provider_response);
              if (data.raw_response) console.warn('Raw response:', data.raw_response);
            }
          }
        })
        .catch((err) => {
          console.error('Notify fetch error', err);
          showAlert("Error sending SMS notification");
        });
    });
  });

  const trashBtns = document.querySelectorAll(".trash-btn");
  trashBtns.forEach((t) => {
    t.addEventListener("click", (e) => {
      e.stopPropagation();
      const studId = t.dataset.studid;
      const tenantDiv = t.closest(".tenant-dropdown");
      const nameEl = tenantDiv ? tenantDiv.querySelector(".tenant-name") : null;
      const displayName = nameEl ? nameEl.textContent.trim() : studId;
      openConfirmDelete(studId, displayName);
    });
  });

  updateCaretAndPencil();
}

toggleEditBtn.addEventListener("click", () => {
  editMode = !editMode;
  toggleEditBtn.textContent = editMode ? "Cancel" : "Edit";

  if (editMode) {
    document.querySelectorAll(".tenant-details").forEach((d) => d.classList.remove("open"));
    document.querySelectorAll(".tenant-btn").forEach((b) => b.classList.remove("active"));
    document.querySelectorAll(".tenant-dropdown").forEach((td) => td.classList.remove("tenant-open"));
  }

  tenantListContainer.classList.toggle("edit-mode", editMode);

  updateCaretAndPencil();
});

function updateCaretAndPencil() {
  document.querySelectorAll(".tenant-btn").forEach((tenantBtn) => {
    const rightControls = tenantBtn.querySelector(".right-controls");
    const caret = rightControls ? rightControls.querySelector(".caret") : null;
    let pencil = rightControls ? rightControls.querySelector(".edit-pencil-btn") : null;

    if (editMode) {
      if (caret) caret.style.display = "none";
      if (!pencil && rightControls) {
        pencil = document.createElement("span");
        pencil.className = "edit-pencil-btn";
        pencil.title = "Edit Tenant";
        pencil.innerHTML = "&#9998;";
        pencil.addEventListener("click", (e) => {
          e.stopPropagation();
          const tenantDiv = pencil.closest(".tenant-dropdown");
          const studId = tenantDiv ? tenantDiv.querySelector(".tenant-btn").dataset.studid : null;
          if (studId) openEditForm(studId);
        });
        rightControls.appendChild(pencil);
      }
    } else {
      if (caret) caret.style.display = "inline-block";
      if (pencil) pencil.remove();
    }
  });
}

function openEditForm(studId) {
  const tenant = tenants.find((t) => String(t.stud_id) === String(studId));
  if (!tenant) {
    showAlert("Tenant data not found");
    return;
  }

  studentId.value = tenant.stud_id;
  firstName.value = toTitleCase(tenant.stud_first_name);
  middleName.value = toTitleCase(tenant.stud_mid_name);
  lastName.value = toTitleCase(tenant.stud_last_name);
  guardianName.value = toTitleCase(tenant.guar_name);
  guardianContact.value = tenant.guar_cont_no;

  [studentId, firstName, middleName, lastName, guardianName, guardianContact].forEach((el) => {
    if (el) el.classList.remove("error");
  });
  [studentIdError, firstNameError, middleNameError, lastNameError, guardianNameError, guardianContactError].forEach((se) => {
    if (se) se.textContent = "";
  });

  popupForm.classList.add("show");
  popupForm.dataset.editingStudId = tenant.stud_id;
  submitBtn.textContent = "Update";
  formTitle.textContent = "Edit Tenant";
}

submitBtn.addEventListener("click", (e) => {
  e.preventDefault();
  if (!validateAll()) return;

  const fd = new FormData();
  fd.append("studentId", studentId.value.trim());
  fd.append("firstName", toTitleCase(firstName.value.trim()));
  fd.append("middleName", toTitleCase(middleName.value.trim()));
  fd.append("lastName", toTitleCase(lastName.value.trim()));
  fd.append("guardianName", toTitleCase(guardianName.value.trim()));
  fd.append("guardianContact", guardianContact.value.trim());

  const editingStudId = popupForm.dataset.editingStudId;

  if (editingStudId) {
    fd.append("originalStudId", editingStudId);

    fetch("php/update_tenant.php", {
      method: "POST",
      body: fd,
    })
      .then((res) => res.json())
      .then((data) => {
        showAlert(data.message);
        if (data.success) {
          clearForm();
          popupForm.classList.remove("show");
          fetchTenants();
        }
      })
      .catch(() => {
        showAlert("Error connecting to server");
      });
  } else {
    fetch("php/add_tenant.php", {
      method: "POST",
      body: fd,
    })
      .then((res) => res.json())
      .then((data) => {
        showAlert(data.message);
        if (data.success) {
          clearForm();
          popupForm.classList.remove("show");
          fetchTenants();
        }
      })
      .catch(() => {
        showAlert("Error connecting to server");
      });
  }
});

function openConfirmDelete(studId, displayName) {
  pendingDeleteId = studId;
  confirmMessage.textContent = `Delete ${displayName}? This action cannot be undone.`;
  confirmModal.classList.add("show");
  confirmModal.setAttribute("aria-hidden", "false");
  confirmOkBtn.focus();
}

function closeConfirmModal() {
  pendingDeleteId = null;
  confirmModal.classList.remove("show");
  confirmModal.setAttribute("aria-hidden", "true");
}

confirmCancelBtn.addEventListener("click", () => {
  closeConfirmModal();
});

confirmOkBtn.addEventListener("click", () => {
  if (!pendingDeleteId) {
    closeConfirmModal();
    return;
  }
  const fd = new FormData();
  fd.append("studId", pendingDeleteId);

  fetch("php/delete_tenant.php", {
    method: "POST",
    body: fd,
  })
    .then((res) => res.json())
    .then((data) => {
      closeConfirmModal();
      showAlert(data.message);
      if (data.success) {
        fetchTenants();
      }
    })
    .catch(() => {
      closeConfirmModal();
      showAlert("Error deleting tenant");
    });
});

window.addEventListener("keydown", (ev) => {
  if (ev.key === "Escape") {
    if (confirmModal.classList.contains("show")) closeConfirmModal();
    else if (popupForm.classList.contains("show")) {
      popupForm.classList.remove("show");
    }
  }
});

fetchTenants();
