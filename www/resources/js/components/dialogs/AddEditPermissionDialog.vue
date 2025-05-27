<script setup>
import axios from "axios";

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
    default: false,
  },
  permissionName: {
    type: String,
    required: false,
    default: "",
  },
});

const emit = defineEmits(["update:isDialogVisible", "update:permissionName"]);
const isAlertVisible = ref(false);
const permissionName = ref("");

const onReset = () => {
  console.log("Cerrando diálogo y reseteando...");
  emit("update:isDialogVisible", false);
  permissionName.value = "";
};

const onSubmit = () => {
  // Construir los datos que se enviarán en la solicitud
  const data = {
    permission_name: permissionName.value,
  };

  // Hacer la solicitud POST con axios
  axios
    .post("/api/apps/permissions/create", data)
    .then((response) => {
      console.log("Datos enviados exitosamente:", response.data);
      emit("update:isDialogVisible", false);
      emit("update:permissionName", permissionName.value);
    })
    .catch((error) => {
      // Revisar si el error tiene un código de estado 409
      if (error.response && error.response.status === 409) {
        isAlertVisible.value = true; // Mostrar la alerta si es un 409
      } else {
        console.error("Error al enviar los datos:", error);
      }
    });
};

watch(props, () => {
  permissionName.value = props.permissionName;
});
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 600"
    :model-value="props.isDialogVisible"
  >
    <!-- 👉 dialog close btn -->
    <DialogCloseBtn @click="onReset" />

    <VCard class="pa-sm-8 pa-5">
      <!-- 👉 Title -->
      <VCardItem class="text-center">
        <VCardTitle class="text-h5">
          {{ props.permissionName ? "Editar" : "Agregar" }} Permisos
        </VCardTitle>
        <VCardSubtitle>
          {{ props.permissionName ? "Editar" : "Agregar" }} permisos para su
          requerimiento.
        </VCardSubtitle>
      </VCardItem>

      <VCardText class="mt-1">
        <!-- 👉 Form -->
        <VForm>
          <VAlert type="warning" title="Warning!" variant="tonal" class="mb-6">
            Si editas el nombre del permiso, podrías interrumpir la
            funcionalidad de permisos del sistema. Asegúrate de estar
            absolutamente seguro antes de continuar.
          </VAlert>

          <!-- 👉 Role name -->
          <div class="d-flex align-end gap-3 mb-3">
            <AppTextField
              v-model="permissionName"
              density="compact"
              label="Nombre del permiso"
              placeholder="Enter Permission Name"
            />

            <VBtn @click="onSubmit"> Guardar </VBtn>
          </div>
          <VAlert
            v-model="isAlertVisible"
            closable
            close-label="Close Alert"
            color="error"
          >
            El permiso ya existe
          </VAlert>
          <!-- <VCheckbox label="Set as core permission" /> -->
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
    padding-inline: 0;
  }
}
</style>
