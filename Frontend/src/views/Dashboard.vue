<template>
  <div class="contenedor-principal">
    <header>
      <h1>{{ nombreCasa || 'Mi Casa' }}</h1>
      <button @click="cerrarSesion" class="btn-salir">Cerrar Sesión</button>
    </header>

    <!-- Información de la casa -->
    <div v-if="infoCasa" class="seccion-info-casa">
      <div class="info-header">
        <h2>🏠 Información de la Casa</h2>
        <button @click="mostrarInfoCasa = !mostrarInfoCasa" class="btn-toggle">
          {{ mostrarInfoCasa ? '▼' : '▶' }}
        </button>
      </div>
      
      <div v-if="mostrarInfoCasa" class="info-contenido">
        <div class="info-item">
          <strong>Código de invitación:</strong>
          <span class="codigo-invitacion">{{ infoCasa.invite_code }}</span>
          <button @click="copiarCodigo" class="btn-copiar">📋 Copiar</button>
        </div>
        
        <div class="info-item">
          <strong>Compañeros de piso:</strong>
          <ul class="lista-companeros">
            <li v-for="companero in infoCasa.users" :key="companero.id_user">
              {{ companero.name }}
              <span v-if="companero.id_user === infoCasa.creator_id" class="badge-creador">👑 Creador</span>
            </li>
          </ul>
        </div>

        <button @click="salirDeCasa" class="btn-salir-casa">🚪 Salir de la casa</button>
      </div>
    </div>

    <div class="seccion-gastos">
      <div class="header-gastos">
        <h2>Gastos Recientes</h2>
        <div class="botones-header">
          <button @click="irEstadisticas" class="btn-estadisticas">📊 Ver Estadísticas</button>
          <button @click="abrirModalNuevo" class="btn-agregar">+ Añadir Gasto</button>
        </div>
      </div>
      
      <Spinner v-if="cargando" mensaje="Cargando gastos..." />
      
      <div v-else-if="gastos.length === 0" class="vacio">
        No hay gastos todavía
      </div>

      <div v-else class="lista-gastos">
        <div v-for="gasto in gastos" :key="gasto.id" class="item-gasto">
          <div class="info-gasto">
            <strong>{{ gasto.title }}</strong>
            <p>{{ gasto.date }} - {{ gasto.payer?.name }}</p>
          </div>
          <div class="acciones-gasto">
            <div class="cantidad">{{ gasto.amount }}€</div>
            <button @click="abrirModalEditar(gasto)" class="btn-editar">✏️</button>
            <button @click="eliminarGasto(gasto.id)" class="btn-eliminar">🗑️</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal añadir/editar gasto -->
    <div v-if="mostrarFormulario" class="modal" @click.self="cerrarModal">
      <div class="contenido-modal">
        <h3>{{ modoEdicion ? 'Editar Gasto' : 'Añadir Gasto' }}</h3>
        <input v-model="gastoActual.title" placeholder="Título" />
        <input v-model="gastoActual.amount" type="number" step="0.01" placeholder="Cantidad (€)" />
        <input v-model="gastoActual.date" type="date" />
        <div class="acciones">
          <button @click="guardarGasto" class="btn-guardar">
            {{ modoEdicion ? 'Actualizar' : 'Guardar' }}
          </button>
          <button @click="cerrarModal" class="btn-cancelar">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import Spinner from '../components/Spinner.vue'

