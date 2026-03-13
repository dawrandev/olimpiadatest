document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const perPageSelect = document.getElementById("perPage");

    let searchTimeout;

    perPageSelect.addEventListener("change", function () {
        const url = new URL(window.location);
        url.searchParams.set("per_page", this.value);

        const currentSearch = searchInput.value;
        if (currentSearch) {
            url.searchParams.set("search", currentSearch);
        }

        url.searchParams.delete("page");

        window.location.href = url.toString();
    });

    searchInput.addEventListener("input", function () {
        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(() => {
            performServerSearch();
        }, 800);
    });

    searchInput.addEventListener("keypress", function (e) {
        if (e.key === "Enter") {
            clearTimeout(searchTimeout);
            performServerSearch();
        }
    });

    function performServerSearch() {
        const url = new URL(window.location);
        const searchValue = searchInput.value.trim();

        if (searchValue) {
            url.searchParams.set("search", searchValue);
        } else {
            url.searchParams.delete("search");
        }

        url.searchParams.set("per_page", perPageSelect.value);

        url.searchParams.delete("page");

        window.location.href = url.toString();
    }
});
