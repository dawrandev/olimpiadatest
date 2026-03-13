document.querySelectorAll(".delete-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
        e.preventDefault();
        let form = this.closest("form");

        Swal.fire({
            title: "{{ __('Are you sure?') }}",
            text: "{{ __('This action cannot be undone!') }}",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "{{ __('Yes, delete it!') }}",
            cancelButtonText: "{{ __('Cancel') }}",
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
