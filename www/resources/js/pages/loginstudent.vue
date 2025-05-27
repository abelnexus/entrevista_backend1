<script setup>
import { VForm } from "vuetify/components/VForm";
import AuthProvider from "@/views/pages/authentication/AuthProvider.vue";
import { useGenerateImageVariant } from "@core/composable/useGenerateImageVariant";
import authV2LoginIllustrationBorderedDark from "@images/pages/auth-v2-login-illustration-bordered-dark.png";
import authV2LoginIllustrationBorderedLight from "@images/pages/auth-v2-login-illustration-bordered-light.png";
import authV2LoginIllustrationDark from "@images/pages/auth-v2-login-illustration-dark.png";
import authV2LoginIllustrationLight from "@images/pages/auth-v2-login-illustration-light.png";
import authV2MaskDark from "@images/pages/misc-mask-dark.png";
import authV2MaskLight from "@images/pages/misc-mask-light.png";
import { VNodeRenderer } from "@layouts/components/VNodeRenderer";
import { themeConfig } from "@themeConfig";
import axios from "axios";
import axiosIns from "axios";
import ability from "@/plugins/casl/ability";
import router from "@/router";

const authThemeImg = useGenerateImageVariant(
  authV2LoginIllustrationLight,
  authV2LoginIllustrationDark,
  authV2LoginIllustrationBorderedLight,
  authV2LoginIllustrationBorderedDark,
  true
);
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark);
const isPasswordVisible = ref(false);
const refVForm = ref();
const email = ref("student@gmail.com");
const password = ref("123456");
const rememberMe = ref(false);
let sessionToken = null;
const login = (event) => {
  event.preventDefault(); // Esto previene la recarga de la página
  // Aquí puedes continuar con el código de autenticación
  // por ejemplo, llamando a la API con axios:
  axios
    .post("/api/login", {
      email: email.value,
      password: password.value,
      login_as: "student",
    })
    .then((response) => {
      // Guardar el token en localStorage
      sessionStorage.setItem("accessToken", response.data.accessToken);
      axios
        .get("/api/user")
        .then((response) => {
          // Guardar los datos del usuario en localStorage
          sessionStorage.setItem("userData", JSON.stringify(response.data));
          // Extraer las habilidades del usuario
          const userAbilities = response.data.ability || [];
          // Actualiza las habilidades de CASL
          ability.update(userAbilities);
          // Verifica las habilidades actualizadas
          console.log("Updated abilities: ", ability.rules);
          // window.location.href = "/";
          router.push("/");
        })
        .catch((error) => {
          console.error("Error during login:", error);
        });
    })
    .catch((error) => {
      console.error("Error during login:", error);
    });
};
// const login = (event) => {
//   event.preventDefault();

//   axios
//     .post(
//       "/api/login",
//       {
//         email: email.value,
//         password: password.value,
//         login_as: "student", // o "admin"
//       },
//       {
//         withCredentials: true, // IMPORTANTE para que las cookies se guarden
//       }
//     )
//     .then((res) => {
//       console.log("Login correcto");
//       // No necesitas guardar nada, la cookie está presente
//       axios
//         .get("/api/user", { withCredentials: true })
//         .then((response) => {
//           // Guardar los datos del usuario en localStorage
//           // Extraer las habilidades del usuario
//           const userAbilities = response.data.ability || [];
//           console.log(userAbilities);
//           // Actualiza las habilidades de CASL
//           ability.update(userAbilities);
//           // Verifica las habilidades actualizadas
//           router.push("/"); // NAVEGA sin recargar la página
//         })
//         .catch((error) => {
//           console.error("Error during login:", error);
//         });
//     });
// };
// const login = (event) => {
//   event.preventDefault(); // Esto previene la recarga de la página
//   // Aquí puedes continuar con el código de autenticación
//   // por ejemplo, llamando a la API con axios:
//   axios
//     .post("/api/login", {
//       email: email.value,
//       password: password.value,
//     })
//     .then((response) => {
//       // Guardar el token en localStorage
//       localStorage.setItem("accessToken", response.data.accessToken);
//       axios
//         .get("/api/user")
//         .then((response) => {
//           // Guardar los datos del usuario en localStorage
//           localStorage.setItem("userData", JSON.stringify(response.data));
//           // Extraer las habilidades del usuario
//           const userAbilities = response.data.ability || [];
//           // Actualiza las habilidades de CASL
//           ability.update(userAbilities);
//           // Verifica las habilidades actualizadas
//           console.log("Updated abilities: ", ability.rules);
//           // window.location.href = "/";
//         })
//         .catch((error) => {
//           console.error("Error during login:", error);
//         });
//     })
//     .catch((error) => {
//       console.error("Error during login:", error);
//     });
// };
</script>

<template>
  <VRow no-gutters class="auth-wrapper bg-surface">
    <VCol lg="8" class="d-none d-lg-flex">
      <div class="position-relative bg-background rounded-lg w-100 ma-8 me-0">
        <div class="d-flex align-center justify-center w-100 h-100">
          <VImg
            max-width="505"
            :src="authThemeImg"
            class="auth-illustration mt-16 mb-2"
          />
        </div>

        <VImg :src="authThemeMask" class="auth-footer-mask" />
      </div>
    </VCol>

    <VCol
      cols="12"
      lg="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
        <VCardText>
          <VNodeRenderer :nodes="themeConfig.app.logo" class="mb-6" />

          <h5 class="text-h5 mb-1">entrevista tecnica backend 1</h5>

          <p class="mb-0">login para estudiantes</p>
        </VCardText>

        <VCardText>
          <VAlert color="primary" variant="tonal">
            <p class="text-caption mb-2">
              student Email: <strong>student@demo.com</strong> / Pass:
              <strong>123456</strong>
            </p>
          </VAlert>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="login">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField
                  v-model="email"
                  label="Email"
                  type="email"
                  autofocus
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="password"
                  label="Password"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="
                    isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'
                  "
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div
                  class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4"
                >
                  <VCheckbox v-model="rememberMe" label="Remember me" />
                  <a class="text-primary ms-2 mb-1" href="#">
                    Forgot Password?
                  </a>
                </div>

                <!-- <VBtn block type="submit"> Login </VBtn> -->
                <VBtn block type="button" @click="login"> Login </VBtn>
              </VCol>

              <!-- create account -->
              <VCol cols="12" class="text-center">
                <span>New on our platform?</span>

                <a class="text-primary ms-2" href="#"> Create an account </a>
              </VCol>

              <VCol cols="12" class="d-flex align-center">
                <VDivider />

                <span class="mx-4">or</span>

                <VDivider />
              </VCol>

              <!-- auth providers -->
              <VCol cols="12" class="text-center">
                <AuthProvider />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
  action: read
  subject: Auth
</route>
