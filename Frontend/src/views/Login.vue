<template>
  <div class="login-container">
    <div class="login-brand">
      <div class="brand-content">
        <h1>Comp-Together</h1>
        <p>Tu convivencia, simple y justa.</p>
        <p class="brand-text">Gestiona gastos, tareas y compras sin complicaciones.</p>
      </div>
    </div>
    
    <div class="login-form-wrapper">
      <div class="form-card">
        <h2>Hola de nuevo 👋</h2>
        <p class="subtitle">Ingresa tus datos para continuar</p>
        
        <form @submit.prevent="iniciarSesion">
          <div class="form-group">
            <label>Correo Electrónico</label>
            <input v-model="correo" type="email" placeholder="ejemplo@correo.com" required />
          </div>

          <div class="form-group">
            <label>Contraseña</label>
            <input v-model="contrasena" type="password" placeholder="Tu contraseña segura" required />
          </div>

          <button type="submit" class="btn-primary" :disabled="cargando">
            {{ cargando ? 'Iniciando...' : 'Iniciar Sesión' }}
          </button>
          
          <div v-if="error" class="error-msg">
            <span class="icon">⚠️</span> {{ error }}
          </div>
        </form>

        <div class="login-footer">
          <p>¿Aún no tienes cuenta? <router-link to="/register">Regístrate gratis</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/UserStore'

export default {
  setup() {
    const router = useRouter()
    const userStore = useUserStore()
    
    const correo = ref('')
    const contrasena = ref('')
    const error = ref('')
    const cargando = ref(false)

    const iniciarSesion = async () => {
      error.value = ''
      cargando.value = true
      try {
        const success = await userStore.login({
          email: correo.value,
          password: contrasena.value
        })

        if (success) {
          router.push('/dashboard')
        }
      } catch (err) {
        console.error(err)
        error.value = userStore.error || 'Credenciales incorrectas'
      } finally {
        cargando.value = false
      }
    }

    return {
      correo,
      contrasena,
      error,
      cargando,
      iniciarSesion
    }
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  min-height: 100vh;
  width: 100%;
  background: white;
}

/* Sección Izquierda (Branding) */
.login-brand {
  flex: 1;
  background: linear-gradient(135deg, #42b983 0%, #2e7d5a 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  padding: 40px;
  position: relative;
  overflow: hidden;
}

.login-brand::before {
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
.login-form-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  background: #f8f9fa;
}

.form-card {
  width: 100%;
  max-width: 420px;
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

.login-footer {
  margin-top: 30px;
  text-align: center;
  border-top: 1px solid #edf2f7;
  padding-top: 20px;
}

.login-footer p {
  color: #666;
}

.login-footer a {
  color: #42b983;
  font-weight: 700;
  text-decoration: none;
  margin-left: 5px;
}

.login-footer a:hover {
  text-decoration: underline;
}

/* Responsive - Móvil */
@media (max-width: 900px) {
  .login-container {
    flex-direction: column;
  }
  
  .login-brand {
    padding: 30px 20px;
    flex: 0 0 auto;
    min-height: 200px;
  }
  
  .brand-content h1 {
    font-size: 2.2em;
  }
  
  .brand-content p {
    font-size: 1.1em;
  }
  
  .login-form-wrapper {
    flex: 1;
    padding: 20px;
    align-items: flex-start;
  }
  
  .form-card {
    box-shadow: none;
    padding: 20px 0;
    background: transparent;
  }
}
</style>