<template>
  <div class="contenedor-registro">
    <h1>Registrarse - Comp-Together</h1>
    <form @submit.prevent="registrarUsuario">
      <input v-model="nombre" type="text" placeholder="Nombre" required />
      <input v-model="correo" type="email" placeholder="Email" required />
      <input v-model="telefono" type="text" placeholder="Teléfono" required />
      <input v-model="contrasena" type="password" placeholder="Contraseña" required />
      <button type="submit">Registrarse</button>
      <p v-if="error" class="error">{{ error }}</p>
      <p v-if="exito" class="exito">{{ exito }}</p>
    </form>
    <p>¿Ya tienes cuenta? <router-link to="/login">Inicia sesión</router-link></p>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

export default {
  setup() {
    const router = useRouter()
    const nombre = ref('')
    const correo = ref('')
    const telefono = ref('')
    const contrasena = ref('')
    const error = ref('')
    const exito = ref('')

    const registrarUsuario = async () => {
      try {
        const response = await api.post('/auth/register', {
          name: nombre.value,
          email: correo.value,
          phone: telefono.value,
          registration_date: new Date().toISOString().split('T')[0],
          password: contrasena.value
        })

        if (response.data.status === 'true') {
          exito.value = 'Usuario creado. Redirigiendo...'
          setTimeout(() => {
            localStorage.setItem('token', response.data.token)
            router.push('/create-join-house')
          }, 1500)
        }
      } catch (err) {
        error.value = 'Error al registrar usuario'
      }
    }

    return { nombre, correo, telefono, contrasena, error, exito, registrarUsuario }
  }
}
</script>

<style scoped>
.contenedor-registro {
  max-width: 400px;
  margin: 100px auto;
  padding: 20px;
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
.error { color: red; }
.exito { color: green; }
</style>