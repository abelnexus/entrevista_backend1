<template>
  <div>
    <VCard title="Bienvenido🙌">
      <VCardText>Vista de estudiantes</VCardText>
      <VCardText>
        esta vista solo pueden verla los estudiantes(caso le den los permisos ah
        algun rol mas)
      </VCardText>
    </VCard>
  </div>
</template>
<route lang="yaml">
meta:
  action: dashboard_student.vista
  subject: ACL
</route>
