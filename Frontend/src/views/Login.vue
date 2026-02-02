<template>
  <div class="contenedor-login">
    <h1>Comp-Together</h1>
    <form @submit.prevent="iniciarSesion">
      <input v-model="correo" type="email" placeholder="Email" required />
      <input v-model="contrasena" type="password" placeholder="Contraseña" required />
      <button type="submit">Iniciar Sesión</button>
      <p v-if="error" class="error">{{ error }}</p>
      <p>¿No tienes cuenta? <router-link to="/register">Regístrate</router-link></p>
    </form>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

export default {
  setup() {
    const router = useRouter()
    const correo = ref('')
    const contrasena = ref('')
    const error = ref('')

    const iniciarSesion = async () => {
      try {
        const response = await api.post('/auth/login', {
          email: correo.value,
          password: contrasena.value
        })

        if (response.data.status === 'true') {
          localStorage.setItem('token', response.data.token)
          router.push('/dashboard')
        }
      } catch (err) {
        error.value = 'Credenciales incorrectas'
      }
    }

    return {
      correo,
      contrasena,
      error,
      iniciarSesion
    }
  }
}
</script>

<style scoped>
.contenedor-login {
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

.error {
  color: red;
}
</style>