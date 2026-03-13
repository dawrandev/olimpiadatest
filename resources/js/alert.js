document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".confirm-action").forEach((button) => {
        button.addEventListener("click", function (e) {
            e.preventDefault();
            const form = this.closest("form");
            const actionType = this.dataset.action || "delete";
            const customTitle = this.dataset.title;
            const customText = this.dataset.text;

            // window.alertTranslations dan foydalanamiz
            let title = customTitle || window.alertTranslations.areYouSure;
            let text = customText || window.alertTranslations.cannotUndo;
            let icon = "warning";
            let confirmText = window.alertTranslations.yesConfirm;

            if (actionType === "delete") {
                icon = "error";
                confirmText = window.alertTranslations.yesDelete;
            } else if (actionType === "update") {
                icon = "question";
                confirmText = window.alertTranslations.yesUpdate;
            } else if (actionType === "toggle") {
                icon = "info";
                confirmText = window.alertTranslations.yesConfirm;
            }

            Swal.fire({
                title,
                text,
                icon,
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: confirmText,
                cancelButtonText: window.alertTranslations.cancel,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
