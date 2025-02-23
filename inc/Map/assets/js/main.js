class MapAnimation {
  constructor() {
    this.init();
    this.setupSidebarTransitions();
    this.setupCustomCursor();
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

    //gradient and hero
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

    //map and sidebar
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
      mapContainer,
      {
        paddingLeft: "300px",
        ease: "none",
        onUpdate: () => {
          if (window.map) {
            window.map.invalidateSize();
          }
        },
      },
      "<"
    );
  }

  setupSidebarTransitions() {
    document.addEventListener("DOMContentLoaded", () => {
      const mapSidebar = document.querySelector(".map-sidebar");
      const mapContainer = document.querySelector("#map-container");
      const mapFooter = document.querySelector(".map-footer");

      if (!mapSidebar || !mapFooter) return;

      const handleTransition = (toBookmarks) => {
        const timeline = gsap.timeline();

        timeline
          .to(mapSidebar, {
            width: toBookmarks ? "100%" : "300px",
            duration: 0.3,
            ease: "power2.out",
          })
          .to(
            mapContainer,
            {
              paddingLeft: toBookmarks ? "100%" : "300px",
              duration: 0.3,
              ease: "power2.out",
              onComplete: () => {
                if (window.map) {
                  window.map.invalidateSize();
                }
              },
            },
            "<"
          );
      };

      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.attributeName === "class") {
            const isShowingBookmarks =
              mapFooter.classList.contains("showing-bookmarks");
            handleTransition(isShowingBookmarks);
          }
        });
      });

      document.addEventListener("click", (e) => {
        const stationHeader = e.target.closest(".station-header");
        if (stationHeader) {
          const content = stationHeader.nextElementSibling;
          const isOpen = content.style.display === "block";

          if (isOpen) {
            handleTransition(false);
          }
        }
      });

      observer.observe(mapFooter, {
        attributes: true,
        attributeFilter: ["class"],
      });
    });
  }

  setupCustomCursor() {
    document.addEventListener("DOMContentLoaded", () => {
      const cursor = document.createElement("div");
      cursor.className = "custom-cursor";
      document.body.appendChild(cursor);

      document.addEventListener("mousemove", (e) => {
        cursor.style.left = e.clientX + "px";
        cursor.style.top = e.clientY + "px";

        if (!cursor.classList.contains("visible")) {
          cursor.classList.add("visible");
        }
      });

      document.addEventListener("mousedown", () => {
        cursor.classList.add("clicking");
      });

      document.addEventListener("mouseup", () => {
        cursor.classList.remove("clicking");
      });

      document.addEventListener("mouseleave", () => {
        cursor.classList.remove("visible");
      });

      document.addEventListener("mouseenter", () => {
        cursor.classList.add("visible");
      });
    });
  }
}

new MapAnimation();
