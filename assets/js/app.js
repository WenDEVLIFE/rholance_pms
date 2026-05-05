document.addEventListener("DOMContentLoaded", function () {

    /* =========================
       NAVIGATION
    ========================= */
    document.querySelectorAll('.nav-center a[data-section]').forEach(link => {

    link.addEventListener('click', function (e) {
        e.preventDefault();

        const id = this.dataset.section;
        const target = document.getElementById(id);

        if (!target) return;

        const navbar = document.querySelector('.navbar');
        const offset = navbar ? navbar.offsetHeight : 0;

        const top = target.offsetTop - offset;

        window.scrollTo({
            top: top,
            behavior: 'smooth'
        });

    });

});

/* =========================
   NAV ACTIVE STATE (SCROLL + CLICK)
========================= */

const sections = document.querySelectorAll(".page-section");
const navLinks = document.querySelectorAll(".nav-center a[data-section]");

//IMPORTANT: scroll lock variable
let isScrolling = false;

// =========================
// CLICK → set active + smooth scroll
// =========================
navLinks.forEach(link => {

    link.addEventListener('click', function (e) {
        e.preventDefault();

        const id = this.dataset.section;
        const target = document.getElementById(id);

        if (!target) return;

        // LOCK scroll detection
        isScrolling = true;

        // Set active immediately
        navLinks.forEach(l => l.classList.remove("active"));
        this.classList.add("active");

        const navbar = document.querySelector('.navbar');
        const offset = navbar ? navbar.offsetHeight : 0;

        const top = target.offsetTop - offset;

        window.scrollTo({
            top: top,
            behavior: 'smooth'
        });

        // ✅ UNLOCK after scroll animation
        setTimeout(() => {
            isScrolling = false;
        }, 700); // slightly safer timing
    });

});


// =========================
// SCROLL → FIXED ACTIVE DETECTION
// =========================
function updateActiveNav() {

    if (isScrolling) return;

    let current = "";

    sections.forEach(section => {
        const rect = section.getBoundingClientRect();

        // Detect section in viewport center area
        if (rect.top <= 120 && rect.bottom >= 120) {
            current = section.getAttribute("id");
        }
    });

    navLinks.forEach(link => {
        link.classList.remove("active");

        if (link.dataset.section === current) {
            link.classList.add("active");
        }
    });
}

// Run on scroll
window.addEventListener("scroll", updateActiveNav);

// 🔥 CRITICAL FIX: run on page load
window.addEventListener("load", () => {
    setTimeout(updateActiveNav, 50);
});

    /* =========================
       LOGIN MODAL
    ========================= */
    const openLogin = document.getElementById("openLoginModal");
    const closeLogin = document.getElementById("closeLoginModal");
    const loginModal = document.getElementById("loginModal");

    if (openLogin && loginModal) {
        openLogin.addEventListener("click", () => {
            loginModal.classList.add("active");
        });
    }

    if (closeLogin && loginModal) {
        closeLogin.addEventListener("click", () => {
            loginModal.classList.remove("active");
        });
    }

    /* =========================
       REGISTER MODAL
    ========================= */
    const openRegister = document.getElementById("openRegisterModal");
    const closeRegister = document.getElementById("closeRegisterModal");
    const registerModal = document.getElementById("registerModal");

    if (openRegister && registerModal && loginModal) {
        openRegister.addEventListener("click", () => {
            loginModal.classList.remove("active");
            registerModal.classList.add("active");
        });
    }

    if (closeRegister && registerModal) {
        closeRegister.addEventListener("click", () => {
            registerModal.classList.remove("active");
        });
    }

    /* =========================
       BACK TO LOGIN
    ========================= */
    const backToLogin = document.getElementById("backToLogin");

    if (backToLogin && loginModal && registerModal) {
        backToLogin.addEventListener("click", () => {
            registerModal.classList.remove("active");
            loginModal.classList.add("active");
        });
    }

    /* =========================
   HERO BUTTONS → OPEN LOGIN MODAL
========================= */
const heroLoginBtn = document.getElementById("heroLoginBtn");
const heroTrackBtn = document.getElementById("heroTrackBtn");

// ✅ DO NOT redeclare loginModal here

function openLoginModal(e) {
    e.preventDefault();
    loginModal.classList.add("active");
}

heroLoginBtn?.addEventListener("click", openLoginModal);
heroTrackBtn?.addEventListener("click", openLoginModal);

if (heroTrackBtn && loginModal) {
    heroTrackBtn.addEventListener("click", (e) => {
        e.preventDefault();
        loginModal.classList.add("active");
    });
}

/* =========================
   HERO CAROUSEL (AUTO + CONTROLS)
========================= */
const heroTrack = document.getElementById("heroTrack");
const heroSlides = document.querySelectorAll("#heroTrack .slide");
const heroNext = document.getElementById("heroNext");
const heroPrev = document.getElementById("heroPrev");

let heroIndex = 0;
const totalSlides = heroSlides.length;

// FUNCTION: UPDATE POSITION
function updateHeroCarousel() {
    const offset = -heroIndex * 100;
    heroTrack.style.transform = `translateX(${offset}%)`;
}

// NEXT
function nextSlide() {
    heroIndex = (heroIndex + 1) % totalSlides;
    updateHeroCarousel();
}

// PREV
function prevSlide() {
    heroIndex = (heroIndex - 1 + totalSlides) % totalSlides;
    updateHeroCarousel();
}

// BUTTON CONTROLS
heroNext?.addEventListener("click", nextSlide);
heroPrev?.addEventListener("click", prevSlide);

// AUTO SLIDE
let heroAutoSlide = setInterval(nextSlide, 4000);

// OPTIONAL: PAUSE ON HOVER (BEST UX)
const heroContainer = document.querySelector(".floating-carousel");

heroContainer?.addEventListener("mouseenter", () => {
    clearInterval(heroAutoSlide);
});

heroContainer?.addEventListener("mouseleave", () => {
    heroAutoSlide = setInterval(nextSlide, 4000);
});

/* =========================
   PRODUCT CAROUSEL (FINAL VERSION)
========================= */

const carousel = document.querySelector(".p2-carousel");
const next = document.getElementById("p2Next");
const prev = document.getElementById("p2Prev");
const buttons = document.querySelectorAll(".p2-btn");

if (!carousel) {
    console.error("Carousel NOT FOUND");
} else {

    let speed = 1; // adjust speed here
    let isPaused = false;

    // =========================
    // STORE ORIGINAL ITEMS
    // =========================
    const originalCards = Array.from(carousel.children);

    // =========================
    // BUILD CAROUSEL (FILTER + DUPLICATE)
    // =========================
    function buildCarousel(filter = "all") {

        let filtered = originalCards.filter(card => {
            return filter === "all" || card.dataset.category === filter;
        });

        // Convert to HTML
        const html = filtered.map(card => card.outerHTML).join("");

        // Duplicate for seamless loop
        carousel.innerHTML = html + html;

        // Reset scroll
        carousel.scrollLeft = 0;
    }

    // =========================
    // AUTO SCROLL (SEAMLESS)
    // =========================
    function autoScroll() {
        if (!isPaused) {
            carousel.scrollLeft += speed;

            const halfWidth = carousel.scrollWidth / 2;

            if (carousel.scrollLeft >= halfWidth) {
                carousel.scrollLeft = 0;
            }
        }

        requestAnimationFrame(autoScroll);
    }

    // =========================
    // BUTTON CONTROLS
    // =========================
    next?.addEventListener("click", () => {
        isPaused = true;
        carousel.scrollBy({ left: 320, behavior: "smooth" });

        setTimeout(() => isPaused = false, 500);
    });

    prev?.addEventListener("click", () => {
        isPaused = true;
        carousel.scrollBy({ left: -320, behavior: "smooth" });

        setTimeout(() => isPaused = false, 500);
    });

    // =========================
    // HOVER PAUSE
    // =========================
    carousel.addEventListener("mouseenter", () => isPaused = true);
    carousel.addEventListener("mouseleave", () => isPaused = false);

    document.addEventListener("visibilitychange", () => {
    isPaused = document.hidden;
});

    // =========================
    // FILTER BUTTONS
    // =========================
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {

            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            const filter = btn.dataset.filter;

            // rebuild carousel properly
            buildCarousel(filter);
        });
    });

    // =========================
    // INIT
    // =========================
    buildCarousel("all"); // load all first
    autoScroll();
}

    /* =========================
       FAQ
    ========================= */
    const faqItems = document.querySelectorAll(".faq-item");

    faqItems.forEach(item => {
        const question = item.querySelector(".faq-question");

        if (question) {
            question.addEventListener("click", () => {
                item.classList.toggle("active");
            });
        }
    });


});
/* =========================
   FAQ SEARCH + HIGHLIGHT (FINAL FIX)
========================= */

