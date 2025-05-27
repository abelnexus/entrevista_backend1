<script setup>
import avatar1 from "@images/avatars/avatar-1.png";
import avatar10 from "@images/avatars/avatar-10.png";
import avatar2 from "@images/avatars/avatar-2.png";
import avatar3 from "@images/avatars/avatar-3.png";
import avatar4 from "@images/avatars/avatar-4.png";
import avatar5 from "@images/avatars/avatar-5.png";
import avatar6 from "@images/avatars/avatar-6.png";
import avatar7 from "@images/avatars/avatar-7.png";
import avatar8 from "@images/avatars/avatar-8.png";
import avatar9 from "@images/avatars/avatar-9.png";
import girlUsingMobile from "@images/pages/girl-using-mobile.png";
import { ref, onMounted } from "vue";
import axios from "axios";
import axiosIns from "../../plugins/axios"; // Asegúrate de que la ruta sea la correcta

const roles = ref([]);

const fetchRoles = async () => {
  try {
    const response = await axiosIns.get("/roles"); // Ajusta según la ruta de tu API
    console.log(response);
    roles.value = response.data.roles; // Asigna los roles obtenidos a la variable `roles`
  } catch (error) {
    console.error("Error al obtener roles:", error);
  }
};

// Llama a la función al montar el componente
onMounted(() => {
  fetchRoles();
});
const isRoleDialogVisible = ref(false);
const roleDetail = ref();
const isAddRoleDialogVisible = ref(false);

// const editPermission = (value) => {
//   isRoleDialogVisible.value = true;
//   roleDetail.value = value;
// };
const editPermission = (roles) => {
  // Suponiendo que `role` es el objeto de rol que ya contiene los permisos
  if (roles && roles.permissions) {
    roleDetail.value = {
      id: roles.id,
      name: roles.name,
      permissions: roles.permissions, // Aquí asignas los permisos directamente
    };
    isRoleDialogVisible.value = true; // Abre el diálogo
  } else {
    console.error("El rol no contiene permisos o es undefined");
  }
};
</script>

<template>
  <VRow>
    <!-- 👉 Roles -->
    <VCol v-for="item in roles" :key="item.role" cols="12" sm="6" lg="4">
      <VCard>
        <VCardText class="d-flex align-center pb-1">
          <span>Total {{ item.users.length }} usuarios</span>

          <VSpacer />

          <div class="v-avatar-group">
            <template v-for="(user, index) in item.users" :key="user.id">
              <VAvatar
                v-if="
                  item.users.length > 4 && item.users.length !== 4 && index < 3
                "
                size="36"
                :image="avatar1"
              />

              <VAvatar
                v-if="item.users.length === 4"
                size="36"
                :image="user.name"
              />
            </template>
            <VAvatar
              v-if="item.users.length > 4"
              :color="$vuetify.theme.current.dark ? '#4A5072' : '#f6f6f7'"
            >
              <span> +{{ item.users.length - 3 }} </span>
            </VAvatar>
          </div>
        </VCardText>

        <VCardText class="pb-5">
          <h4 class="text-h4">
            {{ item.name }}
          </h4>
          <div class="d-flex align-center">
            <a href="javascript:void(0)" @click="editPermission(item)">
              Editar Rol
            </a>

            <VSpacer />
            <VBtn icon color="disabled" variant="text" size="x-small">
              <VIcon size="24" icon="tabler-copy" />
            </VBtn>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Add New Role -->
    <VCol cols="12" sm="6" lg="4">
      <VCard
        class="h-100"
        :ripple="false"
        @click="isAddRoleDialogVisible = true"
      >
        <VRow no-gutters class="h-100">
          <VCol
            cols="5"
            class="d-flex flex-column justify-end align-center mt-5"
          >
            <img width="85" :src="girlUsingMobile" />
          </VCol>

          <VCol cols="7">
            <VCardText
              class="d-flex flex-column align-end justify-end gap-2 h-100"
              style="text-align: end"
            >
              <VBtn>Add New Role</VBtn>
              <span>Add role, if it doesn't exist.</span>
            </VCardText>
          </VCol>
        </VRow>
      </VCard>
      <AddEditRoleDialog
        v-bind:is-dialog-visible="isAddRoleDialogVisible"
        @update:isDialogVisible="isAddRoleDialogVisible = $event"
      />
    </VCol>
    <AddEditRoleDialog
      v-bind:is-dialog-visible="isRoleDialogVisible"
      v-bind:role-permissions="roleDetail"
      @update:isDialogVisible="isRoleDialogVisible = $event"
      @updateRoles="fetchRoles()"
    />
  </VRow>
</template>
<route lang="yaml">
meta:
  action: roles.vista
  subject: ACL
</route>
