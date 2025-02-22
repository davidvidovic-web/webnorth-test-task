class MapInitializer {
  constructor() {
    //initial map coordinates ( Copenahgen )
    this.initialCenter = [55.6867243, 12.5700724];
    this.initialZoom = 3;
    this.map = null;
  }

  init() {
    window.mapSettings = {
      initialCenter: this.initialCenter,
      initialZoom: this.initialZoom,
    };

    this.map = L.map("map").setView(this.initialCenter, this.initialZoom);
    window.map = this.map;

    this.addTileLayer();
    this.setupMapReadyHandler();
  }

  addTileLayer() {
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "&copy; OpenStreetMap contributors",
    }).addTo(this.map);
  }

  setupMapReadyHandler() {
    this.map.whenReady(() => {
      this.map.invalidateSize({
        animate: false,
        pan: false,
      });

      this.map.setView(this.initialCenter, this.initialZoom, {
        animate: false,
      });

      window.dispatchEvent(new Event("mapReady"));
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const mapInitializer = new MapInitializer();
  mapInitializer.init();
});
