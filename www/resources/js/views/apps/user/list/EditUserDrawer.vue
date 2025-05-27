<script setup>
import { PerfectScrollbar } from "vue3-perfect-scrollbar";
import { emailValidator, requiredValidator } from "@validators";
import axiosIns from "../../../../plugins/axios";

const props = defineProps({
  isDrawerOpen: Boolean,
  user: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["update:isDrawerOpen", "userData"]);

const isFormValid = ref(false);
const refForm = ref();
const fullName = ref("");
const password = ref("");
const email = ref("");
const contact = ref("");
const role = ref();
const status = ref();
const roles = ref([]);
const id = ref("");

const resetForm = () => {
  fullName.value = "";
  role.value = "";
  contact.value = "";
  email.value = "";
  status.value = "";
};

watch(
  () => props.user,
  (newUser) => {
    if (newUser) {
      // Si hay un usuario seleccionado, llena los campos con sus datos
      id.value = newUser.id || "";
      fullName.value = newUser.name || ""; // Asigna valores por defecto en caso de que falte algún campo
      role.value = newUser.role.id || "";
      contact.value = newUser.cellphone || "";
      email.value = newUser.email || "";
      status.value = newUser.status || "";
    } else {
      // Si no hay usuario seleccionado, resetea el formulario
      resetForm();
    }
  },
  { immediate: true }
);
// 👉 drawer close
const closeNavigationDrawer = () => {
  emit("update:isDrawerOpen", false);
  nextTick(() => {
    refForm.value?.reset();
    refForm.value?.resetValidation();
  });
};

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const userData = {
        id: id.value,
        name: fullName.value,
        role: role.value,
        cellphone: contact.value,
        email: email.value,
        status: status.value,
      };

      // Realiza la petición POST con Axios
      axiosIns
        .post("/users/update", userData)
        .then((response) => {
          // Cierra el drawer si la petición fue exitosa
          emit("update:isDrawerOpen", false);
          // Emitir un evento personalizado al padre para actualizar la lista de usuarios
          emit("user-added");
          // Restablece el formulario
          id.value = "";
          nextTick(() => {
            refForm.value?.reset();
            refForm.value?.resetValidation();
          });
        })
        .catch((error) => {
          console.error("Error al crear el usuario:", error);
        });
    }
  });
};
const getRoles = async () => {
  try {
    const response = await axiosIns.get("/users/roles/id");
    roles.value = response.data;
    // Aquí puedes usar los roles como desees
  } catch (error) {
    console.error("Error al obtener roles:", error);
  }
};
getRoles();
const handleDrawerModelValueUpdate = (val) => {
  emit("update:isDrawerOpen", val);
};
</script>

<template>
  <VNavigationDrawer
    temporary
    :width="400"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- 👉 Title -->
    <AppDrawerHeaderSection title="Add User" @cancel="closeNavigationDrawer" />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
            <VRow>
              <!-- 👉 Full name -->
              <VCol cols="12">
                <AppTextField
                  v-model="fullName"
                  :rules="[requiredValidator]"
                  label="Nombre completo"
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol cols="12">
                <AppTextField
                  v-model="email"
                  :rules="[requiredValidator, emailValidator]"
                  label="Email"
                />
              </VCol>

              <!-- 👉 Email
              <VCol cols="12" v-if="!id">
                <AppTextField
                  v-model="password"
                  :rules="[requiredValidator]"
                  label="Password"
                  type="password"
                />
              </VCol> -->

              <!-- 👉 Contact -->
              <VCol cols="12">
                <AppTextField
                  v-model="contact"
                  type="number"
                  :rules="[requiredValidator]"
                  label="Celular"
                />
              </VCol>

              <!-- 👉 Role -->
              <VCol cols="12">
                <AppSelect
                  v-model="role"
                  label="Select Role"
                  :rules="[requiredValidator]"
                  :items="roles"
                />
              </VCol>

              <!-- 👉 Status -->
              <VCol cols="12">
                <AppSelect
                  v-model="status"
                  label="Select Status"
                  :rules="[requiredValidator]"
                  :items="[
                    { title: 'Activo', value: 'activo' },
                    { title: 'Inactivo', value: 'inactivo' },
                    { title: 'Pendiente', value: 'pendiente' },
                  ]"
                />
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn type="submit" class="me-3"> Registrar </VBtn>
                <VBtn
                  type="reset"
                  variant="tonal"
                  color="secondary"
                  @click="closeNavigationDrawer"
                >
                  Cancel
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
