<script setup>
import { VDataTableServer } from "vuetify/labs/VDataTable";
import { paginationMeta } from "@layouts/tables/utils";
import AddNewUserDrawer from "@/views/apps/user/list/AddNewUserDrawer.vue";
import EditUserDrawer from "@/views/apps/user/list/EditUserDrawer.vue";
import { useUserListStore } from "@/views/apps/user/useUserListStore";
import { avatarText } from "@core/utils/formatters";
import axiosIns from "../../plugins/axios";
import Swal from "sweetalert2";
import ability from "@/plugins/casl/ability";

const userListStore = useUserListStore();
const searchQuery = ref("");
const selectedRole = ref();
const selectedPlan = ref();
const selectedStatus = ref();
const totalPage = ref(1);
const totalUsers = ref(0);
const users = ref([]);
const roles = ref([]);
const selectedUser = ref(null); // Nuevo estado para almacenar los datos del usuario seleccionado
// Verificar si el usuario puede eliminar o editar en 'ACL'
const canDelete = ability.can("usuarios.eliminar", "ACL");
const canEdit = ability.can("usuarios.editar", "ACL");
const canCreate = ability.can("usuarios.crear", "ACL");

const options = ref({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
  groupBy: [],
  search: undefined,
});

// Headers
const headers = [
  {
    title: "Usuario",
    key: "user",
  },
  {
    title: "Role",
    key: "role",
  },
  {
    title: "Celular",
    key: "cellphone",
  },
  {
    title: "Status",
    key: "status",
  },
  {
    title: "Actions",
    key: "actions",
    sortable: false,
  },
];
// 👉 Fetching users
const fetchUsers = () => {
  userListStore
    .fetchUsers({
      q: searchQuery.value,
      status: selectedStatus.value,
      plan: selectedPlan.value,
      role: selectedRole.value,
      options: options.value,
    })
    .then((response) => {
      // Llama a la función para obtener roles
      getRoles();
      users.value = response.data.users;
      totalPage.value = response.data.totalPage;
      totalUsers.value = response.data.totalUsers;
      options.value.page = response.data.page;
    })
    .catch((error) => {
      console.error(error, "asdas");
    });
};

watchEffect(fetchUsers);

// 👉 search filters
// Función para obtener los roles
const getRoles = async () => {
  try {
    const response = await axiosIns.get("/users/roles/name");
    roles.value = response.data;
  } catch (error) {
    console.error("Error al obtener roles:", error);
  }
};
const status = [
  {
    title: "Pendiente",
    value: "pendiente",
  },
  {
    title: "Activo",
    value: "activo",
  },
  {
    title: "Inactivo",
    value: "inactivo",
  },
];

const resolveUserRoleVariant = (role) => {
  const roleLowerCase = role.toLowerCase();
  if (roleLowerCase === "employee")
    return {
      color: "warning",
      icon: "tabler-circle-check",
    };
  if (roleLowerCase === "author")
    return {
      color: "success",
      icon: "tabler-user",
    };
  if (roleLowerCase === "maintainer")
    return {
      color: "primary",
      icon: "tabler-chart-pie-2",
    };
  if (roleLowerCase === "editor")
    return {
      color: "info",
      icon: "tabler-edit",
    };
  if (roleLowerCase === "administrador")
    return {
      color: "error",
      icon: "tabler-device-laptop",
    };

  return {
    color: "primary",
    icon: "tabler-user",
  };
};

const resolveUserStatusVariant = (stat) => {
  const statLowerCase = stat.toLowerCase();
  if (statLowerCase === "pendiente") return "warning";
  if (statLowerCase === "activo") return "success";
  if (statLowerCase === "inactivo") return "secondary";

  return "primary";
};

const isAddNewUserDrawerVisible = ref(false);
const isEditUserDrawerVisible = ref(false);

