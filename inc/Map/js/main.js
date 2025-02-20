document.addEventListener("DOMContentLoaded", function () {
  const mapApp = document.getElementById("map-app");
  const mapContainer = document.getElementById("map-container");

  // Ensure GSAP and ScrollTrigger are loaded
  if (typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined") {
    gsap.registerPlugin(ScrollTrigger);

    ScrollTrigger.create({
      trigger: mapContainer,
      start: "top bottom",
      end: "bottom top",
      onEnter: () => {
        gsap.to(mapApp, {
          background:
            "linear-gradient(to bottom, rgba(40, 39, 44, 1) 0%, rgba(40, 39, 44, 0) 100%)",
          duration: 0.5,
        });
      },
      onLeave: () => {
        gsap.to(mapApp, {
          background: "none",
          duration: 0.5,
        });
      },
      onLeaveBack: () => {
        gsap.to(mapApp, {
          background:
            "linear-gradient(to bottom, rgba(40, 39, 44, 1) 100%, rgba(40, 39, 44, 0) 100%)",
          duration: 0.5,
        });
      },
      onEnterBack: () => {
        gsap.to(mapApp, {
          background:
            "linear-gradient(to bottom, rgba(40, 39, 44, 1) 0%, rgba(40, 39, 44, 0) 100%)",
          duration: 0.5,
        });
      },
    });
  } else {
    console.error("GSAP or ScrollTrigger is not loaded.");
  }
});
