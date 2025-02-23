import { defineConfig } from "vite";
import path from "path";

export default defineConfig({
  build: {
    emptyOutDir: false,
    manifest: false,
    write: true,
    outDir: ".",
    minify: "esbuild",
    cssMinify: true,
    cssCodeSplit: true,
    rollupOptions: {
      input: {
        mapStyles: path.resolve(
          __dirname,
          "inc/Map/assets/scss/mapStyles.scss"
        ),
        adminStyles: path.resolve(__dirname, "assets/scss/adminStyles.scss"),
        mainScript: path.resolve(__dirname, "inc/Map/assets/js/main.js"),
        map: path.resolve(__dirname, "inc/Map/assets/js/map.js"),
      },
      output: {
        dir: ".",
        entryFileNames: (chunkInfo) => {
          if (chunkInfo.name === "mainScript") {
            return "inc/Map/assets/js/main.min.js";
          }
          if (chunkInfo.name === "map") {
            return "inc/Map/assets/js/map.min.js";
          }
          if (chunkInfo.name === "mapStyles") {
            return "inc/Map/assets/css/main.min.css";
          }
          return "assets/css/admin.min.css";
        },
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.includes("mapStyles")) {
            return "inc/Map/assets/css/[name][extname]";
          }
          return "assets/css/[name][extname]";
        },
      },
    },
    css: {
      minify: true,
      devSourcemap: false,
    },
  },
});
