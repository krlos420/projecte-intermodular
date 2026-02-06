<template>
  <div class="contenedor-casa">
    <h1>Gestionar Casa</h1>
    
    <div class="seccion">
      <h2>Crear Nueva Casa</h2>
      <input v-model="nombreCasa" type="text" placeholder="Nombre de la casa" />
      <button @click="crearCasa">Crear Casa</button>
    </div>

    <div class="seccion">
      <h2>Unirse a Casa</h2>
      <input v-model="codigoInvitacion" type="text" placeholder="Código de invitación" />
      <button @click="unirCasa">Unirse</button>
    </div>

    <p v-if="mensaje" :class="tipoMensaje">{{ mensaje }}</p>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

export default {
  setup() {
    const router = useRouter()
    const nombreCasa = ref('')
    const codigoInvitacion = ref('')
    const mensaje = ref('')
    const tipoMensaje = ref('')

    const crearCasa = async () => {
      try {
        const response = await api.post('/houses/create', {
          name: nombreCasa.value
        })
        if (response.data.status === 'true') {
          mensaje.value = `Casa creada. Código: ${response.data.house.invite_code}`
          tipoMensaje.value = 'exito'
          setTimeout(() => router.push('/dashboard'), 2000)
        }
      } catch (err) {
        mensaje.value = err.response?.data?.message || 'Error al crear casa'
        tipoMensaje.value = 'error'
      }
    }

    const unirCasa = async () => {
      try {
        const response = await api.post('/houses/join', {
          invite_code: codigoInvitacion.value
        })
        if (response.data.status === 'true') {
          mensaje.value = 'Te has unido a la casa correctamente'
          tipoMensaje.value = 'exito'
          setTimeout(() => router.push('/dashboard'), 1500)
        }
      } catch (err) {
        mensaje.value = err.response?.data?.message || 'Código inválido'
        tipoMensaje.value = 'error'
      }
    }

    return { nombreCasa, codigoInvitacion, mensaje, tipoMensaje, crearCasa, unirCasa }
  }
}
</script>

<style scoped>
.contenedor-casa {
  max-width: 900px;
  margin: 60px auto;
  padding: 40px 20px;
}

.contenedor-casa h1 {
  text-align: center;
  margin-bottom: 50px;
  background: linear-gradient(135deg, #42b983, #35a372);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 2.2em;
}

.seccion {
  margin: 0 0 30px 0;
  padding: 35px;
  background: white;
  border: none;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
  transition: transform 0.3s, box-shadow 0.3s;
}

.seccion:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.15);
}

.seccion h2 {
  margin: 0 0 25px 0;
  font-size: 1.5em;
  color: #333;
  display: flex;
  align-items: center;
  gap: 10px;
}

.seccion h2::before {
  content: '🏠';
  font-size: 1.2em;
}

.seccion:last-of-type h2::before {
  content: '🔑';
}

input {
  width: 100%;
  padding: 14px 16px;
  margin: 0 0 15px 0;
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
}

button:hover {
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
