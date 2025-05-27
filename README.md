# Entrevista Backend - Laravel con Roles y Permisos

Proyecto en Laravel que implementa autenticación, roles y permisos con Docker.

## 🚀 Cómo ejecutar el proyecto

1. Clona el repositorio:
   ```bash
   git clone https://github.com/abelnexus/entrevista_backend1.git
   cd entrevista_backend1
2. construye los contenedores con docker
   ```bash
   docker-compose up -d --build
3. entrar al contenedor
   ```bash
   docker exec -it <nombre_contenedor> bash
4. Ejecuta los siguientes comandos dentro del contenedor:
   ```bash
   composer install
   npm install
   php artisan key:generate
   php artisan migrate --seed
   npm run dev
5. una vez dentro del sistema, hacer lo siguiente
   - dirigirse al menu permisos -> crear nuevo permiso, el nombre del permiso debe ser = "dashboard_student" (debe ser exactamente igual)
   - dirigirse al menu roles -> crear nuevo rol = "estudiante", checkear el permiso vista de dashboard_student, guardar
   - dirigirse al menu -> usuarios -> crear usuario, llenar los datos y elegir el rol "estudiante" y estado "activo", registrar
   - iniciar session en una pestaña nueva, con los datos del usuario que acabamos de crear, listo probar la funcionalidad

herramientas utilizados
docker-desktop, docker-compose, vsc, laravel 10.8 (php 8.1), vuejs(version 3), template vuexy, git, github, mysql, spa.
librerias, samctun, spatie-laravel-permissions, casl(vue), router, axios,
