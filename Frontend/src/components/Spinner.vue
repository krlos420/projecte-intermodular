<template>
  <div class="spinner-contenedor">
    <div class="spinner"></div>
    <p v-if="mensaje" class="spinner-mensaje">{{ mensaje }}</p>
  </div>
</template>

<script>
export default {
  name: 'Spinner',
  props: {
    mensaje: {
      type: String,
      default: 'Cargando...'
    }
  }
}
</script>

<style scoped>
.spinner-contenedor {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  gap: 15px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #42b983;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.spinner-mensaje {
  margin: 0;
  color: #666;
  font-size: 0.95em;
}
</style>
