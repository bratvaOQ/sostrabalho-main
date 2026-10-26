document.addEventListener("DOMContentLoaded", () => {
    // ─── Carrossel de imagens ──────────────────────────────
    const slides = document.querySelectorAll(".slide");
    let currentIndex = 0;
    const totalSlides = slides.length;
    const intervalTime = 7000;

    if (slides.length > 0) {
        slides[currentIndex].classList.add("active");

        function nextSlide() {
            slides[currentIndex].classList.remove("active");
            currentIndex = (currentIndex + 1) % totalSlides;
            slides[currentIndex].classList.add("active");
        }

        setInterval(nextSlide, intervalTime);
    }
});
