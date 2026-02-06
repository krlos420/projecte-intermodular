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
  max-width: 450px;
  margin: 80px auto;
  padding: 40px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.contenedor-login h1 {
  text-align: center;
  margin-bottom: 30px;
  background: linear-gradient(135deg, #42b983, #35a372);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 2em;
}

form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

input {
  width: 100%;
  padding: 14px 16px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1em;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #42b983;
  box-shadow: 0 0 0 3px rgba(66, 185, 131, 0.1);
}

button {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #42b983, #35a372);
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 8px;
  font-size: 1.05em;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
  margin-top: 10px;
}

button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(66, 185, 131, 0.3);
}

button:active {
  transform: translateY(0);
}

.error {
  color: #f44336;
  background: #ffebee;
  padding: 12px;
  border-radius: 8px;
  text-align: center;
  font-size: 0.95em;
}

form p {
  text-align: center;
  margin: 15px 0 0 0;
  color: #666;
}

form p a {
  color: #42b983;
  text-decoration: none;
  font-weight: 600;
}

form p a:hover {
  text-decoration: underline;
}

/* Responsive para tablets */
@media (max-width: 768px) {
  .contenedor-login {
    margin: 60px 20px;
    padding: 30px;
  }
  
  .contenedor-login h1 {
    font-size: 1.75em;
  }
}

/* Responsive para móviles */
@media (max-width: 480px) {
  .contenedor-login {
    margin: 40px 15px;
    padding: 25px 20px;
    border-radius: 12px;
  }
  
  .contenedor-login h1 {
    font-size: 1.5em;
    margin-bottom: 25px;
  }
  
  input {
    padding: 12px 14px;
    font-size: 0.95em;
  }
  
  button {
    padding: 12px;
    font-size: 1em;
  }
}
</style>