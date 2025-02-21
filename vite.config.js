import { defineConfig } from "vite";
import path from "path";

export default defineConfig({
  build: {
    outDir: "inc/Map/assets/css",
    emptyOutDir: false,
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, "inc/Map/assets/scss/main.scss"),
      },
      output: {
        entryFileNames: "style.css", 
        assetFileNames: "[name][extname]", //ensure no hash in file name ( prevent assets folder creation)
      },
    },
  },
});
