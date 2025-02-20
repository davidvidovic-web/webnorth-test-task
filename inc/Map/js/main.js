class MapAnimation {
  constructor() {
    this.init();
  }

  init() {
    document.addEventListener("DOMContentLoaded", () => {
      gsap.registerPlugin(ScrollTrigger);
      this.setupColoredSections();
      this.setupSidebarAnimation();
    });
  }

  setupColoredSections() {
    let coloredSections = gsap.utils.toArray("[data-color]");
    coloredSections.forEach((section, i) => {
      let bgColor = section.getAttribute("data-color");
      ScrollTrigger.create({
        trigger: section,
        start: "200 bottom",
        end: "+=100%",
        onToggle: self => {
          if (self.isActive) {
            gsap.to("body", {
              backgroundColor: bgColor,
              overwrite: "auto",
            });
          } else if ((i === 0 && self.direction < 0) || (i === coloredSections.length - 1 && self.direction > 0)) {
            gsap.to("body", {
              backgroundColor: "transparent",
              overwrite: "auto"
            });
          }
        }
      });
    });
  }

  setupSidebarAnimation() {
    gsap.from(".map-sidebar", {
      x: "-100%",
      scrollTrigger: {
        trigger: "#map-container",
        start: "top bottom",
        end: "top top",
        scrub: true,
      }
    });
  }
}

new MapAnimation();