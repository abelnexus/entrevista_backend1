import { Ability } from "@casl/ability";

// Configuración inicial de las habilidades por defecto
export const initialAbility = [
  {
    action: "read",
    subject: "Auth",
  },
];

// Obtener userData desde el sessionStorage o localStorage
// const userData = JSON.parse(localStorage.getItem("userData"));
const userData = JSON.parse(sessionStorage.getItem("userData"));
// Verificar si hay habilidades disponibles en userData
const existingAbility = userData && userData.ability ? userData.ability : null;

// Exportar la habilidad con las reglas obtenidas o las habilidades por defecto
export default new Ability(existingAbility || initialAbility);
