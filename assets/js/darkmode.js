document.addEventListener("DOMContentLoaded", function () {

    const toggle = document.getElementById("darkModeToggle");

    if (localStorage.getItem("darkMode") === "enabled") {
        document.body.classList.add("dark");
        document.documentElement.classList.add("dark");

        if (toggle) toggle.checked = true;
    }

    if (toggle) {
        toggle.addEventListener("change", function () {

            if (this.checked) {
                document.body.classList.add("dark");
                document.documentElement.classList.add("dark");
                localStorage.setItem("darkMode", "enabled");
            } else {
                document.body.classList.remove("dark");
                document.documentElement.classList.remove("dark");
                localStorage.setItem("darkMode", "disabled");
            }

        });
    }

});