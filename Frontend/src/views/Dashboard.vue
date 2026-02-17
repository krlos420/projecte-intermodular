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
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useUserStore } from '../stores/UserStore'
import { useHouseStore } from '../stores/HouseStore'
import { useExpenseStore } from '../stores/ExpenseStore'
import Spinner from '../components/Spinner.vue'

export default {
  components: {
    Spinner
  },
  setup() {
    const router = useRouter()
    const userStore = useUserStore()
    const houseStore = useHouseStore()
    const expenseStore = useExpenseStore()
    
    // Usamos storeToRefs para mantener reactividad de las propiedades del store
    const { house } = storeToRefs(houseStore)
    const { user } = storeToRefs(userStore)
    const { expenses } = storeToRefs(expenseStore) // Renombramos si es necesario, pero expenses está bien

    // Estado local solo para UI
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

    // Computed properties
    const nombreCasa = computed(() => house.value?.name || '')
    const infoCasa = computed(() => house.value)
    // Mapeamos gastos para que la vista lo use igual que antes
    const gastos = computed(() => expenses.value)

    const cargarDatos = async () => {
      cargando.value = true
      try {
        // 1. Cargar Usuario si no está
        if (!user.value) await userStore.fetchUser()

        // 2. Cargar Casa
        await houseStore.fetchMyHouse()
        
        if (!house.value) {
          router.push('/create-join-house')
          return
        }

        // 3. Cargar Gastos (Ahora desde el store)
        await expenseStore.fetchExpenses()

      } catch (err) {
        console.error('Error cargando dashboard:', err)
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

    // Funciones de Gastos (Delegando al Store)
    const guardarGasto = async () => {
      try {
        if (!gastoActual.value.title || !gastoActual.value.amount || gastoActual.value.amount <= 0) {
          window.mostrarNotificacion('Por favor, completa todos los campos correctamente', 'advertencia')
          return
        }

        let success = false
        if (modoEdicion.value) {
          success = await expenseStore.updateExpense(gastoActual.value.id, {
            title: gastoActual.value.title,
            amount: gastoActual.value.amount,
            date: gastoActual.value.date
          })
          if (success) window.mostrarNotificacion('Gasto actualizado correctamente', 'exito')
        } else {
          success = await expenseStore.createExpense({
            title: gastoActual.value.title,
            amount: gastoActual.value.amount,
            date: gastoActual.value.date
          })
          if (success) window.mostrarNotificacion('Gasto añadido correctamente', 'exito')
        }
        
        if (success) cerrarModal()
        
      } catch (err) {
        window.mostrarNotificacion('Error al guardar el gasto: ' + (expenseStore.error || err.message), 'error')
      }
    }

    const eliminarGasto = async (id) => {
      if (!confirm('¿Estás seguro de eliminar este gasto?')) {
        return
      }

      try {
        const success = await expenseStore.deleteExpense(id)
        if (success) {
          window.mostrarNotificacion('Gasto eliminado correctamente', 'exito')
        }
      } catch (err) {
        window.mostrarNotificacion('Error al eliminar el gasto: ' + (expenseStore.error || err.message), 'error')
      }
    }

    // Funciones de Casa
    const copiarCodigo = () => {
      if (house.value?.invite_code) {
        navigator.clipboard.writeText(house.value.invite_code)
        window.mostrarNotificacion('Código copiado: ' + house.value.invite_code, 'exito')
      }
    }

    const salirDeCasa = async () => {
      if (!confirm('¿Estás seguro de salir de esta casa?')) {
        return
      }

      try {
        await houseStore.leaveHouse()
        window.mostrarNotificacion('Has salido de la casa correctamente', 'exito')
        router.push('/create-join-house')
      } catch (err) {
        window.mostrarNotificacion('Error al salir de la casa: ' + (houseStore.error || 'Error desconocido'), 'error')
      }
    }

    const irEstadisticas = () => {
      router.push('/estadisticas')
    }

    const cerrarSesion = async () => {
      await userStore.logout()
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
  max-width: 1000px;
  margin: 0 auto;
  padding: 30px 20px;
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
}

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 35px;
  padding: 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

header h1 {
  margin: 0;
  background: linear-gradient(135deg, #42b983, #35a372);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.8em;
}

.btn-salir {
  padding: 10px 20px;
  background: linear-gradient(135deg, #ff5252, #f44336);
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 8px;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-salir:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
}

/* Sección Info Casa */
.seccion-info-casa {
  background: white;
  border: none;
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 25px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: box-shadow 0.3s;
}

.seccion-info-casa:hover {
  box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}

.info-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  user-select: none;
}

.info-header h2 {
  margin: 0;
  font-size: 1.3em;
  color: #333;
}

.btn-toggle {
  background: #f5f5f5;
  border: none;
  font-size: 1.2em;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 8px;
  transition: background 0.2s;
}

.btn-toggle:hover {
  background: #e0e0e0;
}

.info-contenido {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 2px solid #f0f0f0;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.info-item {
  margin: 18px 0;
}

.info-item strong {
  display: block;
  margin-bottom: 10px;
  color: #555;
  font-size: 0.95em;
}

.codigo-invitacion {
  background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
  padding: 12px 16px;
  border: 2px dashed #42b983;
  border-radius: 8px;
  font-family: 'Courier New', monospace;
  font-size: 1.2em;
  font-weight: bold;
  color: #2e7d5a;
  margin-right: 10px;
  display: inline-block;
}

.btn-copiar {
  padding: 10px 18px;
  background: linear-gradient(135deg, #42b983, #35a372);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-copiar:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(66, 185, 131, 0.3);
}

.lista-companeros {
  list-style: none;
  padding: 0;
  margin: 12px 0;
}

.lista-companeros li {
  padding: 14px;
  background: linear-gradient(135deg, #fafafa, #f5f5f5);
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  margin: 8px 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: transform 0.2s, box-shadow 0.2s;
}

.lista-companeros li:hover {
  transform: translateX(5px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.badge-creador {
  background: linear-gradient(135deg, #ffd54f, #ffc107);
  color: #000;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.85em;
  font-weight: bold;
  box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
}

.btn-salir-casa {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #ff5722, #e64a19);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  margin-top: 15px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-salir-casa:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 87, 34, 0.4);
}

/* Sección Gastos */
.seccion-gastos {
  background: white;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.header-gastos {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  flex-wrap: wrap;
  gap: 15px;
}

.header-gastos h2 {
  margin: 0;
  font-size: 1.5em;
  color: #333;
}

.botones-header {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.btn-agregar {
  padding: 12px 24px;
  background: linear-gradient(135deg, #42b983, #35a372);
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 8px;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-agregar:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(66, 185, 131, 0.3);
}

.btn-estadisticas {
  padding: 12px 24px;
  background: linear-gradient(135deg, #ff9800, #f57c00);
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 8px;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-estadisticas:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
}

/* Lista de Gastos */
.lista-gastos {
  margin-top: 20px;
}

.item-gasto {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px;
  border: none;
  margin: 12px 0;
  border-radius: 12px;
  background: linear-gradient(135deg, #fafafa, #f5f5f5);
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  transition: transform 0.2s, box-shadow 0.2s;
}

.item-gasto:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.info-gasto {
  flex: 1;
}

.info-gasto strong {
  display: block;
  margin-bottom: 6px;
  font-size: 1.15em;
  color: #333;
}

.info-gasto p {
  margin: 0;
  color: #777;
  font-size: 0.9em;
}

.acciones-gasto {
  display: flex;
  align-items: center;
  gap: 12px;
}

.cantidad {
  font-size: 1.4em;
  font-weight: bold;
  background: linear-gradient(135deg, #42b983, #35a372);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-right: 12px;
}

.btn-editar,
.btn-eliminar {
  padding: 10px 14px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1.2em;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-editar {
  background: linear-gradient(135deg, #ffc107, #ffb300);
  color: #000;
}

.btn-editar:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
}

.btn-eliminar {
  background: linear-gradient(135deg, #f44336, #d32f2f);
  color: white;
}

.btn-eliminar:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
}

/* Modal */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.contenido-modal {
  background: white;
  padding: 35px;
  border-radius: 16px;
  width: 450px;
  max-width: 90%;
  box-shadow: 0 12px 40px rgba(0,0,0,0.3);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.contenido-modal h3 {
  margin-top: 0;
  margin-bottom: 25px;
  font-size: 1.6em;
  color: #333;
}

.contenido-modal input {
  width: 100%;
  padding: 14px 16px;
  margin: 10px 0;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  box-sizing: border-box;
  font-size: 1em;
  transition: all 0.3s ease;
}

.contenido-modal input:focus {
  outline: none;
  border-color: #42b983;
  box-shadow: 0 0 0 3px rgba(66, 185, 131, 0.1);
}

.acciones {
  display: flex;
  gap: 12px;
  margin-top: 25px;
}

.btn-guardar {
  flex: 1;
  padding: 14px;
  background: linear-gradient(135deg, #42b983, #35a372);
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 8px;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-guardar:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(66, 185, 131, 0.3);
}

.btn-cancelar {
  flex: 1;
  padding: 14px;
  background: #e0e0e0;
  border: none;
  cursor: pointer;
  border-radius: 8px;
  font-weight: 600;
  transition: transform 0.2s, background 0.2s;
}

.btn-cancelar:hover {
  background: #bdbdbd;
  transform: translateY(-2px);
}

.vacio {
  text-align: center;
  color: #999;
  padding: 60px 40px;
  background: linear-gradient(135deg, #fafafa, #f5f5f5);
  border-radius: 12px;
  font-size: 1.1em;
}

/* Responsive para tablets */
@media (max-width: 768px) {
  .contenedor-principal {
    padding: 20px 15px;
  }
  
  header {
    padding: 15px;
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }
  
  header h1 {
    font-size: 1.5em;
  }
  
  .header-gastos {
    flex-direction: column;
    align-items: stretch;
  }
  
  .botones-header {
    width: 100%;
    flex-direction: column;
  }
  
  .btn-agregar,
  .btn-estadisticas {
    width: 100%;
  }
  
  .item-gasto {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }
  
  .acciones-gasto {
    width: 100%;
    justify-content: space-between;
  }
}

/* Responsive para móviles */
@media (max-width: 480px) {
  .contenedor-principal {
    padding: 15px 10px;
  }
  
  header h1 {
    font-size: 1.3em;
  }
  
  .seccion-info-casa,
  .seccion-gastos {
    padding: 18px;
  }
  
  .codigo-invitacion {
    font-size: 1em;
    padding: 10px 12px;
  }
  
  .btn-copiar {
    width: 100%;
    margin-top: 10px;
  }
  
  .info-item {
    display: flex;
    flex-direction: column;
  }
  
  .contenido-modal {
    padding: 25px 20px;
  }
  
  .contenido-modal h3 {
    font-size: 1.3em;
  }
  
  .acciones {
    flex-direction: column;
  }
  
  .btn-guardar,
  .btn-cancelar {
    width: 100%;
  }
}
</style>