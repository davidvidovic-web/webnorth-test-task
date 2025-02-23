class MapAnimation {
  constructor() {
    this.init();
  }

  init() {
    document.addEventListener("DOMContentLoaded", () => {
      gsap.registerPlugin(ScrollTrigger);
      this.setupAnimations();
    });
  }

  setupAnimations() {
    const mapApp = document.querySelector("#map-app");
    const gradientOverlay = mapApp.querySelector(".gradient-overlay");
    const hero = mapApp.querySelector("#hero");
    const mapContainer = mapApp.querySelector("#map-container");
    const mapSidebar = mapApp.querySelector(".map-sidebar");

    // Gradient and hero animations
    ScrollTrigger.create({
      trigger: mapApp,
      start: "top top",
      end: "bottom bottom",
      scrub: true,
      onUpdate: (self) => {
        const progress = self.progress;
        const startOpacity = 1 - progress;
        gradientOverlay.style.background = `linear-gradient(to bottom, 
          rgba(40, 39, 44, ${startOpacity}) 100%, 
          rgba(40, 39, 44, ${startOpacity * 0.8}) 35%, 
          rgba(40, 39, 44, 0) 0%)`;
        hero.style.opacity = 1 - progress;
      },
    });

    // Map and sidebar animations
    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: mapContainer,
        start: "top bottom",
        end: "top top",
        scrub: 1,
      },
    });

    tl.from(mapSidebar, {
      x: "-100%",
      opacity: 0,
    }).to(mapContainer, {
      paddingLeft: "300px",
      ease: "none",
      onUpdate: () => {
        if (window.map) {
          window.map.invalidateSize();
        }
      }
    }, "<");
  }
}

new MapAnimation();
