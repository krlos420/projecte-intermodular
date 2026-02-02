<template>
  <div class="contenedor-casa">
    <h1>Gestionar Casa</h1>
    
    <div class="seccion">
      <h2>Crear Nueva Casa</h2>
      <input v-model="nombreCasa" type="text" placeholder="Nombre de la casa" />
      <button @click="crearCasa">Crear Casa</button>
    </div>

    <div class="seccion">
      <h2>Unirse a Casa</h2>
      <input v-model="codigoInvitacion" type="text" placeholder="Código de invitación" />
      <button @click="unirCasa">Unirse</button>
    </div>

    <p v-if="mensaje" :class="tipoMensaje">{{ mensaje }}</p>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

export default {
  setup() {
    const router = useRouter()
    const nombreCasa = ref('')
    const codigoInvitacion = ref('')
    const mensaje = ref('')
    const tipoMensaje = ref('')

    const crearCasa = async () => {
      try {
        const response = await api.post('/houses/create', {
          name: nombreCasa.value
        })
        if (response.data.status === 'true') {
          mensaje.value = `Casa creada. Código: ${response.data.house.invite_code}`
          tipoMensaje.value = 'exito'
          setTimeout(() => router.push('/dashboard'), 2000)
        }
      } catch (err) {
        mensaje.value = err.response?.data?.message || 'Error al crear casa'
        tipoMensaje.value = 'error'
      }
    }

    const unirCasa = async () => {
      try {
        const response = await api.post('/houses/join', {
          invite_code: codigoInvitacion.value
        })
        if (response.data.status === 'true') {
          mensaje.value = 'Te has unido a la casa correctamente'
          tipoMensaje.value = 'exito'
          setTimeout(() => router.push('/dashboard'), 1500)
        }
      } catch (err) {
        mensaje.value = err.response?.data?.message || 'Código inválido'
        tipoMensaje.value = 'error'
      }
    }

    return { nombreCasa, codigoInvitacion, mensaje, tipoMensaje, crearCasa, unirCasa }
  }
}
</script>

<style scoped>
.contenedor-casa {
  max-width: 500px;
  margin: 50px auto;
  padding: 20px;
}
.seccion {
  margin: 30px 0;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
}
input {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  border: 1px solid #ddd;
  border-radius: 5px;
}
button {
  width: 100%;
  padding: 10px;
  background: #42b983;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}
.exito { color: green; }
.error { color: red; }
</style>
