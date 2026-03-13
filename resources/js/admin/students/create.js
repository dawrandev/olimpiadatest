document.addEventListener("DOMContentLoaded", function () {
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

    // Faculty change event - load related groups
    if (facultySelect && groupSelect) {
        facultySelect.addEventListener("change", function () {
            const facultyId = this.value;

            groupSelect.innerHTML =
                '<option value="">' + window.__("Loading...") + "</option>";
            groupSelect.disabled = true;

            if (facultyId) {
                const allGroups = window.allGroups || [];
                const filteredGroups = allGroups.filter(
                    (group) => group.faculty_id == facultyId
                );

                groupSelect.innerHTML =
                    '<option value="">' +
                    window.__("Select Group") +
                    "</option>";

                if (filteredGroups.length > 0) {
                    filteredGroups.forEach((group) => {
                        const option = document.createElement("option");
                        option.value = group.id;
                        option.textContent = group.name;

                        if (group.id == window.oldGroupId) {
                            option.selected = true;
                        }

                        groupSelect.appendChild(option);
                    });
                    groupSelect.disabled = false;
                } else {
                    groupSelect.innerHTML =
                        '<option value="">' +
                        window.__("No groups available") +
                        "</option>";
                }
            } else {
                groupSelect.innerHTML =
                    '<option value="">' +
                    window.__("First select faculty") +
                    "</option>";
                groupSelect.disabled = true;
            }
        });
    }

    // Password visibility toggle
    if (togglePassword && password && eyeIcon) {
        togglePassword.addEventListener("click", function () {
            const type =
                password.getAttribute("type") === "password"
                    ? "text"
                    : "password";
            password.setAttribute("type", type);
            eyeIcon.className =
                type === "text"
                    ? "icofont icofont-eye-blocked"
                    : "icofont icofont-eye";
        });
    }

    if (togglePasswordConfirm && passwordConfirm && eyeIconConfirm) {
        togglePasswordConfirm.addEventListener("click", function () {
            const type =
                passwordConfirm.getAttribute("type") === "password"
                    ? "text"
                    : "password";
            passwordConfirm.setAttribute("type", type);
            eyeIconConfirm.className =
                type === "text"
                    ? "icofont icofont-eye-blocked"
                    : "icofont icofont-eye";
        });
    }

    // Password match validation
    if (password && passwordConfirm) {
        const validateMatch = () => {
            if (password.value !== passwordConfirm.value) {
                passwordConfirm.setCustomValidity(
                    window.__("Passwords do not match")
                );
            } else {
                passwordConfirm.setCustomValidity("");
            }
        };

        passwordConfirm.addEventListener("input", validateMatch);
        password.addEventListener("input", validateMatch);
    }

    // Trigger faculty change if old value exists
    if (facultySelect && facultySelect.value) {
        facultySelect.dispatchEvent(new Event("change"));
    }
});
