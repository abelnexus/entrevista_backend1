<script setup>
import axiosIns from "../../plugins/axios";
import avatar1 from "@images/avatars/avatar-1.png";
import ability from "@/plugins/casl/ability";

const loading = ref(false);
const error = ref(null);
const userName = ref("Invitado");
const userRole = ref("");

const logout = (event) => {
  event.preventDefault(); // Esto previene la recarga de la página
  loading.value = true; // Cambiar el estado de carga si es necesario
  error.value = null; // Limpiar cualquier error previo

  axiosIns
    .get("/logout") // Llamar a la API de logout
    .then((response) => {
      // Verificar si la respuesta fue exitosa
      if (response.status === 200) {
        // Limpiar el localStorage
        sessionStorage.removeItem("userData");
        sessionStorage.removeItem("accessToken"); // Si también tienes un token que limpiar

        // Limpiar habilidades en CASL
        ability.update([]);
        // Redirigir a la página de login o donde desees
        window.location.href = "/login";
      }
    })
    .catch((error) => {
      error.value = "Error al cerrar sesión. Inténtalo de nuevo.";
      console.error("Error during logout:", error);
    })
    .finally(() => {
      loading.value = false; // Cambiar el estado de carga a falso al final
    });
};
onMounted(() => {
  const userData = sessionStorage.getItem("userData");
  if (userData) {
    const user = JSON.parse(userData);
    userName.value = user.name || "Invitado";
    userRole.value = user.role || "";
  }
});
</script>

<template>
  <VBadge
    dot
    location="bottom right"
    offset-x="3"
    offset-y="3"
    bordered
    color="success"
  >
    <VAvatar class="cursor-pointer" color="primary" variant="tonal">
      <VImg :src="avatar1" />

      <!-- SECTION Menu -->
      <VMenu activator="parent" width="230" location="bottom end" offset="14px">
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                >
                  <VAvatar color="primary" variant="tonal">
                    <VImg :src="avatar1" />
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle class="font-weight-semibold">
              {{ userName }}
            </VListItemTitle>
            <VListItemSubtitle> {{ userRole }}</VListItemSubtitle>
          </VListItem>

          <VDivider class="my-2" />

          <!-- 👉 Profile -->
          <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="tabler-user" size="22" />
            </template>

            <VListItemTitle>Profile</VListItemTitle>
          </VListItem>

          <!-- 👉 Settings -->
          <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="tabler-settings" size="22" />
            </template>

            <VListItemTitle>Settings</VListItemTitle>
          </VListItem>

          <!-- 👉 Pricing -->
          <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="tabler-currency-dollar" size="22" />
            </template>

            <VListItemTitle>Pricing</VListItemTitle>
          </VListItem>

          <!-- 👉 FAQ -->
          <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="tabler-help" size="22" />
            </template>

            <VListItemTitle>FAQ</VListItemTitle>
          </VListItem>

          <!-- Divider -->
          <VDivider class="my-2" />

          <!-- 👉 Logout -->
          <VListItem @click="logout">
            <template #prepend>
              <VIcon class="me-2" icon="tabler-logout" size="22" />
            </template>

            <VListItemTitle>Logout</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>
