<template>
  <transition-group name="notificacion" tag="div" class="contenedor-notificaciones">
    <div 
      v-for="notif in notificaciones" 
      :key="notif.id"
      :class="['notificacion', `notificacion-${notif.tipo}`]"
    >
      <div class="icono">{{ obtenerIcono(notif.tipo) }}</div>
      <div class="mensaje">{{ notif.mensaje }}</div>
      <button @click="cerrarNotificacion(notif.id)" class="btn-cerrar">✕</button>
    </div>
  </transition-group>
</template>

<script>
import { ref } from 'vue'

export default {
  name: 'Notificaciones',
  setup() {
    const notificaciones = ref([])

    const mostrar = (mensaje, tipo = 'info', duracion = 3000) => {
      const id = Date.now()
      notificaciones.value.push({ id, mensaje, tipo })

      if (duracion > 0) {
        setTimeout(() => {
          cerrarNotificacion(id)
        }, duracion)
      }
    }

    const cerrarNotificacion = (id) => {
      const index = notificaciones.value.findIndex(n => n.id === id)
      if (index !== -1) {
        notificaciones.value.splice(index, 1)
      }
    }

    const obtenerIcono = (tipo) => {
      const iconos = {
        exito: '✓',
        error: '✕',
        advertencia: '⚠',
        info: 'ℹ'
      }
      return iconos[tipo] || iconos.info
    }

    // Exponer método globalmente
    window.mostrarNotificacion = mostrar

    return {
      notificaciones,
      cerrarNotificacion,
      obtenerIcono
    }
  }
}
</script>

<style scoped>
.contenedor-notificaciones {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 400px;
}

.notificacion {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 15px 20px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  background: white;
  min-width: 300px;
}

.notificacion-exito {
  border-left: 4px solid #4caf50;
}

.notificacion-error {
  border-left: 4px solid #f44336;
}

.notificacion-advertencia {
  border-left: 4px solid #ff9800;
}

.notificacion-info {
  border-left: 4px solid #2196f3;
}

.icono {
  font-size: 1.5em;
  font-weight: bold;
  min-width: 24px;
  text-align: center;
}

.notificacion-exito .icono {
  color: #4caf50;
}

.notificacion-error .icono {
  color: #f44336;
}

.notificacion-advertencia .icono {
  color: #ff9800;
}

.notificacion-info .icono {
  color: #2196f3;
}

.mensaje {
  flex: 1;
  font-size: 0.95em;
  color: #333;
}

.btn-cerrar {
  background: transparent;
  border: none;
  font-size: 1.2em;
  cursor: pointer;
  color: #999;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-cerrar:hover {
  color: #333;
}

/* Animaciones */
.notificacion-enter-active,
.notificacion-leave-active {
  transition: all 0.3s ease;
}

.notificacion-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notificacion-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

@media (max-width: 600px) {
  .contenedor-notificaciones {
    left: 10px;
    right: 10px;
    max-width: none;
  }
  
  .notificacion {
    min-width: auto;
  }
}
</style>
