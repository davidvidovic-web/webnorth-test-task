document.addEventListener("DOMContentLoaded", function () {
  // Initial map settings
  const initialCenter = [48.8566, 2.3522];
  const initialZoom = 5;

  var map = L.map("map").setView(initialCenter, initialZoom);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors",
  }).addTo(map);

  // Make map instance and settings globally available
  window.map = map;
  window.mapSettings = {
    initialCenter,
    initialZoom,
  };
});
