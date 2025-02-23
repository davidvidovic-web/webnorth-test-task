class MapInitializer {
  constructor() {
    // fallback values ( Copenhagen, Denmark )
    const fallbackCenter = [55.6867243, 12.5700724];
    const fallbackZoom = 3;

    this.initialCenter = fallbackCenter;
    this.initialZoom = fallbackZoom;

    this.map = null;
    this.markers = new Map();
    this.activeStation = null;
    this.userInitiated = false;

    // image path for markers, check if data exists first otherwise returns undefined
    if (typeof weatherStationData !== "undefined") {
      const mapAppPath = weatherStationData.map_app_path;
      if (mapAppPath) {
        L.Icon.Default.imagePath = `${mapAppPath}/assets/images/`;
      }
    }

    this.handleUrlHash = this.handleUrlHash.bind(this);

    this.bookmarkedStations = this.loadBookmarkedStations();
    this.setupMyLocationsHandler();
  }

  init() {
    // ensure map element exists
    const mapElement = document.getElementById("map");
    if (!mapElement) {
      console.error("Map element not found");
      return;
    }

    window.mapSettings = {
      initialCenter: this.initialCenter,
      initialZoom: this.initialZoom,
    };

    // map init
    try {
      this.map = L.map(mapElement).setView(
        this.initialCenter,
        this.initialZoom
      );
      window.map = this.map;
      this.addTileLayer();
      this.handleUrlHash();

      // add stations if data exists
      if (typeof weatherStationData !== "undefined") {
        this.addWeatherStations();
      }

      this.setupMapReadyHandler();
      this.map.on("click", (e) => this.handleMapClick(e));

      // url history
      this.setupUrlHandling();
    } catch (error) {
      console.error("Error initializing map:", error);
    }
  }

  setupUrlHandling() {
    if (window.location.pathname.endsWith("/")) {
      const newPath =
        window.location.pathname.slice(0, -1) + window.location.hash;
      window.history.replaceState(null, "", newPath);
    }

    window.addEventListener("popstate", () => this.handleUrlHash());
  }

  handleUrlHash() {
    const hash = window.location.hash;
    if (!hash || !this.map) return;

    const stationId = parseInt(hash.replace("#", ""));
    if (isNaN(stationId)) return;

    const station = window.weatherStationData?.stations?.find(
      (s) => s.id === stationId
    );

    if (station) {
      // set zoom and center immediately
      this.map.setView([station.lat, station.lon], 12, {
        animate: false,
      });

      this.activeStation = station.id;
      this.userInitiated = false;

      // Update header visibility
      this.updateMapHeader(station);

      // Fetch weather data if needed
      this.checkAndFetchWeatherData(station);
    }
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

      // only reset view if no station is active
      if (!this.activeStation) {
        this.map.setView(this.initialCenter, this.initialZoom, {
          animate: false,
        });
      }

      window.dispatchEvent(new Event("mapReady"));
    });
  }

  handleMapClick(e) {
    if (!window.weatherStationData?.stations) return;

    this.userInitiated = true;

    const clickedPoint = e.latlng;
    let closest = null;
    let closestDistance = Infinity;

    window.weatherStationData.stations.forEach((station) => {
      const distance = L.latLng(station.lat, station.lon).distanceTo(
        clickedPoint
      );
      if (distance < closestDistance) {
        closestDistance = distance;
        closest = station;
      }
    });

    if (closest) {
      this.activateStation(closest);
    }
  }

  activateStation(station) {
    if (!station || !this.map) return;

    const path = window.location.pathname.replace(/\/+$/, "");
    window.history.pushState(null, "", `${path}#${station.id}`);

    this.map.setView([station.lat, station.lon], 12, {
      animate: true,
    });

    this.activeStation = station.id;

    if (this.userInitiated) {
      this.checkAndFetchWeatherData(station);
    }

    this.updateMapHeader(station);
  }

  checkAndFetchWeatherData(station) {
    const cachedData = station.weather_data;

    if (cachedData && cachedData !== "false" && cachedData !== "null") {
      try {
        const parsedData = JSON.parse(cachedData);
        console.log(`%cData source: Cache`, "color: green; font-weight: bold");
        this.updateWeatherContent(station, parsedData);
        return;
      } catch (e) {
        this.fetchWeatherData(station);
      }
    }

    this.fetchWeatherData(station);
  }

  fetchWeatherData(station) {
    if (!weatherStationData?.ajax_url || !weatherStationData?.nonce) {
      console.error("Missing AJAX configuration");
      return;
    }

    const lat = parseFloat(station.lat).toFixed(6);
    const lon = parseFloat(station.lon).toFixed(6);

    if (isNaN(lat) || isNaN(lon)) {
      console.error("Invalid coordinates");
      return;
    }

    const formData = new URLSearchParams();
    formData.append("action", "get_weather_data");
    formData.append("post_id", station.id);
    formData.append("lat", lat);
    formData.append("lon", lon);
    formData.append("nonce", weatherStationData.nonce);

    fetch(weatherStationData.ajax_url, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: formData.toString(),
    })
      .then((response) =>
        response.ok
          ? response.json()
          : Promise.reject(`HTTP error! status: ${response.status}`)
      )
      .then((response) => {
        if (!response.success) {
          throw new Error(response.data || "Unknown error occurred");
        }

        // data source
        const source = response.data.source;
        console.log(
          `%cData source: ${source}`,
          `color: ${source === "cache" ? "green" : "blue"}; font-weight: bold`
        );

        station.weather_data = JSON.stringify(response.data.data);

        // TODO: Update UI with weather data
        this.updateWeatherContent(station, response.data.data);
        return response.data;
      })
      .catch((error) => {
        console.error("Error:", error.message);
      });
  }

  addWeatherStations() {
    if (!window.weatherStationData?.stations) return;

    const markers = window.weatherStationData.stations
      .filter((station) => station.lat && station.lon)
      .map((station) => {
        const marker = L.marker([station.lat, station.lon]);
        this.markers.set(station.id, marker);
        return marker;
      });

    if (markers.length > 0) {
      const group = L.featureGroup(markers).addTo(this.map);

      // if no active station fit markers
      if (!this.activeStation) {
        this.map.fitBounds(group.getBounds(), {
          padding: [50, 50],
          maxZoom: 10,
        });
      }
    }
  }

  updateMapHeader(station) {
    const mapHeader = document.querySelector(".map-header");
    if (!mapHeader) return;

    const logo = mapHeader.querySelector(".webnorth-sidebar-logo");
    const unitsSwitcher = mapHeader.querySelector(".units-switcher");
    const bookmarkIcon = mapHeader.querySelector(".bookmark-icon");

    // Toggle visibility
    logo.classList.add("hidden");
    unitsSwitcher.classList.add("visible");
    bookmarkIcon.classList.add("visible");
  }

  updateWeatherContent(station, weatherData, unit = "metric") {
    const mapContent = document.querySelector(".map-content");
    if (!mapContent) return;

    // Remove default message if exists
    const defaultMessage = mapContent.querySelector(".default-message");
    if (defaultMessage) {
      defaultMessage.remove();
    }

    const unitSymbol = unit === "metric" ? "°C" : "°F";
    const temps = weatherData[unit];

    // Create or update weather content
    let weatherContent = mapContent.querySelector(".weather-content");
    if (!weatherContent) {
      weatherContent = document.createElement("div");
      weatherContent.className = "weather-content";
      mapContent.appendChild(weatherContent);
    }

    weatherContent.innerHTML = `
        <div class="weather-info">
            <h2 class="station-title">${station.title}</h2>
            <p class="weather-main">
                Weather: <span>${weatherData.weather.main} - ${weatherData.weather.description}</span>
            </p>
            <p class="weather-temp">
                Temp: <span>${temps.temp}${unitSymbol} / Feels like: ${temps.feels_like}${unitSymbol}</span>
            </p>
            <p class="weather-pressure">
                Pressure: <span>${weatherData.weather.pressure} hPa</span>
            </p>
            <p class="weather-humidity">
                Humidity: <span>${weatherData.weather.humidity}%</span>
            </p>
        </div>
    `;
  }

  toggleMyLocations() {
    const mapSidebar = document.querySelector(".map-sidebar");
    const myLocationsLink = document.querySelector(".map-footer a");

    if (myLocationsLink.textContent === "My locations") {
      myLocationsLink.textContent = "Close";
      mapSidebar.classList.add("showing-bookmarks");
      this.showBookmarkedStations();
    } else {
      myLocationsLink.textContent = "My locations";
      mapSidebar.classList.remove("showing-bookmarks");
      this.showActiveStation();
    }
  }

  showBookmarkedStations() {
    const mapContent = document.querySelector(".map-content");
    if (!mapContent || !this.bookmarkedStations.length) return;

    mapContent.innerHTML = `
      <div class="bookmarked-stations">
        ${this.bookmarkedStations
          .map(
            (station, index) => `
          <div class="station-accordion" data-station-id="${station.id}">
            <div class="station-header" data-index="${index}">
              <h3>${station.title}</h3>
              <span class="toggle-icon">▼</span>
            </div>
            <div class="station-content" style="display: none;">
              ${this.formatWeatherData(station)}
            </div>
          </div>
        `
          )
          .join("")}
      </div>
    `;

    // Add click handlers for accordions
    const accordions = mapContent.querySelectorAll(".station-accordion");
    accordions.forEach((accordion) => {
      const header = accordion.querySelector(".station-header");
      const content = accordion.querySelector(".station-content");
      const icon = header.querySelector(".toggle-icon");
      const stationId = parseInt(accordion.dataset.stationId);

      header.addEventListener("click", () => {
        const station = this.bookmarkedStations.find((s) => s.id === stationId);
        if (station) {
          // Center map on station
          this.map.setView([station.lat, station.lon], 12, {
            animate: true,
          });

          // Set active station
          this.activeStation = station.id;

          // Check if accordion is open
          const isOpen = content.style.display === "block";

          // Close all other accordions
          accordions.forEach((acc) => {
            if (acc !== accordion) {
              acc.querySelector(".station-content").style.display = "none";
              acc.querySelector(".toggle-icon").textContent = "▼";
            }
          });

          // Toggle clicked accordion
          content.style.display = isOpen ? "none" : "block";
          icon.textContent = isOpen ? "▼" : "▲";

          // Only switch to single view if closing the accordion
          if (isOpen) {
            const myLocationsLink = document.querySelector(".map-footer a");
            myLocationsLink.click();

            // Update URL
            const path = window.location.pathname.replace(/\/+$/, "");
            window.history.pushState(null, "", `${path}#${station.id}`);
          }
        }
      });
    });
  }

  loadBookmarkedStations() {
    const saved = localStorage.getItem("bookmarkedStations");
    return saved ? JSON.parse(saved) : [];
  }

  setupMyLocationsHandler() {
    const myLocationsLink = document.querySelector(".map-footer a");
    if (!myLocationsLink) return;

    myLocationsLink.addEventListener("click", (e) => {
      e.preventDefault();
      this.toggleMyLocations();
    });
  }

  formatWeatherData(station) {
    if (!station.weather_data) {
      return '<p class="no-data">No weather data available</p>';
    }

    try {
      const weatherData = JSON.parse(station.weather_data);
      const temps = weatherData.metric; // Default to metric units

      return `
        <div class="weather-info">
          <p class="weather-main">
            Weather: <span>${weatherData.weather.main} - ${weatherData.weather.description}</span>
          </p>
          <p class="weather-temp">
            Temp: <span>${temps.temp}°C / Feels like: ${temps.feels_like}°C</span>
          </p>
          <p class="weather-pressure">
            Pressure: <span>${weatherData.weather.pressure} hPa</span>
          </p>
          <p class="weather-humidity">
            Humidity: <span>${weatherData.weather.humidity}%</span>
          </p>
        </div>
      `;
    } catch (e) {
      console.error("Error formatting weather data:", e);
      return '<p class="error">Error displaying weather data</p>';
    }
  }

  showActiveStation() {
    if (!this.activeStation) return;

    const station = window.weatherStationData?.stations?.find(
      (s) => s.id === this.activeStation
    );

    if (station?.weather_data) {
      try {
        const weatherData = JSON.parse(station.weather_data);
        this.updateWeatherContent(station, weatherData);
      } catch (e) {
        console.error("Error parsing weather data:", e);
      }
    }
  }
}

document.addEventListener("DOMContentLoaded", () => {
  if (typeof L === "undefined") {
    console.error("Leaflet library not loaded");
    return;
  }
  const mapInitializer = new MapInitializer();
  mapInitializer.init();
});
