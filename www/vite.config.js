import { fileURLToPath } from "node:url";
import vue from "@vitejs/plugin-vue";
import vueJsx from "@vitejs/plugin-vue-jsx";
import AutoImport from "unplugin-auto-import/vite";
import Components from "unplugin-vue-components/vite";
import { defineConfig } from "vite";
import Pages from "vite-plugin-pages";
import Layouts from "vite-plugin-vue-layouts";
import vuetify from "vite-plugin-vuetify";
import laravel from "laravel-vite-plugin";

// @ts-expect-error Known error: https://github.com/sxzz/unplugin-vue-macros/issues/257#issuecomment-1410752890
import DefineOptions from "unplugin-vue-define-options/vite";

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/js/main.js"],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    vueJsx(),

    // https://github.com/vuetifyjs/vuetify-loader/tree/next/packages/vite-plugin
    vuetify({
      styles: {
        configFile: "resources/styles/variables/_vuetify.scss",
      },
    }),
    Pages({
      dirs: ["./resources/js/pages"],
    }),
    Layouts({
      layoutsDirs: "./resources/js/layouts/",
    }),
    Components({
      dirs: [
        "resources/js/@core/components",
        "resources/js/views/demos",
        "resources/js/components",
      ],
      dts: true,
    }),
    AutoImport({
      eslintrc: {
        enabled: true,
        filepath: "./.eslintrc-auto-import.json",
      },
      imports: ["vue", "vue-router", "@vueuse/core", "@vueuse/math", "pinia"],
      vueTemplate: true,
    }),
    DefineOptions(),
  ],
  define: { "process.env": {} },
  server: {
    host: "localhost", // Add this to force IPv4 only
    port: 5173,
    hmr: {
      host: "localhost",
      port: 5173,
    },
    // Proxy Laravel API requests to the backend
    proxy: {
      "/api": {
        target: "http://localhost:8000", // Laravel backend
        changeOrigin: true,
        secure: false,
      },
    },
    watch: {
      // Ignore Laravel backend directories to prevent full page reload
      ignored: [
        "**/vendor/**",
        "**/storage/**",
        "**/public/**",
        "**/bootstrap/**",
        "**/app/**",
      ],
    },
  },
  resolve: {
    alias: {
      "@core-scss": fileURLToPath(
        new URL("./resources/styles/@core", import.meta.url)
      ),
      "@": fileURLToPath(new URL("./resources/js", import.meta.url)),
      "@themeConfig": fileURLToPath(
        new URL("./themeConfig.js", import.meta.url)
      ),
      "@core": fileURLToPath(new URL("./resources/js/@core", import.meta.url)),
      "@layouts": fileURLToPath(
        new URL("./resources/js/@layouts", import.meta.url)
      ),
      "@images": fileURLToPath(new URL("./resources/images/", import.meta.url)),
      "@styles": fileURLToPath(new URL("./resources/styles/", import.meta.url)),
      "@configured-variables": fileURLToPath(
        new URL("./resources/styles/variables/_template.scss", import.meta.url)
      ),
      "@axios": fileURLToPath(
        new URL("./resources/js/plugins/axios", import.meta.url)
      ),
      "@validators": fileURLToPath(
        new URL("./resources/js/@core/utils/validators", import.meta.url)
      ),
      apexcharts: fileURLToPath(
        new URL("node_modules/apexcharts-clevision", import.meta.url)
      ),
    },
  },
  build: {
    chunkSizeWarningLimit: 5000,
  },
  optimizeDeps: {
    exclude: ["vuetify"],
    entries: ["./resources/js/**/*.vue"],
  },
});
