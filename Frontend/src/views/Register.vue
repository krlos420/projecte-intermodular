<template>
  <div class="register-container">
    <div class="register-brand">
      <div class="brand-content">
        <h1>Únete a nosotros</h1>
        <p>Empieza a organizar tu casa hoy mismo.</p>
        <p class="brand-text">Crea grupos, divide gastos y vive tranquilo.</p>
      </div>
    </div>
    
    <div class="register-form-wrapper">
      <div class="form-card">
        <h2>Crear Cuenta 🚀</h2>
        <p class="subtitle">Rellena el formulario para comenzar</p>
        
        <form @submit.prevent="registrarUsuario">
          <div class="form-grid">
            <div class="form-group">
              <label>Nombre Completo</label>
              <input 
                v-model="nombre" 
                type="text" 
                placeholder="Tu nombre" 
                :class="{ 'input-error': erroresValidacion.nombre }"
                @blur="validarNombre"
                required 
              />
              <span v-if="erroresValidacion.nombre" class="error-text">{{ erroresValidacion.nombre }}</span>
            </div>

            <div class="form-group">
              <label>Teléfono</label>
              <input 
                v-model="telefono" 
                type="text" 
                placeholder="Tu teléfono" 
                :class="{ 'input-error': erroresValidacion.telefono }"
                @blur="validarTelefono"
                required 
              />
              <span v-if="erroresValidacion.telefono" class="error-text">{{ erroresValidacion.telefono }}</span>
            </div>
          </div>

          <div class="form-group">
            <label>Correo Electrónico</label>
            <input 
              v-model="correo" 
              type="email" 
              placeholder="ejemplo@correo.com" 
              :class="{ 'input-error': erroresValidacion.correo }"
              @blur="validarEmail"
              required 
            />
            <span v-if="erroresValidacion.correo" class="error-text">{{ erroresValidacion.correo }}</span>
          </div>

          <div class="form-group">
            <label>Contraseña</label>
            <input 
              v-model="contrasena" 
              type="password" 
              placeholder="Mínimo 8 caracteres" 
              :class="{ 'input-error': erroresValidacion.contrasena }"
              @blur="validarContrasena"
              required 
            />
            <span v-if="erroresValidacion.contrasena" class="error-text">{{ erroresValidacion.contrasena }}</span>
          </div>

          <button type="submit" class="btn-primary" :disabled="cargando">
            {{ cargando ? 'Registrando...' : 'Crear Cuenta' }}
          </button>
          
          <div v-if="error" class="error-msg">
            <span class="icon">⚠️</span> {{ error }}
          </div>
          
          <div v-if="exito" class="success-msg">
            <span class="icon">✅</span> {{ exito }}
          </div>
        </form>

        <div class="register-footer">
          <p>¿Ya tienes cuenta? <router-link to="/login">Inicia sesión aquí</router-link></p>
        </div>
      </div>
    </div>
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
    const cargando = ref(false)
    
    const erroresValidacion = reactive({
      nombre: '',
      correo: '',
      telefono: '',
      contrasena: ''
    })

    const validarNombre = () => {
      if (nombre.value.trim().length < 2) {
        erroresValidacion.nombre = 'Mínimo 2 caracteres'
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
        erroresValidacion.telefono = 'Entre 9 y 15 dígitos'
        return false
      }
      erroresValidacion.telefono = ''
      return true
    }

    const validarContrasena = () => {
      if (contrasena.value.length < 8) {
        erroresValidacion.contrasena = 'Mínimo 8 caracteres'
        return false
      }
      erroresValidacion.contrasena = ''
      return true
    }

    const registrarUsuario = async () => {
      const nombreValido = validarNombre()
      const emailValido = validarEmail()
      const telefonoValido = validarTelefono()
      const contrasenaValida = validarContrasena()

      if (!nombreValido || !emailValido || !telefonoValido || !contrasenaValida) {
        error.value = 'Por favor, corrige los errores marcados'
        return
      }

      error.value = ''
      exito.value = ''
      cargando.value = true

      try {
        const success = await userStore.register({
          name: nombre.value,
          email: correo.value,
          phone: telefono.value,
          registration_date: new Date().toISOString().split('T')[0],
          password: contrasena.value
        })

        if (success) {
          exito.value = '¡Cuenta creada! Redirigiendo...'
          setTimeout(() => {
            router.push('/create-join-house')
          }, 1500)
        }
      } catch (err) {
        console.error(err)
        if (err.response?.data?.errors) {
          const errors = err.response.data.errors
          const firstKey = Object.keys(errors)[0]
          error.value = errors[firstKey][0]
        } else if (userStore.error) {
          error.value = userStore.error
        } else {
          error.value = 'Error al registrar usuario'
        }
      } finally {
        cargando.value = false
      }
    }

    return { 
      nombre, 
      correo, 
      telefono, 
      contrasena, 
      error, 
      exito,
      cargando,
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
.register-container {
  display: flex;
  min-height: 100vh;
  width: 100%;
  background: white;
}

/* Sección Izquierda (Branding) */
.register-brand {
  flex: 1;
  background: linear-gradient(135deg, #35a372 0%, #2e7d5a 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  padding: 40px;
  position: relative;
  overflow: hidden;
}

.register-brand::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIiBvcGFjaXR5PSIwLjEiPjxwYXRoIGQ9Ik0wIDBoNDB2NDBIMHoiIGZpbGw9IiNmZmYiLz48L3N2Zz4=') repeat;
  opacity: 0.1;
}

.brand-content {
  max-width: 450px;
  text-align: center;
  z-index: 1;
}

.brand-content h1 {
  font-size: 3.5em;
  font-weight: 800;
  margin-bottom: 20px;
  letter-spacing: -1px;
}

.brand-content p {
  font-size: 1.5em;
  font-weight: 300;
  opacity: 0.9;
}

.brand-text {
  font-size: 1.1em;
  margin-top: 15px;
  opacity: 0.8;
}

/* Sección Derecha (Formulario) */
.register-form-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  background: #f8f9fa;
}

