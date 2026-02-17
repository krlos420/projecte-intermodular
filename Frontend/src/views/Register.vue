<template>
  <div class="contenedor-registro">
    <h1>Registrarse - Comp-Together</h1>
    <form @submit.prevent="registrarUsuario">
      <div class="campo">
        <input 
          v-model="nombre" 
          type="text" 
          placeholder="Nombre" 
          :class="{ 'input-error': erroresValidacion.nombre }"
          @blur="validarNombre"
          required 
        />
        <span v-if="erroresValidacion.nombre" class="mensaje-error">{{ erroresValidacion.nombre }}</span>
      </div>

      <div class="campo">
        <input 
          v-model="correo" 
          type="email" 
          placeholder="Email" 
          :class="{ 'input-error': erroresValidacion.correo }"
          @blur="validarEmail"
          required 
        />
        <span v-if="erroresValidacion.correo" class="mensaje-error">{{ erroresValidacion.correo }}</span>
      </div>

      <div class="campo">
        <input 
          v-model="telefono" 
          type="text" 
          placeholder="Teléfono" 
          :class="{ 'input-error': erroresValidacion.telefono }"
          @blur="validarTelefono"
          required 
        />
        <span v-if="erroresValidacion.telefono" class="mensaje-error">{{ erroresValidacion.telefono }}</span>
      </div>

      <div class="campo">
        <input 
          v-model="contrasena" 
          type="password" 
          placeholder="Contraseña (mínimo 8 caracteres)" 
          :class="{ 'input-error': erroresValidacion.contrasena }"
          @blur="validarContrasena"
          required 
        />
        <span v-if="erroresValidacion.contrasena" class="mensaje-error">{{ erroresValidacion.contrasena }}</span>
      </div>

      <button type="submit">Registrarse</button>
      <p v-if="error" class="error">{{ error }}</p>
      <p v-if="exito" class="exito">{{ exito }}</p>
    </form>
    <p>¿Ya tienes cuenta? <router-link to="/login">Inicia sesión</router-link></p>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/UserStore'

export default {
  setup() {
    const router = useRouter()
    const userStore = useUserStore()

    const nombre = ref('')
    const correo = ref('')
    const telefono = ref('')
    const contrasena = ref('')
    const error = ref('')
    const exito = ref('')
    
    const erroresValidacion = reactive({
      nombre: '',
      correo: '',
      telefono: '',
      contrasena: ''
    })

    const validarNombre = () => {
      if (nombre.value.trim().length < 2) {
        erroresValidacion.nombre = 'El nombre debe tener al menos 2 caracteres'
        return false
      }
      erroresValidacion.nombre = ''
      return true
    }

    const validarEmail = () => {
      const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!regex.test(correo.value)) {
        erroresValidacion.correo = 'Email inválido'
        return false
      }
      erroresValidacion.correo = ''
      return true
    }

    const validarTelefono = () => {
      const regex = /^[0-9]{9,15}$/
      if (!regex.test(telefono.value.trim())) {
        erroresValidacion.telefono = 'Teléfono debe tener entre 9 y 15 dígitos'
        return false
      }
      erroresValidacion.telefono = ''
      return true
    }

    const validarContrasena = () => {
      if (contrasena.value.length < 8) {
        erroresValidacion.contrasena = 'La contraseña debe tener al menos 8 caracteres'
        return false
      }
      erroresValidacion.contrasena = ''
      return true
    }

    const registrarUsuario = async () => {
      // Validar todos los campos antes de enviar
      const nombreValido = validarNombre()
      const emailValido = validarEmail()
      const telefonoValido = validarTelefono()
      const contrasenaValida = validarContrasena()

      if (!nombreValido || !emailValido || !telefonoValido || !contrasenaValida) {
        error.value = 'Por favor, corrige los errores antes de continuar'
        return
      }

      error.value = ''
      exito.value = ''

      try {
        const success = await userStore.register({
          name: nombre.value,
          email: correo.value,
          phone: telefono.value,
          registration_date: new Date().toISOString().split('T')[0],
          password: contrasena.value
        })

        if (success) {
          exito.value = 'Usuario creado. Redirigiendo...'
          setTimeout(() => {
            router.push('/create-join-house')
          }, 1500)
        }
      } catch (err) {
        console.error(err)
        // El store ya maneja el error y lo guarda en userStore.error si es generico
        // Pero para errores de validacion especificos del backend, podemos acceder a err.response
        if (err.response?.data?.errors) {
          const errors = err.response.data.errors
          const firstKey = Object.keys(errors)[0]
          error.value = errors[firstKey][0]
        } else if (userStore.error) {
          error.value = userStore.error
        } else {
          error.value = 'Error al registrar usuario'
        }
      }
    }

    return { 
      nombre, 
      correo, 
      telefono, 
      contrasena, 
      error, 
      exito, 
      erroresValidacion,
      validarNombre,
      validarEmail,
      validarTelefono,
      validarContrasena,
      registrarUsuario 
    }
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

.campo {
  display: flex;
  flex-direction: column;
  gap: 6px;
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

input.input-error {
  border-color: #f44336;
  background: #ffebee;
}

input.input-error:focus {
  border-color: #f443 36;
  box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.1);
}

.mensaje-error {
  color: #f44336;
  font-size: 0.85em;
  padding-left: 4px;
  display: flex;
  align-items: center;
  gap: 5px;
}

.mensaje-error::before {
  content: '⚠️';
  font-size: 0.9em;
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