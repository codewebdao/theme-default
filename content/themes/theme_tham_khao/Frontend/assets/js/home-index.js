window.lazySizesConfig = window.lazySizesConfig || {};
lazySizesConfig.expand = 0;
lazySizesConfig.expFactor = 1.2;

const batchDOM = {
    reads: [],
    writes: [],
    read: (e) => {
        batchDOM.reads.push(e);
        if (!batchDOM.scheduled) {
            batchDOM.scheduled = true;
            requestAnimationFrame(() => batchDOM.flush());
        }
    },
    write: (e) => {
        batchDOM.writes.push(e);
        if (!batchDOM.scheduled) {
            batchDOM.scheduled = true;
            requestAnimationFrame(() => batchDOM.flush());
        }
    },
    flush: () => {
        batchDOM.reads.forEach((e) => e());
        batchDOM.reads.length = 0;
        batchDOM.writes.forEach((e) => e());
        batchDOM.writes.length = 0;
        batchDOM.scheduled = false;
    }
};

document.addEventListener("DOMContentLoaded", function () {
    // Hero Slider
    const heroSlider = new BlazeSlider(document.querySelector(".hero-swiper"), {
        all: {
            enableAutoplay: true,
            slidesToScroll: 1,
            slidesToShow: 1,
            transitionDuration: 300,
            loop: true
        },
        "(max-width: 768px)": {
            autoplayInterval: 5000
        }
    });

    // Tab functionality
    const e = document.querySelectorAll(".tab-btn");
    e.forEach((t) => {
        t.addEventListener("click", function () {
            const t = this.dataset.tab;
            batchDOM.write(() => {
                e.forEach((e) => {
                    e.classList.remove("active", "bg-white", "text-blue-600", "shadow-sm");
                    e.classList.add("border-b", "border-gray-200");
                });
                document.querySelectorAll(".tab-content").forEach((e) => {
                    e.classList.remove("active");
                });
                this.classList.add("active", "bg-white", "text-blue-600", "shadow-sm");
                this.classList.remove("border-b", "text-white");
                document.querySelector(`#${t}`).classList.add("active");
            });
        });
    });

    // FAQ functionality
    document.querySelectorAll(".faq-item").forEach((e) => {
        e.addEventListener("click", function () {
            const e = this.closest(".faq-item").querySelector(".faq-answer");
            const t = this.querySelector(".faq-icon");
            const s = e.classList.contains("active");
            batchDOM.write(() => {
                document.querySelectorAll(".faq-answer").forEach((e) => {
                    e.classList.remove("active");
                });
                document.querySelectorAll(".faq-icon").forEach((e) => {
                    e.classList.remove("rotate");
                });
                if (!s) {
                    e.classList.add("active");
                    t.classList.add("rotate");
                }
            });
        });
    });

    // Smooth scroll functionality
    document.querySelectorAll('a[href^="#"]').forEach((e) => {
        e.addEventListener("click", function (e) {
            e.preventDefault();
            const t = document.querySelector(this.getAttribute("href"));
            if (t) {
                requestAnimationFrame(() => {
                    window.scrollTo({
                        top: t.offsetTop - 80,
                        behavior: "smooth"
                    });
                });
                if (mobileMenu && mobileMenu.classList.contains("active")) {
                    batchDOM.write(() => {
                        mobileMenu.classList.remove("active");
                        if (mobileMenuOverlay) {
                            mobileMenuOverlay.classList.add("hidden");
                        }
                    });
                }
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const e = document.querySelectorAll(".tab-button");
    const t = document.querySelectorAll(".tab-content");
    e.forEach((s) => {
        s.addEventListener("click", function () {
            const s = this.getAttribute("data-tab");
            batchDOM.write(() => {
                e.forEach((e) => {
                    e.setAttribute("aria-selected", "false");
                    e.setAttribute("data-state", "inactive");
                    e.classList.remove("bg-white", "text-slate-900", "shadow-sm");
                    e.classList.add("text-slate-600");
                });
                this.setAttribute("aria-selected", "true");
                this.setAttribute("data-state", "active");
                this.classList.add("bg-white", "text-slate-900", "shadow-sm");
                this.classList.remove("text-slate-600");
                t.forEach((e) => {
                    e.setAttribute("data-state", "inactive");
                    e.classList.add("hidden");
                    e.classList.remove("active");
                });
                const a = document.getElementById(`content-${s}`);
                if (a) {
                    a.setAttribute("data-state", "active");
                    a.classList.remove("hidden");
                    a.classList.add("active");
                }
            });
        });
    });

    document.addEventListener("keydown", function (t) {
        if (t.target.classList.contains("tab-button") && (t.key === "ArrowLeft" || t.key === "ArrowRight")) {
            t.preventDefault();
            const s = Array.from(e).indexOf(t.target);
            let a;
            if (t.key === "ArrowLeft") {
                a = s > 0 ? s - 1 : e.length - 1;
            } else {
                a = s < e.length - 1 ? s + 1 : 0;
            }
            e[a].focus();
            e[a].click();
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("[data-faq]").forEach((e) => {
        e.addEventListener("click", function () {
            const e = this.getAttribute("data-faq");
            const t = document.querySelector(`[data-answer="${e}"]`);
            const s = this.querySelector(".faq-icon");
            let a = 0;
            batchDOM.read(() => {
                a = t.scrollHeight;
            });
            batchDOM.write(() => {
                if (t.style.maxHeight && t.style.maxHeight !== "0px") {
                    t.style.maxHeight = "0px";
                    s.style.transform = "rotate(0deg)";
                } else {
                    t.style.maxHeight = a + "px";
                    s.style.transform = "rotate(180deg)";
                }
            });
        });
    });
});

// Code example tabs
(function () {
    "use strict";
    ({
        buttons: document.querySelectorAll(".code-example-tab-btn"),
        contents: document.querySelectorAll(".code-example-tab-content"),
        init: function () {
            this.bindEvents();
        },
        bindEvents: function () {
            this.buttons.forEach((e) => {
                e.addEventListener("click", (e) => {
                    this.handleTabClick(e.target);
                });
            });
        },
        handleTabClick: function (e) {
            const t = e.getAttribute("data-code-tab");
            batchDOM.write(() => {
                this.resetAllButtons();
                this.setActiveButton(e);
                this.hideAllContents();
                this.showContent(t);
            });
        },
        resetAllButtons: function () {
            this.buttons.forEach((e) => {
                e.setAttribute("aria-selected", "false");
                e.setAttribute("data-state", "inactive");
                e.classList.remove("bg-blue-600", "text-white");
                e.classList.add("text-slate-300");
            });
        },
        setActiveButton: function (e) {
            e.setAttribute("aria-selected", "true");
            e.setAttribute("data-state", "active");
            e.classList.add("bg-blue-600", "text-white");
            e.classList.remove("text-slate-300");
        },
        hideAllContents: function () {
            this.contents.forEach((e) => {
                e.setAttribute("data-state", "inactive");
                e.classList.add("hidden");
            });
        },
        showContent: function (e) {
            const t = `code-example-content-${e}`;
            const s = document.getElementById(t);
            if (s) {
                s.setAttribute("data-state", "active");
                s.classList.remove("hidden");
            }
        }
    }).init();
})();

// Plugin theme tabs
(function () {
    "use strict";
    ({
        currentTab: "themes",
        init: function () {
            this.bindTabEvents();
            this.updateDisplay();
        },
        bindTabEvents: function () {
            document.querySelectorAll(".plugin-theme-tab-btn").forEach((e) => {
                e.addEventListener("click", (t) => {
                    const s = e.getAttribute("data-plugin-theme-tab");
                    this.selectTab(s);
                });
            });
        },
        selectTab: function (e) {
            this.currentTab = e;
            this.updateTabSelection();
            this.updateContentDisplay();
        },
        updateTabSelection: function () {
            document.querySelectorAll(".plugin-theme-tab-btn").forEach((e) => {
                if (e.getAttribute("data-plugin-theme-tab") === this.currentTab) {
                    e.classList.remove("bg-transparent", "text-slate-600", "hover:text-slate-900");
                    e.classList.add("bg-white", "text-blue-600", "shadow-sm");
                } else {
                    e.classList.remove("bg-white", "text-blue-600", "shadow-sm");
                    e.classList.add("bg-transparent", "text-slate-600", "hover:text-slate-900");
                }
            });
        },
        updateContentDisplay: function () {
            document.querySelectorAll(".plugin-theme-content").forEach((e) => {
                if (e.getAttribute("data-plugin-theme-content") === this.currentTab) {
                    e.classList.remove("hidden");
                } else {
                    e.classList.add("hidden");
                }
            });
        },
        updateDisplay: function () {
            batchDOM.write(() => {
                this.updateTabSelection();
                this.updateContentDisplay();
            });
        }
    }).init();
})();

// Services Slider
document.addEventListener("DOMContentLoaded", function () {
    const servicesSlider = new BlazeSlider(document.querySelector(".services-slide"), {
        all: {
            enableAutoplay: false,
            slidesToScroll: 1,
            slidesToShow: 3,
            transitionDuration: 400,
            loop: false
        },
        "(max-width: 1360px)": {
            slidesToShow: 2.5,
            slidesGap: "16px"
        },
        "(max-width: 1024px)": {
            slidesToShow: 2.2,
            slidesGap: "16px"
        },
        "(max-width: 768px)": {
            slidesToShow: 1.7,
            slidesGap: "16px"
        },
        "(max-width: 600px)": {
            slidesToShow: 1.3,
            slidesGap: "10px"
        },
        "(max-width: 480px)": {
            slidesToShow: 1,
            slidesGap: "8px"
        }
    });
});

// Reviews Slider
document.addEventListener("DOMContentLoaded", function () {
    const reviewsSlider = new BlazeSlider(document.querySelector(".reviews-slider"), {
        all: {
            enableAutoplay: true,
            slidesToScroll: 4,
            slidesToShow: 4,
            transitionDuration: 300,
            loop: true
        },
        "(max-width: 1360px)": {
            slidesToShow: 3,
            slidesToShow: 3,
            slidesGap: "40px"
        },
        "(max-width: 1020px)": {
            slidesToShow: 2,
            slidesToShow: 2,
            slidesGap: "40px"
        },
        "(max-width: 600px)": {
            slidesToShow: 1,
            slidesToScroll: 1
        }
    });

    // Listen for slide event
    reviewsSlider.onSlide((pageIndex, firstVisibleSlideIndex, lastVisibleSlideIndex) => {
        console.log({
            pageIndex,
            firstVisibleSlideIndex,
            lastVisibleSlideIndex
        });
    });
});

// Marquee functionality
document.addEventListener("DOMContentLoaded", () => {
    const e = document.querySelector(".marquee-content");
    if (!e) return;

    e.addEventListener("click", () => {
        batchDOM.write(() => {
            e.classList.toggle("animate-marquee");
            e.classList.toggle("paused");
        });
    });

    document.querySelectorAll(".brand-logo-container").forEach((t) => {
        t.addEventListener("focus", () => {
            batchDOM.write(() => {
                e.classList.add("paused");
                e.classList.remove("animate-marquee");
            });
        });
        t.addEventListener("blur", () => {
            batchDOM.write(() => {
                e.classList.remove("paused");
                e.classList.add("animate-marquee");
            });
        });
    });
});
