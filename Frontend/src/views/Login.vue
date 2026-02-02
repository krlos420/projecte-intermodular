<template>
    <div class="login-container">
      <h1>Comp-Together</h1>
      <form @submit.prevent="handleLogin">
        <input 
          v-model="email" 
          type="email" 
          placeholder="Email" 
          required
        />
        <input 
          v-model="password" 
          type="password" 
          placeholder="Contraseña" 
          required
        />
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
      const email = ref('')
      const password = ref('')
      const error = ref('')
  
      const handleLogin = async () => {
        try {
          const response = await api.post('/auth/login', {
            email: email.value,
            password: password.value
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
        email,
        password,
        error,
        handleLogin
      }
    }
  }
  </script>
  
  <style scoped>
  .login-container {
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
  
  .error {
    color: red;
  }
  </style>