export default {
  components: {
    Spinner
  },
  setup() {
    const router = useRouter()
    const gastos = ref([])
    const nombreCasa = ref('')
    const infoCasa = ref(null)
    const mostrarInfoCasa = ref(false)
    const mostrarFormulario = ref(false)
    const modoEdicion = ref(false)
    const cargando = ref(true)
    const gastoActual = ref({
      id: null,
      title: '',
      amount: '',
      date: new Date().toISOString().split('T')[0]
    })

    const cargarDatos = async () => {
      try {
        const casaRes = await api.get('/houses/my-house')
        if (casaRes.data.status === 'true') {
          infoCasa.value = casaRes.data.house
          nombreCasa.value = casaRes.data.house.name
        }

        const gastosRes = await api.get('/expenses')
        if (gastosRes.data.status === 'true') {
          gastos.value = gastosRes.data.expenses
        }
      } catch (err) {
        if (err.response?.status === 404) {
          router.push('/create-join-house')
        }
      } finally {
        cargando.value = false
      }
    }

    const abrirModalNuevo = () => {
      modoEdicion.value = false
      gastoActual.value = {
        id: null,
        title: '',
        amount: '',
        date: new Date().toISOString().split('T')[0]
      }
      mostrarFormulario.value = true
    }

    const abrirModalEditar = (gasto) => {
      modoEdicion.value = true
      gastoActual.value = {
        id: gasto.id,
        title: gasto.title,
        amount: gasto.amount,
        date: gasto.date
      }
      mostrarFormulario.value = true
    }

    const cerrarModal = () => {
      mostrarFormulario.value = false
      modoEdicion.value = false
      gastoActual.value = {
        id: null,
        title: '',
        amount: '',
        date: new Date().toISOString().split('T')[0]
      }
    }

    const guardarGasto = async () => {
      try {
        // Validación básica
        if (!gastoActual.value.title || !gastoActual.value.amount || gastoActual.value.amount <= 0) {
          window.mostrarNotificacion('Por favor, completa todos los campos correctamente', 'advertencia')
          return
        }

        if (modoEdicion.value) {
          // Actualizar gasto existente
          const response = await api.put(`/expenses/update/${gastoActual.value.id}`, {
            title: gastoActual.value.title,
            amount: gastoActual.value.amount,
            date: gastoActual.value.date
          })
          
          if (response.data.status === 'true') {
            // Actualizar en la lista
            const index = gastos.value.findIndex(g => g.id === gastoActual.value.id)
            if (index !== -1) {
              gastos.value[index] = { ...gastos.value[index], ...response.data.expense }
            }
            window.mostrarNotificacion('Gasto actualizado correctamente', 'exito')
            cerrarModal()
          }
        } else {
          // Crear nuevo gasto
          const response = await api.post('/expenses/store', {
            title: gastoActual.value.title,
            amount: gastoActual.value.amount,
            date: gastoActual.value.date
          })
          
          if (response.data.status === 'true') {
            gastos.value.unshift(response.data.expense)
            window.mostrarNotificacion('Gasto añadido correctamente', 'exito')
            cerrarModal()
          }
        }
      } catch (err) {
        window.mostrarNotificacion('Error al guardar el gasto: ' + (err.response?.data?.message || err.message), 'error')
      }
    }

    const eliminarGasto = async (id) => {
      if (!confirm('¿Estás seguro de eliminar este gasto?')) {
        return
      }

      try {
        const response = await api.delete(`/expenses/destroy/${id}`)
        if (response.data.status === 'true') {
          gastos.value = gastos.value.filter(g => g.id !== id)
          window.mostrarNotificacion('Gasto eliminado correctamente', 'exito')
        }
      } catch (err) {
        window.mostrarNotificacion('Error al eliminar el gasto: ' + (err.response?.data?.message || err.message), 'error')
      }
    }

    const copiarCodigo = () => {
      if (infoCasa.value?.invite_code) {
        navigator.clipboard.writeText(infoCasa.value.invite_code)
        window.mostrarNotificacion('Código copiado: ' + infoCasa.value.invite_code, 'exito')
      }
    }

    const salirDeCasa = async () => {
      if (!confirm('¿Estás seguro de salir de esta casa?')) {
        return
      }

      try {
        const response = await api.post('/houses/leave')
        if (response.data.status === 'true') {
          window.mostrarNotificacion('Has salido de la casa correctamente', 'exito')
          router.push('/create-join-house')
        }
      } catch (err) {
        window.mostrarNotificacion('Error al salir de la casa: ' + (err.response?.data?.message || err.message), 'error')
      }
    }

    const irEstadisticas = () => {
      router.push('/estadisticas')
    }

    const cerrarSesion= () => {
      localStorage.removeItem('token')
      router.push('/login')
    }

    onMounted(cargarDatos)

    return {
      gastos,
      nombreCasa,
      infoCasa,
      mostrarInfoCasa,
      mostrarFormulario,
      modoEdicion,
      cargando,
      gastoActual,
      abrirModalNuevo,
      abrirModalEditar,
      cerrarModal,
      guardarGasto,
      eliminarGasto,
      copiarCodigo,
      salirDeCasa,
      irEstadisticas,
      cerrarSesion
    }
  }
}
</script>

