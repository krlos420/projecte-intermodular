<template>
  <div class="house-container">
    <div class="content-wrapper">
      <div class="header-section">
        <h1>¡Bienvenido a Comp-Together! 👋</h1>
        <p>Para comenzar, necesitas formar parte de una casa.</p>
      </div>

      <div class="cards-grid">
        <!-- Tarjeta Crear Casa -->
        <div class="action-card create-card">
          <div class="card-icon">🏠</div>
          <h2>Crear una nueva casa</h2>
          <p>Sé el administrador y crea un espacio para ti y tus compañeros.</p>
          
          <div class="card-form">
            <input 
              v-model="nombreCasa" 
              type="text" 
              placeholder="Nombre de tu casa (ej. Casa playa)" 
              class="input-field"
            />
            <button 
              @click="crearCasa" 
              class="btn-action btn-create"
              :disabled="loading"
            >
              {{ loading ? 'Creando...' : 'Crear Casa' }}
            </button>
          </div>
        </div>

        <!-- Divisor (visible en desktop) -->
        <div class="divider">
          <span>O</span>
        </div>

        <!-- Tarjeta Unirse a Casa -->
        <div class="action-card join-card">
          <div class="card-icon">🔑</div>
          <h2>Unirme a una casa</h2>
          <p>Si ya tienes un código de invitación, ingrésalo aquí.</p>
          
          <div class="card-form">
            <input 
              v-model="codigoInvitacion" 
              type="text" 
              placeholder="Código de invitación" 
              class="input-field"
            />
            <button 
              @click="unirCasa" 
              class="btn-action btn-join"
              :disabled="loading"
            >
              {{ loading ? 'Uniéndome...' : 'Unirme a Casa' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Mensajes de Feedback -->
      <transition name="fade">
        <div v-if="mensaje" :class="['feedback-message', tipoMensaje]">
          <span class="feedback-icon">{{ tipoMensaje === 'exito' ? '✅' : '⚠️' }}</span>
          {{ mensaje }}
        </div>
      </transition>
      
      <div class="logout-section">
        <button @click="cerrarSesion" class="btn-text">Cerrar Sesión</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useHouseStore } from '../stores/HouseStore'
import { useUserStore } from '../stores/UserStore'

export default {
  setup() {
    const router = useRouter()
    const houseStore = useHouseStore()
    const userStore = useUserStore()
    
    const nombreCasa = ref('')
    const codigoInvitacion = ref('')
    const mensaje = ref('')
    const tipoMensaje = ref('')
    const loading = ref(false)

    const crearCasa = async () => {
      if (!nombreCasa.value.trim()) {
        mostrarMensaje('Escribe un nombre para la casa', 'error')
        return
      }
      
      loading.value = true
      mensaje.value = ''
      
      try {
        const house = await houseStore.createHouse(nombreCasa.value)
        if (house) {
          mostrarMensaje(`Casa creada! Código: ${house.invite_code}`, 'exito')
          setTimeout(() => router.push('/dashboard'), 2000)
        }
      } catch (err) {
        mostrarMensaje(houseStore.error || 'Error al crear la casa', 'error')
      } finally {
        loading.value = false
      }
    }

    const unirCasa = async () => {
      if (!codigoInvitacion.value.trim()) {
        mostrarMensaje('Ingresa un código válido', 'error')
        return
      }

      loading.value = true
      mensaje.value = ''

      try {
        const success = await houseStore.joinHouse(codigoInvitacion.value)
        if (success) {
          mostrarMensaje('¡Te has unido correctamente!', 'exito')
          setTimeout(() => router.push('/dashboard'), 1500)
        }
      } catch (err) {
        mostrarMensaje(houseStore.error || 'Código incorrecto o error al unirse', 'error')
      } finally {
        loading.value = false
      }
    }

    const mostrarMensaje = (texto, tipo) => {
      mensaje.value = texto
      tipoMensaje.value = tipo
      // Auto-ocultar error después de 5s, éxito no (porque redirige)
      if (tipo === 'error') {
        setTimeout(() => {
          mensaje.value = ''
        }, 5000)
      }
    }
    
    const cerrarSesion = async () => {
      await userStore.logout()
      router.push('/login')
    }

    return { 
      nombreCasa, 
      codigoInvitacion, 
      mensaje, 
      tipoMensaje, 
      loading,
      crearCasa, 
      unirCasa,
      cerrarSesion
    }
  }
}
</script>

<style scoped>
.house-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.content-wrapper {
  width: 100%;
  max-width: 900px;
}

.header-section {
  text-align: center;
  margin-bottom: 50px;
}

.header-section h1 {
  font-size: 2.5em;
  color: #2d3748;
  margin-bottom: 10px;
}

.header-section p {
  font-size: 1.2em;
  color: #718096;
}

/* Grid de Tarjetas */
.cards-grid {
  display: flex;
  align-items: stretch;
  justify-content: center;
  gap: 30px;
  position: relative;
}

.action-card {
  flex: 1;
  background: white;
  padding: 40px 30px;
  border-radius: 20px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.action-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.card-icon {
  font-size: 3.5em;
  margin-bottom: 20px;
  background: #f7fafc;
  width: 80px;
  height: 80px;
  line-height: 80px;
  border-radius: 50%;
}

.action-card h2 {
  font-size: 1.5em;
  color: #2d3748;
  margin-bottom: 10px;
}

.action-card p {
  color: #718096;
  margin-bottom: 30px;
  min-height: 48px; /* Para alinear alturas */
}

.card-form {
  width: 100%;
  margin-top: auto; /* Empuja el formulario al fondo */
}

.input-field {
  width: 100%;
  padding: 14px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1em;
  margin-bottom: 15px;
  transition: border-color 0.2s;
  box-sizing: border-box;
  text-align: center;
}

.input-field:focus {
  outline: none;
  border-color: #42b983;
}

.btn-action {
  width: 100%;
  padding: 14px;
  border: none;
  border-radius: 10px;
  font-size: 1.1em;
  font-weight: 600;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(66, 185, 131, 0.4);
}

button:active {
  transform: translateY(0);
}

.contenedor-casa > p {
  text-align: center;
  padding: 15px;
  border-radius: 8px;
  font-size: 1em;
  margin: 20px auto;
  max-width: 500px;
}

.exito {
  color: #4caf50;
  background: #e8f5e9;
  border: 1px solid #4caf50;
}

.error {
  color: #f44336;
  background: #ffebee;
  border: 1px solid #f44336;
}

/* Layout de dos columnas en pantallas grandes */
@media (min-width: 769px) {
  .contenedor-casa {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0;
  }
  
  .contenedor-casa h1 {
    grid-column: 1 / -1;
  }
  
  .contenedor-casa > p {
    grid-column: 1 / -1;
  }
}

/* Responsive para tablets */
@media (max-width: 768px) {
  .contenedor-casa {
    margin: 40px 20px;
    padding: 20px;
  }
  
  .contenedor-casa h1 {
    font-size: 1.8em;
    margin-bottom: 35px;
  }
  
  .seccion {
    padding: 25px;
  }
  
  .seccion h2 {
    font-size: 1.3em;
  }
}

/* Responsive para móviles */
@media (max-width: 480px) {
  .contenedor-casa {
    margin: 30px 15px;
    padding: 15px;
  }
  
  .contenedor-casa h1 {
    font-size: 1.6em;
    margin-bottom: 30px;
  }
  
  .seccion {
    padding: 20px;
    margin-bottom: 20px;
  }
  
  .seccion h2 {
    font-size: 1.2em;
    margin-bottom: 20px;
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
