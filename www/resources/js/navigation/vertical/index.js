import ability from "@/plugins/casl/ability"; // Importa tu ability configurada con CASL

// Verifica si el usuario puede navegar a la ruta con base en las abilities
const canNavigate = (action, subject) => {
  return ability.can(action, subject);
};

// Función para filtrar las rutas basadas en permisos
const filterRoutes = (routes) => {
  return routes.filter((route) => {
    // Si la ruta tiene hijos, verifica cada hijo
    if (route.children) {
      route.children = filterRoutes(route.children);
      // Filtra la ruta solo si tiene hijos visibles
      return route.children.length > 0;
    }

    // Si la ruta tiene acción y sujeto, verifica el permiso
    if (route.action && route.subject) {
      return canNavigate(route.action, route.subject);
    }

    // Si no tiene acción o sujeto, mostrar por defecto
    return true;
  });
};

// Aquí defines tus rutas originales
const routes = [
  {
    title: "Home",
    to: { name: "index" },
    icon: { icon: "tabler-smart-home" },
    action: "read", // Requiere este permiso
    subject: "Home",
  },
  {
    title: "dashboard administrador",
    to: { name: "dashboard-admin" },
    icon: { icon: "tabler-file" },
    action: "dashboard_admin.vista", // Requiere este permiso
    subject: "ACL",
  },
  {
    title: "dashboard estudiante",
    to: { name: "dashboard-student" },
    icon: { icon: "tabler-file" },
    action: "dashboard_student.vista", // Requiere este permiso
    subject: "ACL",
  },
  {
    title: "Users & Roles",
    icon: { icon: "tabler-settings" },
    children: [
      {
        title: "Roles",
        to: { name: "user-management-roles" },
        action: "roles.vista", // Requiere este permiso
        subject: "ACL",
      },
      {
        title: "Usuarios",
        to: { name: "user-management-Users" },
        action: "usuarios.vista", // Requiere este permiso
        subject: "ACL",
      },
      {
        title: "Permisos",
        to: { name: "user-management-Permisos" },
        action: "permisos.vista", // Requiere este permiso
        subject: "ACL",
      },
    ],
  },
];

// Filtra las rutas
const filteredRoutes = filterRoutes(routes);

// Exporta las rutas filtradas para que sean usadas en el menú
export default filteredRoutes;