.form-card {
  width: 100%;
  max-width: 500px;
  padding: 40px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.05);
}

.form-card h2 {
  font-size: 2em;
  color: #1a1a1a;
  margin-bottom: 10px;
  font-weight: 700;
}

.subtitle {
  color: #666;
  margin-bottom: 35px;
  font-size: 1.1em;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group {
  margin-bottom: 25px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 600;
  font-size: 0.95em;
}

input {
  width: 100%;
  padding: 16px;
  border: 2px solid #e1e4e8;
  border-radius: 12px;
  font-size: 1em;
  transition: all 0.3s ease;
  background: #fdfdfd;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #42b983;
  background: white;
  box-shadow: 0 0 0 4px rgba(66, 185, 131, 0.1);
}

.input-error {
  border-color: #f44336;
  background: #fff5f5;
}

.error-text {
  color: #f44336;
  font-size: 0.85em;
  margin-top: 5px;
  display: block;
}

.btn-primary {
  width: 100%;
  padding: 16px;
  background: #42b983;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.1em;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 10px;
}

.btn-primary:hover:not(:disabled) {
  background: #35a372;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(66, 185, 131, 0.3);
}

.btn-primary:disabled {
  background: #a0dcb8;
  cursor: not-allowed;
  transform: none;
}

.error-msg {
  margin-top: 20px;
  padding: 12px;
  background: #fff5f5;
  border: 1px solid #fed7d7;
  color: #c53030;
  border-radius: 8px;
  font-size: 0.95em;
  display: flex;
  align-items: center;
  gap: 10px;
}

.success-msg {
  margin-top: 20px;
  padding: 12px;
  background: #f0fff4;
  border: 1px solid #c6f6d5;
  color: #2f855a;
  border-radius: 8px;
  font-size: 0.95em;
  display: flex;
  align-items: center;
  gap: 10px;
}

.register-footer {
  margin-top: 30px;
  text-align: center;
  border-top: 1px solid #edf2f7;
  padding-top: 20px;
}

.register-footer p {
  color: #666;
}

.register-footer a {
  color: #42b983;
  font-weight: 700;
  text-decoration: none;
  margin-left: 5px;
}

.register-footer a:hover {
  text-decoration: underline;
}

/* Responsive - Móvil */
@media (max-width: 900px) {
  .register-container {
    flex-direction: column;
  }
  
  .register-brand {
    padding: 30px 20px;
    flex: 0 0 auto;
    min-height: 180px;
  }
  
  .brand-content h1 {
    font-size: 2em;
  }
  
  .register-form-wrapper {
    flex: 1;
    padding: 20px;
    align-items: flex-start;
  }
  
  .form-card {
    box-shadow: none;
    padding: 20px 0;
    max-width: 100%;
    background: transparent;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
    gap: 0;
  }
}
</style>