<style scoped>
.contenedor-principal {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}
.btn-salir {
  padding: 10px 20px;
  background: #f44336;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}

.seccion-info-casa {
  background: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 20px;
}

.info-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
}

.info-header h2 {
  margin: 0;
  font-size: 1.2em;
}

.btn-toggle {
  background: transparent;
  border: none;
  font-size: 1.2em;
  cursor: pointer;
  padding: 5px 10px;
}

.info-contenido {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #ddd;
}

.info-item {
  margin: 15px 0;
}

.info-item strong {
  display: block;
  margin-bottom: 8px;
  color: #333;
}

.codigo-invitacion {
  background: #fff;
  padding: 8px 12px;
  border: 2px dashed #42b983;
  border-radius: 5px;
  font-family: monospace;
  font-size: 1.1em;
  font-weight: bold;
  color: #42b983;
  margin-right: 10px;
}

.btn-copiar {
  padding: 8px 15px;
  background: #42b983;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.btn-copiar:hover {
  background: #3aa876;
}

.lista-companeros {
  list-style: none;
  padding: 0;
  margin: 10px 0;
}

.lista-companeros li {
  padding: 10px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 5px;
  margin: 5px 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.badge-creador {
  background: #ffc107;
  color: #000;
  padding: 3px 8px;
  border-radius: 12px;
  font-size: 0.85em;
  font-weight: bold;
}

.btn-salir-casa {
  width: 100%;
  padding: 12px;
  background: #ff5722;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
  margin-top: 15px;
}

.btn-salir-casa:hover {
  background: #e64a19;
}

.btn-agregar {
  padding: 10px 20px;
  background: #42b983;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}

.btn-agregar:hover {
  background: #3aa876;
}

.header-gastos {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
}

.header-gastos h2 {
  margin: 0;
}

.botones-header {
  display: flex;
  gap: 10px;
}

.btn-estadisticas {
  padding: 10px 20px;
  background: #ff9800;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
  font-weight: bold;
}

.btn-estadisticas:hover {
  background: #f57c00;
}

.lista-gastos {
  margin-top: 20px;
}
.item-gasto {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border: 1px solid #ddd;
  margin: 10px 0;
  border-radius: 8px;
  background: white;
}
.info-gasto {
  flex: 1;
}
.info-gasto strong {
  display: block;
  margin-bottom: 5px;
  font-size: 1.1em;
}
.info-gasto p {
  margin: 0;
  color: #666;
  font-size: 0.9em;
}
.acciones-gasto {
  display: flex;
  align-items: center;
  gap: 10px;
}
.cantidad {
  font-size: 1.3em;
  font-weight: bold;
  color: #42b983;
  margin-right: 10px;
}
.btn-editar,
.btn-eliminar {
  padding: 8px 12px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1.2em;
  transition: transform 0.2s;
}
.btn-editar {
  background: #ffc107;
}
.btn-editar:hover {
  transform: scale(1.1);
  background: #ffb300;
}
.btn-eliminar {
  background: #f44336;
}
.btn-eliminar:hover {
  transform: scale(1.1);
  background: #d32f2f;
}
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.contenido-modal {
  background: white;
  padding: 30px;
  border-radius: 10px;
  width: 400px;
  max-width: 90%;
}
.contenido-modal h3 {
  margin-top: 0;
  margin-bottom: 20px;
}
.contenido-modal input {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  border: 1px solid #ddd;
  border-radius: 5px;
  box-sizing: border-box;
}
.acciones {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}
.btn-guardar {
  flex: 1;
  padding: 10px;
  background: #42b983;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}
.btn-guardar:hover {
  background: #3aa876;
}
.btn-cancelar {
  flex: 1;
  padding: 10px;
  background: #ccc;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}
.btn-cancelar:hover {
  background: #bbb;
}
.vacio {
  text-align: center;
  color: #999;
  padding: 40px;
  background: #f9f9f9;
  border-radius: 8px;
}
</style>