const addNewUser = (userData) => {
  userListStore.addUser(userData);
  // refetch User
  fetchUsers();
};
// 👉 List
const userListMeta = [
  {
    icon: "tabler-user",
    color: "primary",
    title: "Session",
    stats: "21,459",
    percentage: +29,
    subtitle: "Total Users",
  },
  {
    icon: "tabler-user-plus",
    color: "error",
    title: "Paid Users",
    stats: "4,567",
    percentage: +18,
    subtitle: "Last week analytics",
  },
  {
    icon: "tabler-user-check",
    color: "success",
    title: "Active Users",
    stats: "19,860",
    percentage: -14,
    subtitle: "Last week analytics",
  },
  {
    icon: "tabler-user-exclamation",
    color: "warning",
    title: "Pending Users",
    stats: "237",
    percentage: +42,
    subtitle: "Last week analytics",
  },
];

const deleteUser = (id) => {
  // Mostrar SweetAlert2 para confirmar la eliminación
  Swal.fire({
    title: "¿Estás seguro?",
    text: "¡Este usuario será eliminado permanentemente!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#7367f0", // Rojo para el botón de confirmación
    cancelButtonColor: "#007bff", // Azul para el botón de cancelar
    confirmButtonText: "Sí, eliminarlo!",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      userListStore
        .deleteUser(id)
        .then(() => {
          // Refresca la lista de usuarios solo después de que la eliminación haya sido exitosa
          fetchUsers();
        })
        .catch((error) => {
          console.error("Error al eliminar el usuario:", error);
        });
    }
  });

  // Cambiar el color del texto de los botones a blanco
  const buttons = document.querySelectorAll(".swal2-confirm, .swal2-cancel");
  buttons.forEach((button) => {
    button.style.color = "#ffffff"; // Establece el color del texto en blanco
  });
};
const editUser = (user) => {
  selectedUser.value = user; // Establece el usuario seleccionado
  isEditUserDrawerVisible.value = true; // Abre el drawer
};
</script>
<template>
  <section>
    <VRow>
      <VCol cols="12">
        <VCard title="Search Filter">
          <!-- 👉 Filters -->
          <VCardText>
            <VRow>
              <!-- 👉 Select Role -->
              <VCol cols="12" sm="4">
                <AppSelect
                  v-model="selectedRole"
                  label="Select Role"
                  :items="roles"
                  clearable
                  clear-icon="tabler-x"
                />
              </VCol>

              <!-- 👉 Select Status -->
              <VCol cols="12" sm="4">
                <AppSelect
                  v-model="selectedStatus"
                  label="Select Status"
                  :items="status"
                  clearable
                  clear-icon="tabler-x"
                />
              </VCol>
            </VRow>
          </VCardText>

          <VDivider />

          <VCardText class="d-flex flex-wrap py-4 gap-4">
            <div class="me-3 d-flex gap-3">
              <AppSelect
                :model-value="options.itemsPerPage"
                :items="[
                  { value: 10, title: '10' },
                  { value: 25, title: '25' },
                  { value: 50, title: '50' },
                  { value: 100, title: '100' },
                  { value: -1, title: 'All' },
                ]"
                style="width: 6.25rem"
                @update:model-value="
                  options.itemsPerPage = parseInt($event, 10)
                "
              />
            </div>
            <VSpacer />

            <div
              class="app-user-search-filter d-flex align-center flex-wrap gap-4"
            >
              <!-- 👉 Search  -->
              <div style="inline-size: 10rem">
                <AppTextField
                  v-model="searchQuery"
                  placeholder="Search"
                  density="compact"
                />
              </div>

              <!-- 👉 Export button -->
              <VBtn
                variant="tonal"
                color="secondary"
                prepend-icon="tabler-screen-share"
              >
                Export
              </VBtn>

              <!-- 👉 Add user button -->
              <VBtn
                v-if="canCreate"
                prepend-icon="tabler-plus"
                @click="isAddNewUserDrawerVisible = true"
              >
                Agregar Usuario
              </VBtn>
            </div>
          </VCardText>

          <VDivider />

          <!-- SECTION datatable -->
          <VDataTableServer
            v-model:items-per-page="options.itemsPerPage"
            v-model:page="options.page"
            :items="users"
            :items-length="totalUsers"
            :headers="headers"
            class="text-no-wrap"
            @update:options="options = $event"
          >
            <!-- User -->
            <template #item.user="{ item }">
              <div class="d-flex align-center">
                <VAvatar
                  size="34"
                  :variant="!item.raw.avatar ? 'tonal' : undefined"
                  :color="
                    !item.raw.avatar
                      ? resolveUserRoleVariant(item.raw.role.name).color
                      : undefined
                  "
                  class="me-3"
                >
                  <VImg v-if="item.raw.avatar" :src="item.raw.avatar" />
                  <span v-else>{{ avatarText(item.raw.name) }}</span>
                </VAvatar>

                <div class="d-flex flex-column">
                  <h6 class="text-base">
                    {{ item.raw.name }}
                  </h6>

                  <span class="text-sm text-medium-emphasis">{{
                    item.raw.email
                  }}</span>
                </div>
              </div>
            </template>

            <!-- 👉 Role -->
            <template #item.role="{ item }">
              <div class="d-flex align-center gap-4">
                <VAvatar
                  :size="30"
                  :color="resolveUserRoleVariant(item.raw.role.name).color"
                  variant="tonal"
                >
                  <VIcon
                    :size="20"
                    :icon="resolveUserRoleVariant(item.raw.role.name).icon"
                  />
                </VAvatar>
                <span class="text-capitalize">{{ item.raw.role.name }}</span>
              </div>
            </template>

            <!-- Plan -->
            <template #item.cellphone="{ item }">
              <span class="text-capitalize font-weight-medium">{{
                item.raw.cellphone
              }}</span>
            </template>

            <!-- Status -->
            <template #item.status="{ item }">
              <VChip
                :color="resolveUserStatusVariant(item.raw.status)"
                size="small"
                label
                class="text-capitalize"
              >
                {{ item.raw.status }}
              </VChip>
            </template>

            <!-- Actions -->
            <template #item.actions="{ item }">
              <IconBtn v-if="canDelete" @click="deleteUser(item.raw.id)">
                <VIcon icon="tabler-trash" />
              </IconBtn>

              <IconBtn v-if="canEdit" @click="editUser(item.raw)">
                <VIcon icon="tabler-edit" />
              </IconBtn>
            </template>

            <!-- pagination -->
            <template #bottom>
              <VDivider />
              <div
                class="d-flex align-center justify-sm-space-between justify-center flex-wrap gap-3 pa-5 pt-3"
              >
                <p class="text-sm text-disabled mb-0">
                  {{ paginationMeta(options, totalUsers) }}
                </p>

                <VPagination
                  v-model="options.page"
                  :length="Math.ceil(totalUsers / options.itemsPerPage)"
                  :total-visible="
                    $vuetify.display.xs
                      ? 1
                      : Math.ceil(totalUsers / options.itemsPerPage)
                  "
                >
                  <template #prev="slotProps">
                    <VBtn
                      variant="tonal"
                      color="default"
                      v-bind="slotProps"
                      :icon="false"
                    >
                      Previous
                    </VBtn>
                  </template>

                  <template #next="slotProps">
                    <VBtn
                      variant="tonal"
                      color="default"
                      v-bind="slotProps"
                      :icon="false"
                    >
                      Next
                    </VBtn>
                  </template>
                </VPagination>
              </div>
            </template>
          </VDataTableServer>
          <!-- SECTION -->
        </VCard>

        <!-- 👉 Add New User -->
        <AddNewUserDrawer
          v-model:isDrawerOpen="isAddNewUserDrawerVisible"
          @user-data="addNewUser"
          @user-added="fetchUsers"
          :user="selectedUser"
        />
        <EditUserDrawer
          v-model:isDrawerOpen="isEditUserDrawerVisible"
          @user-data="addNewUser"
          @user-added="fetchUsers"
          :user="selectedUser"
        />
      </VCol>
    </VRow>
  </section>
</template>

<style lang="scss">
.app-user-search-filter {
  inline-size: 31.6rem;
}

.text-capitalize {
  text-transform: capitalize;
}

.user-list-name:not(:hover) {
  color: rgba(var(--v-theme-on-background), var(--v-medium-emphasis-opacity));
}
</style>
<route lang="yaml">
meta:
  action: usuarios.vista
  subject: ACL
</route>
