import { defineConfig } from "vite";
import path from "path";

export default defineConfig({
  build: {
    emptyOutDir: false,
    manifest: false,
    write: true,
    outDir: ".",
    rollupOptions: {
      input: {
        mapStyles: path.resolve(__dirname, "inc/Map/assets/scss/mapStyles.scss"),
        adminStyles: path.resolve(__dirname, "assets/scss/adminStyles.scss"),
      },
      output: {
        dir: ".",
        entryFileNames: (chunkInfo) => {
          if (chunkInfo.name === "mapStyles") {
            return "inc/Map/assets/css/main.css";
          }
          return "assets/css/admin.css";
        },
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.includes("mapStyles")) {
            return "inc/Map/assets/css/[name][extname]";
          }
          return "assets/css/[name][extname]";
        },
      },
    },
  },
});
