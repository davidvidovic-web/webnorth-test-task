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

  handleMapResize() {
    const map = window.map;
    const { initialCenter, initialZoom } = window.mapSettings;

    if (map) {
      console.log("Resizing map");
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
        zoom: {
          animate: true,
        },
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
      },
    });

    tl.from(mapSidebar, {
      x: "-100%",
      opacity: 0,
    }).to(
      map,
      {
        width: "calc(100% - 300px)",
        x: "300px",

        //resize map when sidebar is opened
        onComplete: () => {
          this.handleMapResize();
        },
      },
      "<"
    );
  }
}

new MapAnimation();
