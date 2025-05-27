<script setup>
import { VDataTableServer } from "vuetify/labs/VDataTable";
import { paginationMeta } from "@layouts/tables/utils";
import axiosIns from "../../plugins/axios";

const headers = [
  {
    title: "Descripcion",
    key: "name",
  },
  {
    title: "Asignado a",
    key: "assignedTo",
    sortable: false,
  },
  {
    title: "Fecha de creacion",
    key: "createdDate",
    sortable: false,
  },
];
const permissions = ref([]);
const search = ref("");
const totalPermissions = ref(0);

const isPermissionDialogVisible = ref(false);
const isAddPermissionDialogVisible = ref(false);
const permissionName = ref("");
const totalPages = ref(1);

const options = ref({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
  groupBy: [],
});

const fetchPermissions = () => {
  axiosIns
    .get("/apps/permissions/data", {
      params: {
        q: search.value,
        options: options.value,
      },
    })
    .then((response) => {
      // Asegúrate de que `totalPages` sea un número entero
      totalPages.value = Number(response.data.totalPages) || 1;
      permissions.value = response.data.permissions;
      totalPermissions.value = response.data.totalPermissions;
    })
    .catch((error) => {
      console.log(error);
    });
};

// onMounted(fetchPermissions);

watchEffect(fetchPermissions);

const editPermission = (name) => {
  isPermissionDialogVisible.value = true;
  permissionName.value = name;
};
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h5 class="text-h4 mb-6">Permissions List</h5>
      <p class="mb-0">
        Each category (Basic, Professional, and Business) includes the four
        predefined roles shown below.
      </p>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText
          class="d-flex align-center justify-space-between flex-wrap gap-4"
        >
          <AppSelect
            :model-value="options.itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
              { value: -1, title: 'All' },
            ]"
            style="width: 5rem"
            @update:model-value="options.itemsPerPage = parseInt($event, 10)"
          />

          <div class="d-flex align-center gap-4 flex-wrap">
            <AppTextField
              v-model="search"
              placeholder="Search"
              density="compact"
              style="width: 12.5rem"
            />
            <VBtn
              density="default"
              @click="isAddPermissionDialogVisible = true"
            >
              Add Permission
            </VBtn>
          </div>
        </VCardText>

        <VDivider />

        <VDataTableServer
          :v-model:items-per-page="options.itemsPerPage"
          :v-model:page="options.page"
          :items-length="totalPermissions"
          :headers="headers"
          :items="permissions"
          class="text-medium-emphasis text-no-wrap"
          @update:options="options = $event"
        >
          <!-- Assigned To -->
          <template #item.assignedTo="{ item }">
            <!-- {{ item.raw.assignedTo }} -->
            <div class="d-flex gap-2">
              <VChip
                v-for="text in item.raw.assignedTo"
                :key="text"
                label
                class="font-weight-medium"
              >
                {{ text }}
              </VChip>
            </div>
          </template>

          <template #bottom>
            <VDivider />

            <div
              class="d-flex align-center justify-space-between flex-wrap gap-3 pa-5 pt-3"
            >
              <p class="text-sm text-medium-emphasis mb-0">
                {{ paginationMeta(options, totalPermissions) }}
              </p>
              <VPagination v-model="options.page" :length="totalPages" />
            </div>
          </template>

          <template #item.createdDate="{ item }">
            <span>{{ item.raw.createdDate }}</span>
          </template>
        </VDataTableServer>
      </VCard>

      <AddEditPermissionDialog
        v-model:isDialogVisible="isPermissionDialogVisible"
        v-model:permission-name="permissionName"
      />
      <AddEditPermissionDialog
        v-model:isDialogVisible="isAddPermissionDialogVisible"
      />
    </VCol>
  </VRow>
</template>
<route lang="yaml">
meta:
  action: permisos.vista
  subject: ACL
</route>
