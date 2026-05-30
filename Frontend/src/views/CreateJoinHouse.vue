<template>
  <div class="house-container">
    <!-- Botón de Logout posicionado arriba a la derecha -->
    <button @click="cerrarSesion" class="btn-logout-floating" title="Cerrar Sesión">
      <span class="icon">🚪</span> Salir
    </button>

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
              v-model="formCasa.name" 
              type="text" 
              placeholder="Nombre de tu casa (ej. Casa playa)" 
              class="input-field"
            />
            <input 
              v-model="formCasa.max_capacity" 
              type="number" 
              placeholder="Aforo máximo (ej. 4)" 
              class="input-field" min="1"
            />
            <input 
              v-model="formCasa.total_rent" 
              type="number" 
              placeholder="Alquiler total € (ej. 800)" 
              class="input-field" min="0" step="50"
            />
            
            <button @click="obtenerUbicacion" class="btn-location" :class="{'located': ubicacionObtenida}">
              {{ ubicacionObtenida ? '📍 Ubicación Detectada' : '📍 Detectar Ubicación' }}
            </button>

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

        <div class="divider mobile-hide">
          <span>O</span>
        </div>

        <!-- Tarjeta Mapa -->
        <div class="action-card map-card">
          <div class="card-icon">🗺️</div>
          <h2>Explorar Mapa</h2>
          <p>Busca casas con aforo disponible cerca de ti y solicita entrar.</p>
          
          <div class="card-form" style="margin-top: auto;">
            <button 
              @click="$router.push('/map')" 
              class="btn-action btn-map"
            >
              Abrir Mapa Interactivo
            </button>
          </div>
        </div>
      </div>

      <!-- Mensajes de Feedback movidos al Toast Global -->

    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useHouseStore } from '../stores/HouseStore'
import { useUserStore } from '../stores/UserStore'

export default {
  setup() {
    const router = useRouter()
    const houseStore = useHouseStore()
    const userStore = useUserStore()
    const formCasa = ref({
      name: '',
      max_capacity: '',
      total_rent: '',
      latitude: null,
      longitude: null
    })
    const codigoInvitacion = ref('')
    const ubicacionObtenida = ref(false)
    const mensaje = ref('')
    const tipoMensaje = ref('')
    const loading = ref(false)

    const obtenerUbicacion = () => {
      if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition((position) => {
          formCasa.value.latitude = position.coords.latitude
          formCasa.value.longitude = position.coords.longitude
          ubicacionObtenida.value = true
        }, () => {
          mostrarMensaje('No se pudo obtener la ubicación. Da permisos al navegador.', 'error')
        })
      } else {
        mostrarMensaje('Tu navegador no soporta geolocalización', 'error')
      }
    }

    const crearCasa = async () => {
      if (!formCasa.value.name.trim()) {
        mostrarMensaje('Escribe un nombre para la casa', 'error')
        return
      }

      if (!formCasa.value.latitude || !formCasa.value.longitude) {
        mostrarMensaje('Debes detectar tu ubicación antes de crear la casa', 'error')
        return
      }
      
      loading.value = true
      mensaje.value = ''
      
      try {
        const payload = {
          name: formCasa.value.name,
          max_capacity: formCasa.value.max_capacity ? parseInt(formCasa.value.max_capacity) : 4,
          total_rent: formCasa.value.total_rent ? parseFloat(formCasa.value.total_rent) : 0,
          latitude: formCasa.value.latitude,
          longitude: formCasa.value.longitude
        }
        
        const house = await houseStore.createHouse(payload)
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
      if(window.mostrarNotificacion) {
        window.mostrarNotificacion(texto, tipo)
      }
    }
    
    const cerrarSesion = async () => {
      await userStore.logout()
      router.push('/login')
    }

    let pollingInterval = null

    onMounted(() => {
      pollingInterval = setInterval(async () => {
        await userStore.fetchUser()
        if (userStore.user?.house_id) {
          if (window.mostrarNotificacion) window.mostrarNotificacion('¡Solicitud aceptada! Redirigiendo...', 'exito')
          router.push('/dashboard')
        }
      }, 5000)
    })

    onUnmounted(() => {
      if (pollingInterval) clearInterval(pollingInterval)
    })

    return { 
      formCasa, 
      codigoInvitacion, 
      ubicacionObtenida,
      mensaje, 
      tipoMensaje, 
      loading,
      obtenerUbicacion,
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
  background: #42b983;
  color: white;
  box-shadow: 0 6px 16px rgba(66, 185, 131, 0.4);
}

.btn-location {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 2px dashed #a0aec0;
  background: transparent;
  color: #4a5568;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-location:hover {
  border-color: #42b983;
  color: #42b983;
  background: #f0fff4;
}

.btn-location.located {
  border-style: solid;
  border-color: #42b983;
  background: #e6fffa;
  color: #234e52;
}

button:active {
  transform: translateY(0);
}

.btn-logout-floating {
  position: absolute;
  top: 20px;
  right: 20px;
  background: white;
  border: 1px solid #e2e8f0;
  padding: 10px 18px;
  border-radius: 12px;
  cursor: pointer;
  color: #e53e3e;
  font-size: 1em;
  font-weight: 600;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.btn-logout-floating:hover {
  background: #fff5f5;
  border-color: #feb2b2;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(229, 62, 62, 0.15);
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
