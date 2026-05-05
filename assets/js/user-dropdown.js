document.addEventListener("DOMContentLoaded", function () {

    const userMenu = document.getElementById("userMenu");
    const dropdown = document.getElementById("userDropdown");

    userMenu.addEventListener("click", function (e) {
        e.stopPropagation();
        dropdown.classList.toggle("show");
    });

    document.addEventListener("click", function () {
        dropdown.classList.remove("show");
    });

});