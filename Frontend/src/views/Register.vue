<template>
    <div class="register-container">
      <h1>Registrarse - Comp-Together</h1>
      <form @submit.prevent="handleRegister">
        <input v-model="name" type="text" placeholder="Nombre" required />
        <input v-model="email" type="email" placeholder="Email" required />
        <input v-model="phone" type="text" placeholder="Teléfono" required />
        <input v-model="password" type="password" placeholder="Contraseña" required />
        <button type="submit">Registrarse</button>
        <p v-if="error" class="error">{{ error }}</p>
        <p v-if="success" class="success">{{ success }}</p>
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
      const name = ref('')
      const email = ref('')
      const phone = ref('')
      const password = ref('')
      const error = ref('')
      const success = ref('')
  
      const handleRegister = async () => {
        try {
          const response = await api.post('/auth/register', {
            name: name.value,
            email: email.value,
            phone: phone.value,
            registration_date: new Date().toISOString().split('T')[0],
            password: password.value
          })
  
          if (response.data.status === 'true') {
            success.value = 'Usuario creado. Redirigiendo...'
            setTimeout(() => {
              localStorage.setItem('token', response.data.token)
              router.push('/create-join-house')
            }, 1500)
          }
        } catch (err) {
          error.value = 'Error al registrar usuario'
        }
      }
  
      return { name, email, phone, password, error, success, handleRegister }
    }
  }
  </script>
  
  <style scoped>
  .register-container {
    max-width: 400px;
    margin: 100px auto;
    padding: 20px;
  }
  input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
  }
  button {
    width: 100%;
    padding: 10px;
    background: #42b983;
    color: white;
    border: none;
    cursor: pointer;
  }
  .error { color: red; }
  .success { color: green; }
  </style>