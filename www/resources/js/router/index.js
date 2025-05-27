// import { setupLayouts } from "virtual:generated-layouts";
// import { createRouter, createWebHistory } from "vue-router";
// import routes from "~pages";
// import { isUserLoggedIn } from "./utils";

// const router = createRouter({
//   history: createWebHistory(import.meta.env.BASE_URL),
//   routes: [...setupLayouts(routes)],
// });
// // Guardia de navegación
// router.beforeEach((to, from, next) => {
//   const isLoggedIn = isUserLoggedIn();
//   console.log("isLoggedIn:", isLoggedIn);

//   // Si el usuario no está logueado y está intentando acceder a una ruta que no sea /login, redirigir al login
//   if (!isLoggedIn && to.name !== "login") {
//     console.log("Redirigiendo al login...");
//     next({ name: "login" }); // Cambia esto a la ruta que maneja tu login
//   } else {
//     next(); // Permitir acceso a la ruta
//   }
// });

// // Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
// export default router;

import { setupLayouts } from "virtual:generated-layouts";
import { createRouter, createWebHistory } from "vue-router";
import { isUserLoggedIn } from "./utils";
import axios from "axios";
import routes from "~pages";
import { canNavigate } from "@layouts/plugins/casl";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // ℹ️ We are redirecting to different pages based on role.
    // NOTE: Role is just for UI purposes. ACL is based on abilities.
    // {
    //   path: "/",
    //   redirect: (to) => {
    //     const userData = JSON.parse(localStorage.getItem("userData") || "{}");
    //     const userRole = userData && userData.role ? userData.role : null;
    //     if (userRole === "admin") return { name: "dashboards-analytics" };
    //     if (userRole === "client") return { name: "access-control" };

    //     return { name: "login", query: to.query };
    //   },
    // },
    // {
    //   path: "/pages/user-profile",
    //   redirect: () => ({
    //     name: "pages-user-profile-tab",
    //     params: { tab: "profile" },
    //   }),
    // },
    // {
    //   path: "/pages/account-settings",
    //   redirect: () => ({
    //     name: "pages-account-settings-tab",
    //     params: { tab: "account" },
    //   }),
    // },
    ...setupLayouts(routes),
  ],
});
// Opcional: mostrar loading mientras verifica la sesión
let isCheckingAuth = false;
async function checkAuth() {
  try {
    const res = await axios.get("/api/user", { withCredentials: true });
    return res.data; // usuario logueado
  } catch (err) {
    return null; // no autenticado
  }
}
router.beforeEach(async (to, from, next) => {
  if (isCheckingAuth) {
    // Espera que termine la comprobación si ya está en curso
    await new Promise((resolve) => {
      const interval = setInterval(() => {
        if (!isCheckingAuth) {
          clearInterval(interval);
          resolve();
        }
      }, 50);
    });
    return next();
  }

  isCheckingAuth = true;
  const user = await checkAuth();
  isCheckingAuth = false;

  if (to.meta.requiresAuth && !user) {
    // No está logueado y ruta protegida: ir a login
    return next({ name: "login" });
  }

  if (to.meta.redirectIfLoggedIn && user) {
    // Ya está logueado y quiere ir a login u otra ruta pública
    return next({ path: "/" });
  }

  return next();
});
// Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
// router.beforeEach((to, from, next) => {
//   const isLoggedIn = isUserLoggedIn();

//   // ℹ️ Commented code is legacy code

//   if (!canNavigate(to)) {
//     console.log("asdasd", to);
//     // Redirect to login if not logged in
//     // ℹ️ Only add `to` query param if `to` route is not index route
//     if (!isLoggedIn)
//       return next({
//         name: "login",
//         query: { to: to.name !== "index" ? to.fullPath : undefined },
//       });

//     // If logged in => not authorized
//     return next({ name: "not-authorized" });
//   }

//   // Redirect if logged in
//   if (to.meta.redirectIfLoggedIn && isLoggedIn) next("/");

//   return next();
// });
export default router;
