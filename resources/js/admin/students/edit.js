// resources/js/admin/student-form.js

document.addEventListener("DOMContentLoaded", function () {
    // Get configuration from window object (passed from blade)
    const config = window.studentFormConfig || {};

    // DOM elements
    const facultySelect = document.getElementById("faculty");
    const groupSelect = document.getElementById("group");
    const togglePassword = document.getElementById("togglePassword");
    const togglePasswordConfirm = document.getElementById(
        "togglePasswordConfirm"
    );
    const password = document.getElementById("password");
    const passwordConfirm = document.getElementById("password_confirmation");
    const eyeIcon = document.getElementById("eyeIcon");
    const eyeIconConfirm = document.getElementById("eyeIconConfirm");
    const studentForm =
        document.getElementById("studentEditForm") ||
        document.querySelector("form");

    // Check if required elements exist
    if (!facultySelect || !groupSelect) {
        console.error("Required form elements not found");
        return;
    }

    /**
     * Handle faculty change event
     */
    function handleFacultyChange() {
        const facultyId = facultySelect.value;

        // Reset group select
        groupSelect.innerHTML = `<option value="">${
            config.translations?.loading || "Loading..."
        }</option>`;
        groupSelect.disabled = true;

        if (facultyId && config.allGroups) {
            // Filter groups by faculty_id
            const filteredGroups = config.allGroups.filter(
                (group) => group.faculty_id == facultyId
            );

            groupSelect.innerHTML = `<option value="">${
                config.translations?.selectGroup || "Select Group"
            }</option>`;

            if (filteredGroups.length > 0) {
                filteredGroups.forEach((group) => {
                    const option = document.createElement("option");
                    option.value = group.id;
                    option.textContent = group.name;

                    // Select current group if editing
                    if (
                        config.currentGroupId &&
                        config.currentGroupId == group.id
                    ) {
                        option.selected = true;
                    }

                    groupSelect.appendChild(option);
                });
                groupSelect.disabled = false;
            } else {
                groupSelect.innerHTML = `<option value="">${
                    config.translations?.noGroupsAvailable ||
                    "No groups available"
                }</option>`;
            }
        } else {
            groupSelect.innerHTML = `<option value="">${
                config.translations?.firstSelectFaculty ||
                "First select faculty"
            }</option>`;
            groupSelect.disabled = true;
        }
    }

    /**
     * Toggle password visibility
     * @param {HTMLElement} inputElement
     * @param {HTMLElement} iconElement
     */
    function togglePasswordVisibility(inputElement, iconElement) {
        if (!inputElement || !iconElement) return;

        const currentType = inputElement.getAttribute("type");
        const newType = currentType === "password" ? "text" : "password";

        inputElement.setAttribute("type", newType);

        // Update icon
        if (newType === "text") {
            iconElement.className = "icofont icofont-eye-blocked";
        } else {
            iconElement.className = "icofont icofont-eye";
        }
    }

    /**
     * Validate password match
     */
    function validatePasswordMatch() {
        if (!password || !passwordConfirm) return;

        const passwordValue = password.value;
        const confirmValue = passwordConfirm.value;

        if (confirmValue && passwordValue !== confirmValue) {
            const errorMessage =
                config.translations?.passwordsDoNotMatch ||
                "Passwords do not match";
            passwordConfirm.setCustomValidity(errorMessage);
        } else {
            passwordConfirm.setCustomValidity("");
        }
    }

    /**
     * Reset form to initial state
     */
    function resetForm() {
        if (studentForm) {
            studentForm.reset();

            // Reset group select to initial state
            groupSelect.innerHTML = `<option value="">${
                config.translations?.firstSelectFaculty ||
                "First select faculty"
            }</option>`;
            groupSelect.disabled = true;

            // Reset password visibility
            if (password && eyeIcon) {
                password.setAttribute("type", "password");
                eyeIcon.className = "icofont icofont-eye";
            }

            if (passwordConfirm && eyeIconConfirm) {
                passwordConfirm.setAttribute("type", "password");
                eyeIconConfirm.className = "icofont icofont-eye";
            }
        }
    }

    // Event listeners
    if (facultySelect) {
        facultySelect.addEventListener("change", handleFacultyChange);
    }

    if (togglePassword && password && eyeIcon) {
        togglePassword.addEventListener("click", function (e) {
            e.preventDefault();
            togglePasswordVisibility(password, eyeIcon);
        });
    }

    if (togglePasswordConfirm && passwordConfirm && eyeIconConfirm) {
        togglePasswordConfirm.addEventListener("click", function (e) {
            e.preventDefault();
            togglePasswordVisibility(passwordConfirm, eyeIconConfirm);
        });
    }

    if (password) {
        password.addEventListener("input", validatePasswordMatch);
    }

    if (passwordConfirm) {
        passwordConfirm.addEventListener("input", validatePasswordMatch);
    }

    // Reset button functionality (if exists)
    const resetButton = document.querySelector('button[type="reset"]');
    if (resetButton && !config.isEditMode) {
        resetButton.addEventListener("click", function (e) {
            e.preventDefault();
            resetForm();
        });
    }

    // Form submission validation
    if (studentForm) {
        studentForm.addEventListener("submit", function (e) {
            // Validate password match before submission
            validatePasswordMatch();

            // Check if password confirmation has custom validity error
            if (passwordConfirm && !passwordConfirm.checkValidity()) {
                e.preventDefault();
                passwordConfirm.focus();
                return false;
            }
        });
    }

    // Initialize form on page load
    function initializeForm() {
        // Trigger faculty change if faculty is pre-selected (for edit mode)
        if (facultySelect.value) {
            handleFacultyChange();
        }

        // Set focus to first input
        const firstInput = studentForm?.querySelector(
            'input[type="text"]:not([readonly])'
        );
        if (firstInput) {
            firstInput.focus();
        }
    }

    // Initialize form
    initializeForm();

    // Export functions for potential external use
    window.studentFormHelpers = {
        handleFacultyChange,
        togglePasswordVisibility,
        validatePasswordMatch,
        resetForm,
        initializeForm,
    };
});
