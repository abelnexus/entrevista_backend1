<script setup>
import { ref, computed, watch } from "vue";
import { VForm } from "vuetify/components/VForm";
import axiosIns from "../../plugins/axios";
import ability from "@/plugins/casl/ability";

const props = defineProps({
  rolePermissions: {
    type: Object,
    required: false,
    default: () => ({
      name: "",
      permissions: [],
    }),
  },

  isDialogVisible: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["update:isDialogVisible", "update:rolePermissions"]);

// Crear una variable local para manejar el nombre del rol
const roleName = ref(props.rolePermissions?.name || "");
const permissions = ref([]);
const isSelectAll = ref(false);
const role = ref("");
const refPermissionForm = ref(null);
const AllPermissions = ref([]);
const isIndeterminate = ref(false);

const checkedCount = computed(() => {
  let counter = 0;
  permissions.value.forEach((permission) => {
    if (permission.vista) counter++;
    if (permission.editar) counter++;
    if (permission.crear) counter++;
    if (permission.eliminar) counter++;
  });
  return counter;
});
watch(
  () => props.rolePermissions.name,
  (newName) => {
    roleName.value = newName || "";
  },
  { immediate: true }
);
const toggleSelectAll = () => {
  AllPermissions.value.forEach((permission) => {
    permission.vista = isSelectAll.value;
    permission.editar = isSelectAll.value;
    permission.crear = isSelectAll.value;
    permission.eliminar = isSelectAll.value;
  });
};
watch(
  () => props.isDialogVisible,
  (newVal) => {
    if (newVal) {
      // Sincroniza el nombre del rol cuando se abre el diálogo
      roleName.value = props.rolePermissions?.name || "";

      // Llama a la función para obtener permisos solo cuando el diálogo se abre
      fetchPermissions();
    }
  }
);
// Observador para manejar la selección de todos
watch(isSelectAll, toggleSelectAll);

// Observador para manejar el estado indeterminado
watch(
  AllPermissions,
  (newPermissions) => {
    const allChecked = newPermissions.every(
      (p) => p.vista && p.editar && p.crear && p.eliminar // Verificar que todos los campos estén marcados
    );
    const noneChecked = newPermissions.every(
      (p) => !p.vista && !p.editar && !p.crear && !p.eliminar // Verificar que todos los campos estén desmarcados
    );

    isIndeterminate.value = !allChecked && !noneChecked;
  },
  { deep: true }
);

//Ajusta el `watch` que observa `rolePermissions` para que refleje el estado de los permisos.

const onReset = () => {
  emit("update:isDialogVisible", false);
  isSelectAll.value = false;
  refPermissionForm.value?.reset();
};

const fetchPermissions = async () => {
  try {
    const response = await axiosIns.get("/permissions"); // Ajusta según la ruta de tu API

    // Agrupar permisos por su nombre base
    const groupedPermissions = response.data.reduce((acc, permission) => {
      // Obtener el nombre base del permiso
      const baseName = permission.name.split(".")[0].replace(/_/g, " "); // Reemplaza guiones bajos por espacios

      // Si el grupo no existe, lo inicializamos
      if (!acc[baseName]) {
        acc[baseName] = {
          name: baseName.replace(/\b\w/g, (c) => c.toUpperCase()), // Capitaliza el nombre
          vista: false,
          editar: false,
          crear: false,
          eliminar: false,
        };
      }

      return acc;
    }, {});

    // Convertir el objeto en un array
    AllPermissions.value = Object.values(groupedPermissions);

    // Ahora, marca los permisos que tiene el rol
    props.rolePermissions.permissions.forEach((rolePermission) => {
      const baseName = rolePermission.name.split(".")[0].replace(/_/g, " "); // Nombre base
      const permissionGroup = AllPermissions.value.find(
        (p) => p.name.toLowerCase() === baseName.toLowerCase()
      );

      if (permissionGroup) {
        // Solo marca el permiso correspondiente
        if (rolePermission.name.endsWith("vista")) {
          permissionGroup.vista = true;
        } else if (rolePermission.name.endsWith("editar")) {
          permissionGroup.editar = true;
        } else if (rolePermission.name.endsWith("crear")) {
          permissionGroup.crear = true;
        } else if (rolePermission.name.endsWith("eliminar")) {
          permissionGroup.eliminar = true;
        }
      }
    });
  } catch (error) {
    console.error("Error al obtener roles:", error);
  }
};

// Observa cambios en isDialogVisible
watch(
  () => props.isDialogVisible,
  (newVal) => {
    if (newVal) {
      fetchPermissions();
    }
  }
);
const updateRolePermissions = async () => {
  const selectedPermissions = getSelectedPermissions();

  const data = {
    permissions: selectedPermissions,
    name: roleName.value,
  };

  if (props.rolePermissions.id) {
    data.role_id = props.rolePermissions.id;
  }

  try {
    // Actualizar los roles
    const response = await axiosIns.post("/roles/update", data);

    if (response.status === 200) {
      // Obtener la información del usuario actualizada
      const resp = await axiosIns.get("/user");

      if (resp.status === 200) {
        // Actualiza el localStorage con los nuevos datos de usuario
        sessionStorage.setItem("userData", JSON.stringify(resp.data));

        // Actualiza las habilidades de CASL
        const userAbilities = resp.data.ability || [];
        ability.update(userAbilities);

        console.log("Updated abilities: ", ability.rules);

        // Cierra el diálogo y resetea el formulario
        emit("update:isDialogVisible", false);
        isSelectAll.value = false;
        refPermissionForm.value?.reset();

        // Emitir evento al padre para actualizar la lista de roles
        emit("updateRoles");
        window.location.reload();
      }
    }
  } catch (error) {
    console.error("Error updating permissions", error);
  }
};
const getSelectedPermissions = () => {
  const selectedPermissions = [];

  AllPermissions.value.forEach((permission) => {
    if (permission.vista) {
      selectedPermissions.push(
        `${permission.name.toLowerCase().replace(/ /g, "_")}.vista`
      );
    }
    if (permission.editar) {
      selectedPermissions.push(
        `${permission.name.toLowerCase().replace(/ /g, "_")}.editar`
      );
    }
    if (permission.crear) {
      selectedPermissions.push(
        `${permission.name.toLowerCase().replace(/ /g, "_")}.crear`
      );
    }
    if (permission.eliminar) {
      selectedPermissions.push(
        `${permission.name.toLowerCase().replace(/ /g, "_")}.eliminar`
      );
    }
  });

  return selectedPermissions;
};
const onSubmit = () => {
  updateRolePermissions();
};
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 900"
    :model-value="props.isDialogVisible"
    @update:model-value="onReset"
  >
    <!-- 👉 Dialog close btn -->
    <DialogCloseBtn @click="onReset" />

    <VCard class="pa-sm-8 pa-5">
      <!-- 👉 Title -->
      <VCardItem class="text-center">
        <VCardTitle class="text-h3 mb-3">
          {{ props.rolePermissions.name ? "Edit" : "Add New" }} Role
        </VCardTitle>
        <p class="text-base mb-0">Set Role Permissions</p>
      </VCardItem>

      <VCardText class="mt-6">
        <!-- 👉 Form -->
        <VForm ref="refPermissionForm" @submit.native.prevent="onSubmit">
          <!-- 👉 Role name -->
          <AppTextField
            v-model="roleName"
            label="Role Name"
            placeholder="Enter Role Name"
          />
          <h6 class="text-h4 mt-8 mb-3">Role Permissions</h6>
          <!-- 👉 Role Permissions -->
          <VTable class="permission-table text-no-wrap">
            <!-- 👉 Administrator Access -->
            <tr>
              <td>Administrator Access</td>
              <td colspan="4">
                <div class="d-flex justify-end">
                  <VCheckbox
                    v-model="isSelectAll"
                    v-model:indeterminate="isIndeterminate"
                    label="Select All"
                  />
                </div>
              </td>
            </tr>
            <template v-for="permission in AllPermissions" :key="permission.id">
              <tr>
                <td>{{ permission.name }}</td>
                <td>
                  <div class="d-flex justify-end">
                    <VCheckbox v-model="permission.vista" label="vista" />
                  </div>
                </td>
                <td>
                  <div class="d-flex justify-end">
                    <VCheckbox v-model="permission.editar" label="editar" />
                  </div>
                </td>
                <td>
                  <div class="d-flex justify-end">
                    <VCheckbox v-model="permission.crear" label="crear" />
                  </div>
                </td>
                <td>
                  <div class="d-flex justify-end">
                    <VCheckbox v-model="permission.eliminar" label="eliminar" />
                  </div>
                </td>
              </tr>
            </template>
          </VTable>

          <!-- 👉 Actions button -->
          <div class="d-flex align-center justify-center gap-3 mt-6">
            <VBtn type="button" @click.prevent="onSubmit"> Submit </VBtn>
            <VBtn color="secondary" variant="tonal" @click="onReset">
              Cancel
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss">
.permission-table {
  td {
    border-block-end: 1px solid
      rgba(var(--v-border-color), var(--v-border-opacity));
    padding-block: 0.5rem;

    .v-checkbox {
      min-inline-size: 4.75rem;
    }

    &:not(:first-child) {
      padding-inline: 0.5rem;
    }

    .v-label {
      white-space: nowrap;
    }
  }
}
</style>
