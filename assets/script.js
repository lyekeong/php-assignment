document.addEventListener("DOMContentLoaded", () => {

    // Smooth fade on page load
    document.body.style.opacity = 0;
    setTimeout(() => {
        document.body.style.transition = "opacity 0.5s";
        document.body.style.opacity = 1;
    }, 100);

});