<script setup>
import { useTheme } from "vuetify";
import ScrollToTop from "@core/components/ScrollToTop.vue";
import { useThemeConfig } from "@core/composable/useThemeConfig";
import { hexToRgb } from "@layouts/utils";
import { onMounted } from "vue";
import axios from "axios";
import ability from "@/plugins/casl/ability";
import { useAbility } from "@casl/vue"; // o cómo importes ability
const {
  syncInitialLoaderTheme,
  syncVuetifyThemeWithTheme: syncConfigThemeWithVuetifyTheme,
  isAppRtl,
  handleSkinChanges,
} = useThemeConfig();

const { global } = useTheme();

// ℹ️ Sync current theme with initial loader theme
syncInitialLoaderTheme();
syncConfigThemeWithVuetifyTheme();
handleSkinChanges();

// onMounted(() => {
//   const token = sessionStorage.getItem("sessionToken");
//   if (token) {
//     axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
//     axios
//       .get("/api/user")
//       .then((res) => {
//         ability.update(res.data.ability || []);
//       })
//       .catch(() => {
//         ability.update([]);
//       });
//   } else {
//     ability.update([]);
//   }
// });
</script>

<template>
  <VLocaleProvider :rtl="isAppRtl">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp
      :style="`--v-global-theme-primary: ${hexToRgb(
        global.current.value.colors.primary
      )}`"
    >
      <RouterView />
      <ScrollToTop />
    </VApp>
  </VLocaleProvider>
</template>
