class MapAnimation {
  constructor() {
    this.init();
  }

  init() {
    document.addEventListener("DOMContentLoaded", () => {
      gsap.registerPlugin(ScrollTrigger);
      this.setupAnimations();

      window.addEventListener("mapReady", () => {
        this.handleMapResize();
      });
    });
  }

  //for smooth and centered map resizing
  handleMapResize() {
    const map = window.map;

    if (map && window.mapSettings) {
      const { initialCenter, initialZoom } = window.mapSettings;
      const mapContainer = map.getContainer();

      mapContainer.style.width = "calc(100% - 300px)";

      // Force a repaint before invalidating size
      void mapContainer.offsetHeight;
      requestAnimationFrame(() => {
        map.invalidateSize({
          animate: false,
          pan: false,
        });

        map.setView(initialCenter, initialZoom, {
          animate: true,
          duration: 0.3,
          pan: {
            animate: true,
            duration: 0.3,
            easeLinearity: 0.5,
          },
        });
      });
    }
  }

  setupAnimations() {
    const mapApp = document.querySelector("#map-app");
    const gradientOverlay = mapApp.querySelector(".gradient-overlay");
    const hero = mapApp.querySelector("#hero");
    const mapContainer = mapApp.querySelector("#map-container");
    const map = mapContainer.querySelector("#map");
    const mapSidebar = mapApp.querySelector(".map-sidebar");

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
          rgba(40, 39, 44, 0) 0%)`;

        hero.style.opacity = 1 - progress;
      },
    });

    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: mapContainer,
        start: "top bottom",
        end: "top top",
        scrub: 1,
        onUpdate: () => {
          this.handleMapResize();
        },
      },
    });

    tl.from(mapSidebar, {
      x: "-100%",
      opacity: 0,
    }).to(
      map,
      {
        x: "300px",
        ease: "none",
        onComplete: () => {
          this.handleMapResize();
        },
      },
      "<"
    );
  }
}

new MapAnimation();
