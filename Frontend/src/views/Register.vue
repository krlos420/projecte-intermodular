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
  max-width: 450px;
  margin: 60px auto;
  padding: 40px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.contenedor-registro h1 {
  text-align: center;
  margin-bottom: 30px;
  background: linear-gradient(135deg, #42b983, #35a372);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.8em;
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

.exito {
  color: #4caf50;
  background: #e8f5e9;
  padding: 12px;
  border-radius: 8px;
  text-align: center;
  font-size: 0.95em;
}

.contenedor-registro > p {
  text-align: center;
  margin: 20px 0 0 0;
  color: #666;
}

.contenedor-registro > p a {
  color: #42b983;
  text-decoration: none;
  font-weight: 600;
}

.contenedor-registro > p a:hover {
  text-decoration: underline;
}

/* Responsive para tablets */
@media (max-width: 768px) {
  .contenedor-registro {
    margin: 40px 20px;
    padding: 30px;
  }
  
  .contenedor-registro h1 {
    font-size: 1.6em;
  }
}

/* Responsive para móviles */
@media (max-width: 480px) {
  .contenedor-registro {
    margin: 30px 15px;
    padding: 25px 20px;
    border-radius: 12px;
  }
  
  .contenedor-registro h1 {
    font-size: 1.4em;
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