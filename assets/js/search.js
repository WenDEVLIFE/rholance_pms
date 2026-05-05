document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("liveSearch");
    const resultsBox = document.getElementById("searchResults");

    let selectedIndex = -1;
    let currentResults = [];

    function highlight(text, query) {
        const regex = new RegExp(`(${query})`, "gi");
        return text.replace(regex, "<mark>$1</mark>");
    }

    input.addEventListener("keyup", function (e) {
        let query = this.value;

        // NAVIGATION
        if (e.key === "ArrowDown") {
            selectedIndex++;
            updateSelection();
            return;
        }

        if (e.key === "ArrowUp") {
            selectedIndex--;
            updateSelection();
            return;
        }

        if (e.key === "Enter") {
            if (currentResults[selectedIndex]) {
                window.location.href = currentResults[selectedIndex].link;
            }
            return;
        }

        if (query.length < 2) {
            resultsBox.style.display = "none";
            return;
        }

        fetch("/rholance_pms/search_api.php?q=" + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {

                currentResults = data;
                selectedIndex = -1;

                if (!data || data.length === 0) {
                    resultsBox.innerHTML = "<div class='search-item'>No results</div>";
                    resultsBox.style.display = "block";
                    return;
                }

                let html = "";

                data.forEach((item, index) => {
                    html += `
                        <div class="search-item" data-index="${index}">
                            <div class="search-title">
                                ${highlight(item.title, query)}
                            </div>
                            <div class="search-sub">
                                ${item.type} • ${highlight(item.sub, query)}
                            </div>
                        </div>
                    `;
                });

                resultsBox.innerHTML = html;
                resultsBox.style.display = "block";

                // CLICK EVENT
                document.querySelectorAll(".search-item").forEach(el => {
                    el.addEventListener("click", function () {
                        let i = this.getAttribute("data-index");
                        window.location.href = currentResults[i].link;
                    });
                });

            });
    });

    function updateSelection() {
        let items = document.querySelectorAll(".search-item");

        if (items.length === 0) return;

        if (selectedIndex >= items.length) selectedIndex = 0;
        if (selectedIndex < 0) selectedIndex = items.length - 1;

        items.forEach(el => el.classList.remove("active"));
        items[selectedIndex].classList.add("active");
    }

    document.addEventListener("click", function (e) {
        if (!input.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = "none";
        }
    });

});