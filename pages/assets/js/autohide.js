let lastScroll = 0;
const header = document.getElementById("header");
const enabled = false; // Set to true to enable autohide, false to disable

window.addEventListener("scroll", () => {
  if (!enabled) return; // Exit early if disabled
  
  const currentScroll = window.pageYOffset;
  if (currentScroll > lastScroll) {
    // Scroll down
    header.style.transform = "translateY(-100%)";
  } else {
    // Scroll up
    header.style.transform = "translateY(0)";
  }
  lastScroll = currentScroll;
});