const searchInput = document.getElementById("faqSearch");
const faqNoResults = document.getElementById("faqNoResults");
const faqItems = document.querySelectorAll(".faq-item");

// =========================
// STORE ORIGINAL TEXT (IMPORTANT)
// =========================
faqItems.forEach(item => {
    const qText = item.querySelector(".faq-text");
    const aEl = item.querySelector(".faq-answer");

    qText.dataset.original = qText.innerHTML;
    aEl.dataset.original = aEl.innerHTML;
});

// =========================
// HIGHLIGHT FUNCTION
// =========================
function highlightText(element, query) {
    if (!query) {
        element.innerHTML = element.dataset.original;
        return;
    }

    const text = element.dataset.original;
    const regex = new RegExp(`(${query})`, "gi");

    element.innerHTML = text.replace(regex, `<mark>$1</mark>`);
}

// =========================
// SEARCH FUNCTION
// =========================
searchInput?.addEventListener("input", function () {

    const query = this.value.toLowerCase().trim();
    let matchCount = 0;

    faqItems.forEach(item => {

        const qEl = item.querySelector(".faq-text");   // ✅ FIXED
        const aEl = item.querySelector(".faq-answer");

        const question = qEl.dataset.original.toLowerCase();
        const answer = aEl.dataset.original.toLowerCase();

        if (query === "") {
            item.style.display = "block";
            item.classList.remove("active");

            highlightText(qEl, "");
            highlightText(aEl, "");
        }
        else if (question.includes(query) || answer.includes(query)) {

            item.style.display = "block";
            
            highlightText(qEl, query);
            highlightText(aEl, query);

            matchCount++;
        }
        else {
            item.style.display = "none";
            item.classList.remove("active");

            highlightText(qEl, "");
            highlightText(aEl, "");
        }

    });

    // NO RESULTS MESSAGE
    if (faqNoResults) {
        faqNoResults.style.display =
            (query !== "" && matchCount === 0) ? "block" : "none";
    }

});

document.querySelectorAll('.footer-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();

        const target = document.querySelector(this.getAttribute('href'));
        const offset = 80;

        const top = target.offsetTop - offset;

        window.scrollTo({
            top: top,
            behavior: 'smooth'
        });
    });
});

const togglePassword = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");

togglePassword.addEventListener("click", function () {

    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);

    // toggle icon
    this.classList.toggle("fa-eye");
    this.classList.toggle("fa-eye-slash");
});

document.querySelectorAll(".toggle-password").forEach(icon => {
    icon.addEventListener("click", function () {

        const input = this.previousElementSibling;

        const type = input.getAttribute("type") === "password" ? "text" : "password";
        input.setAttribute("type", type);

        // toggle icon
        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");
    });
});

const forgotLink = document.getElementById("forgotPasswordLink");
const forgotModal = document.getElementById("forgotModal");
const closeForgot = document.getElementById("closeForgot");

if (forgotLink && forgotModal) {
    forgotLink.addEventListener("click", (e) => {
        e.preventDefault();
        forgotModal.style.display = "flex";
    });
}

if (closeForgot && forgotModal) {
    closeForgot.addEventListener("click", () => {
        forgotModal.style.display = "none";
    });
}
