document.addEventListener("DOMContentLoaded", function () {
  // Initial map settings
  const initialCenter = [55.6867243, 12.5700724];
  const initialZoom = 10;

  // Make settings globally available first
  window.mapSettings = {
    initialCenter,
    initialZoom,
  };

  // Create map instance
  var map = L.map("map").setView(initialCenter, initialZoom);

  // Make map instance available
  window.map = map;

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors",
  }).addTo(map);

  // Handle initial map load and set final width
  map.whenReady(() => {
    map.invalidateSize({
      animate: false,
      pan: false,
    });

    map.setView(initialCenter, initialZoom, {
      animate: false,
    });

    window.dispatchEvent(new Event("mapReady"));
  });
});
