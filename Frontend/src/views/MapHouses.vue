<template>
  <div class="map-layout">
    <nav class="navbar">
      <div class="nav-content">
        <h1 class="logo">Comp-Together</h1>
        <button @click="$router.push('/create-join-house')" class="btn-logout">Volver</button>
      </div>
    </nav>

    <main class="map-content">
      <div class="header-map">
        <h2>🗺️ Mapa de Casas Disponibles</h2>
        <p>Encuentra casas con aforo libre y solicita unirte.</p>
      </div>

      <div class="map-wrapper">
        <div id="leaflet-map"></div>
      </div>
    </main>
  </div>
</template>

<script>
import { onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/UserStore'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import api from '../services/api'
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

// Corregir icono por defecto de Leaflet en Vue
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
})

export default {
  name: 'MapHouses',
  setup() {
    const router = useRouter()
    const map = ref(null)
    const houses = ref([])

    const loadMap = async () => {
      // Intentar obtener geolocalización, sino por defecto Madrid
      let lat = 40.4168
      let lng = -3.7038

      map.value = L.map('leaflet-map').setView([lat, lng], 13)

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(map.value)

      // Obtener casas de la API
      try {
        const response = await api.get('/houses/available')
        if (response.data.status === 'true') {
          houses.value = response.data.houses
          
          houses.value.forEach(house => {
            if (house.latitude && house.longitude) {
              const currentUsers = house.users_count || 0
              const maxCap = house.max_capacity || 4
              const rent = parseFloat(house.total_rent || 0)
              // users_count ya excluye al admin, +1 para incluir al nuevo inquilino
              const estimatedPerPerson = (currentUsers + 1) > 0 ? rent / (currentUsers + 1) : 0

              const popupContent = `
                <div style="text-align: center;">
                  <h3 style="margin-bottom: 5px; color: #2d3748;">${house.name}</h3>
                  <p style="margin: 0; color: #718096;">Ocupación: ${currentUsers} / ${maxCap}</p>
                  <p style="margin: 5px 0; font-weight: bold; color: #42b983;">Estimado: ${estimatedPerPerson.toFixed(2)}€/persona</p>
                  <button onclick="window.requestJoin(${house.id})" style="background: #42b983; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; margin-top: 5px;">Solicitar Unirse</button>
                </div>
              `
              L.marker([house.latitude, house.longitude]).addTo(map.value).bindPopup(popupContent)
            }
          })
        }
      } catch (e) {
        console.error('Error fetching houses', e)
      }

      // Geolocalizar si se puede
      if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(position => {
          map.value.setView([position.coords.latitude, position.coords.longitude], 13)
        })
      }
    }

    // Exponer la función al contexto global para que el onClick del popup la pueda llamar
    window.requestJoin = async (houseId) => {
      try {
        const res = await api.post('/houses/request-join', { house_id: houseId })
        if (res.data.status === 'true') {
          if(window.mostrarNotificacion) window.mostrarNotificacion('Solicitud enviada correctamente', 'exito')
        }
      } catch (err) {
        const msg = err.response?.data?.message || 'Error al solicitar unirse'
        if(window.mostrarNotificacion) window.mostrarNotificacion(msg, 'error')
      }
    }

    let pollingInterval = null

    onMounted(() => {
      loadMap()
      pollingInterval = setInterval(async () => {
        const userStore = useUserStore()
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

    return {}
  }
}
</script>

<style scoped>
.map-layout {
  min-height: 100vh;
  background-color: #f3f4f6;
}

.navbar {
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  padding: 0 20px;
}

.nav-content {
  max-width: 1200px;
  margin: 0 auto;
  height: 64px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-size: 1.5rem;
  font-weight: 800;
  background: linear-gradient(135deg, #42b983, #2e7d5a);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin: 0;
}

.btn-logout {
  background: none;
  border: 1px solid #e2e8f0;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  color: #4a5568;
}

.map-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px;
}

.header-map {
  text-align: center;
  margin-bottom: 20px;
}

.header-map h2 {
  color: #2d3748;
}

.map-wrapper {
  background: white;
  padding: 10px;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

#leaflet-map {
  width: 100%;
  height: 65vh;
  border-radius: 12px;
  z-index: 1; /* Para no sobreponer a los menús */
}
</style>
